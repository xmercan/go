<?php

namespace GO\Services;

use GO\Models\ProjectModel;
use GO\Models\ChatMessageModel;

/**
 * GO! Chat State Machine — V1 (AI-free, rule-based)
 *
 * Her state bir soru grubunu temsil eder.
 * Kullanıcının cevabı alınır, project_answers ve project tablosuna yazılır,
 * sonraki state hesaplanır ve yanıt üretilir.
 *
 * State akışı:
 *   welcome → sector → business_name → target_audience → brand_type
 *   → domain_needs → hosting_needs → social_media → budget → urgency
 *   → special_notes → summary → done
 */
class ChatStateMachine
{
    private ProjectModel     $projects;
    private ChatMessageModel $messages;

    // Tüm state'lerin sıralı listesi
    private const FLOW = [
        'welcome',
        'sector',
        'business_name',
        'target_audience',
        'brand_type',
        'domain_needs',
        'hosting_needs',
        'social_media',
        'budget',
        'urgency',
        'special_notes',
        'summary',
        'done',
    ];

    public function __construct()
    {
        $this->projects = new ProjectModel();
        $this->messages = new ChatMessageModel();
    }

    /**
     * Kullanıcıdan mesaj al, cevabı kaydet, sonraki soruyu döndür.
     *
     * @return array{message: string, state: string, is_done: bool, quick_replies: array}
     */
    public function handle(int $projectId, int $userId, string $userMessage): array
    {
        $project = $this->projects->find($projectId);
        if (!$project) {
            return $this->wrap('Proje bulunamadı.', 'error', true);
        }

        $currentState = $this->detectCurrentState($project, $projectId);

        // Kullanıcı mesajını kaydet
        if (!empty(trim($userMessage)) && $currentState !== 'welcome') {
            $this->messages->addMessage($projectId, $userId, 'user', $userMessage, $currentState);
            $this->processAnswer($project, $currentState, $userMessage);
        }

        // Sonraki state'e geç
        $nextState = $this->nextState($currentState);

        // Yanıt oluştur
        $response = $this->buildResponse($project, $nextState, $projectId);

        // Bot mesajını kaydet
        $this->messages->addMessage($projectId, null, 'system', $response['message'], $nextState);

        // State done ise projeyi tamamla
        if ($nextState === 'done') {
            $this->projects->setChatCompleted($projectId);
        }

        return $response;
    }

    /**
     * Proje için ilk hoşgeldin mesajını oluştur.
     */
    public function initProject(int $projectId, int $userId): array
    {
        $project = $this->projects->find($projectId);
        $msg     = $this->buildResponse($project ?? [], 'welcome', $projectId);
        $this->messages->addMessage($projectId, null, 'system', $msg['message'], 'welcome');
        return $msg;
    }

    // ─── State Helpers ────────────────────────────────────────────────────────

    private function detectCurrentState(array $project, int $projectId): string
    {
        // Son bot mesajının state_key'ini al
        $lastBot = $this->queryLastBotState($projectId);
        if ($lastBot && in_array($lastBot, self::FLOW, true)) {
            return $lastBot;
        }
        return 'welcome';
    }

    private function nextState(string $current): string
    {
        $idx = array_search($current, self::FLOW, true);
        if ($idx === false || $idx >= count(self::FLOW) - 1) {
            return 'done';
        }
        return self::FLOW[$idx + 1];
    }

    private function processAnswer(array $project, string $state, string $answer): void
    {
        $id      = (int)$project['id'];
        $updates = [];

        switch ($state) {
            case 'sector':
                $updates['description'] = $answer;
                break;
            case 'business_name':
                $updates['name'] = trim($answer);
                break;
            case 'target_audience':
                $updates['target_audience'] = $answer;
                break;
            case 'brand_type':
                $lower = strtolower(trim($answer));
                $updates['brand_type'] = str_contains($lower, 'mevcut') ? 'existing' : 'new';
                break;
            case 'domain_needs':
                $lower = strtolower(trim($answer));
                $updates['has_domain']  = str_contains($lower, 'var') || str_contains($lower, 'evet') ? 1 : 0;
                $updates['domain_ideas'] = json_encode([$answer], JSON_UNESCAPED_UNICODE);
                break;
            case 'hosting_needs':
                $updates['hosting_need'] = $answer;
                break;
            case 'social_media':
                $updates['social_media_needs'] = $answer;
                break;
            case 'budget':
                $updates['budget_range'] = $answer;
                break;
            case 'urgency':
                $updates['urgency'] = $answer;
                break;
            case 'special_notes':
                $updates['important_notes'] = $answer;
                break;
        }

        if (!empty($updates)) {
            $this->projects->update($id, $updates);
        }

        // project_answers'a kaydet
        $this->saveAnswer($id, $state, $answer);
    }

    private function saveAnswer(int $projectId, string $key, string $answer): void
    {
        try {
            $pdo = \GO\Core\Database::getInstance();
            $stmt = $pdo->prepare("
                INSERT INTO project_answers (project_id, question_key, answer_text, source)
                VALUES (?, ?, ?, 'chat')
                ON DUPLICATE KEY UPDATE answer_text = VALUES(answer_text)
            ");
            $stmt->execute([$projectId, $key, $answer]);
        } catch (\Throwable) {}
    }

    private function queryLastBotState(int $projectId): string
    {
        try {
            $pdo  = \GO\Core\Database::getInstance();
            $stmt = $pdo->prepare(
                "SELECT state_key FROM chat_messages
                 WHERE project_id = ? AND role = 'system' AND state_key != ''
                 ORDER BY created_at DESC LIMIT 1"
            );
            $stmt->execute([$projectId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row['state_key'] ?? '';
        } catch (\Throwable) {
            return '';
        }
    }

    // ─── Response Builder ─────────────────────────────────────────────────────

    private function buildResponse(array $project, string $state, int $projectId): array
    {
        $name = !empty($project['name']) ? ', ' . $project['name'] : '';
        $base = ['state' => $state, 'is_done' => false, 'quick_replies' => []];

        switch ($state) {
            case 'welcome':
                return array_merge($base, [
                    'message' => "👋 Merhaba{$name}! Ben GO!'nun dijital danışmanıyım.\n\nSize en uygun dijital çözümü hazırlamak için birkaç soru soracağım. Hazır mısınız?",
                    'quick_replies' => ['Evet, başlayalım!', 'Hızlıca anlat'],
                ]);

            case 'sector':
                return array_merge($base, [
                    'message' => "Harika! İlk olarak: **İşletmeniz hangi sektörde faaliyet gösteriyor?**\n\nÖrneğin: Kahve dükkanı, emlak ofisi, klinik, avukatlık bürosu gibi.",
                    'quick_replies' => ['Kahve & Kafe', 'Emlak', 'Güzellik & Kuaför', 'Avukatlık', 'Sağlık', 'Eğitim', 'Restoran', 'E-Ticaret'],
                ]);

            case 'business_name':
                return array_merge($base, [
                    'message' => "Anladım! **İşletmenizin adı nedir?**\n\nMarka isminizi veya ticaret unvanınızı yazabilirsiniz.",
                ]);

            case 'target_audience':
                return array_merge($base, [
                    'message' => "Teşekkürler! **Hedef kitlenizi tanımlayabilir misiniz?**\n\nKimlere hitap ediyorsunuz? Yaş grubu, konum, ilgi alanı gibi detaylar işinize yarayacak.",
                ]);

            case 'brand_type':
                return array_merge($base, [
                    'message' => "Peki, **markanız ile ilgili durum nedir?**",
                    'quick_replies' => ['Yeni marka oluşturuyoruz', 'Mevcut markamı dijitale taşıyacağım'],
                ]);

            case 'domain_needs':
                return array_merge($base, [
                    'message' => "Anladım! **Alan adı (domain) durumunuz nedir?**\n\nMevcut bir alan adınız var mı, yoksa yeni bir alan adı tescil ettirecek misiniz?",
                    'quick_replies' => ['Mevcut alan adım var', 'Yeni alan adı lazım', 'Bilmiyorum, siz önerin'],
                ]);

            case 'hosting_needs':
                return array_merge($base, [
                    'message' => "Anladım. **Web hosting ihtiyacınız hakkında ne düşünüyorsunuz?**\n\nHalihazırda bir hosting hesabınız var mı?",
                    'quick_replies' => ['Evet, hostingim var', 'Hayır, hosting de lazım', 'Ne olduğunu bilmiyorum'],
                ]);

            case 'social_media':
                return array_merge($base, [
                    'message' => "Tamam! **Sosyal medya ihtiyaçlarınız neler?**\n\nHangi platformlarda aktif olmak istiyorsunuz?",
                    'quick_replies' => ['Instagram', 'Instagram & Facebook', 'TikTok', 'LinkedIn', 'Hepsi', 'Sosyal medya yok'],
                ]);

            case 'budget':
                return array_merge($base, [
                    'message' => "Neredeyse bitti! **Bütçe aralığınız hakkında bir fikir verir misiniz?**\n\nBu bilgi size en uygun paketi önerememize yardımcı olur.",
                    'quick_replies' => ['5.000₺ altı', '5.000 – 15.000₺', '15.000 – 50.000₺', '50.000₺ üzeri', 'Teklif bekliyorum'],
                ]);

            case 'urgency':
                return array_merge($base, [
                    'message' => "**Ne zaman yayına çıkmak istiyorsunuz?**",
                    'quick_replies' => ['Mümkün olan en kısa sürede', '1 ay içinde', '2-3 ay içinde', 'Acele yok'],
                ]);

            case 'special_notes':
                return array_merge($base, [
                    'message' => "Son olarak: **Eklemek istediğiniz özel bir not veya istek var mı?**\n\nBu alan tamamen opsiyonel.",
                    'quick_replies' => ['Hayır, hepsi bu kadar'],
                ]);

            case 'summary':
                return array_merge($base, [
                    'message' => $this->buildSummary($project, $projectId),
                    'quick_replies' => ['Evet, gönder!', 'Değişiklik yapmak istiyorum'],
                ]);

            case 'done':
                return array_merge($base, [
                    'message' => "🎉 **Mükemmel! Proje analiziniz ekibimize iletildi.**\n\nEn kısa sürede size ulaşacağız. Projenizin ilerleyişini **Müşteri Paneli**'nden takip edebilirsiniz.\n\nBaşarılar! 🚀",
                    'is_done' => true,
                    'quick_replies' => ['Panele Git'],
                ]);

            default:
                return array_merge($base, [
                    'message' => "Bir adım daha...",
                ]);
        }
    }

    private function buildSummary(array $project, int $projectId): string
    {
        // En güncel project verisini al
        $p = $this->projects->find($projectId) ?? $project;

        $lines = ["📋 **Proje Özetiniz**\n"];
        if (!empty($p['name']))           $lines[] = "🏢 İşletme: " . $p['name'];
        if (!empty($p['target_audience'])) $lines[] = "👥 Hedef kitle: " . $p['target_audience'];
        if (!empty($p['budget_range']))   $lines[] = "💰 Bütçe: " . $p['budget_range'];
        if (!empty($p['urgency']))        $lines[] = "⏰ Süre: " . $p['urgency'];
        if (!empty($p['social_media_needs'])) $lines[] = "📱 Sosyal medya: " . $p['social_media_needs'];

        $lines[] = "\nBu bilgileri ekibimize göndermemi onaylıyor musunuz?";

        return implode("\n", $lines);
    }

    private function wrap(string $msg, string $state, bool $done = false): array
    {
        return ['message' => $msg, 'state' => $state, 'is_done' => $done, 'quick_replies' => []];
    }
}

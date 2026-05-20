<?php

namespace GO\Integrations\Ai;

/**
 * V1 varsayılan AI provider.
 * Gerçek LLM API çağrısı yapmaz.
 * State machine GO! Chat kendi akışını yönetir.
 * Bu provider, provider interface uyumluluğu için vardır.
 */
class NullAiProvider implements AiProviderInterface
{
    public function generateResponse(string $message, array $context = [], array $history = []): string
    {
        // V1'de GO\Services\ChatService state machine yanıt üretir.
        // Bu metod direkt çağrılmaz.
        return 'GO! mesajınızı aldı. Uzman ekibimiz işleminizi inceliyor.';
    }

    public function generateSummary(array $projectData): string
    {
        // V1 kural tabanlı özet — ChatService::buildSummary() ile üretilir.
        $parts = [];

        if (!empty($projectData['name'])) {
            $parts[] = $projectData['name'] . ' projesi';
        }
        if (!empty($projectData['sector'])) {
            $parts[] = $projectData['sector'] . ' sektörü';
        }
        if (!empty($projectData['budget_range'])) {
            $parts[] = 'bütçe: ' . $projectData['budget_range'];
        }
        if (!empty($projectData['urgency'])) {
            $parts[] = 'aciliyet: ' . $projectData['urgency'];
        }

        return empty($parts)
            ? 'Proje bilgileri bekleniyor.'
            : implode(', ', $parts) . '. GO! Chat üzerinden onboarding tamamlandı.';
    }

    public function isAvailable(): bool
    {
        return false; // V1'de false; ChatService kendi state machine'ini kullanır
    }

    public function getName(): string
    {
        return 'null';
    }
}

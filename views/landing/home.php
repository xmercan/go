<?php
$layout  = 'layouts/app';
$seoTitle = 'Dijital Dönüşümde Tavsiye Değil, İcra';
$metaDesc = 'GO! — KOBİ\'ler için web, domain, hosting ve yapay zeka destekli danışmanlık platformu. Projenizi GO! ile hayata geçirin.';
?>

<style>
/* ── Hero ── */
.hero {
    min-height: calc(100vh - 64px);
    display: flex;
    align-items: center;
    position: relative;
    overflow: hidden;
    padding: 4rem 0;
}
.hero-blob-1 {
    width: 600px; height: 600px;
    background: radial-gradient(circle, rgba(0,212,255,.15) 0%, transparent 70%);
    top: -100px; right: -100px;
}
.hero-blob-2 {
    width: 400px; height: 400px;
    background: radial-gradient(circle, rgba(34,197,94,.08) 0%, transparent 70%);
    bottom: 0; left: -50px;
}
.hero-content {
    position: relative; z-index: 1;
    max-width: 680px;
}
.hero-badge { margin-bottom: 1.5rem; }
.hero-title {
    font-size: clamp(2.2rem, 5vw, 3.8rem);
    font-weight: 900;
    line-height: 1.1;
    letter-spacing: -1.5px;
    margin-bottom: 1.25rem;
}
.hero-title span { color: var(--blue); }
.hero-desc {
    font-size: 1.1rem;
    color: var(--muted);
    max-width: 520px;
    margin-bottom: 2rem;
    line-height: 1.7;
}
.hero-actions { display: flex; gap: 1rem; flex-wrap: wrap; }
.hero-stats {
    margin-top: 3.5rem;
    display: flex; gap: 2rem; flex-wrap: wrap;
}
.hero-stat-num {
    font-size: 2rem; font-weight: 900; color: var(--blue); line-height: 1;
}
.hero-stat-label { font-size: .8rem; color: var(--muted); margin-top: .25rem; }

/* ── How it works ── */
.steps-grid {
    display: grid; grid-template-columns: repeat(4,1fr); gap: 1.5rem; margin-top: 3rem;
}
@media (max-width: 900px)  { .steps-grid { grid-template-columns: repeat(2,1fr); } }
@media (max-width: 480px)  { .steps-grid { grid-template-columns: 1fr; } }
.step-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 1.75rem 1.5rem;
    position: relative;
}
.step-card::before {
    content: attr(data-step);
    position: absolute; top: 1.25rem; right: 1.25rem;
    font-size: .75rem; font-weight: 800;
    color: var(--blue); opacity: .4;
    font-family: var(--font);
}
.step-icon {
    font-size: 2rem; margin-bottom: 1rem; display: block;
}
.step-title { font-size: 1rem; font-weight: 700; margin-bottom: .5rem; }
.step-desc  { font-size: .88rem; color: var(--muted); line-height: 1.6; }

/* ── Sectors ── */
.sector-grid {
    display: grid; grid-template-columns: repeat(4,1fr); gap: 1rem; margin-top: 2.5rem;
}
@media (max-width: 900px)  { .sector-grid { grid-template-columns: repeat(2,1fr); } }
@media (max-width: 480px)  { .sector-grid { grid-template-columns: 1fr; } }
.sector-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 1.5rem 1.25rem;
    text-decoration: none;
    color: var(--text);
    transition: background .2s, border-color .2s, transform .2s;
}
.sector-card:hover {
    background: var(--card-h);
    border-color: var(--blue-glow);
    transform: translateY(-3px);
}
.sector-icon { font-size: 2rem; margin-bottom: .75rem; display: block; }
.sector-name { font-size: .95rem; font-weight: 700; margin-bottom: .35rem; }
.sector-desc { font-size: .8rem; color: var(--muted); line-height: 1.5; }

/* ── CTA Banner ── */
.cta-banner {
    background: linear-gradient(135deg, rgba(0,212,255,.12) 0%, rgba(34,197,94,.08) 100%);
    border: 1px solid rgba(0,212,255,.2);
    border-radius: 24px;
    padding: 4rem 2rem;
    text-align: center;
}
.cta-banner h2 { font-size: clamp(1.6rem,3vw,2.4rem); font-weight: 900; margin-bottom: 1rem; }
.cta-banner p  { font-size: 1.05rem; color: var(--muted); margin-bottom: 2rem; max-width: 480px; margin-inline: auto; }

/* ── Section title ── */
.section-title { font-size: clamp(1.5rem,2.5vw,2.2rem); font-weight: 800; letter-spacing: -.5px; }
.section-sub   { font-size: 1rem; color: var(--muted); margin-top: .5rem; }

/* ── Features ── */
.feature-list { list-style: none; }
.feature-list li {
    display: flex; align-items: flex-start; gap: .75rem;
    padding: .75rem 0; border-bottom: 1px solid var(--border); font-size: .95rem;
}
.feature-list li:last-child { border-bottom: none; }
.feature-list .check { color: var(--green); font-size: 1.1rem; flex-shrink: 0; margin-top: .1rem; }
</style>

<!-- ═══ HERO ═══ -->
<section class="hero">
    <div class="blob hero-blob-1"></div>
    <div class="blob hero-blob-2"></div>
    <div class="container">
        <div class="hero-content">
            <div class="hero-badge">
                <span class="badge badge-blue">🚀 GO! V1 — Beta Erken Erişim</span>
            </div>
            <h1 class="hero-title">
                Dijital dönüşümde<br>
                tavsiye değil,<br>
                <span>icra var.</span>
            </h1>
            <p class="hero-desc">
                GO! — işletmenizin web varlığını, domain, hosting ve yazılım ihtiyaçlarını
                yapay zeka destekli bir platform üzerinden hayata geçirir.
                Sizi anlar, planlar ve uygular.
            </p>
            <div class="hero-actions">
                <a href="/kayit" class="btn btn-primary btn-lg">Ücretsiz Başla →</a>
                <a href="/#nasil-calisir" class="btn btn-outline btn-lg">Nasıl Çalışır?</a>
            </div>
            <div class="hero-stats">
                <div>
                    <div class="hero-stat-num">50+</div>
                    <div class="hero-stat-label">Sektör şablonu</div>
                </div>
                <div>
                    <div class="hero-stat-num">7/24</div>
                    <div class="hero-stat-label">GO! Chat desteği</div>
                </div>
                <div>
                    <div class="hero-stat-num">%100</div>
                    <div class="hero-stat-label">Türkiye odaklı</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══ NASIL ÇALIŞIR ═══ -->
<section id="nasil-calisir">
    <div class="container">
        <div class="text-center reveal">
            <span class="badge badge-blue">Süreç</span>
            <h2 class="section-title mt-2">4 adımda dijital varlık</h2>
            <p class="section-sub">Karmaşık teknik süreçler bizde, siz işinize odaklanın.</p>
        </div>
        <div class="steps-grid">
            <div class="step-card reveal" data-step="01">
                <span class="step-icon">💬</span>
                <div class="step-title">GO! Chat ile Keşif</div>
                <p class="step-desc">Yapay zeka destekli sohbet asistanımız işletmenizi, hedef kitlenizi ve ihtiyaçlarınızı öğrenir.</p>
            </div>
            <div class="step-card reveal" data-step="02">
                <span class="step-icon">📋</span>
                <div class="step-title">Proje Analizi</div>
                <p class="step-desc">Uzman ekibimiz ihtiyaç analizinizi inceler, size özel bir dijital strateji oluşturur.</p>
            </div>
            <div class="step-card reveal" data-step="03">
                <span class="step-icon">⚡</span>
                <div class="step-title">Hızlı Üretim</div>
                <p class="step-desc">Domain, hosting, tasarım ve yazılım işlemleri tek platformdan yönetilir, hızla hayata geçirilir.</p>
            </div>
            <div class="step-card reveal" data-step="04">
                <span class="step-icon">📈</span>
                <div class="step-title">Sürekli Destek</div>
                <p class="step-desc">Proje tesliminin ardından büyümenizi desteklemeye devam ederiz. Tek seferlik değil, ortaklık.</p>
            </div>
        </div>
    </div>
</section>

<!-- ═══ SEKTÖRLER ═══ -->
<section id="sektorler" style="background:var(--bg2);border-top:1px solid var(--border);border-bottom:1px solid var(--border)">
    <div class="container">
        <div class="text-center reveal">
            <span class="badge badge-blue">Sektörler</span>
            <h2 class="section-title mt-2">Her sektöre özel çözüm</h2>
            <p class="section-sub">Sektörünüze özgü hazır şablonlar ve iş modelleriyle hızlı başlangıç.</p>
        </div>
        <div class="sector-grid">
            <?php foreach ($sectors ?? [] as $sector): ?>
            <a href="/sektorler/<?= e($sector['slug']) ?>" class="sector-card reveal">
                <span class="sector-icon"><?= sectorIcon($sector['icon']) ?></span>
                <div class="sector-name"><?= e($sector['name']) ?></div>
                <p class="sector-desc"><?= e($sector['description']) ?></p>
            </a>
            <?php endforeach; ?>
            <?php if (empty($sectors ?? [])): ?>
            <?php $defaultSectors = [
                ['icon'=>'☕','name'=>'Kahve Dükkanı',    'desc'=>'Lokal kafe ve roastery\'ler için dijital dönüşüm'],
                ['icon'=>'🏠','name'=>'Emlak Ofisi',      'desc'=>'Gayrimenkul danışmanları için dijital çözümler'],
                ['icon'=>'✂️','name'=>'Güzellik & Kuaför', 'desc'=>'Salonlar ve klinikler için dijital altyapı'],
                ['icon'=>'⚖️','name'=>'Avukatlık Bürosu', 'desc'=>'Hukuk profesyonelleri için kurumsal çözümler'],
                ['icon'=>'🍽️','name'=>'Restoran & Cafe',  'desc'=>'Yeme-içme işletmeleri için dijital altyapı'],
                ['icon'=>'❤️','name'=>'Sağlık & Klinik',  'desc'=>'Sağlık hizmetleri için dijital çözümler'],
                ['icon'=>'📚','name'=>'Eğitim & Kurs',    'desc'=>'Eğitim kurumları için dijital dönüşüm'],
                ['icon'=>'🛒','name'=>'Ticaret & E-Ticaret','desc'=>'Ticari işletmeler için online çözümler'],
            ]; ?>
            <?php foreach ($defaultSectors as $s): ?>
            <div class="sector-card reveal">
                <span class="sector-icon"><?= $s['icon'] ?></span>
                <div class="sector-name"><?= e($s['name']) ?></div>
                <p class="sector-desc"><?= e($s['desc']) ?></p>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ═══ ÖZELLIKLER ═══ -->
<section>
    <div class="container">
        <div class="grid-2" style="align-items:center;gap:4rem">
            <div class="reveal">
                <span class="badge badge-blue">Platform</span>
                <h2 class="section-title mt-2 mb-2">
                    Tek platform,<br>tüm dijital ihtiyaçlar
                </h2>
                <ul class="feature-list">
                    <li><span class="check">✓</span><span><strong>GO! Chat</strong> — Yapay zeka destekli ihtiyaç analizi ve proje keşfi</span></li>
                    <li><span class="check">✓</span><span><strong>GO! Web</strong> — Sektörel web sitesi oluşturma ve yönetimi</span></li>
                    <li><span class="check">✓</span><span><strong>Domain</strong> — Tescil, DNS yönetimi, nameserver işlemleri</span></li>
                    <li><span class="check">✓</span><span><strong>Hosting</strong> — cPanel tabanlı güvenilir barındırma hizmetleri</span></li>
                    <li><span class="check">✓</span><span><strong>Yazılım</strong> — CMS, e-ticaret ve özel yazılım çözümleri</span></li>
                    <li><span class="check">✓</span><span><strong>Destek</strong> — 7/24 ticket sistemi ve WhatsApp desteği</span></li>
                </ul>
            </div>
            <div class="reveal">
                <!-- Feature visual card -->
                <div style="background:var(--card);border:1px solid var(--border);border-radius:20px;padding:2rem;position:relative;overflow:hidden">
                    <div style="position:absolute;top:-50px;right:-50px;width:200px;height:200px;background:radial-gradient(circle,rgba(0,212,255,.1) 0%,transparent 70%);border-radius:50%"></div>
                    <p style="font-size:.75rem;color:var(--muted);font-weight:600;letter-spacing:.08em;text-transform:uppercase;margin-bottom:1rem">GO! Chat Önizleme</p>
                    <div style="display:flex;flex-direction:column;gap:.75rem">
                        <div style="align-self:flex-start;background:var(--bg2);border:1px solid var(--border);border-radius:12px 12px 12px 4px;padding:.75rem 1rem;max-width:80%;font-size:.9rem">
                            👋 Merhaba! Ben GO!'nun dijital danışmanıyım. İşletmeniz hakkında bana bir şeyler anlatır mısınız?
                        </div>
                        <div style="align-self:flex-end;background:var(--blue-dim);border:1px solid var(--blue-glow);border-radius:12px 12px 4px 12px;padding:.75rem 1rem;max-width:80%;font-size:.9rem">
                            Trabzon'da kahve dükkanım var, sosyal medya hesabı açmak istiyorum.
                        </div>
                        <div style="align-self:flex-start;background:var(--bg2);border:1px solid var(--border);border-radius:12px 12px 12px 4px;padding:.75rem 1rem;max-width:80%;font-size:.9rem">
                            Harika! Kahve dükkanları için sektörel paketimizde Instagram, Google Maps ve web sitesi bütünleşik sunuyoruz. Devam edelim mi?
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══ CTA ═══ -->
<section class="tight" id="fiyatlar">
    <div class="container">
        <div class="cta-banner reveal">
            <h2>Projenizi bugün başlatın</h2>
            <p>Kayıt ücretsiz. GO! Chat ile ihtiyaçlarınızı anlatalım, size özel teklif hazırlayalım.</p>
            <a href="/kayit" class="btn btn-primary btn-lg">Ücretsiz Hesap Aç →</a>
        </div>
    </div>
</section>

<?php
function sectorIcon(string $icon): string {
    $map = [
        'coffee'        => '☕',
        'home'          => '🏠',
        'scissors'      => '✂️',
        'briefcase'     => '⚖️',
        'utensils'      => '🍽️',
        'heart'         => '❤️',
        'book'          => '📚',
        'shopping-cart' => '🛒',
    ];
    return $map[$icon] ?? '💼';
}
?>

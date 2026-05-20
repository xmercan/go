<!DOCTYPE html>
<html lang="tr" id="html-root" class="<?= $_COOKIE['theme'] ?? 'dark' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SEO -->
    <title><?= e($seoTitle ?? $title ?? 'GO!') ?> — <?= e(defined('APP_NAME') ? APP_NAME : 'GO.NET.TR') ?></title>
    <meta name="description" content="<?= e($metaDesc ?? 'GO.NET.TR — KOBİ\'lerin dijital dönüşüm ortağı. Web, domain, hosting, yapay zeka.') ?>">
    <meta name="keywords"    content="<?= e($metaKeys ?? 'GO, GO.NET.TR, dijital dönüşüm, KOBİ, web sitesi, domain, hosting') ?>">
    <link rel="canonical"   href="<?= e(defined('APP_URL') ? APP_URL . ($_SERVER['REQUEST_URI'] ?? '/') : '/') ?>">

    <!-- OG -->
    <meta property="og:type"        content="website">
    <meta property="og:title"       content="<?= e($seoTitle ?? $title ?? 'GO!') ?>">
    <meta property="og:description" content="<?= e($metaDesc ?? '') ?>">
    <meta property="og:image"       content="<?= e(defined('APP_URL') ? APP_URL : '') ?>/assets/img/og-image.jpg">
    <meta property="og:url"         content="<?= e(defined('APP_URL') ? APP_URL . ($_SERVER['REQUEST_URI'] ?? '/') : '/') ?>">
    <meta name="twitter:card"       content="summary_large_image">

    <!-- Icons -->
    <link rel="icon" href="/favicon.ico" type="image/x-icon">

    <!-- Preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        /* ── CSS Variables ── */
        :root {
            --bg:        #0A1628;
            --bg2:       #0E1E35;
            --bg3:       #0F2340;
            --card:      rgba(255,255,255,.04);
            --card-h:    rgba(255,255,255,.07);
            --border:    rgba(255,255,255,.1);
            --blue:      #00D4FF;
            --blue-dim:  rgba(0,212,255,.15);
            --blue-glow: rgba(0,212,255,.3);
            --green:     #22C55E;
            --orange:    #F97316;
            --red:       #EF4444;
            --text:      #F0F6FF;
            --muted:     rgba(240,246,255,.55);
            --input-bg:  rgba(255,255,255,.06);
            --nav-bg:    rgba(10,22,40,.85);
            --radius:    12px;
            --shadow:    0 4px 24px rgba(0,0,0,.35);
            --font:      'Inter', system-ui, -apple-system, sans-serif;
        }
        html.light {
            --bg:       #F8FAFC;
            --bg2:      #EFF3F8;
            --bg3:      #E2EAF4;
            --card:     rgba(0,0,0,.03);
            --card-h:   rgba(0,0,0,.06);
            --border:   rgba(0,0,0,.1);
            --text:     #0F172A;
            --muted:    rgba(15,23,42,.55);
            --input-bg: rgba(0,0,0,.05);
            --nav-bg:   rgba(248,250,252,.92);
            --shadow:   0 4px 24px rgba(0,0,0,.12);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { scroll-behavior: smooth; }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: var(--font);
            font-size: 16px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        /* ── Navigation ── */
        nav.go-nav {
            position: fixed; top: 0; left: 0; right: 0;
            z-index: 999;
            background: var(--nav-bg);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
            padding: 0 1.5rem;
            height: 64px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .nav-logo { font-size: 1.6rem; font-weight: 900; color: var(--blue); letter-spacing: -1px; text-decoration: none; }
        .nav-logo:hover { opacity: .85; }
        .nav-links { display: flex; gap: .25rem; align-items: center; }
        .nav-links a {
            padding: .45rem .85rem; border-radius: 8px; font-size: .9rem; font-weight: 500;
            color: var(--muted); text-decoration: none; transition: color .2s, background .2s;
        }
        .nav-links a:hover { color: var(--text); background: var(--card); }
        .nav-links a.active { color: var(--blue); }
        .nav-actions { display: flex; gap: .5rem; align-items: center; }
        .btn-theme {
            background: var(--card); border: 1px solid var(--border); border-radius: 8px;
            padding: .45rem .6rem; cursor: pointer; color: var(--muted); font-size: .9rem;
            transition: color .2s, background .2s; display: flex; align-items: center; gap: .3rem;
        }
        .btn-theme:hover { color: var(--text); background: var(--card-h); }
        .nav-mobile-toggle {
            display: none; background: var(--card); border: 1px solid var(--border);
            border-radius: 8px; padding: .45rem .55rem; cursor: pointer; color: var(--muted);
        }
        @media (max-width: 768px) {
            .nav-links, .nav-actions .btn-login, .nav-actions .btn-cta { display: none; }
            .nav-mobile-toggle { display: flex; }
        }

        /* Buttons */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: .4rem;
            padding: .7rem 1.5rem; border-radius: var(--radius); font-size: .95rem; font-weight: 600;
            cursor: pointer; border: none; text-decoration: none; transition: opacity .2s, transform .1s;
            white-space: nowrap;
        }
        .btn:active { transform: scale(.98); }
        .btn-primary { background: var(--blue); color: #0A1628; }
        .btn-primary:hover { opacity: .88; }
        .btn-outline { background: transparent; border: 1px solid var(--border); color: var(--text); }
        .btn-outline:hover { background: var(--card-h); }
        .btn-ghost { background: transparent; color: var(--muted); }
        .btn-ghost:hover { color: var(--text); }
        .btn-sm { padding: .5rem 1rem; font-size: .85rem; border-radius: 8px; }
        .btn-lg { padding: .9rem 2.25rem; font-size: 1.05rem; }

        /* Container */
        .container { max-width: 1180px; margin: 0 auto; padding: 0 1.5rem; }

        /* Flash */
        .flash { padding: .85rem 1rem; border-radius: 10px; margin-bottom: 1rem; font-size: .9rem; }
        .flash-error   { background: rgba(239,68,68,.12);  border: 1px solid rgba(239,68,68,.3);  color: #fca5a5; }
        .flash-success { background: rgba(34,197,94,.12);  border: 1px solid rgba(34,197,94,.3);  color: #86efac; }
        .flash-info    { background: rgba(0,212,255,.08);   border: 1px solid rgba(0,212,255,.2);  color: #67e8f9; }
        .flash-warning { background: rgba(251,191,36,.1);  border: 1px solid rgba(251,191,36,.3); color: #fde68a; }

        /* Section spacing */
        section { padding: 5rem 0; }
        section.tight { padding: 3rem 0; }

        /* Badge */
        .badge {
            display: inline-flex; align-items: center; gap: .3rem;
            padding: .3rem .75rem; border-radius: 20px; font-size: .78rem; font-weight: 600;
        }
        .badge-blue { background: var(--blue-dim); color: var(--blue); border: 1px solid var(--blue-glow); }

        /* Card */
        .card {
            background: var(--card); border: 1px solid var(--border);
            border-radius: 16px; padding: 1.5rem;
        }
        .card:hover { background: var(--card-h); transition: background .2s; }

        /* Grid */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; }
        @media (max-width: 1024px) {
            .grid-4 { grid-template-columns: repeat(2, 1fr); }
            .grid-3 { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 640px) {
            .grid-2, .grid-3, .grid-4 { grid-template-columns: 1fr; }
        }

        /* Footer */
        footer.go-footer {
            background: var(--bg2);
            border-top: 1px solid var(--border);
            padding: 3rem 0 2rem;
            margin-top: 4rem;
        }
        .footer-grid {
            display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 2rem; margin-bottom: 2.5rem;
        }
        @media (max-width: 768px) {
            .footer-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 480px) {
            .footer-grid { grid-template-columns: 1fr; }
        }
        .footer-logo { font-size: 1.4rem; font-weight: 900; color: var(--blue); margin-bottom: .5rem; }
        .footer-desc { font-size: .85rem; color: var(--muted); line-height: 1.7; }
        .footer-heading { font-size: .8rem; font-weight: 700; color: var(--muted); letter-spacing: .08em; text-transform: uppercase; margin-bottom: .75rem; }
        .footer-links { list-style: none; }
        .footer-links li { margin-bottom: .5rem; }
        .footer-links a { font-size: .85rem; color: var(--muted); text-decoration: none; transition: color .2s; }
        .footer-links a:hover { color: var(--text); }
        .footer-bottom { border-top: 1px solid var(--border); padding-top: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: .75rem; }
        .footer-legal { font-size: .8rem; color: var(--muted); }
        .footer-legal a { color: var(--muted); text-decoration: none; }
        .footer-legal a:hover { color: var(--text); }

        /* Utility */
        .text-center { text-align: center; }
        .text-muted  { color: var(--muted); }
        .text-blue   { color: var(--blue); }
        .text-green  { color: var(--green); }
        .fw-700      { font-weight: 700; }
        .mt-1  { margin-top: .5rem; }
        .mt-2  { margin-top: 1rem; }
        .mt-3  { margin-top: 1.5rem; }
        .mt-4  { margin-top: 2rem; }
        .mb-1  { margin-bottom: .5rem; }
        .mb-2  { margin-bottom: 1rem; }
        .mb-3  { margin-bottom: 1.5rem; }
        .gap-1 { gap: .5rem; }
        .gap-2 { gap: 1rem; }
        .flex  { display: flex; }
        .flex-col { flex-direction: column; }
        .items-center { align-items: center; }

        /* Hero gradient blobs */
        .blob {
            position: absolute; border-radius: 50%; filter: blur(80px); pointer-events: none; z-index: 0;
        }

        /* Scroll reveal */
        .reveal { opacity: 0; transform: translateY(20px); transition: opacity .6s ease, transform .6s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0); }
    </style>
</head>
<body>

<!-- Navigation -->
<nav class="go-nav" aria-label="Ana menü">
    <a href="/" class="nav-logo">GO!</a>

    <div class="nav-links">
        <a href="/#nasil-calisir">Nasıl Çalışır?</a>
        <a href="/#sektorler">Sektörler</a>
        <a href="/#fiyatlar">Fiyatlar</a>
        <a href="/blog">Blog</a>
        <a href="/iletisim">İletişim</a>
    </div>

    <div class="nav-actions">
        <button class="btn-theme" id="theme-toggle" title="Tema değiştir" aria-label="Tema değiştir">
            <span id="theme-icon">☾</span>
        </button>
        <?php if (is_logged_in()): ?>
            <a href="/panel" class="btn btn-sm btn-outline btn-login">Panelim</a>
        <?php else: ?>
            <a href="/giris" class="btn btn-sm btn-ghost btn-login">Giriş</a>
            <a href="/kayit" class="btn btn-sm btn-primary btn-cta">Ücretsiz Başla</a>
        <?php endif; ?>
        <button class="nav-mobile-toggle" id="nav-mobile-btn" aria-label="Menü">☰</button>
    </div>
</nav>

<!-- Mobile menu (overlay) -->
<div id="mobile-menu" style="display:none;position:fixed;top:64px;left:0;right:0;bottom:0;background:var(--bg);z-index:998;padding:1.5rem;overflow-y:auto">
    <div style="display:flex;flex-direction:column;gap:.5rem;margin-bottom:1.5rem">
        <a href="/#nasil-calisir" style="padding:.75rem 1rem;border-radius:10px;color:var(--text);text-decoration:none;background:var(--card)">Nasıl Çalışır?</a>
        <a href="/#sektorler"     style="padding:.75rem 1rem;border-radius:10px;color:var(--text);text-decoration:none;background:var(--card)">Sektörler</a>
        <a href="/#fiyatlar"      style="padding:.75rem 1rem;border-radius:10px;color:var(--text);text-decoration:none;background:var(--card)">Fiyatlar</a>
        <a href="/blog"           style="padding:.75rem 1rem;border-radius:10px;color:var(--text);text-decoration:none;background:var(--card)">Blog</a>
        <a href="/iletisim"       style="padding:.75rem 1rem;border-radius:10px;color:var(--text);text-decoration:none;background:var(--card)">İletişim</a>
    </div>
    <div style="display:flex;flex-direction:column;gap:.75rem">
        <a href="/giris"  class="btn btn-outline" style="justify-content:center">Giriş Yap</a>
        <a href="/kayit"  class="btn btn-primary" style="justify-content:center">Ücretsiz Başla</a>
    </div>
</div>

<!-- Main content (64px nav offset) -->
<main style="padding-top:64px">
    <?php foreach (get_flashes() as $flash): ?>
    <div class="container" style="padding-top:1rem">
        <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    </div>
    <?php endforeach; ?>

    <?= $content ?? '' ?>
</main>

<!-- Footer -->
<footer class="go-footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <div class="footer-logo">GO!</div>
                <p class="footer-desc">
                    KOBİ'lerin dijital dönüşüm ortağı. Web sitesi, domain, hosting, yazılım ve yapay zeka danışmanlığı tek platformda.
                </p>
            </div>
            <div>
                <p class="footer-heading">Platform</p>
                <ul class="footer-links">
                    <li><a href="/#nasil-calisir">Nasıl Çalışır?</a></li>
                    <li><a href="/#sektorler">Sektörler</a></li>
                    <li><a href="/#fiyatlar">Fiyatlar</a></li>
                    <li><a href="/panel">Müşteri Paneli</a></li>
                </ul>
            </div>
            <div>
                <p class="footer-heading">Hizmetler</p>
                <ul class="footer-links">
                    <li><a href="/domain">Domain Tescil</a></li>
                    <li><a href="/hosting">Web Hosting</a></li>
                    <li><a href="/yazilim">Yazılım</a></li>
                    <li><a href="/destek">Destek</a></li>
                </ul>
            </div>
            <div>
                <p class="footer-heading">Hukuki</p>
                <ul class="footer-links">
                    <li><a href="/kvkk">KVKK</a></li>
                    <li><a href="/gizlilik-politikasi">Gizlilik</a></li>
                    <li><a href="/kullanim-kosullari">Kullanım Koşulları</a></li>
                    <li><a href="/iletisim">İletişim</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p class="footer-legal">
                © <?= date('Y') ?> <strong>Genç Grup Yazılım Ltd. Şti.</strong> — Tüm hakları saklıdır.
            </p>
            <p class="footer-legal">
                <a href="mailto:yatirim@go.net.tr">yatirim@go.net.tr</a>
                &nbsp;·&nbsp;
                <a href="tel:+905417885432">+90 541 788 54 32</a>
            </p>
        </div>
    </div>
</footer>

<!-- Theme toggle script -->
<script>
(function() {
    const root   = document.getElementById('html-root');
    const toggle = document.getElementById('theme-toggle');
    const icon   = document.getElementById('theme-icon');
    const mobileBtn = document.getElementById('nav-mobile-btn');
    const mobileMenu = document.getElementById('mobile-menu');

    // Init from cookie
    const saved = document.cookie.split(';').find(c => c.trim().startsWith('theme='));
    const theme = saved ? saved.split('=')[1].trim() : 'dark';
    root.className = theme;
    icon.textContent = theme === 'dark' ? '☾' : '☀';

    toggle.addEventListener('click', () => {
        const next = root.className === 'dark' ? 'light' : 'dark';
        root.className = next;
        icon.textContent = next === 'dark' ? '☾' : '☀';
        document.cookie = 'theme=' + next + ';path=/;max-age=31536000;SameSite=Lax';
    });

    mobileBtn.addEventListener('click', () => {
        mobileMenu.style.display = mobileMenu.style.display === 'none' ? 'block' : 'none';
        mobileBtn.textContent = mobileMenu.style.display === 'none' ? '☰' : '✕';
    });

    // Scroll reveal
    const observer = new IntersectionObserver(entries => {
        entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); } });
    }, { threshold: 0.1 });
    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
})();
</script>
</body>
</html>

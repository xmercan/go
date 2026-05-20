<!DOCTYPE html>
<html lang="tr" id="html-root" class="<?= $_COOKIE['theme'] ?? 'dark' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Panel') ?> — GO!</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg:       #0A1628;
            --bg2:      #0E1E35;
            --bg3:      #0F2340;
            --sidebar:  #091220;
            --card:     rgba(255,255,255,.04);
            --card-h:   rgba(255,255,255,.07);
            --border:   rgba(255,255,255,.1);
            --blue:     #00D4FF;
            --blue-dim: rgba(0,212,255,.15);
            --blue-glow:rgba(0,212,255,.3);
            --green:    #22C55E;
            --orange:   #F97316;
            --red:      #EF4444;
            --text:     #F0F6FF;
            --muted:    rgba(240,246,255,.55);
            --input-bg: rgba(255,255,255,.06);
            --radius:   12px;
            --sidebar-w: 240px;
        }
        html.light {
            --bg:      #F5F8FC;
            --bg2:     #EDF2F9;
            --bg3:     #E4EBF5;
            --sidebar: #0A1628;
            --card:    rgba(0,0,0,.03);
            --card-h:  rgba(0,0,0,.06);
            --border:  rgba(0,0,0,.1);
            --text:    #0F172A;
            --muted:   rgba(15,23,42,.55);
            --input-bg:rgba(0,0,0,.05);
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', system-ui, sans-serif;
            font-size: 15px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            display: flex;
            min-height: 100vh;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--sidebar);
            border-right: 1px solid rgba(255,255,255,.07);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
            transition: transform .3s;
        }
        .sidebar-logo {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,.07);
            font-size: 1.6rem;
            font-weight: 900;
            color: var(--blue);
            letter-spacing: -1px;
            text-decoration: none;
            display: block;
        }
        .sidebar-user {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,.07);
        }
        .sidebar-user-name { font-size: .85rem; font-weight: 600; color: #fff; margin-bottom: .15rem; }
        .sidebar-user-email{ font-size: .75rem; color: rgba(255,255,255,.4); }
        .sidebar-nav {
            flex: 1;
            padding: .75rem .75rem;
            overflow-y: auto;
        }
        .sidebar-nav-label {
            font-size: .67rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: rgba(255,255,255,.3);
            padding: .5rem .75rem .25rem;
            margin-top: .5rem;
        }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .55rem .75rem;
            border-radius: 8px;
            font-size: .88rem;
            font-weight: 500;
            color: rgba(255,255,255,.6);
            text-decoration: none;
            transition: background .2s, color .2s;
            margin-bottom: .1rem;
        }
        .sidebar-nav a:hover { background: rgba(255,255,255,.06); color: #fff; }
        .sidebar-nav a.active { background: var(--blue-dim); color: var(--blue); }
        .sidebar-nav a .nav-icon { font-size: 1rem; width: 18px; text-align: center; flex-shrink: 0; }
        .sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(255,255,255,.07);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .sidebar-footer a { font-size: .8rem; color: rgba(255,255,255,.4); text-decoration: none; transition: color .2s; }
        .sidebar-footer a:hover { color: #fff; }

        /* ── Main area ── */
        .panel-main {
            margin-left: var(--sidebar-w);
            flex: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Top bar ── */
        .panel-topbar {
            background: var(--bg2);
            border-bottom: 1px solid var(--border);
            padding: 0 2rem;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .topbar-title { font-size: 1rem; font-weight: 600; }
        .topbar-actions { display: flex; gap: .5rem; align-items: center; }
        .btn-icon {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: .4rem .55rem;
            cursor: pointer;
            color: var(--muted);
            font-size: .9rem;
            display: flex;
            align-items: center;
            transition: color .2s, background .2s;
            text-decoration: none;
        }
        .btn-icon:hover { color: var(--text); background: var(--card-h); }

        /* ── Content ── */
        .panel-content { flex: 1; padding: 1.75rem 2rem; }

        /* ── Cards ── */
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1.5rem;
        }
        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
        }
        .card-title { font-size: 1rem; font-weight: 700; }

        /* ── Buttons ── */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: .4rem;
            padding: .65rem 1.25rem; border-radius: 8px; font-size: .88rem; font-weight: 600;
            cursor: pointer; border: none; text-decoration: none; transition: opacity .2s;
            white-space: nowrap;
        }
        .btn-primary { background: var(--blue); color: #0A1628; }
        .btn-primary:hover { opacity: .85; }
        .btn-outline { background: transparent; border: 1px solid var(--border); color: var(--text); }
        .btn-outline:hover { background: var(--card-h); }
        .btn-danger { background: rgba(239,68,68,.15); border: 1px solid rgba(239,68,68,.3); color: #fca5a5; }
        .btn-sm { padding: .4rem .75rem; font-size: .8rem; border-radius: 6px; }

        /* ── Flash ── */
        .flash { padding: .85rem 1rem; border-radius: 10px; margin-bottom: 1rem; font-size: .88rem; }
        .flash-error   { background: rgba(239,68,68,.12);  border: 1px solid rgba(239,68,68,.3);  color: #fca5a5; }
        .flash-success { background: rgba(34,197,94,.12);  border: 1px solid rgba(34,197,94,.3);  color: #86efac; }
        .flash-info    { background: rgba(0,212,255,.08);   border: 1px solid rgba(0,212,255,.2);  color: #67e8f9; }
        .flash-warning { background: rgba(251,191,36,.1);  border: 1px solid rgba(251,191,36,.3); color: #fde68a; }

        /* ── Badges / Status ── */
        .badge { display: inline-flex; align-items: center; gap: .3rem; padding: .2rem .6rem; border-radius: 20px; font-size: .75rem; font-weight: 600; }
        .badge-blue   { background: var(--blue-dim); color: var(--blue); }
        .badge-green  { background: rgba(34,197,94,.15); color: #4ade80; }
        .badge-orange { background: rgba(249,115,22,.15); color: #fb923c; }
        .badge-red    { background: rgba(239,68,68,.15);  color: #f87171; }
        .badge-gray   { background: rgba(255,255,255,.08); color: var(--muted); }

        /* ── Forms ── */
        .form-group { margin-bottom: 1.1rem; }
        label { display: block; font-size: .8rem; color: var(--muted); margin-bottom: .35rem; font-weight: 500; }
        input[type="text"], input[type="email"], input[type="password"], input[type="tel"],
        input[type="number"], input[type="url"], input[type="date"], select, textarea {
            width: 100%;
            background: var(--input-bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: .65rem 1rem;
            color: var(--text);
            font-size: .9rem;
            outline: none;
            font-family: inherit;
            transition: border-color .2s;
        }
        input:focus, select:focus, textarea:focus { border-color: var(--blue); }
        textarea { resize: vertical; min-height: 100px; }

        /* ── Table ── */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: .88rem; }
        th { text-align: left; font-size: .75rem; font-weight: 700; color: var(--muted); letter-spacing: .05em; text-transform: uppercase; padding: .65rem 1rem; border-bottom: 1px solid var(--border); }
        td { padding: .75rem 1rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: var(--card-h); }

        /* ── Grid ── */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
        .grid-3 { display: grid; grid-template-columns: repeat(3,1fr); gap: 1.25rem; }
        .grid-4 { display: grid; grid-template-columns: repeat(4,1fr); gap: 1.25rem; }
        @media (max-width: 1100px) { .grid-4 { grid-template-columns: repeat(2,1fr); } .grid-3 { grid-template-columns: repeat(2,1fr); } }
        @media (max-width: 768px)  { .grid-2, .grid-3, .grid-4 { grid-template-columns: 1fr; } }

        /* ── Stat card ── */
        .stat-card { background: var(--card); border: 1px solid var(--border); border-radius: 14px; padding: 1.25rem 1.5rem; }
        .stat-label { font-size: .75rem; color: var(--muted); font-weight: 600; letter-spacing: .05em; text-transform: uppercase; }
        .stat-value { font-size: 2rem; font-weight: 900; margin: .35rem 0; line-height: 1; }
        .stat-sub   { font-size: .78rem; color: var(--muted); }

        /* ── Mobile sidebar ── */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .panel-main { margin-left: 0; }
            .panel-content { padding: 1.25rem 1rem; }
            .panel-topbar { padding: 0 1rem; }
            .sidebar-overlay {
                display: none; position: fixed; inset: 0; background: rgba(0,0,0,.6); z-index: 99;
            }
            .sidebar-overlay.open { display: block; }
        }
    </style>
</head>
<body>

<?php
$user         = current_user();
$currentPath  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
function isActive(string $path, string $current): string {
    return str_starts_with($current, $path) ? 'active' : '';
}
?>

<!-- Sidebar overlay (mobile) -->
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <a href="/panel" class="sidebar-logo">GO!</a>

    <div class="sidebar-user">
        <div class="sidebar-user-name"><?= e($user['full_name'] ?? 'Kullanıcı') ?></div>
        <div class="sidebar-user-email"><?= e($user['email'] ?? '') ?></div>
    </div>

    <nav class="sidebar-nav">
        <div class="sidebar-nav-label">Ana Menü</div>
        <a href="/panel" class="<?= isActive('/panel', $currentPath) ?>">
            <span class="nav-icon">🏠</span> Genel Bakış
        </a>
        <a href="/chat" class="<?= isActive('/chat', $currentPath) ?>">
            <span class="nav-icon">💬</span> GO! Chat
        </a>
        <a href="/panel/projeler" class="<?= isActive('/panel/projeler', $currentPath) ?>">
            <span class="nav-icon">📁</span> Projelerim
        </a>

        <div class="sidebar-nav-label">Hizmetler</div>
        <a href="/panel/domain" class="<?= isActive('/panel/domain', $currentPath) ?>">
            <span class="nav-icon">🌐</span> Domain
        </a>
        <a href="/panel/hosting" class="<?= isActive('/panel/hosting', $currentPath) ?>">
            <span class="nav-icon">🖥️</span> Hosting
        </a>
        <a href="/panel/yazilim" class="<?= isActive('/panel/yazilim', $currentPath) ?>">
            <span class="nav-icon">💻</span> Yazılım
        </a>

        <div class="sidebar-nav-label">Hesap</div>
        <a href="/panel/faturalar" class="<?= isActive('/panel/faturalar', $currentPath) ?>">
            <span class="nav-icon">🧾</span> Faturalar
        </a>
        <a href="/panel/destek" class="<?= isActive('/panel/destek', $currentPath) ?>">
            <span class="nav-icon">🎯</span> Destek
        </a>
        <a href="/panel/ayarlar" class="<?= isActive('/panel/ayarlar', $currentPath) ?>">
            <span class="nav-icon">⚙️</span> Ayarlar
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="/" target="_blank">Ana Sayfa</a>
        <a href="/cikis" style="color:rgba(239,68,68,.7)">Çıkış</a>
    </div>
</aside>

<!-- Main -->
<div class="panel-main">
    <!-- Topbar -->
    <header class="panel-topbar">
        <div style="display:flex;align-items:center;gap:.75rem">
            <button class="btn-icon" id="sidebar-toggle" aria-label="Menü">☰</button>
            <span class="topbar-title"><?= e($title ?? 'Panel') ?></span>
        </div>
        <div class="topbar-actions">
            <button class="btn-icon" id="theme-toggle" title="Tema" aria-label="Tema değiştir">
                <span id="theme-icon"><?= ($_COOKIE['theme'] ?? 'dark') === 'dark' ? '☾' : '☀' ?></span>
            </button>
            <a href="/panel/bildirimler" class="btn-icon" title="Bildirimler" aria-label="Bildirimler">🔔</a>
        </div>
    </header>

    <!-- Content -->
    <main class="panel-content">
        <?php foreach (get_flashes() as $flash): ?>
        <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endforeach; ?>

        <?= $content ?? '' ?>
    </main>
</div>

<script>
(function() {
    const root      = document.getElementById('html-root');
    const toggle    = document.getElementById('theme-toggle');
    const icon      = document.getElementById('theme-icon');
    const sidebar   = document.getElementById('sidebar');
    const overlay   = document.getElementById('sidebar-overlay');
    const sidebarBtn= document.getElementById('sidebar-toggle');

    // Theme
    toggle.addEventListener('click', () => {
        const next = root.className === 'dark' ? 'light' : 'dark';
        root.className = next;
        icon.textContent = next === 'dark' ? '☾' : '☀';
        document.cookie = 'theme=' + next + ';path=/;max-age=31536000;SameSite=Lax';
    });

    // Mobile sidebar
    function openSidebar() { sidebar.classList.add('open'); overlay.classList.add('open'); }
    function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('open'); }
    sidebarBtn.addEventListener('click', () => sidebar.classList.contains('open') ? closeSidebar() : openSidebar());
    overlay.addEventListener('click', closeSidebar);
})();
</script>
</body>
</html>

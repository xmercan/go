<!DOCTYPE html>
<html lang="tr" id="html-root" class="<?= $_COOKIE['theme'] ?? 'dark' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Admin') ?> — GO! Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:#0A1628;--bg2:#0E1E35;--bg3:#0F2340;--sidebar:#060F1E;
            --card:rgba(255,255,255,.04);--card-h:rgba(255,255,255,.08);--border:rgba(255,255,255,.1);
            --blue:#00D4FF;--blue-dim:rgba(0,212,255,.15);--blue-glow:rgba(0,212,255,.3);
            --green:#22C55E;--orange:#F97316;--red:#EF4444;
            --text:#F0F6FF;--muted:rgba(240,246,255,.55);--input-bg:rgba(255,255,255,.06);
            --sidebar-w:260px;--topbar-h:56px;
        }
        html.light{--bg:#F5F8FC;--bg2:#EDF2F9;--bg3:#E4EBF5;--sidebar:#0A1628;--card:rgba(0,0,0,.03);--card-h:rgba(0,0,0,.06);--border:rgba(0,0,0,.1);--text:#0F172A;--muted:rgba(15,23,42,.55);--input-bg:rgba(0,0,0,.05);}
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
        body{background:var(--bg);color:var(--text);font-family:'Inter',system-ui,sans-serif;font-size:15px;line-height:1.6;-webkit-font-smoothing:antialiased;display:flex;min-height:100vh;}
        .sidebar{width:var(--sidebar-w);background:var(--sidebar);border-right:1px solid rgba(255,255,255,.06);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:100;transition:transform .3s;}
        .sidebar-logo{padding:1.25rem 1.5rem;border-bottom:1px solid rgba(255,255,255,.07);font-size:1.5rem;font-weight:900;color:var(--blue);letter-spacing:-1px;text-decoration:none;display:flex;align-items:center;gap:.5rem;}
        .sidebar-logo span{font-size:.7rem;background:rgba(239,68,68,.2);color:#fca5a5;padding:.15rem .4rem;border-radius:4px;font-weight:700;letter-spacing:.05em;}
        .sidebar-user{padding:.85rem 1.5rem;border-bottom:1px solid rgba(255,255,255,.07);}
        .sidebar-user-name{font-size:.83rem;font-weight:600;color:#fff;}
        .sidebar-user-email{font-size:.72rem;color:rgba(255,255,255,.35);}
        .sidebar-nav{flex:1;padding:.75rem;overflow-y:auto;}
        .nav-label{font-size:.65rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.25);padding:.5rem .75rem .25rem;margin-top:.5rem;}
        .sidebar-nav a{display:flex;align-items:center;gap:.6rem;padding:.55rem .75rem;border-radius:8px;font-size:.85rem;font-weight:500;color:rgba(255,255,255,.55);text-decoration:none;transition:background .2s,color .2s;margin-bottom:.1rem;}
        .sidebar-nav a:hover{background:rgba(255,255,255,.06);color:#fff;}
        .sidebar-nav a.active{background:var(--blue-dim);color:var(--blue);}
        .sidebar-nav a .ni{font-size:.95rem;width:18px;text-align:center;flex-shrink:0;}
        .sidebar-nav a .badge-count{margin-left:auto;background:var(--red);color:#fff;font-size:.65rem;font-weight:700;padding:.1rem .4rem;border-radius:10px;}
        .sidebar-footer{padding:1rem 1.5rem;border-top:1px solid rgba(255,255,255,.07);display:flex;justify-content:space-between;}
        .sidebar-footer a{font-size:.78rem;color:rgba(255,255,255,.35);text-decoration:none;}
        .sidebar-footer a:hover{color:#fff;}
        .admin-main{margin-left:var(--sidebar-w);flex:1;min-height:100vh;display:flex;flex-direction:column;}
        .admin-topbar{background:var(--bg2);border-bottom:1px solid var(--border);padding:0 1.75rem;height:var(--topbar-h);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50;}
        .topbar-title{font-size:.95rem;font-weight:600;}
        .topbar-actions{display:flex;gap:.5rem;align-items:center;}
        .btn-icon{background:var(--card);border:1px solid var(--border);border-radius:8px;padding:.4rem .55rem;cursor:pointer;color:var(--muted);font-size:.9rem;display:flex;align-items:center;transition:color .2s,background .2s;text-decoration:none;}
        .btn-icon:hover{color:var(--text);background:var(--card-h);}
        .admin-content{flex:1;padding:1.75rem;}
        .card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:1.5rem;}
        .card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;}
        .card-title{font-size:.95rem;font-weight:700;}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.6rem 1.2rem;border-radius:8px;font-size:.85rem;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:opacity .2s;white-space:nowrap;}
        .btn-primary{background:var(--blue);color:#0A1628;}
        .btn-primary:hover{opacity:.85;}
        .btn-outline{background:transparent;border:1px solid var(--border);color:var(--text);}
        .btn-outline:hover{background:var(--card-h);}
        .btn-danger{background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5;}
        .btn-success{background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.3);color:#4ade80;}
        .btn-sm{padding:.38rem .75rem;font-size:.78rem;border-radius:6px;}
        .flash{padding:.85rem 1rem;border-radius:10px;margin-bottom:1rem;font-size:.88rem;}
        .flash-error{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);color:#fca5a5;}
        .flash-success{background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.3);color:#86efac;}
        .flash-info{background:rgba(0,212,255,.08);border:1px solid rgba(0,212,255,.2);color:#67e8f9;}
        .flash-warning{background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.3);color:#fde68a;}
        .badge{display:inline-flex;align-items:center;gap:.25rem;padding:.2rem .55rem;border-radius:20px;font-size:.72rem;font-weight:600;}
        .badge-blue{background:var(--blue-dim);color:var(--blue);}
        .badge-green{background:rgba(34,197,94,.15);color:#4ade80;}
        .badge-orange{background:rgba(249,115,22,.15);color:#fb923c;}
        .badge-red{background:rgba(239,68,68,.15);color:#f87171;}
        .badge-gray{background:rgba(255,255,255,.08);color:var(--muted);}
        .form-group{margin-bottom:1rem;}
        label{display:block;font-size:.78rem;color:var(--muted);margin-bottom:.3rem;font-weight:500;}
        input[type="text"],input[type="email"],input[type="password"],input[type="tel"],input[type="number"],input[type="url"],input[type="date"],select,textarea{width:100%;background:var(--input-bg);border:1px solid var(--border);border-radius:8px;padding:.6rem 1rem;color:var(--text);font-size:.88rem;outline:none;font-family:inherit;transition:border-color .2s;}
        input:focus,select:focus,textarea:focus{border-color:var(--blue);}
        textarea{resize:vertical;min-height:80px;}
        .table-wrap{overflow-x:auto;}
        table{width:100%;border-collapse:collapse;font-size:.85rem;}
        th{text-align:left;font-size:.72rem;font-weight:700;color:var(--muted);letter-spacing:.05em;text-transform:uppercase;padding:.6rem 1rem;border-bottom:1px solid var(--border);}
        td{padding:.7rem 1rem;border-bottom:1px solid var(--border);vertical-align:middle;}
        tr:last-child td{border-bottom:none;}
        tr:hover td{background:var(--card-h);}
        .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;}
        .grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem;}
        .grid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:1.25rem;}
        @media(max-width:1100px){.grid-4{grid-template-columns:repeat(2,1fr)}.grid-3{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:768px){.grid-2,.grid-3,.grid-4{grid-template-columns:1fr}}
        .stat-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:1.25rem 1.5rem;}
        .stat-label{font-size:.7rem;color:var(--muted);font-weight:600;letter-spacing:.05em;text-transform:uppercase;}
        .stat-value{font-size:2rem;font-weight:900;margin:.35rem 0;line-height:1;}
        .stat-sub{font-size:.75rem;color:var(--muted);}
        .text-blue{color:var(--blue)}.text-green{color:var(--green)}.text-orange{color:var(--orange)}.text-red{color:var(--red)}
        @media(max-width:768px){.sidebar{transform:translateX(-100%)}.sidebar.open{transform:translateX(0)}.admin-main{margin-left:0}.admin-content{padding:1.25rem 1rem}.admin-topbar{padding:0 1rem}}
    </style>
</head>
<body>

<?php
$admin       = current_admin();
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
function isAdminActive(string $path, string $current): string {
    return str_starts_with($current, $path) ? 'active' : '';
}

// Pending counts
$pendingCounts = [];
try {
    $pdo = \GO\Core\Database::getInstance();
    $rows = $pdo->query("SELECT 'support' as t, COUNT(*) as cnt FROM support_tickets WHERE status='open' AND deleted_at IS NULL
        UNION ALL SELECT 'domain', COUNT(*) FROM domain_requests WHERE kanban_status='pending'
        UNION ALL SELECT 'hosting', COUNT(*) FROM hosting_requests WHERE kanban_status='pending'
        UNION ALL SELECT 'payment', COUNT(*) FROM payment_notifications WHERE status='pending'
        UNION ALL SELECT 'projects', COUNT(*) FROM projects WHERE process_status='queued' AND deleted_at IS NULL")->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($rows as $r) $pendingCounts[$r['t']] = (int)$r['cnt'];
} catch (\Throwable) {}
function pc(string $key): string {
    global $pendingCounts;
    $c = $pendingCounts[$key] ?? 0;
    return $c > 0 ? '<span class="badge-count">' . $c . '</span>' : '';
}
?>

<aside class="sidebar" id="admin-sidebar">
    <a href="/admin/dashboard" class="sidebar-logo">GO! <span>ADMIN</span></a>

    <div class="sidebar-user">
        <div class="sidebar-user-name"><?= e($admin['full_name'] ?? 'Admin') ?></div>
        <div class="sidebar-user-email"><?= e($admin['email'] ?? '') ?></div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-label">Yönetim</div>
        <a href="/admin/dashboard" class="<?= isAdminActive('/admin/dashboard', $currentPath) ?>">
            <span class="ni">📊</span> Dashboard
        </a>
        <a href="/admin/projeler" class="<?= isAdminActive('/admin/projeler', $currentPath) ?>">
            <span class="ni">📁</span> Projeler <?= pc('projects') ?>
        </a>
        <a href="/admin/kullanicilar" class="<?= isAdminActive('/admin/kullanicilar', $currentPath) ?>">
            <span class="ni">👥</span> Kullanıcılar
        </a>

        <div class="nav-label">Operasyonel</div>
        <a href="/admin/kanban" class="<?= isAdminActive('/admin/kanban', $currentPath) ?>">
            <span class="ni">🗂️</span> Kanban
        </a>
        <a href="/admin/destek" class="<?= isAdminActive('/admin/destek', $currentPath) ?>">
            <span class="ni">🎯</span> Destek <?= pc('support') ?>
        </a>
        <a href="/admin/faturalar" class="<?= isAdminActive('/admin/faturalar', $currentPath) ?>">
            <span class="ni">🧾</span> Faturalar <?= pc('payment') ?>
        </a>
        <a href="/admin/domain" class="<?= isAdminActive('/admin/domain', $currentPath) ?>">
            <span class="ni">🌐</span> Domain <?= pc('domain') ?>
        </a>
        <a href="/admin/hosting" class="<?= isAdminActive('/admin/hosting', $currentPath) ?>">
            <span class="ni">🖥️</span> Hosting <?= pc('hosting') ?>
        </a>
        <a href="/admin/yazilim" class="<?= isAdminActive('/admin/yazilim', $currentPath) ?>">
            <span class="ni">💻</span> Yazılım
        </a>

        <div class="nav-label">Sistem</div>
        <a href="/admin/loglar" class="<?= isAdminActive('/admin/loglar', $currentPath) ?>">
            <span class="ni">📋</span> Loglar
        </a>
        <a href="/admin/silinen" class="<?= isAdminActive('/admin/silinen', $currentPath) ?>">
            <span class="ni">🗑️</span> Silinen Kayıtlar
        </a>
        <a href="/admin/ayarlar" class="<?= isAdminActive('/admin/ayarlar', $currentPath) ?>">
            <span class="ni">⚙️</span> Ayarlar
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="/" target="_blank">Ana Sayfa</a>
        <a href="/admin/cikis" style="color:rgba(239,68,68,.7)">Çıkış</a>
    </div>
</aside>

<div class="admin-main">
    <header class="admin-topbar">
        <div style="display:flex;align-items:center;gap:.75rem">
            <button class="btn-icon" id="sidebar-toggle" aria-label="Menü">☰</button>
            <span class="topbar-title"><?= e($title ?? 'Admin Panel') ?></span>
        </div>
        <div class="topbar-actions">
            <button class="btn-icon" id="theme-toggle" title="Tema" aria-label="Tema">
                <span id="theme-icon"><?= ($_COOKIE['theme'] ?? 'dark') === 'dark' ? '☾' : '☀' ?></span>
            </button>
            <a href="/panel" class="btn-icon" title="Müşteri Paneli" aria-label="Müşteri Paneli">🔗</a>
        </div>
    </header>

    <main class="admin-content">
        <?php foreach (get_flashes() as $flash): ?>
        <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endforeach; ?>

        <?= $content ?? '' ?>
    </main>
</div>

<script>
(function(){
    const root=document.getElementById('html-root');
    const icon=document.getElementById('theme-icon');
    document.getElementById('theme-toggle').addEventListener('click',()=>{
        const n=root.className==='dark'?'light':'dark';
        root.className=n;icon.textContent=n==='dark'?'☾':'☀';
        document.cookie='theme='+n+';path=/;max-age=31536000;SameSite=Lax';
    });
    const sidebar=document.getElementById('admin-sidebar');
    document.getElementById('sidebar-toggle').addEventListener('click',()=>sidebar.classList.toggle('open'));
})();
</script>
</body>
</html>

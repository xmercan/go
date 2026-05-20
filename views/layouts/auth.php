<!DOCTYPE html>
<html lang="tr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'GO!') ?> — <?= e(defined('APP_NAME') ? APP_NAME : 'GO.NET.TR') ?></title>
    <meta name="robots" content="noindex, nofollow">

    <!-- Preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:       #0A1628;
            --bg2:      #0E1E35;
            --card:     rgba(255,255,255,.04);
            --border:   rgba(255,255,255,.1);
            --blue:     #00D4FF;
            --blue-dim: rgba(0,212,255,.15);
            --green:    #22C55E;
            --red:      #EF4444;
            --text:     #F0F6FF;
            --muted:    rgba(240,246,255,.55);
            --input-bg: rgba(255,255,255,.06);
        }

        html.light {
            --bg:     #F8FAFC;
            --bg2:    #EFF3F8;
            --card:   rgba(0,0,0,.03);
            --border: rgba(0,0,0,.12);
            --text:   #0F172A;
            --muted:  rgba(15,23,42,.55);
            --input-bg: rgba(0,0,0,.05);
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 440px;
        }

        .auth-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .auth-logo-text {
            font-size: 2.4rem;
            font-weight: 900;
            color: var(--blue);
            letter-spacing: -2px;
            line-height: 1;
        }
        .auth-logo-sub {
            font-size: .8rem;
            color: var(--muted);
            margin-top: .25rem;
        }

        .auth-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2.25rem 2rem;
            backdrop-filter: blur(20px);
        }

        .auth-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 1.75rem;
            text-align: center;
        }

        .form-group { margin-bottom: 1.1rem; }
        label {
            display: block;
            font-size: .8rem;
            color: var(--muted);
            margin-bottom: .35rem;
            font-weight: 500;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"] {
            width: 100%;
            background: var(--input-bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: .75rem 1rem;
            color: var(--text);
            font-size: .95rem;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
            -webkit-appearance: none;
        }
        input:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px var(--blue-dim);
        }
        input::placeholder { color: var(--muted); }

        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: .85rem;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: opacity .2s, transform .1s;
            text-decoration: none;
            gap: .4rem;
        }
        .btn:active { transform: scale(.98); }
        .btn-primary { background: var(--blue); color: #0A1628; }
        .btn-primary:hover { opacity: .88; }

        .auth-links {
            margin-top: 1.25rem;
            text-align: center;
            font-size: .85rem;
            color: var(--muted);
        }
        .auth-links a { color: var(--blue); text-decoration: none; }
        .auth-links a:hover { text-decoration: underline; }

        /* Flash messages */
        .flash { padding: .85rem 1rem; border-radius: 10px; margin-bottom: 1.25rem; font-size: .9rem; line-height: 1.5; }
        .flash-error   { background: rgba(239,68,68,.12);  border: 1px solid rgba(239,68,68,.3);  color: #fca5a5; }
        .flash-success { background: rgba(34,197,94,.12);  border: 1px solid rgba(34,197,94,.3);  color: #86efac; }
        .flash-info    { background: rgba(0,212,255,.08);   border: 1px solid rgba(0,212,255,.2);  color: #67e8f9; }
        .flash-warning { background: rgba(251,191,36,.1);  border: 1px solid rgba(251,191,36,.3); color: #fde68a; }

        .divider {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin: 1.25rem 0;
            color: var(--muted);
            font-size: .8rem;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-top: 1px solid var(--border);
        }

        @media (max-width: 480px) {
            .auth-card { padding: 1.75rem 1.25rem; }
        }
    </style>
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-logo">
        <div class="auth-logo-text">GO!</div>
        <div class="auth-logo-sub">GO.NET.TR — Dijital Dönüşüm Platformu</div>
    </div>

    <div class="auth-card">
        <?php if (isset($title)): ?>
        <div class="auth-title"><?= e($title) ?></div>
        <?php endif; ?>

        <?php foreach (get_flashes() as $flash): ?>
        <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endforeach; ?>

        <?= $content ?? '' ?>
    </div>
</div>
</body>
</html>

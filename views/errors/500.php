<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hata | GO!</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #0A1628;
            color: #fff;
            font-family: system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .container { padding: 2rem; }
        .code { font-size: 6rem; font-weight: 900; color: #EF4444; line-height: 1; }
        h1 { font-size: 1.5rem; margin: 1rem 0 0.5rem; }
        p  { color: rgba(255,255,255,.6); margin-bottom: 2rem; }
        a  {
            display: inline-block;
            padding: .75rem 2rem;
            background: #00D4FF;
            color: #0A1628;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="code">500</div>
        <h1>Bir Hata Oluştu</h1>
        <p>GO! ekibimiz bilgilendirildi. Lütfen daha sonra tekrar deneyin.</p>
        <a href="<?= defined('APP_URL') ? APP_URL : '/' ?>">Ana Sayfaya Dön</a>
    </div>
</body>
</html>

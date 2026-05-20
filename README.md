<<<<<<< HEAD
# go
go.net.tr
=======
# GO! V1 — Dijital Dönüşüm Platformu

**GO.NET.TR — GO!** KOBİ'lerin dijital dönüşüm süreçlerini yapay zeka destekli danışmanlık, web, domain, hosting ve yazılım hizmetleriyle tek platformdan yöneten SaaS çözümüdür.

---

## Gereksinimler

| Bileşen | Minimum |
|---------|---------|
| PHP | 8.1+ |
| MySQL | 5.7+ / MariaDB 10.4+ |
| Extensions | PDO, PDO_MySQL, mbstring, json, curl, openssl, zip |
| Web Server | Apache (mod_rewrite) |
| Panel | cPanel uyumlu |

> Composer ve Node.js gerektirmez.

---

## Kurulum

### 1. Dosyaları yükleyin

```bash
# FTP veya cPanel Dosya Yöneticisi ile root dizinine yükleyin
```

### 2. .env dosyasını oluşturun

```bash
cp .env.example .env
```

### 3. Kurulum sihirbazını çalıştırın

```
https://yourdomain.com/install/
```

Sihirbaz 6 adımda:
1. Sistem gereksinimlerini kontrol eder
2. Veritabanı bağlantısını test eder
3. `GoV1.sql` dosyasını içe aktarır
4. Süper admin hesabı oluşturur
5. Site ayarlarını ve SMTP'yi yapılandırır
6. Kurulumu kilitler (`storage/installed.lock`)

---

## Klasör Yapısı

```
/
├── cli/               CLI araçları (worker, update)
├── controllers/       MVC Controller'ları
│   ├── Admin/
│   └── Customer/
├── core/              Çekirdek sınıflar
├── database/          SQL şeması + migration'lar
├── events/            Event dispatcher
├── helpers/           Yardımcı fonksiyonlar
├── install/           Kurulum sihirbazı
├── integrations/      AI, Storage, Domain, Payment arayüzleri
├── jobs/              Queue job'ları
├── models/            Model sınıfları
├── repositories/      Repository Contract'ları
├── routes/            Route tanımları
├── services/          Servis katmanı
├── storage/           Önbellek, log, export
├── uploads/           Kullanıcı yüklemeleri
└── views/             Tüm view'lar (layout + components)
    ├── admin/
    ├── customer/
    ├── landing/
    ├── layouts/
    └── errors/
```

---

## CLI Araçları

### Queue Worker (Cron ile çalıştırın)

```bash
# Cron: Her dakika
* * * * * php /path/to/site/cli/worker.php >> /path/to/site/storage/logs/worker.log 2>&1
```

### Update / Migration

```bash
# Versiyon kontrol
php cli/update.php --check

# Migration listesi
php cli/update.php --list

# Güncelleme uygula
php cli/update.php --version=1.0.1
```

---

## Güvenlik Notları

- `.env`, `config.php`, `core/`, `storage/` dizinleri `.htaccess` ile web erişimine kapalıdır
- `uploads/` dizininde PHP çalıştırılması engellenmiştir
- CSRF koruması tüm POST formlarında aktiftir
- XSS koruması için `e()`, `e_attr()` helper fonksiyonları kullanılmaktadır
- SQL Injection koruması için PDO prepared statements zorunludur
- `password_hash(PASSWORD_DEFAULT)` ile şifre güvenliği
- Rate limiting: giriş, şifre sıfırlama, chat
- Audit trail: kritik admin aksiyonları imzalı loglanır

---

## Önemli URL'ler

| Sayfa | URL |
|-------|-----|
| Ana Sayfa | `/` |
| Müşteri Girişi | `/giris` |
| Müşteri Paneli | `/panel` |
| GO! Chat | `/chat` |
| Admin Girişi | `/admin/giris` |
| Admin Dashboard | `/admin/dashboard` |
| Kurulum | `/install/` |

---

## Geliştirme Notları

- Tüm PHP dosyaları `declare(strict_types=1)` ile başlamalıdır (controller/model)
- View'lar `$layout = 'layouts/layout-adi';` ile layout seçer
- Helper'lar `config.php` aracılığıyla otomatik yüklenir
- `GO\Core\BaseModel::generateUuid()` ile UUID üretilir
- Debug modu: `.env` dosyasında `APP_DEBUG=true` yapın

---

## Lisans

© 2025 Genç Grup Yazılım Ltd. Şti. — Tüm hakları saklıdır.

Ticari lisanslı yazılım. İzinsiz kopyalanamaz, dağıtılamaz.
>>>>>>> e46bba1 (Fix 500 errors, add cPanel deploy and secure update system)

# GO! V1 — Changelog

## v1.0.0-alpha (2025)

**Aşama 1: Temel Mimari**
- MVC-like klasör yapısı oluşturuldu
- `core/` — App, Router, Database, BaseController, BaseModel, Env
- `helpers/` — escape, csrf, flash, validation, auth
- `services/` — Cache, FeatureFlag, Queue, Audit, RateLimit, Storage, Mail, Notification
- `integrations/` — Storage (Local, S3), AI (Null, Interface), Domain, Payment
- `events/` — EventDispatcher, listeners
- `jobs/` — BaseJob, SendEmailJob, ProcessExportJob
- `repositories/Contracts/` — ProjectRepositoryInterface, UserRepositoryInterface
- `.env.example`, `config.php`, `.htaccess`, `index.php` oluşturuldu
- `rules.md`, `MASTER_CONTEXT.md`, `CHANGELOG.md`, `TODO.md` oluşturuldu

**Aşama 2: Veritabanı + Install**
- `database/GoV1.sql` — 34 tablo + seed data (sektörler, ayarlar, feature flags)
- `install/` — 6 adımlı kurulum sihirbazı
  - Adım 1: Sistem gereksinimleri kontrolü
  - Adım 2: Veritabanı bağlantı testi
  - Adım 3: SQL içe aktarma
  - Adım 4: Süper admin oluşturma
  - Adım 5: SMTP + site ayarları, .env üretimi
  - Adım 6: installed.lock ile kilitleme

**Aşama 3: Auth Sistemi**
- `UserModel`, `AdminModel`, `PasswordResetModel`, `LoginLogModel`
- Müşteri auth: giriş, kayıt, çıkış, şifre sıfırlama (e-posta tabanlı)
- Admin auth: güvenli giriş, rate limiting, login log
- Auth layout (`views/layouts/auth.php`) — dark/light, mobil uyumlu
- CSRF koruması tüm formlarda aktif

**Aşama 4: Landing Sayfası**
- `views/layouts/app.php` — Navigation, footer, dark/light tema, mobil menü
- `views/landing/home.php` — Hero, nasıl çalışır, sektörler, özellikler, CTA
- `LandingController` — home, sector detail, KVKK, iletişim, blog
- SEO: meta tags, OG, canonical, Twitter card
- JS scroll reveal animasyonları

**Aşama 5: GO! Chat State Machine**
- `ChatStateMachine` — 13 state, kural tabanlı (AI-free V1)
- `ChatMessageModel`, `ProjectModel`
- `ChatController` — index, project, send (AJAX), history
- `views/customer/chat/index.php` — modern chat UI, quick replies, typing indicator

**Aşama 6: Müşteri Paneli**
- `views/layouts/panel.php` — sidebar, topbar, mobile uyum
- `PanelController` — dashboard, settings
- `ProjectController` — index, show, export (ZIP)
- GO! Web dosya yönetimi + ZIP export
- Proje timeline (project_activities)
- Dashboard: stat cards, son projeler, CTA

**Aşama 7: Domain/Hosting/Yazılım**
- `DomainRequestModel`, `HostingRequestModel`, `SoftwareRequestModel`
- `DomainController`, `HostingController`, `SoftwareController`
- Müşteri panel view'ları (domain/index, hosting/index)
- QueueService entegrasyonu (admin bildirim kuyruğu)

**Aşama 8: Fatura & Ödeme**
- `InvoiceModel` — fatura oluşturma, listeleme, UUID bazlı erişim
- `InvoiceController` — görüntüleme, ödeme bildirimi
- IBAN kopyalama, online link, banka transferi
- `payment_notifications` Kanban entegrasyonu

**Aşama 9: Destek & E-posta**
- `SupportTicketModel`, `SupportController`
- Ticket oluşturma, görüntüleme, müşteri yanıtı
- `MailService` — raw SMTP socket implementasyonu (PHPMailer gerektirmez)
- E-posta şablonları: welcome, password_reset, ticket_created, invoice_paid
- `email_logs` tablosuna kayıt

**Aşama 10: Admin Panel**
- `views/layouts/admin.php` — Sidebar + pending count badge'leri
- `DashboardController` — istatistikler, son projeler, bekleyen ödemeler
- `KanbanController` — 5 sütunlu Kanban (drag & drop), 5 tip
- `SettingsController` — Site, SMTP, Feature Flags tab yönetimi
- `LogController` — activity, login, audit, email log görüntüleyicisi
- Silinen kayıtlar görüntüleyicisi (snapshot modal)

**Aşama 11: Update + README**
- `cli/update.php` — versiyon kontrol, migration uygulama
- `README.md` — kurulum, klasör yapısı, CLI, güvenlik notları
- `routes/web.php` — tüm rotalar eksiksiz tanımlandı
- Helper fonksiyonları tamamlandı (`get_flashes`, `current_user`, `old`, vb.)

---

## Bilinen Eksiklikler (V1 Scope-Out)

- AI provider gerçek entegrasyonu (V1.1+)
- Admin invoice CRUD (view stub — geliştirme bekliyor)
- Admin support/project detail views (stub)
- E-posta gönderimi cPanel SMTP ile doğrulama gerektiriyor
- Backup modülü (V1.1)
- 2FA (V2)
- Public API endpoint'leri (V2)

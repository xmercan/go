# GO! V1 — Kalıcı Geliştirme Kuralları

Bu dosya projenin tüm geliştirme süreçlerinde bağlayıcıdır.
Her oturumda referans alınmalı, ihlal edilmemelidir.

---

## Teknoloji Kuralları

- Laravel **kullanılmayacak**
- Composer **zorunlu olmayacak**
- Node.js **zorunlu olmayacak**
- Plain PHP 8.1+ + MySQL / MariaDB kullanılacak
- cPanel paylaşımlı hosting uyumlu olacak
- Framework yükü **olmayacak**

---

## Mimari Kurallar

- MVC benzeri katmanlı mimari kullanılacak (`/controllers`, `/models`, `/views`)
- SQL sorguları **doğrudan view dosyalarında yazılmayacak**
- SQL sorguları yalnızca **Model** katmanında olacak
- İş mantığı **Controller** veya **Service** içinde olacak
- HTML çıktı yalnızca **View** içinde olacak
- Ortak helper fonksiyonlar `/helpers` klasöründe olacak
- Tekrar eden business logic **Service** katmanına taşınacak
- Controller metodları maksimum ~30 satır iş mantığı içerecek
- Repository interface'leri soyutlama için hazır tutulacak

---

## Güvenlik Kuralları

- PDO prepared statement **zorunlu** — ham SQL string interpolasyonu yasak
- Şifreler asla düz metin tutulmayacak — `password_hash(PASSWORD_DEFAULT)` kullanılacak
- E-posta ile şifre **asla gönderilmeyecek**
- Her POST/AJAX formunda **CSRF token** zorunlu
- Tüm çıktılarda `e()` escape helper kullanılacak — ham `echo $_` yasak
- Session: `session_regenerate_id(true)` her girişte
- Session cookie: HttpOnly, Secure (üretimde), SameSite=Lax
- Upload: MIME + uzantı whitelist, random isim, PHP execution kapalı
- `.env` dosyası asla web'e açık olmayacak
- Sandbox path traversal için `realpath()` kontrolü zorunlu
- Soft delete kullanılacak — kayıtlar fiziksel olarak silinmeyecek
- Kritik işlemlerde `AuditService` çağrısı zorunlu

---

## Tasarım Kuralları

- Responsive ve **mobil-first** tasarım zorunlu
- Dark / light mode desteklenecek (CSS variables + localStorage)
- SEO temel yapısı (title, description, OG) her sayfada hazır olacak
- Reusable UI component sistemi kullanılacak (badge, toast, modal, skeleton)
- Tablo yerine kart düzeni mobilde zorunlu

---

## Kod Kalite Kuralları

- Kod tekrarından kaçınılacak — DRY prensibi
- Anlamlı değişken ve metod isimleri kullanılacak
- Kısa, tek sorumluluğu olan metodlar yazılacak
- View içinde uzun PHP blokları olmayacak
- Yorum satırı: yalnızca karmaşık iş mantığı için, `// ne yapıyor` değil `// neden yapıyor`

---

## Süreç Kuralları

- Her büyük değişiklikten önce kısa plan çıkarılacak
- Kod **silinmeden önce** neden silindiği açıklanacak
- Her modül tamamlandıktan sonra **test notu** yazılacak
- Proje **tek seferde devasa yazılmayacak** — 11 aşamalı modüler ilerleme
- Bir aşama bitmeden sonraki aşamaya **kullanıcı onayı olmadan geçilmeyecek**
- Her aşama bitişinde CHANGELOG.md ve TODO.md güncellenecek

---

## Dosya Silme / Değiştirme Kuralları

- Dosya silinmeden önce: CHANGELOG'da neden silindiği yazılacak
- Kritik dosya refactor'ı: önce plan, sonra uygulama
- Config, SQL, .env değişiklikleri: README'de belgelenmeli

---

## V2/V3 Hazırlık Kuralları

- Service metodları HTTP'den bağımsız olacak (API-ready)
- Integration interface'ler soyut tutulacak (AI, storage, payment, domain)
- Feature flag olmayan yeni modül açılmayacak (admin'den kontrol)
- Event sistemi üzerinden yan etkiler tetiklenecek (mail, log, notification)
- `APP_MODE` davranışları kodun her yerine dağıtılmayacak — `App::isProduction()` helper

---

*Son güncelleme: v1.0.0 — Aşama 1*

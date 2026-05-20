# GO.NET.TR — MASTER CONTEXT

Bu dosya projenin tüm bağlamını ve vizyonunu özetler.
Her oturumda referans noktasıdır. Kod yazmadan önce okunmalıdır.

---

## 1. Proje Kimliği

| Alan | Değer |
|------|-------|
| Platform adı | GO! |
| Domain | go.net.tr |
| Şirket | Genç Grup Yazılım Ltd. Şti. |
| Vergi No | 3931274985 |
| İletişim | +90 541 788 54 32 |
| E-posta | yatirim@go.net.tr |
| Marka ajanı | XMERCAN Dijital Ajans |
| Altyapı ortağı | Genç Host Bilişim Hizmetleri |
| BTK kaydı | Yer sağlayıcı lisansı aktif |

---

## 2. Kurucu Hikayesi

**Ömer Can ÖZDOĞANCI** — 1 Mayıs 2020'de, COVID-19 salgını nedeniyle tüm işletmeler kapanırken Isparta'da XMERCAN Dijital Ajans'ı kurdu. Güzel Sanatlar Resim Lisesi mezunu, Anadolu Üniversitesi Web Tasarım & Kodlama öğrencisi. Tekstil e-ticaret girişimciliği, Süleyman Demirel Üniversitesi İşletme deneyimi. KOSGEB Genç Girişimci destekçisi.

GO! projesi: "Yapay zekalar fikir veriyor ama domain tescil edemiyor, hosting açamıyor, kodu canlıya alamıyor" tespitinden doğdu.

---

## 3. Marka Vizyonu

- **GO** → Hızlı hareket, icraat, aksiyoner AI
- **NET** → İnternet hizmetleri entegrasyonu
- **TR** → Yerli ve milli başlangıç; global büyüme vizyonu

**Ana slogan:** "Dijital dönüşümde tavsiye veren değil, icraat yapan AI."

**Alt metin:** "GO!, işletmenizi marka fikrinden domain seçimine, hosting altyapısından web sitesi taslağına, sosyal medya içeriklerinden ajans desteğine kadar tek platformda dijital dönüşüme hazırlar."

---

## 4. Platform Konumlandırması

GO! = **Dijital dönüşüm işletim sistemi** — genel chatbot değil.

| Modül | Rol |
|-------|-----|
| CRM | Müşteri yönetimi, profil, aktivite |
| AI Onboarding | GO! Chat + proje hafızası |
| Proje Yönetimi | Projelerim, timeline, ZIP export |
| Domain Operasyonu | Manuel kuyruk + Kanban |
| Hosting Operasyonu | Manuel kuyruk + Kanban |
| Teklif/Fatura | Fatura, ödeme bildirimi, IBAN/link |
| Ajans Görev Sistemi | Admin Kanban + GO! Web teslim |
| Destek Sistemi | Ticket + cevap + dosya eki |

---

## 5. Etik ve İş Akışı Dili

### Kullanılacak ifadeler
- "GO! işleminizi aldı."
- "GO! uzman onaylı işlem kuyruğuna aktardı."
- "GO! destek ekibi işlemi kontrol ediyor."
- "İşlem tamamlandığında size e-posta ile bilgi verilecek."

### Kesinlikle kullanılmayacak ifadeler
- "Otomatik tamamlandı."
- "Sistem anında oluşturdu."
- "Yapay zeka halletti."
- "Domain/hosting otomatik açıldı."

---

## 6. V1 — Yarı Otomatik / Uzman Onaylı Mantık

V1'de **tüm operasyonel işlemler admin onaylıdır:**

| İşlem | V1 gerçek mekanizma |
|-------|---------------------|
| Domain transfer kodu | Admin manuel girer |
| NS/DNS değişikliği | Admin manuel işler |
| Hosting açılışı | Admin manuel yapar |
| FTP/cPanel bilgisi | Admin manuel gönderir |
| Yazılım kurulumu | Admin yapar, sonuç yazar |
| Ödeme onayı | Admin kontrol eder, onaylar |
| Fatura oluşturma | Admin oluşturur |
| GO! Web taslak | Admin kodları yükler |

Kullanıcı deneyiminde bu işlemler "sistem tarafından işleme alındı" diliyle sunulur.

---

## 7. V1 Teknik Sınırları

| Alan | V1 |
|------|-----|
| Domain registrar API | YOK — manuel |
| Hosting/WHM API | YOK — manuel |
| Ödeme API (iyzico/PayTR) | YOK — link/IBAN + onay |
| GO! Chat AI (LLM) | YOK — state machine |
| WebSocket | YOK — fetch poll |
| S3/cloud storage | YOK — local only |
| Redis | YOK — dosya cache |
| Public API | YOK — internal JSON |
| Otomatik backup | YOK — manuel tetik |

---

## 8. GO! Chat V1 Mantığı

GO! Chat = **durum makinesi (state machine)** + kural tabanlı soru akışı.

- Harici AI API olmadan tam stabil çalışır
- `ai_enabled=0` varsayılan
- Her cevap `chat_messages` + `project_answers` + `ai_context` JSON'a yazılır
- Kullanıcı geri geldiğinde bağlam devam eder (memory)
- V2: `AiProviderInterface` impl ile LLM takılır

---

## 9. V2/V3 Geliştirme Yolu

| Versiyon | Hedef |
|---------|-------|
| V1.1 | Kanban polish, notification tam sayfa, test, audit CSV |
| V2 | OpenAI/yerli AI, domain registrar API, iyzico webhook, S3, Redis, public API |
| V3 | GO! Agent, fırsat tarayıcı, WebSocket, multi-tenant |

**Modüler genişleme:** `integrations/` klasörü — `AiProviderInterface`, `StorageInterface`, `PaymentProviderInterface`, `DomainProviderInterface`.

---

## 10. Tasarım Dili

| Element | Değer |
|---------|-------|
| Arkaplan | `#0A1628` (koyu lacivert) |
| Ana metin | `#FFFFFF` |
| Vurgu — elektrik mavi | `#00D4FF` |
| Başarı | `#22C55E` |
| Aksiyon / uyarı | `#F97316` |
| Hata | `#EF4444` |
| Kart arka planı | `rgba(255,255,255,0.05)` glassmorphism |
| Gölge | `0 4px 24px rgba(0,212,255,0.08)` |

Tipografi: `Inter`, `system-ui`, `sans-serif` — harici CDN opsiyonel.

Stil: Modern SaaS panel, glassmorphism kartlar, soft gölgeler, mobil hamburger / masaüstü sol sidebar.

---

## 11. Güvenlik Özeti

- `password_hash(PASSWORD_DEFAULT)` — şifre asla düz metin
- PDO prepared statement — SQL injection yok
- `htmlspecialchars()` + `e()` helper — XSS yok
- CSRF token — her POST/AJAX formunda
- `session_regenerate_id(true)` — her girişte
- Sandbox `realpath` kontrolü — path traversal yok
- `.env` web erişimine kapalı
- `audit_trail` — kritik işlemler immutable
- Soft delete — `deleted_at` + `deleted_records`

---

## 12. Kullanıcı Rolleri

| Rol | Oturum | Erişim |
|-----|--------|--------|
| Ziyaretçi | Yok | Landing, GO! Chat kayıt |
| Müşteri | `$_SESSION['customer']` | `/customer/*` |
| Admin | `$_SESSION['admin']` | `/admin/*` |
| Kurulum | Geçici | `/install/*` (sadece `installed=0`) |

---

## 13. Modül Amaç Özeti

| Modül | Müşteri amacı | Admin amacı |
|-------|---------------|-------------|
| GO! Chat | Kayıt + proje onboarding | Chat geçmişi görme |
| Projelerim | CRUD + detay + timeline | Tüm projeler + kuyruk |
| GO! Web | Kodları gör/kopyala/önizle | Kod ekle, sürüm not |
| Alan Adlarım | NS/DNS/transfer talep | Manuel işlem + Kanban |
| Hosting | FTP/cPanel/yedek talep | Manuel işlem + Kanban |
| Yazılımlar | Lisans, kurulum/güncelleme | Manuel işlem |
| Faturalar | Görüntüle, ödeme yap, bildir | Oluştur, onayla |
| Destek | Ticket aç, takip et | Cevapla, bağla, kapat |

---

*Son güncelleme: v1.0.0 — Aşama 1*

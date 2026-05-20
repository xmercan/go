# GO! V1 — Güvenli Güncelleme Sistemi

Bu klasör, uygulama güncellemelerini (veritabanı şeması değişiklikleri ve PHP migration betikleri) içerir.

## Dosya Adlandırma Kuralı

Güncellemeler alfabetik sırayla uygulandığı için dosya adları sürüm numarasıyla başlamalıdır:

```
v1_0_1.sql      # SQL değişiklikleri (ALTER TABLE, INSERT, vb.)
v1_0_1.php      # PHP mantık değişiklikleri (veri dönüşümleri, vb.)
v1_1_0.sql
v1_1_0.php
```

**Önemli:** Her sürüm için `.sql` veya `.php` dosyasından en az birini oluşturun.

## Kullanım

```bash
# Mevcut DB versiyonunu kontrol et
php cli/update.php --check

# Uygulanmış migration'ları listele
php cli/update.php --list

# Bekleyen tüm migration'ları uygula
php cli/update.php --run
```

## Güncelleme Dosyası Yazma Kuralları

### SQL Güncellemesi (`vX_Y_Z.sql`)

- `IF NOT EXISTS` ve `IF EXISTS` kullanın (idempotent olsun)
- Her zaman geri alınabilir değişiklikler tercih edin
- Büyük tablo değişikliklerinden önce yorum satırı ekleyin

```sql
-- GO! v1.0.1 — Kullanıcılara telefon kolonu eklendi
ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20) NULL AFTER email;
```

### PHP Migration (`vX_Y_Z.php`)

- `$pdo` değişkeni inject edilmiş olarak gelir
- Exception fırlatırsanız migration durur
- `true` döndürmeniz gerekir

```php
<?php
// GO! v1.0.1 — Mevcut kullanıcı verilerini güncelle
$stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE status IS NULL");
$stmt->execute();
return true;
```

## Güvenlik

- Bu klasör `.htaccess` ile dışarıdan erişime kapalıdır
- Her güncelleme öncesi otomatik DB yedeği alınır (`storage/backups/`)
- Uygulanmış migration'lar `schema_migrations` tablosunda saklanır ve tekrar uygulanmaz

-- GO! v1.0.1 — Örnek güncelleme dosyası
-- Bu dosya, gelecekteki DB şema değişikliklerinin nasıl ekleneceğini gösterir.
-- Gerçek bir değişiklik yapmaz (yorum satırı olduğu için güvenlidir).

-- Örnek: Kullanıcı tablosuna telefon kolonu ekle
-- ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20) NULL AFTER email;

-- Örnek: Yeni bir settings kaydı ekle
-- INSERT IGNORE INTO settings (setting_key, setting_value, group_name, created_at)
-- VALUES ('new_feature_enabled', '0', 'feature', NOW());

-- Sürüm kaydı (cli/update.php tarafından otomatik eklenir, burada gerekmez)

-- ============================================================
-- GO.NET.TR — GO! V1 Veritabanı Şeması
-- Versiyon: 1.0.0
-- Karakter seti: utf8mb4_unicode_ci
-- Engine: InnoDB
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- ─── 1. settings ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `settings` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key`   VARCHAR(100) NOT NULL,
  `setting_value` TEXT,
  `group_name`    VARCHAR(50)  NOT NULL DEFAULT 'site',
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_setting_key` (`setting_key`),
  KEY `idx_group` (`group_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 2. smtp_settings ────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `smtp_settings` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `host`        VARCHAR(255) NOT NULL DEFAULT '',
  `port`        SMALLINT UNSIGNED NOT NULL DEFAULT 587,
  `encryption`  ENUM('tls','ssl','none') NOT NULL DEFAULT 'tls',
  `username`    VARCHAR(255) NOT NULL DEFAULT '',
  `password`    TEXT,
  `from_email`  VARCHAR(255) NOT NULL DEFAULT '',
  `from_name`   VARCHAR(150) NOT NULL DEFAULT 'GO!',
  `is_active`   TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 3. sectors ──────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `sectors` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`        VARCHAR(100) NOT NULL,
  `name`        VARCHAR(150) NOT NULL,
  `icon`        VARCHAR(50)  NOT NULL DEFAULT 'briefcase',
  `description` TEXT,
  `sort_order`  SMALLINT     NOT NULL DEFAULT 0,
  `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sector_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 4. sector_templates ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `sector_templates` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sector_id`   INT UNSIGNED NOT NULL,
  `title`       VARCHAR(255) NOT NULL,
  `features`    JSON,
  `sample_copy` TEXT,
  `cta_label`   VARCHAR(100) NOT NULL DEFAULT 'Bu Sektörü İncele',
  `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sector_id` (`sector_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 5. admins ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `admins` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name`     VARCHAR(150) NOT NULL,
  `email`         VARCHAR(191) NOT NULL,
  `phone`         VARCHAR(20)  NOT NULL DEFAULT '',
  `password`      VARCHAR(255) NOT NULL,
  `role`          ENUM('super_admin','admin') NOT NULL DEFAULT 'admin',
  `status`        ENUM('active','suspended') NOT NULL DEFAULT 'active',
  `last_login_at` DATETIME,
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`    DATETIME,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admin_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 6. users ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
  `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`                CHAR(36)     NOT NULL,
  `full_name`           VARCHAR(150) NOT NULL,
  `email`               VARCHAR(191) NOT NULL,
  `phone`               VARCHAR(20)  NOT NULL DEFAULT '',
  `password`            VARCHAR(255) NOT NULL,
  `email_verified_at`   DATETIME,
  `status`              ENUM('active','suspended') NOT NULL DEFAULT 'active',
  `last_login_at`       DATETIME,
  `created_at`          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`          DATETIME,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_email` (`email`),
  UNIQUE KEY `uq_user_uuid`  (`uuid`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 7. password_resets ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email`      VARCHAR(191) NOT NULL,
  `token`      VARCHAR(100) NOT NULL,
  `expires_at` DATETIME     NOT NULL,
  `used_at`    DATETIME,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_token` (`token`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 8. projects ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `projects` (
  `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`                CHAR(36)     NOT NULL,
  `user_id`             INT UNSIGNED NOT NULL,
  `sector_id`           INT UNSIGNED,
  `name`                VARCHAR(255) NOT NULL DEFAULT '',
  `description`         TEXT,
  `target_audience`     TEXT,
  `brand_type`          ENUM('new','existing') NOT NULL DEFAULT 'new',
  `domain_ideas`        JSON,
  `has_domain`          TINYINT(1)   NOT NULL DEFAULT 0,
  `need_website`        TINYINT(1)   NOT NULL DEFAULT 1,
  `traffic_estimate`    VARCHAR(100) NOT NULL DEFAULT '',
  `hosting_need`        VARCHAR(255) NOT NULL DEFAULT '',
  `social_media_needs`  TEXT,
  `branding_needs`      TEXT,
  `special_modules`     JSON,
  `budget_range`        VARCHAR(100) NOT NULL DEFAULT '',
  `urgency`             VARCHAR(100) NOT NULL DEFAULT '',
  `process_status`      ENUM('draft','queued','reviewing','in_progress','completed','cancelled') NOT NULL DEFAULT 'draft',
  `quote_status`        ENUM('none','preparing','sent','accepted','rejected') NOT NULL DEFAULT 'none',
  `payment_status`      ENUM('none','pending','partial','paid') NOT NULL DEFAULT 'none',
  `chat_completed_at`   DATETIME,
  `ai_summary`          TEXT,
  `important_notes`     TEXT,
  `ai_context`          JSON,
  `last_ai_analysis`    DATETIME,
  `created_at`          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`          DATETIME,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_project_uuid` (`uuid`),
  KEY `idx_user_id`   (`user_id`),
  KEY `idx_process`   (`process_status`),
  KEY `idx_sector_id` (`sector_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 9. project_answers ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `project_answers` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id`    INT UNSIGNED NOT NULL,
  `question_key`  VARCHAR(100) NOT NULL,
  `question_text` TEXT,
  `answer_text`   TEXT,
  `source`        ENUM('chat','panel') NOT NULL DEFAULT 'chat',
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_project_id`   (`project_id`),
  KEY `idx_question_key` (`question_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 10. project_activities (timeline) ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS `project_activities` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id`     INT UNSIGNED NOT NULL,
  `user_id`        INT UNSIGNED,
  `admin_id`       INT UNSIGNED,
  `activity_type`  VARCHAR(50)  NOT NULL,
  `title`          VARCHAR(255) NOT NULL,
  `description`    TEXT,
  `meta`           JSON,
  `icon`           VARCHAR(30)  NOT NULL DEFAULT 'circle',
  `color`          VARCHAR(20)  NOT NULL DEFAULT 'blue',
  `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_project_id` (`project_id`),
  KEY `idx_type`       (`activity_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 11. project_export_logs ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `project_export_logs` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`        CHAR(36)     NOT NULL,
  `project_id`  INT UNSIGNED NOT NULL,
  `user_id`     INT UNSIGNED NOT NULL,
  `exported_by` ENUM('user','admin') NOT NULL DEFAULT 'user',
  `file_count`  INT UNSIGNED NOT NULL DEFAULT 0,
  `zip_size`    BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `ip_address`  VARCHAR(45)  NOT NULL DEFAULT '',
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_export_uuid` (`uuid`),
  KEY `idx_project_id` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 12. chat_messages ───────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED,
  `project_id`  INT UNSIGNED,
  `session_id`  VARCHAR(100) NOT NULL DEFAULT '',
  `role`        ENUM('system','user','admin') NOT NULL DEFAULT 'user',
  `message`     TEXT         NOT NULL,
  `state_key`   VARCHAR(100) NOT NULL DEFAULT '',
  `metadata`    JSON,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id`    (`user_id`),
  KEY `idx_project_id` (`project_id`),
  KEY `idx_session`    (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 13. go_web_files ────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `go_web_files` (
  `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id`         INT UNSIGNED NOT NULL,
  `version`            VARCHAR(20)  NOT NULL DEFAULT 'v1',
  `file_name`          VARCHAR(255) NOT NULL,
  `file_path`          VARCHAR(500) NOT NULL,
  `file_type`          ENUM('html','css','js','php','json','txt','other') NOT NULL DEFAULT 'html',
  `content`            LONGTEXT,
  `is_previewable`     TINYINT(1)   NOT NULL DEFAULT 1,
  `admin_note`         TEXT,
  `created_by_admin_id` INT UNSIGNED,
  `created_at`         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`         DATETIME,
  PRIMARY KEY (`id`),
  KEY `idx_project_id` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 14. domains ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `domains` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`      INT UNSIGNED NOT NULL,
  `project_id`   INT UNSIGNED,
  `domain_name`  VARCHAR(255) NOT NULL,
  `registrar`    VARCHAR(100) NOT NULL DEFAULT '',
  `registered_at` DATE,
  `expires_at`   DATE,
  `nameservers`  JSON,
  `status`       ENUM('active','pending','expired','transferred') NOT NULL DEFAULT 'pending',
  `created_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`   DATETIME,
  PRIMARY KEY (`id`),
  KEY `idx_user_id`    (`user_id`),
  KEY `idx_project_id` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 15. domain_requests ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `domain_requests` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `domain_id`      INT UNSIGNED,
  `user_id`        INT UNSIGNED NOT NULL,
  `project_id`     INT UNSIGNED,
  `request_type`   ENUM('ns_change','dns_add','dns_delete','transfer_code','internal_transfer','renewal','other') NOT NULL,
  `payload`        JSON,
  `status`         ENUM('pending','reviewing','in_progress','completed','rejected') NOT NULL DEFAULT 'pending',
  `kanban_status`  ENUM('pending','reviewing','in_progress','completed','rejected') NOT NULL DEFAULT 'pending',
  `priority`       ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  `sort_order`     INT NOT NULL DEFAULT 0,
  `admin_note`     TEXT,
  `user_note`      TEXT,
  `resolved_by`    INT UNSIGNED,
  `resolved_at`    DATETIME,
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id`       (`user_id`),
  KEY `idx_domain_id`     (`domain_id`),
  KEY `idx_kanban_status` (`kanban_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 16. hosting_services ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `hosting_services` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`       INT UNSIGNED NOT NULL,
  `project_id`    INT UNSIGNED,
  `package_name`  VARCHAR(100) NOT NULL DEFAULT '',
  `disk_quota`    VARCHAR(50)  NOT NULL DEFAULT '',
  `bandwidth`     VARCHAR(50)  NOT NULL DEFAULT '',
  `cpanel_url`    VARCHAR(255) NOT NULL DEFAULT '',
  `ftp_host`      VARCHAR(255) NOT NULL DEFAULT '',
  `status`        ENUM('active','suspended','expired') NOT NULL DEFAULT 'active',
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`    DATETIME,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 17. hosting_requests ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `hosting_requests` (
  `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `hosting_service_id` INT UNSIGNED,
  `user_id`            INT UNSIGNED NOT NULL,
  `project_id`         INT UNSIGNED,
  `request_type`       ENUM('ftp_info','cpanel_info','backup','upgrade','other') NOT NULL,
  `message`            TEXT,
  `status`             ENUM('pending','reviewing','in_progress','completed','rejected') NOT NULL DEFAULT 'pending',
  `kanban_status`      ENUM('pending','reviewing','in_progress','completed','rejected') NOT NULL DEFAULT 'pending',
  `priority`           ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  `sort_order`         INT NOT NULL DEFAULT 0,
  `admin_response`     JSON,
  `resolved_by`        INT UNSIGNED,
  `resolved_at`        DATETIME,
  `created_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id`       (`user_id`),
  KEY `idx_kanban_status` (`kanban_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 18. software_services ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `software_services` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`      INT UNSIGNED NOT NULL,
  `project_id`   INT UNSIGNED,
  `product_name` VARCHAR(255) NOT NULL,
  `license_key`  TEXT,
  `status`       ENUM('active','expired','suspended') NOT NULL DEFAULT 'active',
  `installed_at` DATETIME,
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`   DATETIME,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 19. software_requests ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `software_requests` (
  `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `software_service_id` INT UNSIGNED,
  `user_id`             INT UNSIGNED NOT NULL,
  `project_id`          INT UNSIGNED,
  `request_type`        ENUM('install','update','support','other') NOT NULL,
  `message`             TEXT,
  `status`              ENUM('pending','reviewing','in_progress','completed','rejected') NOT NULL DEFAULT 'pending',
  `kanban_status`       ENUM('pending','reviewing','in_progress','completed','rejected') NOT NULL DEFAULT 'pending',
  `priority`            ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  `sort_order`          INT NOT NULL DEFAULT 0,
  `admin_response`      JSON,
  `resolved_by`         INT UNSIGNED,
  `resolved_at`         DATETIME,
  `created_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id`       (`user_id`),
  KEY `idx_kanban_status` (`kanban_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 20. invoices ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `invoices` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`         CHAR(36)     NOT NULL,
  `user_id`      INT UNSIGNED NOT NULL,
  `project_id`   INT UNSIGNED,
  `invoice_no`   VARCHAR(50)  NOT NULL,
  `status`       ENUM('draft','waiting','payment_pending','payment_reported','paid','cancelled') NOT NULL DEFAULT 'draft',
  `subtotal`     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `tax_rate`     DECIMAL(5,2)  NOT NULL DEFAULT 20.00,
  `tax_amount`   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total`        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `currency`     VARCHAR(3)   NOT NULL DEFAULT 'TRY',
  `due_date`     DATE,
  `notes`        TEXT,
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`   DATETIME,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_invoice_no`   (`invoice_no`),
  UNIQUE KEY `uq_invoice_uuid` (`uuid`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status`  (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 21. invoice_items ───────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `invoice_items` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id`  INT UNSIGNED NOT NULL,
  `description` VARCHAR(500) NOT NULL,
  `quantity`    DECIMAL(10,2) NOT NULL DEFAULT 1.00,
  `unit_price`  DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total`       DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_invoice_id` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 22. payment_methods ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `payment_methods` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id`      INT UNSIGNED NOT NULL,
  `type`            ENUM('online_link','bank_transfer') NOT NULL,
  `online_url`      TEXT,
  `bank_name`       VARCHAR(100) NOT NULL DEFAULT '',
  `iban`            VARCHAR(50)  NOT NULL DEFAULT '',
  `account_holder`  VARCHAR(150) NOT NULL DEFAULT '',
  `instructions`    TEXT,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_invoice_id` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 23. payment_notifications ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `payment_notifications` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id`     INT UNSIGNED NOT NULL,
  `user_id`        INT UNSIGNED NOT NULL,
  `method_type`    ENUM('online_link','bank_transfer') NOT NULL,
  `user_note`      TEXT,
  `status`         ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `kanban_status`  ENUM('pending','reviewing','in_progress','completed','rejected') NOT NULL DEFAULT 'pending',
  `priority`       ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  `sort_order`     INT NOT NULL DEFAULT 0,
  `reviewed_by`    INT UNSIGNED,
  `reviewed_at`    DATETIME,
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_invoice_id`    (`invoice_id`),
  KEY `idx_user_id`       (`user_id`),
  KEY `idx_kanban_status` (`kanban_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 24. support_tickets ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `support_tickets` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`          CHAR(36)     NOT NULL,
  `user_id`       INT UNSIGNED NOT NULL,
  `project_id`    INT UNSIGNED,
  `invoice_id`    INT UNSIGNED,
  `subject`       VARCHAR(255) NOT NULL,
  `category`      VARCHAR(100) NOT NULL DEFAULT 'genel',
  `priority`      ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  `status`        ENUM('open','answered','waiting_customer','closed') NOT NULL DEFAULT 'open',
  `kanban_status` ENUM('pending','reviewing','in_progress','completed','rejected') NOT NULL DEFAULT 'pending',
  `sort_order`    INT NOT NULL DEFAULT 0,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`    DATETIME,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ticket_uuid` (`uuid`),
  KEY `idx_user_id`       (`user_id`),
  KEY `idx_status`        (`status`),
  KEY `idx_kanban_status` (`kanban_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 25. support_replies ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `support_replies` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id`       INT UNSIGNED NOT NULL,
  `author_type`     ENUM('user','admin') NOT NULL,
  `author_id`       INT UNSIGNED NOT NULL,
  `message`         TEXT NOT NULL,
  `attachment_path` VARCHAR(500),
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ticket_id` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 26. notifications ───────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `notifications` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`       CHAR(36)     NOT NULL,
  `user_id`    INT UNSIGNED,
  `admin_id`   INT UNSIGNED,
  `type`       ENUM('user','admin') NOT NULL DEFAULT 'user',
  `title`      VARCHAR(255) NOT NULL,
  `body`       TEXT,
  `link_type`  VARCHAR(50)  NOT NULL DEFAULT '',
  `link_id`    INT UNSIGNED,
  `is_read`    TINYINT(1)   NOT NULL DEFAULT 0,
  `read_at`    DATETIME,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_notif_uuid` (`uuid`),
  KEY `idx_user_id`  (`user_id`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_is_read`  (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 27. email_logs ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `email_logs` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `to_email`     VARCHAR(191) NOT NULL,
  `subject`      VARCHAR(255) NOT NULL,
  `body_hash`    VARCHAR(64)  NOT NULL DEFAULT '',
  `template_key` VARCHAR(100) NOT NULL DEFAULT '',
  `status`       ENUM('queued','sent','failed') NOT NULL DEFAULT 'queued',
  `error_message` TEXT,
  `sent_at`      DATETIME,
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_to_email` (`to_email`),
  KEY `idx_status`   (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 28. activity_logs ───────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `actor_type`  ENUM('user','admin','system') NOT NULL DEFAULT 'system',
  `actor_id`    INT UNSIGNED,
  `action`      VARCHAR(100) NOT NULL,
  `entity_type` VARCHAR(50)  NOT NULL DEFAULT '',
  `entity_id`   INT UNSIGNED,
  `ip_address`  VARCHAR(45)  NOT NULL DEFAULT '',
  `user_agent`  TEXT,
  `meta`        JSON,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_actor`      (`actor_type`, `actor_id`),
  KEY `idx_action`     (`action`),
  KEY `idx_entity`     (`entity_type`, `entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 29. login_logs ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `login_logs` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `actor_type`    ENUM('user','admin') NOT NULL,
  `actor_id`      INT UNSIGNED,
  `email_attempt` VARCHAR(191) NOT NULL DEFAULT '',
  `ip_address`    VARCHAR(45)  NOT NULL DEFAULT '',
  `user_agent`    TEXT,
  `status`        ENUM('success','failed') NOT NULL DEFAULT 'failed',
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_actor_id`  (`actor_id`),
  KEY `idx_ip`        (`ip_address`),
  KEY `idx_status`    (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 30. audit_trail (immutable) ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `audit_trail` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`        CHAR(36)     NOT NULL,
  `actor_type`  ENUM('admin','system') NOT NULL DEFAULT 'admin',
  `actor_id`    INT UNSIGNED,
  `action`      VARCHAR(100) NOT NULL,
  `entity_type` VARCHAR(50)  NOT NULL,
  `entity_id`   INT UNSIGNED NOT NULL,
  `entity_uuid` CHAR(36),
  `old_values`  JSON,
  `new_values`  JSON,
  `ip_address`  VARCHAR(45)  NOT NULL DEFAULT '',
  `user_agent`  TEXT,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_audit_uuid` (`uuid`),
  KEY `idx_action`      (`action`),
  KEY `idx_entity`      (`entity_type`, `entity_id`),
  KEY `idx_actor`       (`actor_type`, `actor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 31. deleted_records ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `deleted_records` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `table_name`       VARCHAR(100) NOT NULL,
  `record_id`        INT UNSIGNED NOT NULL,
  `record_snapshot`  JSON,
  `deleted_by_type`  ENUM('user','admin','system') NOT NULL,
  `deleted_by_id`    INT UNSIGNED,
  `reason`           TEXT,
  `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_table_record` (`table_name`, `record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 32. jobs (queue) ────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `jobs` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`         CHAR(36)     NOT NULL,
  `queue`        VARCHAR(50)  NOT NULL DEFAULT 'default',
  `payload`      JSON         NOT NULL,
  `attempts`     TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `max_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 3,
  `status`       ENUM('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
  `available_at` DATETIME     NOT NULL,
  `reserved_at`  DATETIME,
  `created_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_job_uuid` (`uuid`),
  KEY `idx_queue_status` (`queue`, `status`, `available_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 33. backup_logs ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `backup_logs` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type`       ENUM('full','database','files') NOT NULL DEFAULT 'database',
  `file_path`  VARCHAR(500) NOT NULL DEFAULT '',
  `file_size`  BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `status`     ENUM('pending','completed','failed') NOT NULL DEFAULT 'pending',
  `started_by` INT UNSIGNED,
  `error_msg`  TEXT,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 34. schema_migrations (update sistemi) ──────────────────────────────────
CREATE TABLE IF NOT EXISTS `schema_migrations` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `version`    VARCHAR(50)  NOT NULL,
  `applied_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_version` (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Versiyon kaydı
INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES ('1.0.0', NOW());

-- ─── Sektörler ───────────────────────────────────────────────────────────────
INSERT INTO `sectors` (`slug`, `name`, `icon`, `description`, `sort_order`) VALUES
('kahve-dukkan',  'Kahve Dükkanı',     'coffee',    'Lokal kafe ve roastery''ler için dijital dönüşüm', 1),
('emlak',         'Emlak Ofisi',       'home',      'Gayrimenkul danışmanları için dijital çözümler',   2),
('guzellik',      'Güzellik & Kuaför', 'scissors',  'Salonlar ve klinikler için dijital altyapı',        3),
('hukuk',         'Avukatlık Bürosu',  'briefcase', 'Hukuk profesyonelleri için kurumsal çözümler',     4),
('restoran',      'Restoran & Cafe',   'utensils',  'Yeme-içme işletmeleri için dijital altyapı',       5),
('saglik',        'Sağlık & Klinik',   'heart',     'Sağlık hizmetleri için dijital çözümler',          6),
('egitim',        'Eğitim & Kurs',     'book',      'Eğitim kurumları için dijital dönüşüm',            7),
('ticaret',       'Ticaret & E-Ticaret','shopping-cart','Ticari işletmeler için online çözümler',       8);

-- ─── Sektör şablonları ───────────────────────────────────────────────────────
INSERT INTO `sector_templates` (`sector_id`, `title`, `features`, `sample_copy`, `cta_label`) VALUES
(1, 'Kahve Dükkanı Dijital Paketi',
 '["Online menü & QR yapı", "Instagram reels şablonları", "Google Maps optimizasyonu"]',
 'Kahve kültürünüzü dijitale taşıyın. Müşterileriniz QR kod ile menünüze ulaşsın.',
 'Bu Sektörü İncele'),
(2, 'Emlak Ofisi Dijital Paketi',
 '["İlan listeleme modülü", "WhatsApp lead butonu", "Kurumsal portföy sunumu"]',
 'Mülklerinizi dijital vitrine taşıyın. Potansiyel alıcılara 7/24 ulaşın.',
 'Bu Sektörü İncele'),
(3, 'Güzellik & Kuaför Dijital Paketi',
 '["Randevu alma modülü", "Hizmet & fiyat listesi", "Meta reklam görselleri"]',
 'Online randevu ile müşteri kitlenizi büyütün. Sosyal medyada öne çıkın.',
 'Bu Sektörü İncele'),
(4, 'Avukatlık Bürosu Dijital Paketi',
 '["SEO uyumlu makale blogu", "KVKK uyumlu iletişim", "Ağırbaşlı kurumsal tasarım"]',
 'Hukuki uzmanlığınızı dijitalde sergileyin. Güven veren kurumsal web varlığı.',
 'Bu Sektörü İncele');

-- ─── Varsayılan site ayarları ─────────────────────────────────────────────────
INSERT INTO `settings` (`setting_key`, `setting_value`, `group_name`) VALUES
('site_name',          'GO.NET.TR',                  'site'),
('site_url',           'https://go.net.tr',           'site'),
('site_description',   'Dijital dönüşümde tavsiye veren değil, icraat yapan AI platformu.', 'seo'),
('site_keywords',      'GO, GO.NET.TR, dijital dönüşüm, yapay zeka, KOBİ, domain, hosting', 'seo'),
('og_image',           '/assets/img/og-image.jpg',    'seo'),
('app_version',        '1.0.0',                       'version'),
('installed',          '1',                           'site'),
('maintenance_mode',   '0',                           'site'),
('default_locale',     'tr',                          'site'),
('contact_email',      'yatirim@go.net.tr',           'site'),
('contact_phone',      '+90 541 788 54 32',           'site'),
('company_name',       'Genç Grup Yazılım Ltd. Şti.', 'site'),
('company_tax_no',     '3931274985',                  'site'),
('invoice_prefix',     'GO-',                         'site'),
('invoice_counter',    '1000',                        'site'),
-- Feature flags
('ai_enabled',              '0', 'feature'),
('kanban_enabled',           '1', 'feature'),
('zip_export_enabled',       '1', 'feature'),
('domain_module_enabled',    '1', 'feature'),
('hosting_module_enabled',   '1', 'feature'),
('software_module_enabled',  '1', 'feature'),
('chat_enabled',             '1', 'feature'),
('api_enabled',              '0', 'feature'),
('notifications_enabled',    '1', 'feature'),
('support_enabled',          '1', 'feature');

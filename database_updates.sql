-- Database updates for enhanced topup website
-- Add these tables to support new features

-- Rate limiting table for security
CREATE TABLE IF NOT EXISTS `rate_limits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) NOT NULL,
  `attempt_time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `identifier` (`identifier`),
  KEY `attempt_time` (`attempt_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Voucher system
CREATE TABLE IF NOT EXISTS `vouchers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL UNIQUE,
  `type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `value` decimal(10,2) NOT NULL,
  `min_purchase` decimal(10,2) DEFAULT 0,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `valid_from` datetime NOT NULL,
  `valid_until` datetime NOT NULL,
  `status` enum('active','inactive','expired') DEFAULT 'active',
  `created_by` varchar(100) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `code` (`code`),
  KEY `status` (`status`),
  KEY `valid_from` (`valid_from`),
  KEY `valid_until` (`valid_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Voucher usage tracking
CREATE TABLE IF NOT EXISTS `voucher_usage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `voucher_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` varchar(100) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `used_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `voucher_id` (`voucher_id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`),
  FOREIGN KEY (`voucher_id`) REFERENCES `vouchers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Flash sale system
CREATE TABLE IF NOT EXISTS `flash_sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `discount_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `status` enum('scheduled','active','ended','cancelled') DEFAULT 'scheduled',
  `created_by` varchar(100) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `start_time` (`start_time`),
  KEY `end_time` (`end_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Flash sale products
CREATE TABLE IF NOT EXISTS `flash_sale_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `flash_sale_id` int(11) NOT NULL,
  `product_type` enum('pulsa','games','sosmed') NOT NULL,
  `product_id` int(11) NOT NULL,
  `original_price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) NOT NULL,
  `stock_limit` int(11) DEFAULT NULL,
  `sold_count` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `flash_sale_id` (`flash_sale_id`),
  KEY `product_type` (`product_type`),
  KEY `product_id` (`product_id`),
  FOREIGN KEY (`flash_sale_id`) REFERENCES `flash_sales`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Customer service tickets
CREATE TABLE IF NOT EXISTS `support_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` varchar(20) NOT NULL UNIQUE,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `category` enum('general','technical','billing','complaint','suggestion') DEFAULT 'general',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `status` enum('open','in_progress','waiting_customer','resolved','closed') DEFAULT 'open',
  `assigned_to` varchar(100) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Support ticket messages
CREATE TABLE IF NOT EXISTS `support_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `sender_type` enum('user','admin') NOT NULL,
  `sender_id` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `attachments` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `sender_type` (`sender_type`),
  FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notifications system
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `type` enum('info','success','warning','error','promotion') DEFAULT 'info',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `action_url` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `is_global` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `type` (`type`),
  KEY `is_read` (`is_read`),
  KEY `is_global` (`is_global`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Audit log for admin actions
CREATE TABLE IF NOT EXISTS `admin_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_username` varchar(100) NOT NULL,
  `action` varchar(255) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `admin_username` (`admin_username`),
  KEY `action` (`action`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Website settings enhancement
CREATE TABLE IF NOT EXISTS `website_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','json','file') DEFAULT 'text',
  `description` varchar(255) DEFAULT NULL,
  `category` varchar(50) DEFAULT 'general',
  `is_public` tinyint(1) DEFAULT 0,
  `updated_by` varchar(100) DEFAULT NULL,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `setting_key` (`setting_key`),
  KEY `category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default website settings
INSERT INTO `website_settings` (`setting_key`, `setting_value`, `setting_type`, `description`, `category`, `is_public`) VALUES
('maintenance_mode', '0', 'boolean', 'Enable/disable maintenance mode', 'system', 1),
('flash_sale_enabled', '1', 'boolean', 'Enable/disable flash sale feature', 'features', 1),
('voucher_enabled', '1', 'boolean', 'Enable/disable voucher system', 'features', 1),
('customer_service_enabled', '1', 'boolean', 'Enable/disable customer service', 'features', 1),
('max_login_attempts', '5', 'number', 'Maximum login attempts before lockout', 'security', 0),
('session_timeout', '3600', 'number', 'Session timeout in seconds', 'security', 0),
('min_deposit_amount', '10000', 'number', 'Minimum deposit amount', 'payment', 1),
('max_deposit_amount', '10000000', 'number', 'Maximum deposit amount', 'payment', 1);

-- Update users table for better security (if not exists)
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `password_hash` varchar(255) DEFAULT NULL AFTER `password`,
ADD COLUMN IF NOT EXISTS `last_login` timestamp NULL DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `login_attempts` int(11) DEFAULT 0,
ADD COLUMN IF NOT EXISTS `locked_until` timestamp NULL DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `email_verified` tinyint(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS `two_factor_enabled` tinyint(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Add indexes for better performance
ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_username` (`username`);
ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_email` (`email`);
ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_level` (`level`);
ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_last_login` (`last_login`);

-- Update pembelian_pulsa table for voucher support
ALTER TABLE `pembelian_pulsa` 
ADD COLUMN IF NOT EXISTS `voucher_code` varchar(50) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `discount_amount` decimal(10,2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS `original_price` decimal(10,2) DEFAULT NULL;

-- Update pembelian_sosmed table for voucher support  
ALTER TABLE `pembelian_sosmed` 
ADD COLUMN IF NOT EXISTS `voucher_code` varchar(50) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `discount_amount` decimal(10,2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS `original_price` decimal(10,2) DEFAULT NULL;

-- Add language support table
CREATE TABLE IF NOT EXISTS `user_preferences` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `language` varchar(5) DEFAULT 'id',
    `timezone` varchar(50) DEFAULT 'Asia/Jakarta',
    `currency` varchar(5) DEFAULT 'IDR',
    `theme` varchar(20) DEFAULT 'light',
    `notifications_enabled` tinyint(1) DEFAULT 1,
    `email_notifications` tinyint(1) DEFAULT 1,
    `sms_notifications` tinyint(1) DEFAULT 0,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_user_preferences` (`user_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add language settings to website_settings
INSERT INTO `website_settings` (`setting_key`, `setting_value`, `description`, `category`, `is_public`) VALUES
('default_language', 'id', 'Default website language (id/en)', 'language', 1),
('supported_languages', 'id,en', 'Comma-separated list of supported languages', 'language', 1),
('auto_detect_language', '1', 'Auto-detect user language from browser', 'language', 1),
('language_switcher_enabled', '1', 'Enable language switcher in header', 'language', 1),
('rtl_support', '0', 'Enable right-to-left language support', 'language', 1);

-- Add multi-language content table
CREATE TABLE IF NOT EXISTS `content_translations` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `content_type` varchar(50) NOT NULL, -- 'page', 'notification', 'email_template', etc.
    `content_id` int(11) NOT NULL,
    `language` varchar(5) NOT NULL,
    `title` text,
    `content` longtext,
    `meta_description` text,
    `meta_keywords` text,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_content_translation` (`content_type`, `content_id`, `language`),
    INDEX `idx_content_type_lang` (`content_type`, `language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add language-specific voucher names and descriptions
ALTER TABLE `vouchers` 
ADD COLUMN IF NOT EXISTS `name_en` varchar(255) DEFAULT NULL AFTER `code`,
ADD COLUMN IF NOT EXISTS `description_en` text DEFAULT NULL AFTER `name_en`;

-- Add language-specific flash sale content
ALTER TABLE `flash_sales` 
ADD COLUMN IF NOT EXISTS `title_en` varchar(255) DEFAULT NULL AFTER `description`,
ADD COLUMN IF NOT EXISTS `description_en` text DEFAULT NULL AFTER `title_en`;

-- Add language-specific notification content
ALTER TABLE `notifications` 
ADD COLUMN IF NOT EXISTS `title_en` varchar(255) DEFAULT NULL AFTER `message`,
ADD COLUMN IF NOT EXISTS `message_en` text DEFAULT NULL AFTER `title_en`;

-- Add additional indexes for language support
CREATE INDEX IF NOT EXISTS `idx_user_preferences_user` ON `user_preferences`(`user_id`);
CREATE INDEX IF NOT EXISTS `idx_content_translations_lookup` ON `content_translations`(`content_type`, `content_id`, `language`);

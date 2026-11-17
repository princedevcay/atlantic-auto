-- Atlantic Auto initial schema. MySQL 5.7+/MariaDB 10.3+ compatible. Applied by bin/migrate.php.

CREATE TABLE staff (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL,
  name VARCHAR(120) NOT NULL,
  role VARCHAR(10) NOT NULL DEFAULT 'staff',
  password_hash VARCHAR(255) NOT NULL,
  must_change_password TINYINT(1) NOT NULL DEFAULT 1,
  totp_secret_enc VARCHAR(255) NULL,
  totp_last_step BIGINT NULL,
  recovery_codes TEXT NULL,
  failed_logins INT NOT NULL DEFAULT 0,
  locked_until DATETIME NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uq_staff_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE shipments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reference CHAR(8) NOT NULL,
  token_hash CHAR(64) NULL,
  token_created_at DATETIME NULL,
  customer_name VARCHAR(120) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  phone_last4 CHAR(4) NOT NULL,
  email VARCHAR(190) NULL,
  vehicle_year SMALLINT NULL,
  vehicle_make VARCHAR(60) NOT NULL,
  vehicle_model VARCHAR(80) NOT NULL,
  vin VARCHAR(17) NULL,
  stage VARCHAR(20) NOT NULL DEFAULT 'sourcing',
  carrier_ref_type VARCHAR(20) NULL,
  carrier_ref VARCHAR(40) NULL,
  vessel VARCHAR(80) NULL,
  eta DATE NULL,
  carrier_synced_at DATETIME NULL,
  delivered_at DATETIME NULL,
  link_disabled_at DATETIME NULL,
  enquiry_id INT UNSIGNED NULL,
  created_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uq_ship_ref (reference),
  UNIQUE KEY uq_ship_token (token_hash),
  KEY idx_ship_stage (stage)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE enquiries (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type VARCHAR(10) NOT NULL,
  reference CHAR(8) NOT NULL,
  status VARCHAR(12) NOT NULL DEFAULT 'new',
  name VARCHAR(120) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  email VARCHAR(190) NULL,
  services VARCHAR(255) NULL,
  vehicle_year SMALLINT NULL,
  vehicle_make VARCHAR(60) NULL,
  vehicle_model VARCHAR(80) NULL,
  source VARCHAR(12) NULL,
  listing_url VARCHAR(500) NULL,
  vin_or_reference VARCHAR(40) NULL,
  message TEXT NULL,
  shipment_id INT UNSIGNED NULL,
  created_at DATETIME NOT NULL,
  last_activity_at DATETIME NOT NULL,
  UNIQUE KEY uq_enq_ref (reference),
  KEY idx_enq_type_status (type, status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE shipment_updates (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  shipment_id INT UNSIGNED NOT NULL,
  stage VARCHAR(20) NOT NULL,
  note TEXT NULL,
  is_public TINYINT(1) NOT NULL DEFAULT 1,
  created_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL,
  KEY idx_upd_ship (shipment_id, created_at),
  CONSTRAINT fk_upd_ship FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE shipment_photos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  shipment_id INT UNSIGNED NOT NULL,
  file_name CHAR(36) NOT NULL,
  width INT NOT NULL,
  height INT NOT NULL,
  is_public TINYINT(1) NOT NULL DEFAULT 1,
  uploaded_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL,
  KEY idx_photo_ship (shipment_id),
  CONSTRAINT fk_photo_ship FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE carrier_events (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  shipment_id INT UNSIGNED NOT NULL,
  event_time DATETIME NOT NULL,
  description VARCHAR(255) NOT NULL,
  location VARCHAR(120) NULL,
  event_hash CHAR(64) NOT NULL,
  fetched_at DATETIME NOT NULL,
  UNIQUE KEY uq_carrier_event (shipment_id, event_hash),
  CONSTRAINT fk_carrier_ship FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_log (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  staff_id INT UNSIGNED NULL,
  action VARCHAR(40) NOT NULL,
  target_type VARCHAR(20) NULL,
  target_id INT UNSIGNED NULL,
  details VARCHAR(255) NULL,
  ip_hash CHAR(16) NULL,
  created_at DATETIME NOT NULL,
  KEY idx_audit_time (created_at),
  KEY idx_audit_action (action, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE rate_limits (
  rkey VARCHAR(120) NOT NULL,
  window_start INT UNSIGNED NOT NULL,
  hits INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (rkey, window_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE mail_queue (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  to_addr VARCHAR(190) NOT NULL,
  subject VARCHAR(200) NOT NULL,
  body_text MEDIUMTEXT NOT NULL,
  reply_to VARCHAR(190) NULL,
  attempts INT NOT NULL DEFAULT 0,
  last_error VARCHAR(255) NULL,
  next_attempt_at DATETIME NOT NULL,
  sent_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  KEY idx_mail_pending (sent_at, next_attempt_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

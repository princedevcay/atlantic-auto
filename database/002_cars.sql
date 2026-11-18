-- Cars for sale (PG-031 to PG-034, baseline CHG-002).

CREATE TABLE cars (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reference CHAR(8) NOT NULL,
  year SMALLINT NULL,
  make VARCHAR(60) NOT NULL,
  model VARCHAR(80) NOT NULL,
  trim_level VARCHAR(60) NULL,
  body_type VARCHAR(12) NOT NULL,
  mileage INT UNSIGNED NULL,
  mileage_unit CHAR(2) NOT NULL DEFAULT 'mi',
  transmission VARCHAR(10) NULL,
  fuel VARCHAR(10) NULL,
  engine VARCHAR(40) NULL,
  colour VARCHAR(40) NULL,
  location VARCHAR(10) NOT NULL DEFAULT 'usa',
  price_amount DECIMAL(12,2) NULL,
  currency CHAR(3) NOT NULL DEFAULT 'GHS',
  status VARCHAR(10) NOT NULL DEFAULT 'available',
  featured TINYINT(1) NOT NULL DEFAULT 0,
  published TINYINT(1) NOT NULL DEFAULT 0,
  description TEXT NULL,
  vin VARCHAR(17) NULL,
  created_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  published_at DATETIME NULL,
  UNIQUE KEY uq_car_ref (reference),
  KEY idx_car_public (published, status, featured, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE car_photos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  car_id INT UNSIGNED NOT NULL,
  file_name CHAR(36) NOT NULL,
  width INT NOT NULL,
  height INT NOT NULL,
  is_main TINYINT(1) NOT NULL DEFAULT 0,
  sort INT NOT NULL DEFAULT 0,
  uploaded_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL,
  KEY idx_car_photo (car_id, is_main, sort),
  CONSTRAINT fk_car_photo FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE enquiries ADD COLUMN car_id INT UNSIGNED NULL AFTER shipment_id;
ALTER TABLE enquiries ADD KEY idx_enq_car (car_id);

-- Sample listings for demonstration. Shown with a "Sample" badge, take no enquiries,
-- and are removed before launch with: php bin/seed-sample-cars.php --remove
ALTER TABLE cars ADD COLUMN sample TINYINT(1) NOT NULL DEFAULT 0 AFTER published;

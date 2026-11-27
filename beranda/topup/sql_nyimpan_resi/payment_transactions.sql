USE db_users;
CREATE TABLE IF NOT EXISTS transactions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 invoice_no VARCHAR(40) NOT NULL UNIQUE,
 user_id INT NULL,
 nama_pelanggan VARCHAR(100) NOT NULL,
 game VARCHAR(100) NOT NULL,
 item_name VARCHAR(100) NOT NULL,
 user_game VARCHAR(150) NOT NULL,
 amount DECIMAL(12,2) NOT NULL,
 payment_method VARCHAR(30) NOT NULL,
 bank VARCHAR(30) NULL,
 va_number VARCHAR(50) NULL,
 status ENUM('PENDING','PAID','FAILED') DEFAULT 'PENDING',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Jika tabel versi lama sudah ada, tambahkan kolom yang kurang tanpa DROP.
SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='transactions' AND COLUMN_NAME='nama_pelanggan')=0,
 'ALTER TABLE transactions ADD COLUMN nama_pelanggan VARCHAR(100) NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='transactions' AND COLUMN_NAME='user_game')=0,
 'ALTER TABLE transactions ADD COLUMN user_game VARCHAR(150) NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='transactions' AND COLUMN_NAME='account_data')>0,
 'ALTER TABLE transactions MODIFY COLUMN account_data JSON NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='transactions' AND COLUMN_NAME='account_data')>0,
 'UPDATE transactions SET user_game = account_data WHERE user_game IS NULL AND CHAR_LENGTH(account_data)<=150', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

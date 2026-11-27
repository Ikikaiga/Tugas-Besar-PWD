SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `akun` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `akun` (`id`, `username`, `email`, `password`) VALUES
(1, 'asd', NULL, '$2y$12$xWuqDTf5y9G0Hjc3gvPGcOQgr8iv5EDRgdwrqq9ayeK/f3J2hLGVK');

ALTER TABLE `akun`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

ALTER TABLE `akun`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;



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

    status ENUM(
        'PENDING',
        'PAID',
        'FAILED'
    ) DEFAULT 'PENDING',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);





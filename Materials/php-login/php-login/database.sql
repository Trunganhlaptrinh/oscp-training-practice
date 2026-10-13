-- Tạo database và bảng users
CREATE DATABASE IF NOT EXISTS php_login CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE php_login;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Tài khoản mẫu: admin / 123456 (mật khẩu đã được băm bằng password_hash)
INSERT INTO users (username, password)
VALUES ('admin', '$2y$10$LjE87dnXOa9DQCnGdIBvvuWgrXkr4souECEXGG4CL4cE2QbAf/GD2');

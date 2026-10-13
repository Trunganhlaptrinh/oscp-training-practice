-- Bảng cho bản đơn giản: mật khẩu lưu dạng chữ thường (chỉ dùng để học)
USE php_login;

CREATE TABLE IF NOT EXISTS members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(50) NOT NULL
);

INSERT INTO members (username, password) VALUES ('admin', '123456');

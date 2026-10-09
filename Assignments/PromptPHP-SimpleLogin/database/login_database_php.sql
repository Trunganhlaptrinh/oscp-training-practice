-- Simple Login database PHP

DROP DATABASE IF EXISTS simple_login;
CREATE DATABASE simple_login CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE simple_login;

SET NAMES utf8mb4;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (username, password) VALUES
    ('admin', '123456'),
    ('user1', '123456');
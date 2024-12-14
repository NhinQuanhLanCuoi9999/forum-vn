-- Tạo cơ sở dữ liệu
CREATE DATABASE IF NOT EXISTS forum_db;
USE forum_db;

-- Bảng users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
   `desc` TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng posts
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    username VARCHAR(50) NOT NULL,
    description TEXT, -- Thêm mô tả
    file VARCHAR(255), -- Sửa cột hình ảnh thành cột file
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (username) REFERENCES users(username) ON DELETE CASCADE
);

-- Bảng comments
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    content TEXT NOT NULL,
    username VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (username) REFERENCES users(username) ON DELETE CASCADE
);

-- Bảng bans
CREATE TABLE IF NOT EXISTS bans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) DEFAULT NULL,
    ip_address VARCHAR(45) NOT NULL,
    reason TEXT NOT NULL,
    ban_start DATETIME DEFAULT CURRENT_TIMESTAMP,
    ban_end DATETIME DEFAULT NULL,
    permanent TINYINT(1) DEFAULT 0
);

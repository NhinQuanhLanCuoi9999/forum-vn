# forum
## Cách cài
- Chạy xampp , start hết.
- Tải htdocs.zip về rồi giải nén
- Dán file htdocs vào đường dẫn đang chạy xampp
  - Vào phpmyadmin chạy lệnh này :
    ```-- Tạo cơ sở dữ liệu
CREATE DATABASE IF NOT EXISTS forum_db;
USE forum_db;

-- Bảng users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng posts
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    username VARCHAR(50) NOT NULL,
    description TEXT, -- Thêm mô tả
    image VARCHAR(255), -- Thêm hình ảnh
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
);``` 

  ### Cách chạy
  - http://localhost(:port [nếu có] )
  - http://"dảy ip lan của bạn"(:port [nếu có] )
  ### Cách lấy địa chỉ dải ip mạng lan
- Vào cmd gõ lệnh "ipconfig" rồi copy cái dòng ipv4 là được

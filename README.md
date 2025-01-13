## Cách cài
- Chạy xampp , start hết.
- Tải htdocs.zip về rồi giải nén
- Dán file htdocs vào đường dẫn đang chạy xampp
  - Vào phpmyadmin chạy lệnh này :
    [lệnh](https://raw.githubusercontent.com/NhinQuanhLanCuoi9999/forum/refs/heads/main/db.sql) 
  ### Cách chạy
  - http://localhost (:port [nếu có] )
  - http://"dảy ip lan của bạn"(:port [nếu có] )
  ### Cách lấy địa chỉ dải ip mạng lan
- Vào cmd gõ lệnh "ipconfig" rồi copy cái dòng ipv4 là được
# Website
Demo : http://forum-vn.000.pe/
# Lưu ý

- Bạn cần thay đổi API key trong phần network-config để khớp với API key của ipinfo.io.

- Nếu bạn có ý định đưa website lên internet, hãy đảm bảo rằng bạn đã cập nhật tệp config.php để phù hợp với thông tin MySQL của hosting mà bạn đang sử dụng.

- Nếu bạn có tài khoản "admin" , sau khi đăng nhập thì bạn có thể vào trang admin.php để quản lý website 

- Hãy đảm bảo trong htdocs bạn đã tạo 2 folder là uploads và logs


# Sơ đồ 


```C:\xampp\htdocs
├── .htaccess
├── 404.php
├── api
│   ├── Bans.php
│   ├── Comment.php
│   ├── Post.php
│   └── User.php
├── app
│   ├── admin
│   │   ├── logicPHP
│   │   │   ├── Api.php
│   │   │   ├── Auth.php
│   │   │   ├── Comment.php
│   │   │   ├── Info.php
│   │   │   ├── List.php
│   │   │   ├── Log.php
│   │   │   ├── Logout.php
│   │   │   ├── Post.php
│   │   │   ├── Search.php
│   │   │   └── Users.php
│   │   ├── Pagination.css
│   │   ├── php.php
│   │   ├── styles.css
│   │   ├── taskbar.js
│   ├── api
│   │   ├── LogicPHP
│   │   │   ├── Auth.php
│   │   │   ├── CreateAPI.php
│   │   ├── php.php
│   │   ├── styles.css
│   ├── api_docs
│   │   └── styles.css
│   ├── ban
│   │   ├── banOption.js
│   │   ├── LogicPHP
│   │   │   ├── Auth.php
│   │   │   ├── Handle.php
│   │   │   └── unBan.php
│   │   ├── php.php
│   │   ├── style.css
│   ├── captcha
│   │   └── styles.css
│   ├── changePass
│   │   ├── LogicPHP
│   │   │   ├── Auth.php
│   │   │   └── Handle.php
│   │   ├── php.php
│   │   └── styles.css
│   ├── index
│   │   ├── checkBox.js
│   │   ├── logicPHP
│   │   │   ├── badWords.php
│   │   │   ├── Captcha.php
│   │   │   ├── checkBan.php
│   │   │   ├── checkConfig.php
│   │   │   ├── Comment.php
│   │   │   ├── CookieandAdmin.php
│   │   │   ├── deletePost.php
│   │   │   ├── Format.php
│   │   │   ├── Log.php
│   │   │   ├── Login.php
│   │   │   ├── Logout.php
│   │   │   ├── Pagination.php
│   │   │   ├── Post.php
│   │   │   ├── Register.php
│   │   ├── checkBox.js
│   │   ├── Refersh.js
│   │   ├── Size.js
│   │   ├── Spoil.js
│   │   ├── styles.css
│   │   ├── t.js
│   │   ├── taskBar.js
│   │   ├── Toogle.js
│   ├── info
│   │   ├── LogicPHP
│   │   │   ├── Auth.php
│   │   │   ├── Info.php
│   │   │   ├── IPv6.php
│   │   ├── php.php
│   │   ├── styles.css
│   ├── logs
│   │   └── LogicPHP
│   │       ├── Output.php
│   │       └── Read.php
│   ├── network
│   │   ├── Handle.js
│   │   ├── LogicPHP
│   │   │   ├── checkTime.php
│   │   │   └── checkTime2.php
│   │   ├── php.php
│   │   └── styles.css
│   ├── profile
│   │   └── styles.css
│   ├── rule
│   │   └── styles.css
│   ├── tos
│   │   └── styles.css
│   ├── view
│   │   ├── LogicPHP
│   │   │   ├── Auth.php
│   │   │   ├── Auth2.php
│   │   │   ├── badWord.php
│   │   │   ├── Captcha.php
│   │   │   ├── Handle.php
│   │   │   └── Logs.php
│   │   ├── php.php
│   │   └── styles.css
│   └── warning
│       └── styles.css
├── badwords.txt
├── db.sql
├── docs
│   ├── api_docs.html
│   ├── rules.html
│   └── tos.html
├── index.php
├── LICENSE
├── logs
│   └── .readme.bat
├── README.md
├── setup.php
├── src
│   ├── admin.php
│   ├── api.php
│   ├── ban.php
│   ├── captcha_verification.php
│   ├── change_password.php
│   ├── index.php
│   ├── info_user.php
│   ├── logs.php
│   ├── network-config.php
│   ├── profile.php
│   ├── view.php
│   └── warning.php
└── uploads
    └── .readme.bat
```











































































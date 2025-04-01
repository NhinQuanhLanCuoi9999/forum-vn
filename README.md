# Giới thiệu

- Đây là một **web forum** giúp mọi người giao lưu, chia sẻ thông tin và thảo luận về nhiều chủ đề khác nhau. Dự án này được xây dựng với mục tiêu cung cấp một nền tảng mở, dễ dàng cài đặt và sử dụng. Bạn có thể tùy chỉnh, thêm tính năng mới và quản lý nội dung một cách linh hoạt.

## Cách cài đặt

1. Chạy Laragon/XAMPP và khởi động Apache/MySQL.
2. Sử dụng lệnh `git clone` để sao chép repository này về, sau đó di chuyển vào thư mục host Apache của server bạn.

## Cách chạy

- Truy cập vào website qua địa chỉ: `http://localhost(:port [nếu có])`
- Hoặc truy cập từ máy khác trong cùng mạng LAN qua địa chỉ: `http://[địa chỉ IP LAN của bạn](:port [nếu có])`

### Lưu ý
- Nếu bạn có role "owner" hoặc "admin", sau khi đăng nhập, bạn có thể truy cập trang `admin_tool/admin.php` để quản lý website.
- Hãy chắc chắn rằng bạn đã bật 2FA tài khoản Google và đã tạo [App Password](https://myaccount.google.com/apppasswords)
- Để lấy Secret Key và Cilent ID của Google OAuth 2.0 , các bạn vào [Google Cloud Console](https://console.cloud.google.com). Tạo project mới ( nếu chưa có) >> APIs & Services >> Credentials >> Tạo thông tin xác thực mới ( nhớ trỏ domain ở `Authorized redirect URIs` về dưới dạng : "[http/https://]<tên domain>/src/google_auth/google_callback.php")
- Dự án này phát hành theo GPLv3, nghĩa là bạn có quyền phân phối, chỉnh sửa tùy ý nhưng phải giữ mã nguồn mở (open source) và ghi credit chủ sở hữu @NhinQuanhLanCuoi9999 trên GitHub.

### Thư viện & Phụ thuộc

* Dự án này sử dụng các công nghệ và thư viện sau:

- **PHP**: Ngôn ngữ lập trình backend chính.
- **Google OAuth 2.0** : Dùng để xác minh và đăng nhập bằng Google
- **Composer**: Trình quản lý thư viện PHP.
- **PHPMailer**: Thư viện gửi email qua SMTP.
- **Spatie ( cụ thể là db-dumper )**: Bộ thư viện PHP hỗ trợ tính năng dump sql trong page backup của trang quản trị web.
- **Bootstrap**: Framework CSS giúp thiết kế giao diện responsive.
- **jQuery**: Thư viện JavaScript giúp thao tác DOM dễ dàng hơn.
- **Chart.js**: Thư viện vẽ biểu đồ trực quan.
- **Turnstile**: Giải pháp CAPTCHA của Cloudflare để bảo vệ chống spam.
- **Google Font API**: Sử dụng font **Poppins** cho giao diện.
- **Font Awesome**: Bộ icon phổ biến để làm đẹp UI.
- **IPInfo**: Hiện thông số ISP (nhà mạng) từ IP của client.
- **Leaflet**: Thư viện JavaScript dùng để tạo bản đồ tương tác trên web.
- **hCaptcha**: Đã từng sử dụng để chống bot nhưng đã loại bỏ
## Cách cài đặt

1. Chạy XAMPP và khởi động tất cả các dịch vụ.
2. Sử dụng lệnh `git clone` để sao chép repository này về, sau đó di chuyển vào thư mục `htdocs` của XAMPP.

### Cách chạy

- Truy cập vào website qua địa chỉ: `http://localhost(:port [nếu có])`
- Hoặc truy cập từ máy khác trong cùng mạng LAN qua địa chỉ: `http://[địa chỉ IP LAN của bạn](:port [nếu có])`

### Cách lấy địa chỉ IP mạng LAN

1. Mở Command Prompt (CMD) và gõ lệnh `ipconfig`.
2. Sao chép giá trị của dòng "IPv4 Address" (địa chỉ IP).

## Lưu ý

- Bạn cần thay đổi API key trong tệp `network-config` để phù hợp với API key của dịch vụ ipinfo.io.
- Nếu bạn dự định triển khai website lên internet, hãy cập nhật tệp `config.php` với thông tin MySQL của hosting mà bạn đang sử dụng.
- Nếu bạn có tài khoản "admin", sau khi đăng nhập, bạn có thể truy cập trang `admin.php` để quản lý website.
- Đảm bảo rằng trong thư mục `htdocs`, bạn đã tạo 2 thư mục con là `uploads` và `logs` để lưu trữ các dữ liệu liên quan.







































































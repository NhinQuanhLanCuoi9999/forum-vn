## Cách cài đặt

1. Chạy Laragon và khởi động Apache/MySQL.
2. Sử dụng lệnh `git clone` để sao chép repository này về, sau đó di chuyển vào thư mục `www` của Laragon.

### Cách chạy

- Truy cập vào website qua địa chỉ: `http://localhost(:port [nếu có])`
- Hoặc truy cập từ máy khác trong cùng mạng LAN qua địa chỉ: `http://[địa chỉ IP LAN của bạn](:port [nếu có])`

### Cách lấy địa chỉ IP mạng LAN

1. Mở Command Prompt (CMD) và gõ lệnh `ipconfig`.
2. Sao chép giá trị của dòng "IPv4 Address" (địa chỉ IP).

## Lưu ý
- Nên dùng Laragon để tránh lỗi về PHP Mail ( nếu dùng XAMPP thì bạn phải tự setup thủ công)
- Nếu bạn có tài khoản "admin", sau khi đăng nhập, bạn có thể truy cập trang `admin_tool/admin.php` để quản lý website.
- Hãy chắc chắn rằng bạn đã bật 2FA tài khoản Google và đã tạo [App Password](https://myaccount.google.com/apppasswords).
- Nếu bị lỗi SMTP thì hãy vào Laragon >> Setting >> Gởi Mail >> Nhập Account Gmail và App Password gửi đi là xong.
- Vì vấn đề tiêu chuẩn cộng đồng nên tôi không thể ghi từ cấm trong `badwords.txt` được nên các bạn tự ghi vào.


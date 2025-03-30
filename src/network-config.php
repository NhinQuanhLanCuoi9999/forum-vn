<?php
session_start();
// Kết nối cơ sở dữ liệu
include('../config.php');
include('../app/_USERS_LOGIC/network/Auth.php');

// Truy vấn lấy API key từ bảng misc
$query = "SELECT ipinfo_api_key FROM misc LIMIT 1";
$result = $conn->query($query);

// Kiểm tra nếu có bản ghi và trả về giá trị
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $apiKey = $row['ipinfo_api_key']; // Trả về ipinfo_api_key
} else {
    $apiKey = null;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IP Address and Network Info</title>
    <link rel="stylesheet" href="../asset/css/leaflet.css">
    <link rel="icon" href="/favicon.ico" type="image/png">
    <link rel="stylesheet" type="text/css" href="/app/_USERS_LOGIC/network/styles.css">
    <style>
        .network-test {margin-top: 20px;padding: 10px;border: 1px solid #ccc;border-radius: 8px;display: inline-block;vertical-align: top;}
        .network-test table {border-collapse: collapse;}
        .network-test th, .network-test td {border: 1px solid #ddd;padding: 8px 12px;text-align: center;}
        .network-test th {background-color: #f4f4f4;}
    </style>
</head>
<body data-apikey="<?php echo htmlspecialchars($apiKey); ?>">
<div class="container">
    <h2>Thông tin cấu hình mạng</h2>
    <div class="ip-info">
        <p>IPv6: <span id="ipv6">Loading...</span></p>
        <p>IPv4: <span id="ipv4">Loading...</span></p>
        <span class="show-more" onclick="toggleExtraInfo()" id="showMoreBtn">Xem thêm</span>
        <div class="extra-info" id="extraInfo">
            <p>ASN: <span id="asn">Loading...</span></p>
            <p>ISP: <span id="isp">Loading...</span></p>
            <p>Services: <span id="services">None detected</span></p>
            <p>Quốc gia: <span id="country">Loading...</span></p>
            <p>Bang/Vùng lãnh thổ: <span id="region">Loading...</span></p>
            <p>Thành phố: <span id="city">Loading...</span></p>
            <p>Vĩ độ: <span id="latitude">Loading...</span></p>
            <p>Kinh độ: <span id="longitude">Loading...</span></p>
        </div>
    </div>
    
    <!-- Bảng hiển thị kết quả đo tốc độ mạng -->
    <div class="network-test">
        <h3>Test Tốc Độ Mạng</h3>
        <table>
            <tr>
                <th>Ping (ms)</th>
                <th>Jitter (ms)</th>
                <th>Download (Mbps)</th>
            </tr>
            <tr>
                <td id="pingResult">-</td>
                <td id="jitterResult">-</td>
                <td id="downloadResult">-</td>
            </tr>
        </table>
    </div>
    
    <div id="map"></div>
    <div id="timer">Phiên còn lại: <span id="time">30</span> giây</div>
</div>

<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script src="/app/_USERS_LOGIC/network/Handle.js"></script>
<script src="/app/_USERS_LOGIC/network/SpeedTest.js"></script>


</body>
</html>

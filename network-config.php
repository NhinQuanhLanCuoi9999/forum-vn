<?php
session_start(); // Khởi động phiên
// Kiểm tra nếu đã có thời gian truy cập lần trước
if (isset($_SESSION['last_access_time'])) {
    $lastAccessTime = $_SESSION['last_access_time'];
    $currentTime = time();
    $timeElapsed = $currentTime - $lastAccessTime;

    // Nếu thời gian truy cập chưa hết 5 phút (300 giây)
    if ($timeElapsed < 300) {
        // Chuyển hướng về trang index.php và báo lỗi
        $_SESSION['error'] = "Bạn chỉ có thể truy cập vào cấu hình mạng 5 phút / 1 lần.";
        header('Location: index.php');
        exit();
    }
}

// Cập nhật thời gian truy cập hiện tại
$_SESSION['last_access_time'] = time();
// log.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $ip = filter_var($data['ip'], FILTER_VALIDATE_IP); // Lọc địa chỉ IP

    // Ghi địa chỉ IP vào tệp log
    $logFile = 'network-logs.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "$timestamp - $ip\n";

    // Kiểm tra xem tệp có thể ghi được không và thực hiện ghi log
    if (is_writable($logFile)) {
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    } else {
        error_log("Không thể ghi vào tệp $logFile");
    }
}
?>
    <title>IP Address and Location</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="icon" href="favicon.png" type="image/png">
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f0f8ff;
        }
        .container {
            padding: 20px;
        }
        .ip-info {
            background-color: #1E3F66;
            color: white;
            padding: 10px;
            border-radius: 10px;
            display: inline-block;
            margin-bottom: 20px;
            text-align: left;
        }
        .extra-info {
            margin-top: 10px;
            display: none;
            border-top: 1px solid #aaa;
            padding-top: 10px;
            transition: max-height 0.5s ease;
            overflow: hidden;
            max-height: 0;
        }
        #map {
            height: 300px;
            width: 100%;
            margin-top: 20px;
        }
        .show-more {
            color: #00ffff;
            cursor: pointer;
            display: inline-block;
            margin-top: 10px;
        }
        #timer {
            font-size: 20px;
            color: red;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }
    </style>
</head>
<body>

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
        <div id="map"></div>
        <div id="timer">Phiên còn lại: <span id="time">30</span> giây</div>
    </div>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        let isExtraInfoVisible = false;
        let countdown = 30; // Thời gian đếm ngược
        const timerElement = document.getElementById('time');
        const loggedIPs = JSON.parse(localStorage.getItem('loggedIPs')) || {};

        // Hàm để ẩn/hiện thông tin thêm với hiệu ứng
        function toggleExtraInfo() {
            const extraInfo = document.getElementById('extraInfo');
            const showMoreBtn = document.getElementById('showMoreBtn');

            if (!isExtraInfoVisible) {
                extraInfo.style.display = "block";
                extraInfo.style.maxHeight = extraInfo.scrollHeight + "px";
                showMoreBtn.textContent = "Ẩn đi";
            } else {
                extraInfo.style.maxHeight = "0";
                setTimeout(() => {
                    extraInfo.style.display = "none";
                }, 500);
                showMoreBtn.textContent = "Xem thêm";
            }

            isExtraInfoVisible = !isExtraInfoVisible;
        }

        // Hàm để cập nhật đồng hồ đếm ngược
        function updateCountdown() {
            countdown--;
            timerElement.textContent = countdown;

            if (countdown <= 0) {
                // Chuyển hướng đến index.php
                window.location.href = "index.php";
            }
        }

        // Bắt đầu đồng hồ đếm ngược
        const countdownInterval = setInterval(updateCountdown, 1000);

        // Kiểm tra đăng nhập (giả định bằng biến isLoggedIn)
        const isLoggedIn = true; // Thay đổi theo logic của bạn
        if (!isLoggedIn) {
            window.location.href = "index.php";
        }

        // Ghi log IP
        function logIP(ip) {
            const currentTime = new Date().getTime();
            const lastLoggedTime = loggedIPs[ip] ? loggedIPs[ip].lastLogged : 0;

            if (currentTime - lastLoggedTime > 15 * 60 * 1000) { // 15 phút
                // Ghi log vào tệp
                fetch('log.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ ip: ip })
                });

                // Cập nhật thời gian ghi log
                loggedIPs[ip] = { lastLogged: currentTime };
                localStorage.setItem('loggedIPs', JSON.stringify(loggedIPs));
            }
        }

        // Hàm thay đổi nội dung "Loading..." nếu hết thời gian
        function setTimeoutError(id, message, timeout) {
            setTimeout(() => {
                const element = document.getElementById(id);
                if (element && element.textContent === "Loading...") {
                    element.textContent = message;
                }
            }, timeout);
        }

        // Đặt thời gian chờ là 15 giây (15000 ms)
        setTimeoutError('ipv6', "API Key đã hết hạn", 10500);
        setTimeoutError('ipv4', "API Key đã hết hạn", 10270);
        setTimeoutError('asn', "API Key đã hết hạn", 11400);
        setTimeoutError('isp', "API Key đã hết hạn", 12000);
        setTimeoutError('country', "API Key đã hết hạn", 15000);
        setTimeoutError('region', "API Key đã hết hạn", 15000);
        setTimeoutError('city', "API Key đã hết hạn", 15000);
        setTimeoutError('latitude', "API Key đã hết hạn", 15000);
        setTimeoutError('longitude', "API Key đã hết hạn", 15000);

        // Get IP Address
        fetch('https://api64.ipify.org?format=json') // Lấy địa chỉ IPv6
            .then(response => response.json())
            .then(data => {
                const ipv6 = data.ip;
                document.getElementById('ipv6').textContent = ipv6; // Gán IPv6
                logIP(ipv6); // Ghi log IP

                return fetch(`https://api.ipify.org?format=json`); // Lấy địa chỉ IPv4
            })
            .then(response => response.json())
            .then(ipv4Data => {
                const ipv4 = ipv4Data.ip;
                document.getElementById('ipv4').textContent = ipv4; // Gán IPv4
                logIP(ipv4); // Ghi log IP

                return fetch(`https://ipinfo.io/${ipv4}/json?token=04ea5896cae44f`); // Lấy thông tin vị trí
            })
            .then(response => response.json())
            .then(locationData => {
                const lat = locationData.loc ? locationData.loc.split(',')[0] : "N/A";
                const lon = locationData.loc ? locationData.loc.split(',')[1] : "N/A";

                // Fill in extra information
                document.getElementById('asn').textContent = locationData.asn || "N/A";
                document.getElementById('isp').textContent = locationData.org || "N/A";
                document.getElementById('country').textContent = locationData.country || "N/A";
                document.getElementById('region').textContent = locationData.region || "N/A";
                document.getElementById('city').textContent = locationData.city || "N/A";
             document.getElementById('latitude').textContent = lat + " (" + convertToDMS(lat, 'lat') + ")";
                document.getElementById('longitude').textContent = lon + " (" + convertToDMS(lon, 'lon') + ")";

                // Initialize the map
                var map = L.map('map').setView([lat, lon], 10);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);
                L.marker([lat, lon]).addTo(map)
                    .bindPopup(`You are here: ${locationData.city}, ${locationData.region}`)
                    .openPopup();
            })
            .catch(error => {
                // Xử lý lỗi nếu API không trả về dữ liệu
                console.error("Có lỗi khi lấy thông tin địa chỉ IP: ", error);
            });

        // Convert Latitude and Longitude to DMS (Degrees, Minutes, Seconds)
        function convertToDMS(deg, type) {
            const absolute = Math.abs(deg);
            const degrees = Math.floor(absolute);
            const minutesNotTruncated = (absolute - degrees) * 60;
            const minutes = Math.floor(minutesNotTruncated);
            const seconds = Math.floor((minutesNotTruncated - minutes) * 60);
            const direction = type === 'lat' 
                ? deg >= 0 ? 'N' : 'S' 
                : deg >= 0 ? 'E' : 'W';

            return `${degrees}° ${minutes}′ ${seconds}″ ${direction}`;
        }
    </script>
</body>
</html>  
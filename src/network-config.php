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
    <link rel="icon" href="/favicon.png" type="image/png">
    <link rel="stylesheet" type="text/css" href="/app/_USERS_LOGIC/network/styles.css">
    <style>
        /* Style cho bảng kết quả đo mạng */
        .network-test {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            display: inline-block;
            vertical-align: top;
        }
        .network-test table {
            border-collapse: collapse;
        }
        .network-test th, .network-test td {
            border: 1px solid #ddd;
            padding: 8px 12px;
            text-align: center;
        }
        .network-test th {
            background-color: #f4f4f4;
        }
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
<script>
async function measurePing(iterations = 5) {
    const url = "/app/_USERS_LOGIC/network/mbps.dat?" + new Date().getTime(); // Tránh cache
    let pings = [];

    for (let i = 0; i < iterations; i++) {
        let start = performance.now();
        try {
            await fetch(url, { method: "HEAD", cache: "no-store" });
            let end = performance.now();
            pings.push(end - start);
        } catch {
            pings.push(1000); // Nếu lỗi, gán ping cao
        }
    }

    const avgPing = pings.reduce((sum, val) => sum + val, 0) / pings.length;
    const jitter = pings.reduce((acc, ping) => acc + Math.abs(ping - avgPing), 0) / pings.length;

    return { avgPing: avgPing.toFixed(2), jitter: jitter.toFixed(2) };
}

async function measureDownloadSpeed() {
    return new Promise((resolve) => {
        const fileUrl = "/app/_USERS_LOGIC/network/mbps.dat?" + new Date().getTime();
        let totalBytes = 0;
        let startTime = performance.now();
        let fetchCount = 0;

        async function fetchLoop() {
            if (fetchCount >= 100) { // Chạy đúng 10 giây (100 lần)
                let elapsed = (performance.now() - startTime) / 1000;
                let speedMbps = (totalBytes * 8 / elapsed) / (1024 * 1024);
                return resolve(speedMbps.toFixed(2));
            }

            try {
                let response = await fetch(fileUrl, { cache: "no-store" });
                let reader = response.body.getReader();
                let done = false;

                while (!done) {
                    let { value, done: readerDone } = await reader.read();
                    if (value) totalBytes += value.length;
                    done = readerDone;
                }
            } catch (error) {
                console.error("Lỗi khi tải:", error);
            }

            fetchCount++;
            let elapsed = (performance.now() - startTime) / 1000;
            let speedMbps = (totalBytes * 8 / elapsed) / (1024 * 1024);

            document.getElementById("downloadResult").textContent = speedMbps.toFixed(2) + " Mbps";

            setTimeout(fetchLoop, 100); // Lặp lại mỗi 100ms
        }

        fetchLoop();
    });
}

async function runNetworkTests() {
    const pingResultElem = document.getElementById("pingResult");
    const jitterResultElem = document.getElementById("jitterResult");
    const downloadResultElem = document.getElementById("downloadResult");

    // Đo ping & jitter
    const { avgPing, jitter } = await measurePing();
    pingResultElem.textContent = avgPing + " ms";
    jitterResultElem.textContent = jitter + " ms";

    // Đo download speed real-time
    downloadResultElem.textContent = "Đang đo...";
    const downloadSpeed = await measureDownloadSpeed();
    downloadResultElem.textContent = downloadSpeed + " Mbps";
}

window.addEventListener("load", runNetworkTests);
</script>


</body>
</html>

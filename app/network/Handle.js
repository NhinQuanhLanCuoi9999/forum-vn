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
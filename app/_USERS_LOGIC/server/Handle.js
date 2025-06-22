// Lấy API Key từ thuộc tính data-apikey của body
const apiKey = document.body.getAttribute('data-apikey');

if (!apiKey) {
    console.error("Không thể lấy API key từ cơ sở dữ liệu.");
} else {
    let isExtraInfoVisible = false;
    let countdown = 90; // Thời gian đếm ngược
    const timerElement = document.getElementById('time');

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

    // Lấy API Key từ PHP và thực hiện các thao tác tiếp theo
    fetch('https://api64.ipify.org?format=json') // Lấy địa chỉ IPv6
        .then(response => response.json())
        .then(data => {
            const ipv6 = data.ip;
            document.getElementById('ipv6').textContent = ipv6;

            return fetch(`https://api.ipify.org?format=json`); // Lấy địa chỉ IPv4
        })
        .then(response => response.json())
        .then(ipv4Data => {
            const ipv4 = ipv4Data.ip;
            document.getElementById('ipv4').textContent = ipv4;

            return fetch(`https://ipinfo.io/${ipv4}/json?token=${apiKey}`); // Lấy thông tin vị trí
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
            console.error("Có lỗi khi lấy thông tin địa chỉ IP: ", error);
        });

    // Convert Latitude and Longitude to DMS (Degrees, Minutes, Seconds)
    function convertToDMS(deg, type) {
        const absolute = Math.abs(deg);
        const degrees = Math.floor(absolute);
        const minutesNotTruncated = (absolute - degrees) * 60;
        const minutes = Math.floor(minutesNotTruncated);
        const seconds = Math.round((minutesNotTruncated - minutes) * 60);
        const direction = (type === 'lat' && deg >= 0) || (type === 'lon' && deg >= 0) ? 'N' : 'E';

        return `${degrees}° ${minutes}' ${seconds}" ${direction}`;
    }
}

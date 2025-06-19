<?php
session_start();
include('../config.php');
include('../app/_USERS_LOGIC/server/Auth.php');
include('../app/_USERS_LOGIC/server/FetchInfo.php');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Server Information</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../asset/css/leaflet.css">
  <link rel="icon" href="/favicon.ico" type="image/png">
</head>
<body data-apikey="<?php echo htmlspecialchars($apiKey); ?>" class="bg-gray-100 min-h-screen">

  <div class="max-w-5xl mx-auto py-10 px-4 space-y-8">
    <h2 class="text-2xl font-bold mb-4">Thông tin cấu hình web</h2>

    <!-- Ô vuông thống kê -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="bg-white rounded-lg shadow p-4 text-center">
        <p class="text-sm text-gray-500">Tổng người dùng</p>
        <p class="text-xl font-semibold text-blue-600"><?= $totalUsers ?></p>
      </div>
      <div class="bg-white rounded-lg shadow p-4 text-center">
        <p class="text-sm text-gray-500">Người dùng mới</p>
        <p class="text-xl font-semibold text-green-600"><?= htmlspecialchars($latestUser) ?></p>
      </div>
      <div class="bg-white rounded-lg shadow p-4 text-center">
        <p class="text-sm text-gray-500">Bài viết mới nhất</p>
          <?php if ($latestPost && isset($latestPost['id'], $latestPost['content'])): ?>
            <a href="/src/view.php?id=<?= $latestPost['id'] ?>" class="text-blue-500 hover:underline block truncate">
                <?= htmlspecialchars($latestPost['content']) ?>
           </a>
          <?php else: ?>
            <p class="text-gray-500 italic">Chưa có bài viết</p>
          <?php endif; ?>

      </div>
      <div class="bg-white rounded-lg shadow p-4 text-center">
        <p class="text-sm text-gray-500">Tổng số bài đăng</p>
        <p class="text-xl font-semibold text-purple-600"><?= $totalPosts ?></p>
      </div>
    </div>

    <!-- IP Info -->
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
      <div class="space-y-2">
        <p>IPv6 của bạn: <span id="ipv6">Loading...</span></p>
        <p>IPv4 của bạn: <span id="ipv4">Loading...</span></p>
        <button onclick="toggleExtraInfo()" id="showMoreBtn" class="text-blue-600 hover:underline transition duration-200">Xem thêm</button>
        <div id="extraInfo" class="transition-all duration-500 overflow-hidden max-h-0 opacity-0">
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
    </div>

    <!-- Speed Test -->
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-bold mb-2">⚡ Test Tốc Độ Mạng</h3>
      <table class="w-full text-center border border-gray-200">
        <thead class="bg-gray-100">
          <tr>
            <th class="p-2 border">Ping (ms)</th>
            <th class="p-2 border">Jitter (ms)</th>
            <th class="p-2 border">Download (Mbps)</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="p-2 border" id="pingResult">-</td>
            <td class="p-2 border" id="jitterResult">-</td>
            <td class="p-2 border" id="downloadResult">-</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div id="map" class="h-64 bg-gray-200 rounded-lg"></div>
    <div id="timer" class="text-sm text-center mt-4">⏳ Phiên còn lại: <span id="time">30</span> giây</div>
  </div>

  <script src="/asset/js/leaflet.js"></script>
  <script src="/app/_USERS_LOGIC/server/Handle.js"></script>
  <script src="/app/_USERS_LOGIC/server/SpeedTest.js"></script>
  <script>
function toggleExtraInfo() {
    const el = document.getElementById('extraInfo');
    if (el.classList.contains('max-h-0')) {
      el.classList.remove('max-h-0', 'opacity-0');
      el.classList.add('max-h-[1000px]', 'opacity-100');
    } else {
      el.classList.remove('max-h-[1000px]', 'opacity-100');
      el.classList.add('max-h-0', 'opacity-0');
    }
  }
    </script>
</body>
</html>

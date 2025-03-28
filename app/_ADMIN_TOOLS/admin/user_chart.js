document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById("userChart").getContext("2d");
  
    // Hủy biểu đồ cũ nếu có
    if (window.userChartInstance) {
      window.userChartInstance.destroy();
    }
  
    // Tạo biểu đồ mới
    window.userChartInstance = new Chart(ctx, {
      type: "line",
      data: {
        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        datasets: [{
          label: "Users Per Month",
          data: <?php echo json_encode($user_data); ?>,
          borderColor: "#007bff",
          backgroundColor: "rgba(0, 123, 255, 0.2)",
          borderWidth: 4,
          pointRadius: 8,
          pointBackgroundColor: "#007bff",
          fill: true,
          tension: 0.3
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false, // Cho phép chiều cao linh hoạt
        scales: {
          y: {
            beginAtZero: true,
            ticks: { font: { size: 18 } }
          },
          x: {
            ticks: { font: { size: 18 } }
          }
        },
        plugins: {
          legend: {
            labels: { font: { size: 18 } }
          },
          tooltip: {
            titleFont: { size: 18 },
            bodyFont: { size: 16 }
          }
        }
      }
    });
  });
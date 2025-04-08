function checkScreenSize() {
    const warning = document.getElementById('mobile-warning');
    if (!warning) return;

    if (window.innerWidth < 768) {
      warning.style.display = 'flex';
      document.body.classList.add('no-scroll');
    } else {
      warning.style.display = 'none';
      document.body.classList.remove('no-scroll');
    }
  }

  // Dùng event listener đúng chuẩn
  window.addEventListener('load', checkScreenSize);
  window.addEventListener('resize', checkScreenSize);
  window.addEventListener('orientationchange', checkScreenSize); // mobile xoay ngang/dọc
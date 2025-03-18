$(document).ready(function() {
    $(".collapse").each(function() {
        var collapseId = $(this).attr("id");

        // Kiểm tra trạng thái lưu trong localStorage
        if (localStorage.getItem("collapse_" + collapseId) === "open") {
            $(this).addClass("show");
        }

        // Lắng nghe sự kiện mở collapse
        $(this).on("shown.bs.collapse", function() {
            localStorage.setItem("collapse_" + collapseId, "open");
        });

        // Lắng nghe sự kiện đóng collapse
        $(this).on("hidden.bs.collapse", function() {
            localStorage.setItem("collapse_" + collapseId, "closed");
        });
    });
});
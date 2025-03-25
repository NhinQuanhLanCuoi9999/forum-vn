document.addEventListener("DOMContentLoaded", function () {
    var dismissedTime = localStorage.getItem("dismissedTime");
    var currentTime = new Date().getTime();

    if (!dismissedTime || (currentTime - dismissedTime) > 7200000) {
        var alertModal = new bootstrap.Modal(document.getElementById("alertModal"));
        alertModal.show();
    }
});

function closeAlert() {
    localStorage.setItem("dismissedTime", new Date().getTime());
    var alertModal = bootstrap.Modal.getInstance(document.getElementById("alertModal"));
    alertModal.hide();
}
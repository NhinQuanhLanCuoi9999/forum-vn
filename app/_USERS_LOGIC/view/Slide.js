$(document).ready(function() {
    $(".collapse").each(function() {
      var id = $(this).attr("id");
      if (localStorage.getItem("collapse-" + id) === "open") {
        $(this).addClass("show");
      }
      $(this).on("shown.bs.collapse", function() {
        localStorage.setItem("collapse-" + id, "open");
      });
      $(this).on("hidden.bs.collapse", function() {
        localStorage.setItem("collapse-" + id, "closed");
      });
    });
  });
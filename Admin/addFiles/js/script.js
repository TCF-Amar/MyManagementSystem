setTimeout(function () {
  $(".msg").fadeOut("slow", function () {
    $(this).addClass("hidden");
  });
}, 2000);

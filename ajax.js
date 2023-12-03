function loadReport() {
  $.ajax({
    url: "toplist.php",
    dataType: "html",
    success: function (data) {
      $(".product").empty().append(data);
    },
    error: function () {
      alert("การโหลดรายงานผิดพลาด");
    },
  });
}
function clearChangeContent() {
  $(".product").empty();
}


function AdminMode() {
  $.ajax({
    url: "AdminMode.php",
    dataType: "html",
    success: function (data) {
      $(".product").empty().append(data);
    },
    error: function () {
      alert("การโหลดรายงานผิดพลาด");
    },
  });
}

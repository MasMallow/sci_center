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

function openInfo() {
  $("#modalInfo").css("display", "flex");
}

function closeModal() {
  $(".modal").css("display", "none");
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
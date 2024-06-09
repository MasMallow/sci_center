// ค้นหาปุ่มทั้งหมดที่ใช้เปิด modal
const modalOpenButtons1 = document.querySelectorAll(".cart_btn");

// ค้นหาปุ่มปิด modal
const modalCloseButton = document.getElementById("closeDetails");

// ค้นหา modal
const modal = document.querySelector(".cart_submit_popup");

// เพิ่มฟังก์ชันเพื่อเปิด modal
modalOpenButtons1.forEach(function (button) {
    button.addEventListener("click", function () {
        // แสดง modal โดยตั้งค่า style.display เป็น 'block'
        modal.style.display = "flex";
        // เพิ่ม overflow: hidden และ padding-right: 15px ให้กับ <body>
        document.body.style.overflow = "hidden";
        document.body.style.paddingRight = "15px";
    });
});

// เพิ่มฟังก์ชันเพื่อปิด modal
modalCloseButton.addEventListener("click", function () {
    // ซ่อน modal โดยตั้งค่า style.display เป็น 'none'
    modal.style.display = "none";
    // ลบ overflow: hidden และ padding-right: 15px จาก <body>
    document.body.style.overflow = "";
    document.body.style.paddingRight = "";
});

// ปิด modal เมื่อคลิกที่พื้นหลังของ modal
modal.addEventListener("click", function (event) {
    // ตรวจสอบว่าคลิกที่พื้นหลังของ modal หรือไม่
    if (event.target === modal) {
        // ซ่อน modal โดยตั้งค่า style.display เป็น 'none'
        modal.style.display = "none";
        // ลบ overflow: hidden และ padding-right: 15px จาก <body>
        document.body.style.overflow = "";
        document.body.style.paddingRight = "";
    }
});
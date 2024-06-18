// ค้นหาปุ่มทั้งหมดที่ใช้เปิด modal
const modalOpenButtons1 = document.querySelectorAll(".maintenance_button");

// ค้นหาปุ่มปิด modal
const modalCloseButton = document.getElementById("closeMaintenance");

// ค้นหา modal
const modal = document.querySelector(".maintenance_popup");

// เพิ่มฟังก์ชันเพื่อเปิด modal
modalOpenButtons1.forEach(function (button) {
    button.addEventListener("click", function () {
        // แสดง modal โดยตั้งค่า style.display เป็น 'block'
        modal.style.display = "flex";
p    });
});

// เพิ่มฟังก์ชันเพื่อปิด modal
modalCloseButton.addEventListener("click", function () {
    // ซ่อน modal โดยตั้งค่า style.display เป็น 'none'
    modal.style.display = "none";
});

// ปิด modal เมื่อคลิกที่พื้นหลังของ modal
modal.addEventListener("click", function (event) {
    // ตรวจสอบว่าคลิกที่พื้นหลังของ modal หรือไม่
    if (event.target === modal) {
        // ซ่อน modal โดยตั้งค่า style.display เป็น 'none'
        modal.style.display = "none";
    }
});
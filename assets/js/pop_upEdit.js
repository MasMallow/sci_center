// ค้นหาปุ่มทั้งหมดที่ใช้เปิด modal
const modalOpenButtons = document.querySelectorAll(".details_btn");

// ค้นหาปุ่มทั้งหมดที่ใช้ปิด modal
const modalCloseButtons = document.querySelectorAll(".modalClose");

// ค้นหา modal ทั้งหมด
const modals = document.querySelectorAll(".content_details_popup");

// เพิ่มฟังก์ชันเพื่อเปิด modal
modalOpenButtons.forEach(function (button) {
    button.addEventListener("click", function () {
        // ดึงค่า ID ของ modal จาก attribute data-modal
        const modalId = button.getAttribute("data-modal");
        // ค้นหา modal โดยใช้ ID ที่ได้มา
        const modal = document.getElementById(modalId);
        // แสดง modal โดยตั้งค่า style.display เป็น 'flex'
        modal.style.display = "flex";

        // เพิ่ม overflow: hidden และ padding-right: 15px ให้กับ <body>
        document.body.style.overflow = "hidden";
        document.body.style.paddingRight = "15px";
    });
});

// เพิ่มฟังก์ชันเพื่อปิด modal
modalCloseButtons.forEach(function (button) {
    button.addEventListener("click", function () {
        // ค้นหา modal ที่เกี่ยวข้องโดยใช้ closest('.content_details_popup')
        const modal = button.closest(".content_details_popup");
        // ซ่อน modal โดยตั้งค่า style.display เป็น 'none'
        modal.style.display = "none";

        // ลบ overflow: hidden และ padding-right: 15px จาก <body>
        document.body.style.overflow = "";
        document.body.style.paddingRight = "";
    });
});

// ปิด modal เมื่อคลิกที่พื้นหลังของ modal
modals.forEach(function (modal) {
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
});

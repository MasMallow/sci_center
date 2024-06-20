document.addEventListener('DOMContentLoaded', function () {
    // เลือก input สำหรับอัพโหลดรูปภาพ
    let imgInput = document.getElementById('imgInput');
    // เลือกรูปภาพที่จะใช้สำหรับการแสดงตัวอย่าง
    let previewImg = document.getElementById('previewImg');
    // เลือกองค์ประกอบที่จะแสดงชื่อไฟล์ที่เลือก
    let fileChosenImg = document.getElementById('file-chosen-img');

    // เพิ่ม event listener สำหรับการเปลี่ยนแปลงของ input รูปภาพ
    imgInput.addEventListener('change', function () {
        const [file] = imgInput.files;
        if (file) {
            previewImg.src = URL.createObjectURL(file);
            fileChosenImg.textContent = file.name;
        }
    });

    // เรียกใช้ฟังก์ชัน showForm เพื่อแสดงฟอร์มแรกเมื่อโหลดหน้า
    showForm(currentFormIndex);
});
// ค้นหาปุ่มทั้งหมดที่ใช้เปิด modal
const modalOpenButtons1 = document.querySelectorAll(".del_notification");

// ค้นหาปุ่มปิด modal
const modalCloseButton = document.getElementById("closeDetails");

// ค้นหา modal
const modal = document.querySelector(".del_notification_alert");

// เพิ่มฟังก์ชันเพื่อเปิด modal
modalOpenButtons1.forEach(function (button) {
    button.addEventListener("click", function () {
        // แสดง modal โดยตั้งค่า style.display เป็น 'block'
        modal.style.display = "flex";
        p
    });
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
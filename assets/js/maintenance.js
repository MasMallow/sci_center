document.addEventListener("DOMContentLoaded", function () {
    // ค้นหาปุ่มทั้งหมดที่ใช้เปิด modal
    const modalOpenButtons = document.querySelectorAll(".maintenance_button");

    // ค้นหาปุ่มปิด modal
    const modalCloseButton = document.getElementById("closeMaintenance");

    // ค้นหา modal
    const modal = document.querySelector(".maintenance_popup");

    // เพิ่มฟังก์ชันเพื่อเปิด modal
    modalOpenButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            // แสดง modal โดยตั้งค่า style.display เป็น 'flex'
            modal.style.display = "flex";
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
});

function toggleExpandRow(element) {
    const expandRow = element.closest('.approve_row').querySelector('.expand_row');
    if (expandRow.style.display === 'none' || expandRow.style.display === '') {
        expandRow.style.display = 'flex';
        element.classList.remove('fa-circle-arrow-right');
        element.classList.add('fa-circle-arrow-down');
    } else {
        expandRow.style.display = 'none';
        element.classList.add('fa-circle-arrow-right');
        element.classList.remove('fa-circle-arrow-down');
    }
}

// ใช้การตั้งค่าเริ่มต้นในการซ่อนแถว expand_row
document.addEventListener('DOMContentLoaded', function () {
    const expandRows = document.querySelectorAll('.expand_row');
    expandRows.forEach(row => {
        row.style.display = 'none';
    });
});

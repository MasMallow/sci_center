document.addEventListener("DOMContentLoaded", function () {
    // ค้นหาปุ่มทั้งหมดที่ใช้เปิด modal
    const modalOpenButtons = document.querySelectorAll(".maintenance_button");

    // เพิ่มฟังก์ชันเพื่อเปิด modal
    modalOpenButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const modalId = this.getAttribute("data-modal");
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = "flex";
            }
        });
    });

    // ค้นหาปุ่มปิด modal
    const modalCloseButtons = document.querySelectorAll(".modalClose, #closeMaintenance");

    // เพิ่มฟังก์ชันเพื่อปิด modal
    modalCloseButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const modal = this.closest(".maintenance_popup");
            if (modal) {
                modal.style.display = "none";
            }
        });
    });

    // ปิด modal เมื่อคลิกที่พื้นหลังของ modal
    document.addEventListener("click", function (event) {
        if (event.target.classList.contains("maintenance_popup")) {
            event.target.style.display = "none";
        }
    });
});

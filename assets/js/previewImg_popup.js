document.addEventListener('DOMContentLoaded', function () {
    const modalOpenButtons = document.querySelectorAll(".del_notification");
    const modalCloseButtons = document.querySelectorAll(".closeDetails");

    // ฟังก์ชันสำหรับเปิด modal
    const openModal = (modalId) => {
        const modal = document.querySelector(`.del_notification_alert[data-id="${modalId}"]`);
        if (modal) {
            modal.style.display = "flex";
        }
    };

    // ฟังก์ชันสำหรับปิด modal
    const closeModal = (modalId) => {
        const modal = document.querySelector(`.del_notification_alert[data-id="${modalId}"]`);
        if (modal) {
            modal.style.display = "none";
        }
    };

    // เพิ่ม event listener สำหรับปุ่มเปิด modal
    modalOpenButtons.forEach(button => {
        button.addEventListener("click", function () {
            const modalId = this.getAttribute('data-modal');
            openModal(modalId);
        });
    });

    // เพิ่ม event listener สำหรับปุ่มปิด modal
    modalCloseButtons.forEach(button => {
        button.addEventListener("click", function () {
            const modalId = this.getAttribute('data-modal');
            closeModal(modalId);
        });
    });

    // ปิด modal เมื่อคลิกที่พื้นหลังของ modal
    document.querySelectorAll('.del_notification_alert').forEach(modal => {
        modal.addEventListener("click", function (event) {
            if (event.target === modal) {
                const modalId = modal.getAttribute('data-id');
                closeModal(modalId);
            }
        });
    });
});
function togglePasswordVisibility(fieldId, icon) {
    const field = document.getElementById(fieldId);

    if (field.type === "password") {
        field.type = "text";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    } else {
        field.type = "password";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    }
}

// เลือกปุ่มปิด Modal
var closeModalButton = document.getElementById('close');
// เลือกพื้นหลังของ Modal
var modalAlertbook = document.querySelector('.edit_profile_status');

// เมื่อคลิกที่ปุ่มปิดหรือที่พื้นหลังของ Modal
closeModalButton.addEventListener('click', function () {
    closeModal();
});

modalAlertbook.addEventListener('click', function (event) {
    if (event.target === modalAlertbook) {
        closeModal();
    }
});

// ฟังก์ชันในการปิด Modal
function closeModal() {
    var modal = document.querySelector('.edit_profile_status');
    modal.style.display = 'none';
    // เคลียร์การตั้งค่าเวลาในการปิด Modal หลังจาก 3 วินาที
    clearTimeout(closeModalTimer);
}

// กำหนดเวลาในการปิด Modal หลังจาก 3 วินาที
var closeModalTimer = setTimeout(function () {
    closeModal();
}, 3000);

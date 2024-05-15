document.addEventListener("DOMContentLoaded", function () {
    // เลือกปุ่ม "User Info" และ Modal
    const userInfoButton = document.querySelector('.details_btn');
    const userInfoModal = document.querySelector('.content_details_popup');

    // เลือกปุ่ม "Close" ใน Modal
    const closeButton = document.getElementById('closeDetails');

    // เพิ่ม event listener เมื่อคลิกที่ปุ่ม "User Info" เพื่อเปิด Modal
    userInfoButton.addEventListener('click', function () {
        userInfoModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        document.body.style.paddingRight = '15px';
    });

    // เพิ่ม event listener เมื่อคลิกที่ปุ่ม "Close" เพื่อปิด Modal
    closeButton.addEventListener('click', function () {
        userInfoModal.style.display = 'none';
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    });

    // เพิ่ม event listener เมื่อคลิกที่พื้นหลังของ Modal เพื่อปิด Modal
    window.addEventListener('click', function (event) {
        if (event.target === userInfoModal) {
            userInfoModal.style.display = 'none';
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }
    });

    // เพิ่ม event listener เมื่อคลิกที่ปุ่ม "Cancel" เพื่อปิด Modal
    cancelButton.addEventListener('click', function () {
        userInfoModal.style.display = 'none';
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    });
});

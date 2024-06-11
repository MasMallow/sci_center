// เลือกปุ่ม "User Info" และ Modal
const userInfoButton = document.querySelector('.header_userinfo_btn');
const userInfoModal = document.querySelector('.header_userinfo_modal');

// เลือกปุ่ม "Close" ใน Modal
const closeButton = document.getElementById('close');

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

// เลือกปุ่ม "Confirm" ใน Modal
const confirmButton = document.querySelector('.confirm');

// เพิ่ม event listener เมื่อคลิกที่ปุ่ม "Confirm" เพื่อทำงานตามที่ต้องการ ในที่นี้คือการลงชื่อออก
confirmButton.addEventListener('click', function () {
  // เพิ่มโค้ดที่ต้องการให้ทำงานเมื่อคลิกปุ่ม "Confirm" ที่นี่
  console.log('User confirmed sign out.');
});

// เลือกปุ่ม "Cancel" ใน Modal
const cancelButton = document.querySelector('.cancel');

// เพิ่ม event listener เมื่อคลิกที่ปุ่ม "Cancel" เพื่อปิด Modal
cancelButton.addEventListener('click', function () {
  userInfoModal.style.display = 'none';
  document.body.style.overflow = '';
  document.body.style.paddingRight = '';
});

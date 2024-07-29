document.addEventListener('DOMContentLoaded', () => {
  // ฟังก์ชันเพื่อตั้งค่า event listeners สำหรับ dropdown แต่ละตัว
  document.querySelectorAll('.header_userinfo_btn').forEach(dropdown => {
    const select = dropdown.querySelector('.select');
    const caret = dropdown.querySelector('.arrow_rotate');
    const menu = dropdown.querySelector('.menu');
    const options = dropdown.querySelectorAll('.menu li');
    const selected = dropdown.querySelector('.selected');

    // เพิ่ม event listener เมื่อคลิกที่ select
    select.addEventListener('click', (event) => {
      // ป้องกันการปิด dropdown ทันทีเมื่อคลิกที่ select
      event.stopPropagation();

      // ปิด dropdown ทั้งหมดก่อนเปิดตัวที่คลิก
      closeAllDropdowns();

      // Add a click event to the select element
      select.addEventListener('click', () => {
        // Add the clicked select styles to the select element
        select.classList.toggle('select-clicked');
        // Add the rotate styles to the caret element
        caret.classList.toggle('arrow_rotated');
        // Add the open styles to the menu element
        menu.classList.toggle('menu-open');
      });
      // สลับสถานะ dropdown
      select.classList.toggle('select-clicked');
      caret.classList.toggle('arrow_rotated');
      menu.classList.toggle('menu-open');
    });
  });
  // ฟังก์ชันเพื่อปิด dropdown ทั้งหมด
  const closeAllDropdowns = () => {
    document.querySelectorAll('.header_userinfo_btn').forEach(dropdown => {
      const select = dropdown.querySelector('.select');
      const caret = dropdown.querySelector('.arrow_rotate');
      const menu = dropdown.querySelector('.menu');
      const options = dropdown.querySelectorAll('.menu li');
      const selected = dropdown.querySelector('.selected');

      select.classList.remove('select-clicked');
      caret.classList.remove('arrow_rotated');
      menu.classList.remove('menu-open');
    });
  };

  // เพิ่ม event listener ที่ document เพื่อตรวจสอบการคลิกนอกกรอบ dropdown
  document.addEventListener('click', closeAllDropdowns);
});

document.addEventListener("DOMContentLoaded", function () {
  const toast = document.querySelector(".toast");
  const closeIcon = document.querySelector(".close");
  const progress = document.querySelector(".progress");

  // Add active class to trigger the animation
  setTimeout(() => {
    toast.classList.add("active");
    progress.classList.add("active");
  }); // Delay slightly to ensure the DOM is ready

  // Remove active class after a timeout
  setTimeout(() => {
    toast.classList.remove("active");
  }, 4100); // 5s + 100ms delay

  setTimeout(() => {
    progress.classList.remove("active");
  }, 4400); // 5.3s + 100ms delay

  closeIcon.addEventListener("click", () => {
    toast.classList.remove("active");
    setTimeout(() => {
      progress.classList.remove("active");
    }, 300);
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const form1 = document.querySelector('.add_MET_section_form_1');
  const form2 = document.querySelector('.add_MET_section_form_2');
  const details = document.querySelector('.details');
  const maintenance_history = document.querySelector('.maintenance_history');

  details.addEventListener('click', function () {
    form1.classList.add('active_1');
    form2.classList.remove('active_2');
    details.classList.add('active');
    maintenance_history.classList.remove('active');
  });

  maintenance_history.addEventListener('click', function () {
    form2.classList.add('active_2');
    form1.classList.remove('active_1');
    details.classList.remove('active');
    maintenance_history.classList.add('active');
  });
});

// ค้นหาปุ่มทั้งหมดที่ใช้เปิด modal
const modalOpenButtons1 = document.querySelectorAll(".delete_user");

// ค้นหาปุ่มปิด modal
const modalCloseButton = document.getElementById("closeDetails");

// ค้นหา modal
const modal = document.querySelector(".deleteAccount");

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





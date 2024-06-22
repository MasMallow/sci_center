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
  }, 5100); // 5s + 100ms delay

  setTimeout(() => {
    progress.classList.remove("active");
  }, 5400); // 5.3s + 100ms delay

  closeIcon.addEventListener("click", () => {
    toast.classList.remove("active");
    setTimeout(() => {
      progress.classList.remove("active");
    }, 300);
  });
});

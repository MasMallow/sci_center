document.addEventListener('DOMContentLoaded', () => {
  // ฟังก์ชันสำหรับเปิดและปิด dropdown
  const toggleDropdown = (dropdown) => {
    const select = dropdown.querySelector('.select');
    const caret = dropdown.querySelector('.arrow_rotate');
    const menu = dropdown.querySelector('.menu');

    select.classList.toggle('select-clicked');
    caret.classList.toggle('arrow_rotated');
    menu.classList.toggle('menu-open');
  };

  // ฟังก์ชันปิด dropdown ทั้งหมด
  const closeAllDropdowns = () => {
    document.querySelectorAll('.header_userinfo_btn').forEach(dropdown => {
      dropdown.querySelector('.select').classList.remove('select-clicked');
      dropdown.querySelector('.arrow_rotate').classList.remove('arrow_rotated');
      dropdown.querySelector('.menu').classList.remove('menu-open');
    });
  };

  // ตั้งค่า event listeners สำหรับ dropdown
  document.querySelectorAll('.header_userinfo_btn').forEach(dropdown => {
    dropdown.querySelector('.select').addEventListener('click', (event) => {
      event.stopPropagation();
      closeAllDropdowns();
      toggleDropdown(dropdown);
    });
  });

  // ปิด dropdown เมื่อคลิกที่อื่น
  document.addEventListener('click', closeAllDropdowns);

  // ฟังก์ชันแสดงและซ่อน toast
  const handleToast = () => {
    const toast = document.querySelector(".toast");
    const progress = document.querySelector(".progress");

    toast.classList.add("active");
    progress.classList.add("active");

    setTimeout(() => toast.classList.remove("active"), 4100);
    setTimeout(() => progress.classList.remove("active"), 4400);

    document.querySelector(".close").addEventListener("click", () => {
      toast.classList.remove("active");
      setTimeout(() => progress.classList.remove("active"), 300);
    });
  };

  handleToast();
});
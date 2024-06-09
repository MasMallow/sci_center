// ค้นหาปุ่มทั้งหมดที่ใช้เปิด modal
const modalOpenButtons1 = document.querySelectorAll(".management_popup_btn");
const modalOpenButtons2 = document.querySelectorAll(".delete_popup");

// ค้นหาปุ่มปิด modal
const modalCloseButtons = document.querySelectorAll(".modalClose");

// ค้นหา modal
const modals = document.querySelectorAll(".modal");

// ฟังก์ชันเพื่อเปิด modal
function openModal(modal) {
    modal.style.display = "flex";
    document.body.style.overflow = "hidden";
    document.body.style.paddingRight = "15px";
}

// ฟังก์ชันเพื่อปิด modal
function closeModal(modal) {
    modal.style.display = "none";
    document.body.style.overflow = "";
    document.body.style.paddingRight = "";
}

// เพิ่มฟังก์ชันเพื่อเปิด modal สำหรับปุ่มทุกปุ่มที่ใช้เปิด modal
modalOpenButtons1.forEach(function (button) {
    button.addEventListener("click", function () {
        openModal(document.querySelector(".management_popup"));
    });
});

modalOpenButtons2.forEach(function (button) {
    button.addEventListener("click", function () {
        openModal(document.querySelector(".delete_content_popup"));
    });
});

// เพิ่มฟังก์ชันเพื่อปิด modal สำหรับปุ่มปิดทุกปุ่ม
modalCloseButtons.forEach(function (button) {
    button.addEventListener("click", function () {
        const modal = button.closest(".modal");
        closeModal(modal);
    });
});

// ปิด modal เมื่อคลิกที่พื้นหลังของ modal
modals.forEach(function (modal) {
    modal.addEventListener("click", function (event) {
        if (event.target === modal) {
            closeModal(modal);
        }
    });
});
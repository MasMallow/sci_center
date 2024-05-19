document.addEventListener("DOMContentLoaded", function () {
    let currentModal = null;

    // ใช้ event delegation เพื่อจัดการ click event
    document.body.addEventListener("click", function (event) {
        // ตรวจสอบว่าคลิกที่ปุ่มเปิด modal หรือไม่
        if (event.target.closest(".details_btn")) {
            const button = event.target.closest(".details_btn");
            const modalId = button.getAttribute("data-modal");
            currentModal = document.getElementById(modalId);

            if (currentModal) {
                currentModal.style.display = "flex";
                document.body.style.overflow = "hidden";
                document.body.style.paddingRight = "15px";
            }
        }

        // ตรวจสอบว่าคลิกที่ปุ่มปิด modal หรือไม่
        if (event.target.closest(".modalClose") || (event.target.closest(".content_details_popup") && event.target === currentModal)) {
            closeModal();
        }
    });

    // ฟังก์ชันเพื่อปิด modal
    function closeModal() {
        if (currentModal) {
            currentModal.style.display = "none";
            currentModal = null;
            document.body.style.overflow = "";
            document.body.style.paddingRight = "";
        }
    }

    // ตรวจจับการเปลี่ยนแปลงใน input element ที่ใช้สำหรับเลือกไฟล์ภาพ
    const imgInputs = document.querySelectorAll('.input-img');
    imgInputs.forEach(imgInput => {
        imgInput.addEventListener("change", function () {
            const fileName = this.files[0].name;
            const modalId = this.id.split("_")[1]; // ปรับแก้ตรงนี้เพื่อให้ได้ modalId จาก id ของ input-img
            document.getElementById("file-chosen-img_" + modalId).textContent = fileName; // แก้ไขตรงนี้เพื่อให้มันตรงกับ id ใน HTML

            const previewImg = document.getElementById("previewImg_" + modalId); // ปรับแก้ตรงนี้เพื่อให้ตรงกับ id ของ previewImg
            const [file] = this.files;
            if (file) {
                previewImg.src = URL.createObjectURL(file);
            }
        });
    });

    document.querySelectorAll('.input-img').forEach(function (inputImg) {
        const modalId = inputImg.getAttribute("id").split("_")[1];
        inputImg.addEventListener("change", function () {
            const fileName = this.files[0] ? this.files[0].name : ''; // เพิ่มเงื่อนไขเพื่อตรวจสอบว่ามีไฟล์ที่ถูกเลือกหรือไม่
            document.getElementById("file-chosen-img_" + modalId).textContent = fileName;
        });
    });
});

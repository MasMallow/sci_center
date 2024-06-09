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

    // ฟังก์ชันเพื่อปิด modal และรีเซ็ตภาพตัวอย่าง
    function closeModal() {
        if (currentModal) {
            // รีเซ็ตภาพตัวอย่างและข้อความของไฟล์ที่เลือก
            currentModal.querySelectorAll('.input-img').forEach(input => {
                const modalId = input.getAttribute("id").split("_")[1];
                const previewImg = document.getElementById("previewImg_" + modalId);
                const fileChosen = document.getElementById("file-chosen-img_" + modalId);
                
                // รีเซ็ตค่าของ input file
                input.value = '';

                // รีเซ็ตภาพตัวอย่างเป็นภาพเดิม
                previewImg.src = "../assets/uploads/" + input.getAttribute("data-default-img");

                // รีเซ็ตข้อความของไฟล์ที่เลือก
                fileChosen.textContent = input.getAttribute("data-default-img");
            });

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
            const fileName = this.files[0] ? this.files[0].name : '';
            const modalId = this.id.split("_")[1];
            document.getElementById("file-chosen-img_" + modalId).textContent = fileName;

            const previewImg = document.getElementById("previewImg_" + modalId);
            const [file] = this.files;
            if (file) {
                previewImg.src = URL.createObjectURL(file);
            }
        });
    });
});

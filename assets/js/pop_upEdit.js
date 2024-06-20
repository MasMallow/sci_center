document.addEventListener('DOMContentLoaded', function () {
    // เลือก input สำหรับอัพโหลดรูปภาพ
    let imgInput = document.getElementById('imgInput');
    // เลือกรูปภาพที่จะใช้สำหรับการแสดงตัวอย่าง
    let previewImg = document.getElementById('previewImg');
    // เลือกองค์ประกอบที่จะแสดงชื่อไฟล์ที่เลือก
    let fileChosenImg = document.getElementById('file-chosen-img');

    // เพิ่ม event listener สำหรับการเปลี่ยนแปลงของ input รูปภาพ
    imgInput.addEventListener('change', function () {
        const [file] = imgInput.files;
        if (file) {
            previewImg.src = URL.createObjectURL(file);
            fileChosenImg.textContent = file.name;
        }
    });

    // เรียกใช้ฟังก์ชัน showForm เพื่อแสดงฟอร์มแรกเมื่อโหลดหน้า
    showForm(currentFormIndex);
});
document.addEventListener('DOMContentLoaded', function () {
    // เลือกฟอร์มทั้งหมดที่มีคลาส .add_MET_section_form
    const forms = document.querySelectorAll('.add_MET_section_form');
    // เลือกหมายเลขหน้าทั้งหมดใน pagination
    const paginationNumbers = document.querySelectorAll('.pagination .number');
    // เลือกปุ่มก่อนหน้า
    const prevBtns = document.querySelectorAll('.btn_prev');
    // เลือกปุ่มถัดไป
    const nextBtns = document.querySelectorAll('.btn_next');
    // เลือกปุ่มเพิ่ม MET
    const addMET = document.querySelector('.add_MET');

    // ตั้งค่าดัชนีของฟอร์มปัจจุบันเป็น 0
    let currentFormIndex = 0;

    // ฟังก์ชันสำหรับแสดงฟอร์มที่กำหนดโดยดัชนี
    function showForm(index) {
        // วนลูปผ่านฟอร์มทั้งหมดและแสดงฟอร์มที่ตรงกับดัชนี
        forms.forEach((form, i) => {
            form.classList.toggle('active', i === index);
        });

        // วนลูปผ่านหมายเลขหน้าทั้งหมดและแสดงหมายเลขหน้าที่ตรงกับดัชนี
        paginationNumbers.forEach((number, i) => {
            number.classList.toggle('active', i === index);
        });

        // แสดงหรือซ่อนปุ่มก่อนหน้าขึ้นอยู่กับดัชนี
        prevBtns.forEach(btn => btn.style.display = index === 0 ? 'none' : 'inline-block');
        // แสดงหรือซ่อนปุ่มถัดไปขึ้นอยู่กับดัชนี
        nextBtns.forEach(btn => btn.style.display = index === forms.length - 1 ? 'none' : 'inline-block');
        // ปรับระยะห่างของ addMET ขึ้นอยู่กับดัชนี
        addMET.style.margin = index === 0 ? '5rem auto' : '9.5rem auto';
    }

    // เพิ่ม event listener ให้กับปุ่มถัดไป
    nextBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            if (currentFormIndex < forms.length - 1) {
                currentFormIndex++;
                showForm(currentFormIndex);
            }
        });
    });

    // เพิ่ม event listener ให้กับปุ่มก่อนหน้า
    prevBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            if (currentFormIndex > 0) {
                currentFormIndex--;
                showForm(currentFormIndex);
            }
        });
    });

    // เพิ่ม event listener ให้กับหมายเลขหน้า
    paginationNumbers.forEach((number, i) => {
        number.addEventListener('click', function () {
            currentFormIndex = i;
            showForm(currentFormIndex);
        });
    });

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

document.addEventListener('DOMContentLoaded', function () {
    // ดึงข้อมูลฟอร์มทุกฟอร์ม
    const forms = document.querySelectorAll('.form');
    // ดึงตัวเลขที่แสดงในส่วนการแบ่งหน้า
    const paginationNumbers = document.querySelectorAll('.pagination .number');
    // ดึงปุ่มย้อนกลับทั้งหมด
    const prevBtns = document.querySelectorAll('.btn_prev');
    // ดึงปุ่มถัดไปทั้งหมด
    const nextBtns = document.querySelectorAll('.btn_next');

    // ตั้งค่าตำแหน่งของฟอร์มปัจจุบัน
    let currentFormIndex = 0;

    // ฟังก์ชันแสดงฟอร์มตามตำแหน่งที่กำหนด
    function showForm(index) {
        forms.forEach((form, i) => {
            // เพิ่มคลาส 'active' ให้ฟอร์มที่ตรงกับตำแหน่งที่กำหนด และลบคลาส 'active' จากฟอร์มอื่นๆ
            form.classList.toggle('active', i === index);
        });

        paginationNumbers.forEach((number, i) => {
            // เพิ่มคลาส 'active' ให้ตัวเลขที่ตรงกับตำแหน่งที่กำหนด และลบคลาส 'active' จากตัวเลขอื่นๆ
            number.classList.toggle('active', i === index);
        });

        prevBtns.forEach(btn => {
            // แสดงหรือซ่อนปุ่มย้อนกลับ ขึ้นอยู่กับว่าตำแหน่งของฟอร์มอยู่ที่จุดเริ่มต้นหรือไม่
            btn.style.display = index === 0 ? 'none' : 'inline-block';
        });

        nextBtns.forEach(btn => {
            // แสดงหรือซ่อนปุ่มถัดไป ขึ้นอยู่กับว่าตำแหน่งของฟอร์มอยู่ที่จุดสุดท้ายหรือไม่
            btn.style.display = index === forms.length - 1 ? 'none' : 'inline-block';
        });
    }

    nextBtns.forEach(btn => {
        // เพิ่มเหตุการณ์คลิกให้กับปุ่มถัดไป
        btn.addEventListener('click', function (event) {
            event.preventDefault(); // ป้องกันการกระทำเริ่มต้นของแท็ก <a>
            if (currentFormIndex < forms.length - 1) {
                // ถ้าตำแหน่งฟอร์มปัจจุบันไม่ใช่ฟอร์มสุดท้าย ให้เพิ่มตำแหน่งฟอร์มและแสดงฟอร์มถัดไป
                currentFormIndex++;
                showForm(currentFormIndex);
            }
        });
    });

    prevBtns.forEach(btn => {
        // เพิ่มเหตุการณ์คลิกให้กับปุ่มย้อนกลับ
        btn.addEventListener('click', function (event) {
            event.preventDefault(); // ป้องกันการกระทำเริ่มต้นของแท็ก <a>
            if (currentFormIndex > 0) {
                // ถ้าตำแหน่งฟอร์มปัจจุบันไม่ใช่ฟอร์มแรก ให้ลดตำแหน่งฟอร์มและแสดงฟอร์มก่อนหน้า
                currentFormIndex--;
                showForm(currentFormIndex);
            }
        });
    });

    paginationNumbers.forEach((number, i) => {
        // เพิ่มเหตุการณ์คลิกให้กับตัวเลขในส่วนการแบ่งหน้า
        number.addEventListener('click', function () {
            // ตั้งค่าตำแหน่งฟอร์มปัจจุบันเป็นค่าดัชนีของตัวเลขที่ถูกคลิกและแสดงฟอร์มนั้น
            currentFormIndex = i;
            showForm(currentFormIndex);
        });
    });

    showForm(currentFormIndex); // เริ่มต้นแสดงฟอร์มแรกเมื่อโหลดหน้าเว็บ
});

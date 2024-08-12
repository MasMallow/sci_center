document.addEventListener('DOMContentLoaded', () => {
    // ฟังก์ชันจัดการการแสดงผลเนื้อหาและการแสดงผลลัพธ์
    setTimeout(function () {
        // ซ่อนการโหลดข้อมูล
        document.getElementById('loading').style.display = 'none';
        // แสดงเนื้อหาหลัก
        document.getElementById('content').style.display = 'block';

        // ดึงรายการ grid items และ container
        const gridItems = document.querySelectorAll('.grid_content');
        const gridContainer = document.querySelector('.content_area_grid');

        // ทำให้ grid container แสดงผลได้
        gridContainer.style.opacity = 1;

        // แสดงแต่ละ item ใน grid โดยหน่วงเวลา
        gridItems.forEach((item, index) => {
            setTimeout(() => {
                item.classList.add('show');
            }, index * 150);  // หน่วงเวลาในการแสดงแต่ละ item
        });
    }, 1500);  // เวลาที่หน่วงหลังจากเริ่มการโหลดข้อมูล

    const notificationSection = document.querySelector('.notification_section');

    // ฟังก์ชันเพื่อแสดงการแจ้งเตือนทีละรายการ
    function showNotifications() {
        const notifications = document.querySelectorAll('.notification');
        let index = 0;

        // ฟังก์ชันเพื่อแสดงการแจ้งเตือนถัดไป
        function showNextNotification() {
            if (index < notifications.length) {
                notifications[index].classList.add('visible');
                index++;
                setTimeout(showNextNotification, 200); // หน่วงเวลาในการแสดงการแจ้งเตือนแต่ละรายการ
            }
        }

        // เริ่มแสดงการแจ้งเตือน
        showNextNotification();
    }

    // หน่วงเวลาในการซ่อนการโหลดและแสดงเนื้อหาหลัก
    setTimeout(function () {
        notificationSection.style.opacity = '1'; // ทำให้เนื้อหาแสดงผล
        notificationSection.style.overflowY = 'auto'; // แสดง scrollbar แนวตั้ง
        showNotifications(); // เรียกใช้ฟังก์ชันเพื่อแสดงการแจ้งเตือน
    }, 1500); // เวลาที่หน่วงหลังจากเริ่มการโหลดข้อมูล

    // ฟังก์ชันเพื่อแสดงการแจ้งเตือนทีละรายการ
    function UsedPage_row() {
        const notifications = document.querySelectorAll('.UsedPage_row');
        let index = 0;

        // ฟังก์ชันเพื่อแสดงการแจ้งเตือนถัดไป
        function UsedPage_row() {
            if (index < notifications.length) {
                notifications[index].classList.add('visible');
                index++;
                setTimeout(UsedPage_row, 200); // หน่วงเวลาในการแสดงการแจ้งเตือนแต่ละรายการ
            }
        }

        // เริ่มแสดงการแจ้งเตือน
        UsedPage_row();
    }
});
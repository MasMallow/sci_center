// ปรับปรุงวันที่และเวลาอัตโนมัติ
function updateDateTime() {
    const now = new Date();
    const options = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };
    const dateLocaleString = now.toLocaleDateString('th-TH', options) + " พ.ศ. " + (now.getFullYear() + 543); // เพิ่มปีไทยลงไปหลังแปลงวันที่เป็นข้อความไทย
    const timeString = "เวลา " + now.toLocaleTimeString() + " น.";
    document.getElementById("date").textContent = dateLocaleString;
    document.getElementById("time").textContent = timeString;
    requestAnimationFrame(updateDateTime);
}

// เรียกใช้ฟังก์ชัน updateDateTime เพื่อเริ่มต้นการอัปเดต
updateDateTime();

<?php
session_start();
require_once '../assets/database/connect.php';

$form_values = isset($_SESSION['form_values']) ? $_SESSION['form_values'] : array(
    'username' => '',
    'password' => '',
    'confirmpassword' => '',
    'pre' => '',
    'surname' => '',
    'lastname' => '',
    'role' => '',
    'email' => '',
    'phone_number' => '',
    'agency' => '',
);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครบัญชีผู้ใช้</title>
    <!-- ส่วน Link -->
    <link href="../assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="../assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="../assets/css/sign_up.css">
</head>

<body>
        <div class="toast">
            <div class="toast_content">
                <i class="fas fa-solid fa-xmark check"></i>
                <div class="toast_content_message">
                    <span class="text text_2"><?php echo $_SESSION['errorSign_up'] ?></span>
                </div>
                <i class="fa-solid fa-xmark close"></i>
                <div class="progress"></div>
            </div>
        </div>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
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
        </script>
    <?php if (isset($_SESSION['errorSign_up'])) { ?>
        <?php unset($_SESSION['errorSign_up']); ?>
    <?php } ?>
    <form action="../authProcess/sign_upDB.php" method="post">
        <div class="register">
            <div class="register_page">
                <div class="register_page_head">
                    <a href="../../project/"><i class="fa-solid fa-arrow-left-long"></i></a>
                    <span id="B">สมัครบัญชีผู้ใช้</span>
                </div>
                <div class="register_page_body">
                    <div class="pagination">
                        <div class="number active">1</div>
                        <div class="bar"></div>
                        <div class="number">2</div>
                        <div class="bar"></div>
                        <div class="number">3</div>
                    </div>
                    <div class="form active">
                        <div class="form_header">
                            <span id="B">ส่วนที่ 1</span>
                            <span>กรอก USERNAME และ PASSWORD</span>
                        </div>
                        <div class="form_body">
                            <div class="input_box_1">
                                <span>ชื่อผู้ใช้</span>
                                <input type="text" placeholder="กรุณากรอกชื่อผู้ใช้ (Username)" name="username" value="<?php echo htmlspecialchars($form_values['username']); ?>" required autofocus>
                                <span class="description"><b>Note : </b>Username ต้องมีความยาวระหว่าง 6 ถึง 12 ตัวอักษร</span>
                            </div>
                            <div class="input_box_1">
                                <span>รหัสผ่าน</span>
                                <input type="password" placeholder="กรุณากรอกรหัสผ่าน (Password)" name="password" value="<?php echo htmlspecialchars($form_values['password']); ?>" required>
                                <span class="description">
                                    <b>Note : </b>รหัสผ่านต้องมีความยาวระหว่าง 8 ถึง 12 ตัวอักษร<br>
                                    <b>Note : </b>รหัสผ่านต้องประกอบด้วยตัวอักษรตัวเล็ก ตัวอักษรตัวใหญ่ และตัวเลขอย่างน้อย 1 ตัว
                                </span>
                            </div>
                            <div class="input_box_1">
                                <span>ยืนยันรหัสผ่านอีกครั้ง</span>
                                <input type="password" placeholder="กรุณายืนยันรหัสผ่าน" name="confirmpassword" value="<?php echo htmlspecialchars($form_values['confirmpassword']); ?>" required>
                            </div>
                            <div class="register_page_footer_1">
                                <a href="#2" class="btn_next">
                                    <span>ถัดไป</span>
                                    <i class="fa-solid fa-angle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="form">
                        <div class="form_header">
                            <span id="B">ส่วนที่ 2</span>
                            <span>กรอกข้อมูลส่วนบุคคล</span>
                        </div>
                        <div class="form_body">
                            <div class="col">
                                <div class="input_box_2">
                                    <span>คำนำหน้า</span>
                                    <div class="select">
                                        <select name="pre" required>
                                            <option value="" disabled selected>เลือกคำนำหน้า</option>
                                            <option value="นาย" <?php echo ($form_values['pre'] == 'นาย') ? 'selected' : ''; ?>>นาย</option>
                                            <option value="นาง" <?php echo ($form_values['pre'] == 'นาง') ? 'selected' : ''; ?>>นาง</option>
                                            <option value="นางสาว" <?php echo ($form_values['pre'] == 'นางสาว') ? 'selected' : ''; ?>>นางสาว</option>
                                            <option value="ดร." <?php echo ($form_values['pre'] == 'ดร.') ? 'selected' : ''; ?>>ดร.</option>
                                            <option value="ผศ.ดร." <?php echo ($form_values['pre'] == 'ผศ.ดร.') ? 'selected' : ''; ?>>ผศ.ดร.</option>
                                            <option value="อ." <?php echo ($form_values['pre'] == 'อ.') ? 'selected' : ''; ?>>อ.</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="input_box_2">
                                    <span>ชื่อ</span>
                                    <input type="text" placeholder="ชื่อภาษาไทย" name="surname" value="<?php echo htmlspecialchars($form_values['surname']); ?>" required>
                                </div>
                                <div class="input_box_2">
                                    <span>นามสกุล</span>
                                    <input type="text" placeholder="นามสกุลภาษาไทย" name="lastname" value="<?php echo htmlspecialchars($form_values['lastname']); ?>" required>
                                </div>
                            </div>
                            <div class="col">
                                <div class="input_box_2">
                                    <span>ตำแหน่ง</span>
                                    <div class="select">
                                        <select name="role" required>
                                            <option value="" disabled selected>เลือกตำแหน่ง</option>
                                            <option value="อาจารย์" <?php echo ($form_values['role'] == 'อาจารย์') ? 'selected' : ''; ?>>อาจารย์</option>
                                            <option value="บุคลากร" <?php echo ($form_values['role'] == 'บุคลากร') ? 'selected' : ''; ?>>บุคลากร</option>
                                            <option value="เจ้าหน้าที่" <?php echo ($form_values['role'] == 'เจ้าหน้าที่') ? 'selected' : ''; ?>>เจ้าหน้าที่</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="input_box_2">
                                    <span>หน่วยงาน</span>
                                    <input type="text" placeholder="หน่วยงาน" name="agency" value="<?php echo htmlspecialchars($form_values['agency']); ?>" required>
                                </div>
                            </div>
                            <div class="col">
                                <div class="input_box_2">
                                    <span>เบอร์โทรศัพท์</span>
                                    <input type="text" placeholder="000-000-0000" name="phone_number" value="<?php echo htmlspecialchars($form_values['phone_number']); ?>" required>
                                </div>
                                <div class="input_box_2">
                                    <span>E-Mail</span>
                                    <input type="text" placeholder="example@example.com" name="email" value="<?php echo htmlspecialchars($form_values['email']); ?>" required>
                                </div>
                            </div>
                            <div class="register_page_footer_2">
                                <a href="#1" class="btn_prev">
                                    <i class="fa-solid fa-angle-left"></i>
                                    <span>ก่อนหน้า</span>
                                </a>
                                <a href="#2" class="btn_next">
                                    <span>ถัดไป</span>
                                    <i class="fa-solid fa-angle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="form">
                        <div class="form_header_3">
                            <span id="B">กรุณาตรวจสอบข้อมูลการสมัครก่อนกดปุ่มยืนยัน</span>
                        </div>
                        <div class="btn_section_sign_up">
                            <div class="register_page_footer_2">
                                <a href="#1" class="btn_prev">
                                    <i class="fa-solid fa-angle-left"></i>
                                    <span>ก่อนหน้า</span>
                                </a>
                            </div>
                            <div class="btn_sign_up">
                                <button type="submit" class="submit" name="signup">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span>ยืนยัน</span>
                                </button>
                                <a href="sign_in.php" class="cancel">
                                    <i class="fa-solid fa-circle-xmark"></i>
                                    <span>ยกเลิก</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </form>
    <script src="../assets/js/ajax.js"></script>
    <script src="../assets/js/sign_up.js"></script>
</body>

</html>
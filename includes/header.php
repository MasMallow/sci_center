<div class="header">
    <div class="header_nav">
        <div class="header_nav_banner">
            <div class="header_navbanner_img">
                <a href="../project/"><img src="assets/logo/scicenter_logo.png"></a>
            </div>
            <div class="header_navbanner_name">
                <span id="B" class="header_navbanner_name-1">ระบบการจัดการวัสดุอุปกรณ์และเครื่องมือ
                    <?php if (isset($_SESSION['staff_login'])) {
                        echo "|| STAFF";
                    } ?></span><br>
                <span class="header_navbanner_name-2">ศูนย์วิทยาศาสตร์ มหาวิทยาลัยราชภัฏบ้านสมเด็จเจ้าพระยา</span>
            </div>
        </div>
        <div class="header_navigator"></div>
        <div class="header_nav_userinfo">
            <?php if (isset($_SESSION['user_login'])) : ?>
                <button class="header_userinfo_btn">
                    <i class="fa-solid fa-user"></i>
                    <span>
                        <?= $userData['pre'] . $userData['surname'] . '&nbsp;' . $userData['lastname'] ?>
                    </span>
                </button>
            <?php elseif (isset($_SESSION['staff_login'])) : ?>
                <button class="header_userinfo_btn">
                    <i class="fa-solid fa-user"></i>
                    <span>
                        <?= $userData['pre'] . $userData['surname'] . '&nbsp;' . $userData['lastname'] ?>
                    </span>
                </button>
            <?php else : ?>
                <a href="auth/sign_in.php" class="not-login">
                    <i class="ilogion fa-solid fa-right-to-bracket"></i>
                    <span class="text">เข้าสู่ระบบ</span>
                </a>
            <?php endif; ?>
            <!-- POP-UP ของ USER -->
            <div class="header_userinfo_modal">
                <div class="user-info">
                    <div class="user-info-header">
                        <span id="B">รายละเอียดผู้ใช้งาน</span>
                        <div class="modalClose" id="close"><i class="fa-solid fa-xmark"></i></div>
                    </div>
                    <div class="user-info-content">
                        <div class="userid_status">
                            <span class="user_id"><?= $userData['user_id'] ?></span>
                            <?php
                            if ($userData['status'] == 'wait_approved') {
                                echo '<span class="wait_approved" id="B">รอการอนุมัติบัญชี</span>';
                            } elseif ($userData['status'] == 'approved') {
                                echo '<span class="approved" id="B">บัญชีผ่านการอนุมัติ</span>';
                            }
                            ?>
                        </div>
                        <div><span><?= $userData['pre'] . $userData['surname'] . '&nbsp;' . $userData['lastname'] ?> </span></div>
                        <div><span><?= $userData['role'] . '&nbsp;' . $userData['agency'] ?> </span></div>
                        <div class="phone_line">
                            <div class="phone_number"><span>เบอร์โทร <?= $userData['phone_number'] ?></span></div>
                            <div class="lineid"><span>Line ID <?= $userData['lineid'] ?></span></div>
                        </div>
                    </div>
                    <div class="user_info_footer">
                        <a class="edit_users" href="edit_profile/home.php">
                            <i class="fa-solid fa-user-pen"></i>
                            <span>แก้ไขบัญชีผู้ใช้</span>
                        </a>
                        <a class="sign_out_confirm" href="auth/sign_out.php">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>ออกจากระบบ</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
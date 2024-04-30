<header class="header">
    <div class="header_nav">
        <div class="header_nav_banner">
            <div class="header_navbanner_img">
                <img src="assets/logo/scicenter_logo.png">
            </div>
            <div class="header_navbanner_name">
                <span id="B" class="header_navbanner_name-1">ระบบการจัดการวัสดุอุปกรณ์และเครื่องมือ</span><br>
                <span class="header_navbanner_name-2">ศูนย์วิทยาศาสตร์ มหาวิทยาลัยราชภัฏบ้านสมเด็จเจ้าพระยา</span>
            </div>
        </div>
        <div class="header_navigator"></div>
        <div class="header_nav_userinfo">
            <?php if (isset($_SESSION['user_login'])) : ?>
                <button class="header_userinfo_btn">
                    <i class="fa-solid fa-user"></i>
                    <span>
                        <?= $userData['firstname'] . '&nbsp;' . $userData['lastname'] ?>
                    </span>
                </button>
            <?php elseif (isset($_SESSION['admin_login'])) : ?>
                <button class="header_userinfo_btn">
                    <i class="fa-solid fa-user"></i>
                    <span>
                        <?= $userData['firstname'] . '&nbsp;' . $userData['lastname'] ?>
                    </span>
                </button>
            <?php else : ?>
                <button type="button" class="not-login">
                    <a href="auth/sign_in.php">
                        <i class="ilogion fa-solid fa-right-to-bracket"></i>
                        <span class="text">เข้าสู่ระบบ</span>
                    </a>
                </button>
            <?php endif; ?>
            <!-- POP-UP ของ USER -->
            <div class="header_userinfo_modal">
                <div class="user-info">
                    <div class="user-info-header">
                        <span id="B">รายละเอียด</span>
                        <div class="modalClose" id="close"><i class="fa-solid fa-xmark"></i></div>
                    </div>
                    <div class="user-info-content">
                        <span id="B">รายละเอียดผู้ใช้งาน</span>
                        <div class="user-info-content-edit">
                            <span><?= $userData['firstname'] . '&nbsp;' . $userData['lastname'] ?> </span>
                            <a href="edit_profile.php">
                                <i class="fa-solid fa-user-pen"></i>
                            </a>
                        </div>
                    </div>
                    <div class="user-info-footer">
                        <span>ออกจากระบบ</span>
                        <a class="confirm" href="auth/sign_out.php"><i class="fa-solid fa-right-from-bracket"></i><span>ออกจากระบบ</span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
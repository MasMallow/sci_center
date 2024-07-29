<div class="header">
    <div class="header_nav">
        <div class="header_nav_banner">
            <a href="<?php echo $base_url; ?>/" class="header_navbanner_name">
                <div class="header_navbanner_img">
                    <img src="<?php echo $base_url; ?>/assets/logo/scicenter_logo.png">
                </div>
            </a>
            <div class="header_navbanner_name_00">
                <span id="B" class="header_navbanner_name-1">ระบบการจัดการวัสดุอุปกรณ์และเครื่องมือ
                    <?php if (isset($_SESSION['staff_login'])) {
                        echo "|| STAFF";
                    } ?>
                </span>
                <span class="header_navbanner_name-2">ศูนย์วิทยาศาสตร์ มหาวิทยาลัยราชภัฏบ้านสมเด็จเจ้าพระยา</span>
            </div>
        </div>
        <div class="header_nav_userinfo">
            <?php if (isset($_SESSION['user_login'])) : ?>
                <div class="header_userinfo_btn">
                    <div class="select">
                        <i class="fa-solid fa-user"></i>
                        <span>
                            <?= $userData['pre'] . $userData['firstname'] . '&nbsp;' . $userData['lastname'] ?>
                        </span>
                        <i class="arrow_rotate fa-solid fa-chevron-up"></i>
                    </div>
                    <ul class="menu">
                        <li class="menu_li"><a href="<?php echo $base_url; ?>/profile_user">รายละเอียดผู้ใช้งาน</a></li>
                        <li class="menu_li"><a href="<?php echo $base_url; ?>/models/sign_out.php">ออกจากระบบ</a></li>
                    </ul>
                </div>
            <?php elseif (isset($_SESSION['staff_login'])) : ?>
                <div class="header_userinfo_btn">
                    <div class="select">
                        <i class="fa-solid fa-user"></i>
                        <span>
                            <?= $userData['pre'] . $userData['firstname'] . '&nbsp;' . $userData['lastname'] ?>
                        </span>
                        <i class="arrow_rotate fa-solid fa-chevron-up"></i>
                    </div>
                    <ul class="menu">
                        <li class="menu_li"><a href="<?php echo $base_url; ?>/models/sign_out.php">ออกจากระบบ</a></li>
                    </ul>
                </div>
            <?php else : ?>
                <a href="<?php echo $base_url; ?>/sign_in" class="not-login">
                    <i class="ilogion fa-solid fa-right-to-bracket"></i>
                    <span class="text">เข้าสู่ระบบ</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
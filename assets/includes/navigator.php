<div class="header">
    <div class="header_nav">
        <div class="header_nav_banner">
            <a href="<?php echo $base_url; ?>/" class="header_navbanner_name">
                <div class="header_navbanner_img">
                    <img src="<?php echo $base_url; ?>/assets/img/logo/sci_center.png">
                </div>
            </a>
            <div class="header_navbanner_name_00">
                <span id="B" class="header_navbanner_name-1">
                    ระบบการจัดการวัสดุ อุปกรณ์ และเครื่องมือ
                    <?php if (isset($_SESSION['staff_login'])) : ?>
                        || STAFF
                    <?php endif; ?>
                </span>
                <span class="header_navbanner_name-2">
                    ศูนย์วิทยาศาสตร์ มหาวิทยาลัยราชภัฏบ้านสมเด็จเจ้าพระยา
                </span>
            </div>
        </div>
        <div class="header_nav_userinfo">
            <?php if (isset($_SESSION['user_login'])) : ?>
                <!-- ปุ่มที่เปิด modal -->
                <div class="header_userinfo_btn" id="userInfoBtn">
                    <div class="select">
                        <i class="fa-solid fa-user"></i>
                        <span>
                            <?= $userData['pre'] . $userData['firstname'] . '&nbsp;' . $userData['lastname'] ?>
                        </span>
                        <i class="arrow_rotate fa-solid fa-chevron-up"></i>
                    </div>
                </div>

                <!-- โครงสร้าง modal -->
                <div class="logoutMODAL">
                    <div class="logoutMODAL_content">
                        <div class="logoutMODAL_popup">
                            <div class="logoutMODAL_sec01">
                                <i class="fa-solid fa-right-from-bracket"></i>
                                <span id="B">แจ้งเตือนออกจากระบบ</span>
                            </div>
                            <div class="logoutMODAL_sec02">
                                <a class="profileUser" href="<?php echo $base_url; ?>/profile_user"><i class="fa-solid fa-user-pen"></i></a>
                                <a class="confirmLogout" href="<?php echo $base_url; ?>/models/sign_out.php">ออกจากระบบ</a>
                                <div class="cancel_del closeDetails">
                                    <span>ปิดหน้าต่าง</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif (isset($_SESSION['staff_login'])) : ?>
                <!-- ปุ่มที่เปิด modal -->
                <div class="header_userinfo_btn" id="userInfoBtn">
                    <div class="select">
                        <i class="fa-solid fa-user"></i>
                        <span>
                            <?= $userData['pre'] . $userData['firstname'] . '&nbsp;' . $userData['lastname'] ?>
                        </span>
                        <i class="arrow_rotate fa-solid fa-chevron-up"></i>
                    </div>
                </div>

                <!-- โครงสร้าง modal -->
                <div class="logoutMODAL">
                    <div class="logoutMODAL_content">
                        <div class="logoutMODAL_popup">
                            <div class="logoutMODAL_sec01">
                                <i class="fa-solid fa-right-from-bracket"></i>
                                <span id="B">แจ้งเตือนออกจากระบบ</span>
                            </div>
                            <div class="logoutMODAL_sec02">
                                <a class="confirmLogout" href="<?php echo $base_url; ?>/models/sign_out.php">ออกจากระบบ</a>
                                <div class="cancel_del closeDetails">
                                    <span>ปิดหน้าต่าง</span>
                                </div>
                            </div>
                        </div>
                    </div>
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
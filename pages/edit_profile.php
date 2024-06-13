<?php
if (isset($_SESSION['edit_profile_success'])) {
?>
    <div class="edit_profile_status">
        <div class="edit_profile_status_content">
            <div class="edit_profile_header_status">
                <span id="B">แจ้งเตือน</span>
            </div>
            <div class="edit_profile_header_body">
                <div class="edit_profile_header_body_1">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="edit_profile_header_body_2">
                    <span id="B">แก้ไขบัญชีผู้ใช้สำเร็จ</span>
                </div>
            </div>
        </div>
    </div>
<?php
    unset($_SESSION['edit_profile_success']); // Clear session success message
}
?>
<div class="profile_user_00">
    <div class="profile_user_02">
        <div class="profile_user_details">
            <div class="edit_profile_header">
                <span id="B">แก้ไขบัญชีผู้ใช้</span>
            </div>
            <form class="edit_profile_body" action="<?php echo $base_url; ?>/update_profile" method="post">
                <div class="columnData">
                    <div class="input_edit">
                        <span>รหัสผ่านใหม่</span>
                        <div class="show_password">
                            <input type="password" id="password" name="password" placeholder="กรอกรหัสผ่านใหม่">
                            <i class="icon_password fas fa-eye-slash" onclick="togglePassword()"></i>
                        </div>
                    </div>
                    <div class="input_edit">
                        <span>ยืนยันรหัสผ่าน</span>
                        <div class="show_password">
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="ยืนยันรหัสผ่านใหม่">
                            <i class="icon_password fas fa-eye-slash" onclick="togglecPassword()"></i>
                        </div>
                    </div>
                </div>
                <div class="columnData">
                    <div class="input_edit">
                        <span>คำนำหน้า</span>
                        <select name="pre">
                            <?php
                            // Prefixes available
                            $prefixes = ['นาย', 'นาง', 'นางสาว', 'อ.', 'ผศ.ดร.'];
                            foreach ($prefixes as $prefix) {
                                $selected = ($userData['pre'] == $prefix) ? "selected" : "";
                                echo "<option value='$prefix' $selected>$prefix</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="input_edit">
                        <span>ชื่อ</span>
                        <input type="text" name="firstname" value="<?php echo htmlspecialchars($userData['firstname']); ?>">
                    </div>
                    <div class="input_edit">
                        <span>นามสกุล</span>
                        <input type="text" name="lastname" value="<?php echo htmlspecialchars($userData['lastname']); ?>">
                    </div>
                </div>
                <div class="columnData">
                    <div class="input_edit">
                        <span>ตำแหน่ง</span>
                        <select name="role">
                            <?php
                            $roles = ['อาจารย์', 'บุคลากร', 'เจ้าหน้าที่'];
                            foreach ($roles as $role) {
                                $selected = ($userData['role'] == $role) ? "selected" : "";
                                echo "<option value='$role' $selected>$role</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="input_edit">
                        <span>สังกัด</span>
                        <input type="text" name="agency" value="<?php echo htmlspecialchars($userData['agency']); ?>">
                    </div>
                    <div class="input_edit">
                        <span>เบอร์โทรศัพท์</span>
                        <input type="text" name="phone_number" value="<?php echo htmlspecialchars($userData['phone_number']); ?>">
                    </div>
                </div>
                <div class="edit_profile_footer">
                    <button type="submit" class="submit">ยืนยัน</button>
                    <a href="<?php echo htmlspecialchars($base_url); ?>" class="cancel">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
    <div class="profile_user_notification">
        <div class="edit_profile_header">
            <span id="B">ประวัติการใช้งาน</span>
        </div>
        <div class="profile_user_notification_body">
            <div class="profile_user_notification_stack">
                <?php foreach ($userData_log as $log_user) : ?>
                    <div class="profile_user_notification_data">
                        <?php echo htmlspecialchars(thai_date_time_2($log_user['log_Date'])); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
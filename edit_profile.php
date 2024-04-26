<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
</head>

<body>
    <?php
    session_start();
    require_once 'connect.php';

    if (isset($_SESSION['user_login']) || isset($_SESSION['admin_login'])) {
        $user_id = isset($_SESSION['user_login']) ? $_SESSION['user_login'] : $_SESSION['admin_login'];

        // ดึงข้อมูลเดิมจากฐานข้อมูล
        $stmt = $db->query("SELECT * FROM users WHERE id =$user_id");
        $row = $stmt->fetch_assoc();
    ?>
        <form action="update_profile.php" method="post">
            <label for="firstname">First Name:</label>
            <input type="text" id="firstname" name="firstname" value="<?php echo $row['firstname']; ?>"><br><br>

            <label for="lastname">Last Name:</label>
            <input type="text" id="lastname" name="lastname" value="<?php echo $row['lastname']; ?>"><br><br>
            
            <label for="urole">Role:</label>
            <input type="text" id="urole" name="urole" value="<?php echo $row['urole']; ?>"><br><br>
            
            <label for="oldUsername">Old Username:</label>
            <input type="text" id="oldUsername" name="oldUsername" value="<?php echo $row['username']; ?>" readonly><br><br>
            
            <label for="newPassword">New Password:</label>
            <input type="password" id="newPassword" name="newPassword"><br><br>

            <label for="lineid">Line ID:</label>
            <input type="text" id="lineid" name="lineid" value="<?php echo $row['lineid']; ?>"><br><br>

            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" value="<?php echo $row['phone']; ?>"><br><br>

            <input type="submit" value="Update">
        </form>
    <?php
    } else {
        echo "You are not logged in!";
    }
    ?>
</body>

</html>
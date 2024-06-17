<?php
session_start();
require_once 'assets/database/dbConfig.php';

if (isset($_SESSION['staff_login'])) {
    $user_id = $_SESSION['staff_login'];
    $stmt = $conn->prepare("SELECT * FROM users_db WHERE user_ID = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}
// Check if user is logged in
if (!isset($_SESSION['staff_login'])) {
    header("Location: /login.php"); // Redirect to login page if not logged in
    exit;
}

// Fetch user ID from session
$user_id = $_SESSION['staff_login'];

try {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        // Fetch data to edit
        $stmt = $conn->prepare("SELECT * FROM crud INNER JOIN info_sciname ON crud.serial_number = info_sciname.serial_number WHERE crud.ID = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $editData = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Science Center Item</title>
    <link href="<?php echo $base_url; ?>/assets/logo/LOGO.jpg" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/font-awesome/css/all.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/navigator.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/management_systems.css">
</head>

<body>
    <?php include('assets/includes/header.php') ?>
    <main class="add_MET">
        <div class="add_MET_section">
            <div class="add_MET_section_header">
                <a href="<?php echo $base_url; ?>/"><i class="fa-solid fa-arrow-left-long"></i></a>
                <label id="B">Edit Science Center Item</label>
            </div>
            <form action="<?php echo $base_url; ?>/Staff/update.php" method="POST" enctype="multipart/form-data">
                <div class="add_MET_section_form">
                    <div class="input_Data">
                        <label>Name</label>
                        <input type="text" name="sci_name" required placeholder="Enter name" value="<?php echo $editData['sci_name'] ?>">
                    </div>
                    <div class="input_Data">
                        <label>Serial Number</label>
                        <input type="text" name="serial_number" required placeholder="Enter serial number" value="<?php echo $editData['serial_number'] ?>">
                    </div>
                    <div class="col">
                        <div class="input_Data">
                            <label>Category</label>
                            <select name="categories" required>
                                <option value="Material" <?php if ($editData['categories'] === 'Material') echo 'selected'; ?>>Material</option>
                                <option value="Equipment" <?php if ($editData['categories'] === 'Equipment') echo 'selected'; ?>>Equipment</option>
                                <option value="Tool" <?php if ($editData['categories'] === 'Tool') echo 'selected'; ?>>Tool</option>
                            </select>
                        </div>
                        <div class="input_Data">
                            <label>Quantity</label>
                            <input type="number" name="amount" min="1" required placeholder="Enter quantity" value="<?php echo $editData['amount'] ?>">
                        </div>
                    </div>
                    <div class="col">
                        <div class="input_Data">
                            <label>Installation Date</label>
                            <input type="datetime-local" name="installation_date" value="<?php echo $editData['installation_date'] ?>">
                        </div>
                        <div class="input_Data">
                            <label>Company</label>
                            <input type="text" name="company" placeholder="Enter company" value="<?php echo $editData['company'] ?>">
                        </div>
                    </div>
                    <div class="col">
                        <div class="input_Data">
                            <label>Company Contact Number</label>
                            <input type="text" name="contact_number" placeholder="Enter company contact number" value="<?php echo $editData['contact_number'] ?>">
                        </div>
                        <div class="input_Data">
                            <label>Contact Person</label>
                            <input type="text" name="contact" placeholder="Enter contact person" value="<?php echo $editData['contact'] ?>">
                        </div>
                    </div>
                    <div class="col">
                        <div class="input_Data">
                            <label>Brand</label>
                            <input type="text" name="brand" placeholder="Enter brand" value="<?php echo $editData['brand'] ?>">
                        </div>
                        <div class="input_Data">
                            <label>Model</label>
                            <input type="text" name="model" placeholder="Enter model" value="<?php echo $editData['model'] ?>">
                        </div>
                    </div>
                    <div class="col">
                        <div class="input_Data">
                            <label for="details">Details</label>
                            <textarea id="details" name="details" placeholder="Enter details"><?php echo $editData['details'] ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="btn_footer">
                    <input type="hidden" name="id" value="<?php echo $editData['ID']; ?>">
                    <button type="submit" name="update" class="submitADD">Confirm</button>
                    <button type="reset" class="resetADD">Clear</button>
                </div>
            </form>
        </div>
    </main>
    <script src="<?php echo $base_url; ?>/assets/js/ajax.js"></script>
</body>

</html>
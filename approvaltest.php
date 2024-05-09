<?php
session_start();
include_once 'assets/database/connect.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['id'])) {
            $id = $_POST['id'];
            $stmt = $conn->prepare("SELECT * FROM waiting_for_approval WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($data as $row): ?>
                <div class="row">
                    <span class="info">ID:</span> <?php echo $row['id']; ?><br>
                    <span class="info">First Name:</span> <?php echo $row['FirstName']; ?><br>
                    <span class="info">Item Borrowed:</span> <?php echo $row['ItemBorrowed']; ?><br>
                    <span class="info">Borrow DateTime:</span> <?php echo $row['BorrowDateTime']; ?><br>
                    <span class="info">Return Date:</span> <?php echo $row['ReturnDate']; ?><br>
                </div>
                <?php endforeach;
        }
    }
    ?>
</body>

</html>
<?php
session_start();
require_once('assets/database/dbConfig.php');

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $sci_name = $_POST['sci_name'];
    $amount = $_POST['amount'];
    $categories = $_POST['categories'];
    $img = $_FILES['img'];
    $img2 = $_POST['img2'];
    $upload = $_FILES['img']['name'];

    if ($upload != '') {
        $allow = array('jpg', 'jpeg', 'png');
        $extension = explode('.', $img['name']);
        $fileActExt = strtolower(end($extension));
        $fileNew = rand() . "." . $fileActExt;
        $folder = 'assets/uploads/';
        $filePath = $folder . $fileNew;

        if (in_array($fileActExt, $allow)) {
            if ($img['size'] > 0 && $img['error'] == 0) {
                move_uploaded_file($img['tmp_name'], $filePath);
            }
        }
    } else {
        $fileNew = $img2;
    }

    $sql = $conn->prepare("SELECT * FROM crud WHERE ID = :ID");
    $sql->bindParam(":ID", $id);
    $sql->execute();
    $result = $sql->fetch(PDO::FETCH_ASSOC);

    if ($upload == '') {
        $fileNew = $result['img_name'];
    } else {
        @unlink($folder . $result['img_name']);
    }

    $update_sql = $conn->prepare("UPDATE crud SET sci_name = :sci_name, amount = :amount, categories = :categories, img_name = :img_name WHERE ID = :ID");
    $update_sql->bindParam(":ID", $id);
    $update_sql->bindParam(":sci_name", $sci_name);
    $update_sql->bindParam(":amount", $amount);
    $update_sql->bindParam(":categories", $categories);
    $update_sql->bindParam(":img_name", $fileNew);
    $update_sql->execute();

    if ($update_sql) {
        $_SESSION['success'] = "Data has been updated successfully";
        header("Location: management");
    } else {
        $_SESSION['error'] = "Data has not been updated successfully";
        header("Location: management");
    }
}
?>
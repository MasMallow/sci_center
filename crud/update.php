<?php
session_start();
include_once('../assets/database/connect.php');

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
        $filePath = '../assets/uploads/' . $fileNew;

        if (in_array($fileActExt, $allow)) {
            if ($img['size'] > 0 && $img['error'] == 0) {
                move_uploaded_file($img['tmp_name'], $filePath);
            }
        }
    } else {
        $fileNew = $img2;
    }

    $sql = $conn->prepare("UPDATE crud SET sci_name = :sci_name, amount = :amount, categories = :categories, img = :img WHERE id = :id");
    $sql->bindParam(":id", $id);
    $sql->bindParam(":sci_name", $sci_name);
    $sql->bindParam(":amount", $amount);
    $sql->bindParam(":categories", $categories);
    $sql->bindParam(":img", $fileNew);
    $sql->execute();

    if ($sql) {
        $_SESSION['success'] = "Data has been updated successfully";
        header("location: management");
    } else {
        $_SESSION['error'] = "Data has not been updated successfully";
        header("location: management");
    }
}
?>

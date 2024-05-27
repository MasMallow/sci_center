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
    <style>
        .container {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border: 1px solid #ccc;
        }

        .row div {
            flex: 1;
            padding: 5px;
            text-align: center;
        }

        .header {
            font-weight: bold;
            background-color: #f4f4f4;
        }
    </style>
    <script>
        function confirmReturn(event) {
            event.preventDefault();
            if (confirm("Are you sure you want to mark this item as returned?")) {
                event.target.closest('form').submit();
            }
        }
    </script>
</head>

<body>
    <h1>คืนอุปกรณ์</h1>
    <?php
    if (isset($_SESSION['user_login'])) {
        $user_id = $_SESSION['user_login'];
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData) {
            if ($userData['status'] !== 'approved') {
                unset($_SESSION['cart']);
                header("Location: home.php");
                exit();
            }
        }

        $stmt = $conn->prepare("SELECT * FROM waiting_for_approval WHERE udi = :user_id AND situation = 1 AND date_return IS NULL");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $dataList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($dataList) {
            echo '<div class="container">';
            echo '<div class="row header">';
            echo '<div>Item ID</div>';
            echo '<div>UDI</div>';
            echo '<div>SN</div>';
            echo '<div>Firstname</div>';
            echo '<div>Item Borrowed</div>';
            echo '<div>Borrow Date</div>';
            echo '<div>Return Date</div>';
            echo '<div>Approver</div>';
            echo '<div>Approval Date</div>';
            echo '<div>Situation</div>';
            echo '<div>Date Return</div>';
            echo '</div>';
            foreach ($dataList as $data) {
                echo '<div class="row">';
                echo '<div>' . htmlspecialchars($data['id']) . '</div>';
                echo '<div>' . htmlspecialchars($data['udi']) . '</div>';
                echo '<div>' . htmlspecialchars($data['sn']) . '</div>';
                echo '<div>' . htmlspecialchars($data['firstname']) . '</div>';
                echo '<div>';
                $items = explode(',', $data['itemborrowed']);
                foreach ($items as $item) {
                    $item_parts = explode('(', $item);
                    $product_name = trim($item_parts[0]);
                    $quantity = rtrim($item_parts[1], ')');
                    echo $product_name . ' ' . $quantity . ' ชิ้น<br>';
                }
                echo '</div>';
                echo '<div>' . htmlspecialchars($data['borrowdatetime']) . '</div>';
                echo '<div>' . htmlspecialchars($data['returndate']) . '</div>';
                echo '<div>' . htmlspecialchars($data['approver']) . '</div>';
                echo '<div>' . htmlspecialchars($data['approvaldatetime']) . '</div>';
                echo '<div>' . htmlspecialchars($data['situation']) . '</div>';
                echo '<div>
                        <form method="POST" action="Check_the_request_notification.php">
                            <input type="hidden" name="return_id" value="' . htmlspecialchars($data['id']) . '">
                            <input type="hidden" name="user_id" value="' . htmlspecialchars($data['udi']) . '">
                            <button type="submit" onclick="confirmReturn(event)">คืนอุปกรณ์</button>
                        </form>
                      </div>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo 'No data found.';
        }
    }
    ?>
</body>

</html>
<?php
include_once 'admin-panel.php';
?>

<!DOCTYPE html>
<html>

<head>
    <style>
        input[type=text],
        select {
            width: 30%;
            padding: 1px 1px;
            margin: 8px 0;
            display: inline-block;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type=submit] {
            width: 100%;
            background-color: #4CAF50;
            color: white;
            padding: 4px 4px;
            margin: 8px 0;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type=submit]:hover {
            background-color: #45a049;
        }

        div {
            border-radius: 5px;
            background-color: #f2f2f2;
            padding: 5px;
        }
    </style>
</head>

<body>

    <body class="home_body">

        <div class="container">
            <div class="home_header header">
                <h3>Borrower and Book Information</h3>
            </div>
            <form action="insert-borrower-info.php" method="post" class="bookinsert">
                <div class="insert-input">
                    <label>Book ISDN:</label></br>
                    <input type="text" placeholder="Enter book ID" name="book_id" value="">
                </div>
                <div class="insert-input">
                    <label>Book Title:</label></br>
                    <input type="text" placeholder="Enter book title" name="book_title" value="">
                </div>
                <div class="insert-input">
                    <label>Student's ID:</label></br>
                    <input type="text" placeholder="Enter Student's ID" name="sid" value="">
                </div>

                <div class="insert-input">
                    <label>Borrower/Student's Name: </label></br>
                    <input type="text" placeholder="Enter Borrower's name" name="borrower" value="">
                </div>

                <div class="insert-input">
                    <label>Borrowing Date: </label></br>
                    <input type="date" name="borrow_date">
                </div>

                <div class="insert-input">
                    <label>Return Date: </label></br>
                    <input type="date" name="back_date">
                </div>

                <input type="submit" value="Insert">
            </form>

            <div class="bookinfo">

            </div>
        </div>

    </body>

</html>
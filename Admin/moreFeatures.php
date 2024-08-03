<?php
session_start();

$adminId = $_SESSION['admin_id'];
include ('../dbconnection/dbconn.php');

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>More Features</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.2/css/dataTables.dataTables.css" />

    <link rel="stylesheet" href="../src/output.css">
    <script src="../src/jQuery.js"></script>
    <link rel="stylesheet" href="./addFiles/css/main.css">
    <link rel="stylesheet" href="./addFiles/css/msg.css">
    <style>
        .button-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: start;
            align-items: center;
            width: 100%;
        }

        .btns {
            max-width: 190px;
            min-width: 190px;
            height: 70px;
            background-color: #3b82f6;
            color: #fff;
            margin: 5px;
            padding: 10px;
            display: inline-block;
            text-align: center;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            transition: background 0.3s, transform 0.3s;
            font-size: 1rem;

        }
    </style>
</head>

<body>
    <?php include ('./addFiles/header.php') ?>
    <main class="main" id="main">
        <?php include ('./addFiles/header.php') ?>
        <div class="button-container font-bold">
            <button class="btns" id="addAdmin">Add New Admin</button>
            <button class="btns" id="eventManageSectionBtn">Events Manage</button>
            <button class="btns" id="addMoreCourseYearBtn">Add More Courses Years</button>
            <button class="btns" id="addMoreEmpRoleBtn">Add More Employee Roles</button>
            <button class="btns">Add More Exam Names</button>
            <button class="btns">Add More Lectures</button>

        </div>
    </main>
    <script src="./addFiles/js/script.js"></script>
    <script src="https://cdn.datatables.net/2.0.7/js/dataTables.js"></script>
    <script>
        let table = new DataTable('#myTable');
    </script>

    <script>
        $(document).ready(function () {

            $('#eventManageSectionBtn').click(function () {
                window.location.href = "./addFiles/eventSection.php";
            });

            $("#addAdmin").click(function () {
                window.location.href = "./addFiles/Admin.php";
            })
            $("#addMoreCourseYearBtn").click(() => {
                window.location.href = "./addFiles/manageCourseYears.php";
            })
            $("#addMoreEmpRoleBtn").click(function () {
                window.location.href = "./addFiles/manageEmployeeRoles.php";
            })

        });


    </script>

</body>

</html>
<?php
session_start();
if (!isset($_SESSION['employee']) || $_SESSION['employee'] != true) {
    header('Location: ../LandingPage/index.php');
    exit(); // Ensure the script stops executing after the redirect
}
include ('../dbconnection/dbconn.php');

$employeeId = $_SESSION['employee_id'];
$empSqlQuery = "SELECT e.firstName,e.lastName,r.role,e.email,e.contact FROM employee e 
                JOIN empRole r ON r.id = e.role
                WHERE e.id = $employeeId";
$result = $conn->query($empSqlQuery);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $nameEmp = $row['firstName'] . ' ' . $row['lastName'] . ' (' . $row['role'] . ')';
        $empEmail = $row['email'];
        $empContact = $row['contact'];
    }
}



?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="./addFiles/css/main.css">
    <link rel="stylesheet" href="./addFiles/css/dashboard.css">
    <style>
        .main {
            margin-bottom: 10px;
            height: 90vh;
            position: fixed;
            top: 10vh;
            right: 0;
            width: 100%;
            padding: 20px;
            /* background: #e0e0e0; */
            transition: all 0.2s linear;
            overflow-y: auto;

        }

        section {
            box-shadow: 3px 3px 10px #000;
            padding: 20px;
            /* height: 100vh; */
        }
    </style>
</head>

<body>
    <?php include 'addFiles/header.php'; ?>

    <main class="main" id="main">
        <div class=" grid md:grid-cols-2 grid-cols-1 gap-3">

            <section class=" rounded">
                <profile>
                    <span class="font-bold">Name: </span><?php echo $nameEmp ?><br>
                    <span class="font-bold">Email Address: </span><?php echo $empEmail ?><br>
                    <span class="font-bold">Email Address: </span><?php echo $empContact ?><br>

                </profile>
            </section>
            <section class=" rounded">
                <h1 class="font-bold text-center">Events</h1>
                <div class="grid grid-cols-3">
                    <div class="">Upcoming Event</div>
                    <div class="">Live Event</div>
                    <div class="">Past Event</div>
                </div>
            </section>
        </div>

    </main>

</body>

</html>
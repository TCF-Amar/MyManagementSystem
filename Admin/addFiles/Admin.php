<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
    header('Location: ../../LandingPage/index.php');
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
include ('../../dbconnection/dbconn.php');

$adminId = $_SESSION['admin_id'];

$mainAdminSq = "SELECT * from admin WHERE id = 1";
$mainAdminRs = mysqli_query($conn, $mainAdminSq);
$mainAdminData = mysqli_fetch_assoc($mainAdminRs);
$mainAdminPassword = "@@@admin@@@";
$mainAdminEmail = $mainAdminData['email'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['addNewCoAdmin'])) {
        $adminName = $_POST['Co-admin-name'];
        $adminEmail = $_POST['Co-admin-email'];
        $adminContact = $_POST['Co-admin-phone'];
        $adminAddress = $_POST['Co-admin-address'];
        $adminCity = $_POST['Co-admin-city'];
        $adminState = $_POST['Co-admin-state'];
        $adminZip = $_POST['Co-admin-pin'];
        $adminPassword = $_POST['Co-admin-password'];
        $confirmPassword = $_POST['Co-admin-confirm-password'];
        try {
            if ($adminPassword === $confirmPassword) {
                // Hash the password securely
                $adminHashPassword = password_hash($adminPassword, PASSWORD_DEFAULT);

                // Prepare and execute the SQL insert statement
                $sql = "INSERT INTO admin (username, email, contact, address, city, state, pincode, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssssss", $adminName, $adminEmail, $adminContact, $adminAddress, $adminCity, $adminState, $adminZip, $adminHashPassword);

                if ($stmt->execute()) {
                    // Success message for database insert
                    echo "<div class='msg bg-green-500'>Co-Admin added successfully.</div>";

                    // Send email notification
                    $mail = new PHPMailer(true);
                    $MAILpassword = 'your_gmail_app_password_here';
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = $mainAdminEmail;
                    $mail->Password = $MAILpassword;
                    $mail->SMTPSecure = 'ssl';
                    $mail->Port = 465;
                    $mail->setFrom($mainAdminEmail, 'Admin');
                    $mail->addAddress($adminEmail, $adminName);
                    $mail->isHTML(true);
                    $mail->Subject = 'Admin Added Successfully';
                    $mail->Body = '
                        <h1>Dear, ' . htmlspecialchars($adminName) . '</h1>
                        <p>Congratulations! You have been added as a co-admin. Your login details are:</p>
                        <ul>
                            <li>Username: <strong>' . htmlspecialchars($adminName) . '</strong></li>
                            <li>Phone No.: <strong>' . htmlspecialchars($adminContact) . '</strong></li>
                            <li>Login Email: <strong>' . htmlspecialchars($adminEmail) . '</strong></li>
                            <li>Login Password: <strong>' . htmlspecialchars($adminPassword) . '</strong></li>
                        </ul>
                        <p>Thank you for being a part of our team.</p>
                        <p>Regards,</p>
                        <p>Admin</p>';
                    $mail->send();

                } else {
                    // Error message for database insert
                    echo "<div class='msg bg-red-500'>Error: " . $stmt->error . "</div>";
                }

                $stmt->close();
            } else {
                // Password mismatch error message
                echo "<div class='msg bg-red-500'>Passwords do not match.</div>";
            }
        } catch (Exception $e) {
            // Catch any other exceptions
            echo '<div class="msg bg-red-500">Error: ' . $e->getMessage() . '</div>';
        }

    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Manage</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../../src/output.css">
    <link rel="stylesheet" href="./css/msg.css">
    <link rel="stylesheet" href="./css/dataTables.dataTables.css">
    <script src="../../src/jQuery.js"></script>
    <link rel="stylesheet" href="./css/main.css">
    <style>
        header {
            position: fixed;
            top: 0;
            right: 0;
            width: 100%;
            height: 10vh;
            background-color: #fff;
            color: rgb(16, 41, 110);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            font-size: 1.2rem;
            transition: all 0.2s ease-in-out;
            z-index: 10;
            box-shadow: 2px 2px 2px #000;
        }

        dialog {
            border: none;
            padding: 20px;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            width: 50%;
            z-index: 11;
        }

        dialog::-webkit-scrollbar {
            display: none;
        }

        .close {
            background: none;
            border: none;
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            float: right;
        }

        .close:hover,
        .close:focus {
            color: black;
        }

        @media screen and (max-width: 760px) {
            dialog {
                width: 90%;
            }
        }

        .modal-header {
            background-color: #f0f0f0;
            border-bottom: 2px solid #ccc;
            padding: 1rem;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }

        .modal-body {
            padding: 1rem;
            background-color: #fff;
        }
    </style>
</head>

<body>

    <header>
        <nav>
            <button class="font-bold text-2xl" id="AdminSectionCloseBtn">Close</button> |
            <?php if ($adminId === 1): ?>
                <button class="font-bold" id="addAdminBtn">Add Co-Admin's</button> |
            <?php endif; ?>
        </nav>
    </header>

    <main id="main" class="main">
        <?php if ($adminId === 1): ?>
            <div id="main-admin-container" class="main-admin-container ">
                <label class="font-bold text-2xl">Main Admin</label>
                <table class="w-full bg-white border border-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 border-b">Admin Name</th>
                            <th class="px-4 py-2 border-b">Admin Email</th>
                            <th class="px-4 py-2 border-b">Admin Phone</th>
                            <th class="px-4 py-2 border-b">Admin Password</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="px-4 py-2 border-b text-center">
                                <?php echo htmlspecialchars($mainAdminData['username']); ?>
                            </td>
                            <td class="px-4 py-2 border-b text-center">
                                <?php echo htmlspecialchars($mainAdminData['email']); ?>
                            </td>
                            <td class="px-4 py-2 border-b text-center">
                                <?php echo htmlspecialchars($mainAdminData['contact']); ?>
                            </td>
                            <td class="px-4 py-2 border-b flex">
                                <?php

                                if (password_verify($mainAdminPassword, $mainAdminData['password'])) {
                                    echo '<input id="mainAdminPass" type="password" class="outline-none border-none w-full text-center" readonly value="' . htmlspecialchars($mainAdminPassword) . '">';
                                } else {
                                    echo 'Error';
                                }
                                ?>
                                <i class="fa fa-eye float-right" id="showMainAdminPass"></i>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="co-admin-container mt-8">
            <div class="co-admin">
                <label class="font-bold text-2xl">Admin's</label>
                <table class="w-full bg-white border border-gray-200" id="coAdminTable">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 border-b">Admin Name</th>
                            <th class="px-4 py-2 border-b">Admin Email</th>
                            <th class="px-4 py-2 border-b">Admin Phone</th>
                            <th class="px-4 py-2 border-b">Admin Details</th>

                            <th class="px-4 py-2 border-b">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="coAdminTableBody">
                        <?php
                        $allAdminQuery = "SELECT * FROM admin WHERE id != 1";
                        $allAdminResult = mysqli_query($conn, $allAdminQuery);

                        while ($row = mysqli_fetch_assoc($allAdminResult)): ?>
                            <tr>
                                <td class='px-4 py-2 border-b text-center'><?php echo htmlspecialchars($row['username']) ?>
                                </td>
                                <td class='px-4 py-2 border-b text-center'><?php echo htmlspecialchars($row['email']) ?>
                                </td>
                                <td class='px-4 py-2 border-b text-center'><?php echo htmlspecialchars($row['contact']) ?>
                                </td>
                                <td class='px-4 py-2 border-b text-center'> <button>Show</button> </td>

                                <td class='px-4 py-2 border-b text-center'>
                                    <button class='editAdminBtn  text-blue-500 px-2 py-1 rounded'>Edit</button>
                                    <button class='deleteAdminBtn  text-red-500 px-2 py-1 rounded'>Delete</button>
                                </td>
                            </tr>

                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>


    <dialog id="addAdminDialog">
        <div class="modal-header">
            <span class="modal-title">Add Co-Admin</span>
            <button class="close" id="closeAddAdminDialog">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <label for="Co-admin-name">Name:</label>
                <input type="text" name="Co-admin-name" id="Co-admin-name" required
                    class="block w-full p-2 border rounded mb-2">

                <label for="Co-admin-email">Email:</label>
                <input type="email" name="Co-admin-email" id="Co-admin-email" required
                    class="block w-full p-2 border rounded mb-2">

                <label for="Co-admin-phone">Phone:</label>
                <input type="text" name="Co-admin-phone" id="Co-admin-phone" required
                    class="block w-full p-2 border rounded mb-2">

                <label for="Co-admin-address">Address:</label>
                <input type="text" name="Co-admin-address" id="Co-admin-address" required
                    class="block w-full p-2 border rounded mb-2">

                <label for="Co-admin-city">City:</label>
                <input type="text" name="Co-admin-city" id="Co-admin-city" required
                    class="block w-full p-2 border rounded mb-2">

                <label for="Co-admin-state">State:</label>
                <input type="text" name="Co-admin-state" id="Co-admin-state" required
                    class="block w-full p-2 border rounded mb-2">

                <label for="Co-admin-pin">PinCode:</label>
                <input type="text" name="Co-admin-pin" id="Co-admin-pin" required
                    class="block w-full p-2 border rounded mb-2">

                <label for="Co-admin-password">Password:</label>
                <input type="password" name="Co-admin-password" id="Co-admin-password" required
                    class="block w-full p-2 border rounded mb-2">

                <label for="Co-admin-confirm-password">Confirm Password:</label>
                <input type="password" name="Co-admin-confirm-password" id="Co-admin-confirm-password" required
                    class="block w-full p-2 border rounded mb-2">

                <button type="submit" name="addNewCoAdmin" class="bg-blue-500 text-white px-4 py-2 rounded">Add
                    Co-Admin</button>
            </div>
        </form>
    </dialog>
    <script src="./js/script.js"></script>

    <script>
        $(document).ready(function () {
            $('#addAdminBtn').click(function () {
                $('#addAdminDialog').show();
            });

            $('#closeAddAdminDialog').click(function () {
                $('#addAdminDialog').hide();
            });
            $("#AdminSectionCloseBtn").click(function () {
                window.location.href = '../moreFeatures.php';
            })

            $('table').on('click', '.fa-eye', function () {
                let passwordField = $(this).prev('input');
                let passwordFieldType = passwordField.attr('type');
                if (passwordFieldType == 'password') {
                    passwordField.attr('type', 'text');
                } else {
                    passwordField.attr('type', 'password');
                }
            });

            $('.editAdminBtn').click(function () {
                let row = $(this).closest('tr');
                let adminId = row.data('admin-id');
                // Edit admin logic here
            });

            $('.deleteAdminBtn').click(function () {
                let row = $(this).closest('tr');
                let adminId = row.data('admin-id');
                // Delete admin logic here
            });
        });
    </script>
</body>

</html>
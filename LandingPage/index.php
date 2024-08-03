<?php
include ('../dbconnection/dbconn.php');

// Function to create the admin table
function adminTableCreate($conn)
{
    $sql = "CREATE TABLE IF NOT EXISTS `admin` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL UNIQUE,
    `contact` varchar(15) NOT NULL UNIQUE,
    address varchar(255) not null,
    city varchar(255) not  null ,
    state varchar(255) not null,
    pincode varchar(255) not null,
    `password` varchar(255) NOT NULL,
    PRIMARY KEY (`id`))";

    if ($conn->query($sql) === FALSE) {
        die("Error creating table: " . $conn->error);
    }
}

// Function to insert default admin data
function defaultAdminDataInsert($conn)
{
    $adminName = "Amarjeet Mistri";
    $adminEmail = 'amarjeetmistri41@gmail.com';
    $adminContact = '8435876461'; // Changed to string
    $adminAddress = 'Usrar House No. 159';
    $adminCity = 'Satna';
    $adminState = 'Madhya Pradesh';
    $adminPincode = '485447';
    $adminPassword = '@@@admin@@@';

    $hashAdminPassword = password_hash($adminPassword, PASSWORD_DEFAULT);

    $sql = "INSERT IGNORE INTO `admin` (`username`, `email`, `contact`,address, city,state,pincode ,`password`) VALUES(?,?,?,?,?,?,?,?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssisssss", $adminName, $adminEmail, $adminContact, $adminAddress, $adminCity, $adminState, $adminPincode, $hashAdminPassword);
    $stmt->execute();
    $stmt->close();
}

// Start session
session_start();

adminTableCreate($conn);
defaultAdminDataInsert($conn);

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    if (isset($_POST['login_admin'])) {
        $adminEmail = $_POST['a_email'];
        $adminPassword = $_POST['a_password'];

        $sql = "SELECT * FROM admin WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $adminEmail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (password_verify($adminPassword, $row['password'])) {
                $_SESSION["admin"] = true;
                $_SESSION["admin_id"] = $row["id"];
                // $_SESSION["admin_email"] = $row["email"];
                header('Location: ../Admin/Dashboard.php');
                exit;
            } else {
                echo "<div class='error-message absolute bg-red-600 visible text-white flex justify-center gap-2 top-0 left-0 w-full p-4 rounded-sm'><strong>Error!</strong> Invalid Password. Access Denied</div>";
            }
        } else {
            echo "<div class='error-message absolute bg-red-600 text-white flex justify-center gap-2 top-0 left-0 w-full p-4 rounded-sm'><strong>Error!</strong> Invalid Email Address</div>";
        }
    }
    // login employee
    if (isset($_POST['login_employee'])) {
        $employeeEmail = $_POST['email'];
        $employeePassword = $_POST['password'];

        $sql = "SELECT id, password FROM employee WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $employeeEmail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (password_verify($employeePassword, $row['password'])) {
                $_SESSION["employee"] = true;
                $_SESSION["employee_id"] = $row["id"];
                header('Location: ../Employee/Dashboard.php');
                exit;
            } else {
                echo "<div class='error-message absolute bg-red-600 visible text-white flex justify-center gap-2 top-0 left-0 w-full p-4 rounded-sm'><strong>Error!</strong> Invalid Password. Access Denied</div>";
            }
        } else {
            echo "<div class='error-message absolute bg-red-600 text-white flex justify-center gap-2 top-0 left-0 w-full p-4 rounded-sm'><strong>Error!</strong> Invalid Email Address</div>";
        }
    }


    if (isset($_POST['studentLogin'])) {
        $studentEmail = $_POST['s_email'];
        $studentPassword = $_POST['rollNo'];
        $sql = "SELECT * FROM students WHERE email = ? AND rollNo =?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $studentEmail, $studentPassword);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {

            $row = $result->fetch_assoc();
            $_SESSION["student"] = true;
            $_SESSION["student_id"] = $row["id"];
            header('Location: ../Student/Dashboard.php');
            exit;

        } else {
            echo "<div class='error-message absolute bg-red-600 text-white flex justify-center gap-2 top-0 left-0 w-full p-4 rounded-sm'><strong>Error!</strong> Invalid Email Address</div>";
        }


    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../src/output.css">
    <style>
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('./vitsImg.jpg');
            background-position: center center;
            background-repeat: no-repeat;
            background-size: cover;
            filter: blur(4px);
            z-index: -1;
        }

        .contain {
            background-color: #ffffff60;
            /* color: #FFF; */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(104, 104, 104, 0.5);
            z-index: 1;
        }
    </style>
</head>

<body>
    <div class="contain">
        <h3 class="text-2xl font-bold mb-4">Log In</h3>
        <select id="userType"
            class="mb-4 block w-full py-2 px-4 border border-gray-300 text-black rounded-md focus:outline-none focus:border-blue-500">
            <option value="admin" selected>Admin</option>
            <option value="employee">Employee</option>
            <option value="student">Student</option>
        </select>

        <!-- Admin login form -->
        <form id="adminLoginForm" class="mb-6" method="post">
            <input type="hidden" name="login_admin">
            <input type="email" id="a_email" name="a_email" placeholder="Email Address" required
                class="mb-4 block w-full py-2 px-4 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500">
            <div class="relative">
                <input type="password" id="a_password" name="a_password" placeholder="Password" required
                    class="mb-4 block w-full py-2 px-4 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500">
                <i class="fa-solid fa-eye absolute right-3 top-3 cursor-pointer"
                    onclick="togglePasswordVisibility('a_password', 'adminPasswordView')" id="adminPasswordView"></i>
            </div>
            <button type="submit"
                class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Log
                In</button>
        </form>

        <!-- Employee login form -->
        <form id="employeeLoginForm" class="mb-6 hidden" action="" method="post">
            <input type="hidden" name="login_employee">

            <input type="email" id="emp_email" name="email" placeholder="Email Address" required
                class="mb-4 block w-full py-2 px-4 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500">
            <div class="relative">
                <input type="password" id="emp_pass" name="password" placeholder="Password" required
                    class="mb-4 block w-full py-2 px-4 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500">
                <i class="fa-solid fa-eye absolute right-3 top-3 cursor-pointer"
                    onclick="togglePasswordVisibility('emp_pass', 'empPasswordView')" id="empPasswordView"></i>
            </div>
            <button type="submit"
                class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Log
                In</button>
        </form>

        <!-- Student login form -->
        <form id="studentLoginForm" class="mb-6 hidden" action="" method="post">
            <input type="hidden" name="studentLogin" id="">
            <input type="email" id="s_email" name="s_email" placeholder="Email Address" required
                class="mb-4 block w-full py-2 px-4 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500">
            <input type="text" id="s_pass" name="rollNo" placeholder="Roll Number" required
                class="mb-4 block w-full py-2 px-4 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500">
            <button type="submit"
                class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Log
                In</button>
        </form>
    </div>
    <script>
        const forms = {
            admin: document.getElementById("adminLoginForm"),
            employee: document.getElementById("employeeLoginForm"),
            student: document.getElementById("studentLoginForm")
        };

        document.getElementById("userType").addEventListener("change", function () {
            Object.values(forms).forEach(form => form.classList.add("hidden"));
            forms[this.value].classList.remove("hidden");
        });

        function togglePasswordVisibility(passwordFieldId, eyeIconId) {
            const passwordField = document.getElementById(passwordFieldId);
            const eyeIcon = document.getElementById(eyeIconId);
            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.classList.remove("fa-eye");
                eyeIcon.classList.add("fa-eye-slash");
            } else {
                passwordField.type = "password";
                eyeIcon.classList.remove("fa-eye-slash");
                eyeIcon.classList.add("fa-eye");
            }
        }

        function hideElementWithDelay(element, delay) {
            setTimeout(() => element.classList.add("hidden"), delay);
        }

        document.querySelectorAll(".error-message").forEach(errorMessage => {
            hideElementWithDelay(errorMessage, 2000);
        });
    </script>
</body>

</html>
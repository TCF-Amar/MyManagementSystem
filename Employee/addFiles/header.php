<?php
if (isset($_POST['logout'])) {
    session_start();
    session_destroy();
    header('Location: ../LandingPage/index.php');

}

$employeeId = $_SESSION['employee_id'];
include ('.././dbconnection/dbconn.php');
include ('.././Tables/tables.php');


$AccessArray[] = array();

$getAccessSqlQuery = "SELECT accesses FROM accesses WHERE emp_id = $employeeId";

$result = $conn->query($getAccessSqlQuery);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Assuming 'accesses' is a comma-separated string, explode it into an array
        $accesses = explode(',', $row['accesses']);

        // Merge the current accesses with the main access array
        $AccessArray = array_merge($AccessArray, $accesses);
    }
}

$empSql = "SELECT r.role FROM employee e
           JOIN emprole r ON r.id = e.role
           WHERE e.id = $employeeId";
$res = $conn->query($empSql);
if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $empRole = $row['role'];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .header {
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
            font-size: 2rem;
            transition: all 0.2s ease-in-out;
            z-index: 10;
            box-shadow: 2px 2px 2px #000;
        }

        .header.active {
            width: calc(100% - 15%);
        }

        .aside {
            position: fixed;
            top: 10vh;
            left: 0;
            padding: 10px;
            height: 100vh;
            overflow-y: auto;
            background-color: rgb(16, 41, 110);
            color: #ffffff;
            width: 15%;
            transform: translateX(-110%);
            transition: all 0.2s linear;
            z-index: 9;
        }

        .aside.active {
            transform: translateX(0);
        }

        .logo {
            width: 40px;
        }

        .profileLogo {
            width: 40px;
            height: 40px;
            background-image: url("./img/logo.png");
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            border-radius: 50%;
        }

        .toggleAsideBar {
            color: rgb(16, 41, 110);
            cursor: pointer;
        }

        .toggleAsideBar.active {
            transform: translateX(0);
        }

        .logout button {
            width: 100%;
            padding: 0.5rem;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s linear;
        }

        .logout button:hover {
            background: #ffffffaa;
            color: rgb(16, 41, 110);
        }

        .asideOpt a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 10px;
            transition: background 0.2s;
        }

        .asideOpt a:hover {
            background: #ffffffaa;
            color: rgb(16, 41, 110);
        }

        .adminName {
            font-size: 15px;
        }

        .dropDownArrow {
            font-size: 35px;
            font-size: 25px;
            color: gray;
        }

        .adminDetails {
            align-items: end;
            gap: 0px;
        }

        .main {
            height: 90vh;
            position: fixed;
            top: 10vh;
            right: 0;
            width: 100%;
            padding: 20px;
            transition: all 0.2s linear;
        }


        @media (max-width: 768px) {
            .header {
                font-size: 1.5rem;
                padding: 5px;
            }

            .header.active {
                width: 100%;
            }

            .toggleAsideBar {
                top: 10vh;
                left: 0;
                transform: translateX(0);
                border-radius: 0 50% 50% 0;
            }

            .aside {
                width: 50%;
                transform: translateX(-100%);
            }

            .aside.active {
                transform: translateX(0);
            }

            .adminDetails {
                font-size: 1rem;
                width: 150px;
                gap: 10px;
                align-items: end;
            }

            .profileLogo {
                width: 30px;
                height: 30px;
                padding-right: 50px;
            }

            .dropDownArrow {
                font-size: 25px;
                color: gray;
            }
        }

        @media (max-width: 480px) {
            .header {
                font-size: 1.2rem;
            }

            .toggleAsideBar {
                width: 40px;
                height: 40px;
                font-size: 1.5rem;
            }

            .adminDetails {
                width: 100px;
            }
        }
    </style>
    <style>

    </style>
</head>

<body>
    <header id="header" class="header">
        <div id="toggleAsideBar" class="toggleAsideBar">
            <i class="fa-solid fa-bars" id="arrow" aria-label="Toggle Sidebar"></i>
        </div>
        <h1><strong>Management System</strong></h1>
        <div class="flex  items-baseline adminDetails">
        </div>

    </header>


    <aside id="sidebar" class="aside">
        <div class="sideContainer">
            <div class="flex items-center  m-2  gap-3 p-2 rounded">
                <img src="./img/logo.png" alt="VITS Logo" class="logo">
                <span class="font-bold text-2xl">VITS</span>
            </div>
            <hr>
            <div class="p-2 flex flex-col gap-4 asideOpt">
                <a href="./Dashboard.php"
                    class="flex gap-2 items-baseline content-center p-3 transition-all rounded hover:shadow"><i
                        class="fa-solid fa-house"></i>
                    <h2>Dashboard</h2>
                </a>
                <a href="./courses.php"
                    class="flex gap-2 items-baseline content-center p-3 transition-all rounded hover:shadow"><i
                        class="fa-solid fa-book"></i>
                    <h2>Courses</h2>
                </a>
                <a href="./student.php"
                    class="flex gap-2 items-baseline content-center p-3 transition-all rounded hover:shadow"><i
                        class="fa-solid fa-user-large"></i>
                    <h2>Students</h2>
                </a>
                <a href="./employee.php"
                    class="flex gap-2 items-baseline content-center p-3 transition-all rounded hover:shadow"><i
                        class="fa-solid fa-user-group"></i>
                    <h2>Employee</h2>
                </a>
                <?php if ($empRole == 'HOD' || $empRole == 'Teacher'): ?>
                    <a href="./attendance.php"
                        class="flex gap-2 items-baseline content-center p-3 transition-all rounded hover:shadow"><i
                            class="fa-solid fa-user-group"></i>
                        <h2>Attendance</h2>
                    </a>
                <?php endif; ?>
                <?php if ($empRole == 'HOD' || $empRole == 'Teacher'): ?>
                    <a href="./results.php"
                        class="flex gap-2 items-baseline content-center p-3 transition-all rounded hover:shadow"><i
                            class="fa-solid fa-user-group"></i>
                        <h2>Results</h2>
                    </a>
                <?php endif; ?>
                <a href="./TimeTable.php"
                    class="flex gap-2 items-baseline content-center p-3 transition-all rounded hover:shadow"><i
                        class="fa-solid fa-calendar-days"></i>
                    <h2>Time Table</h2>
                </a>

            </div>
            <hr>
            <div class="logout">
                <form action="" method='post'
                    class="flex gap-2 items-baseline content-center p-3 transition-all rounded hover:shadow">
                    <button name="logout">Logout</button>
                </form>
            </div>
        </div>
    </aside>



    <script>
        let bar = document.getElementById("toggleAsideBar");
        let arrow = document.getElementById("arrow");
        let header = document.getElementById("header");
        let main = document.getElementById("main");
        bar.onclick = function () {
            let sidebar = document.getElementById("sidebar");
            sidebar.classList.toggle("active");
            bar.classList.toggle("active");
            arrow.classList.toggle("fa-bars");
            arrow.classList.toggle("fa-xmark");
        };

    </script>
</body>

</html>
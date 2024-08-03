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
    <script src="https://code.jquery.com/jquery-3.7.1.js"
        integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.2/css/dataTables.dataTables.css" />
    <title>Time Table</title>
    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="./addFiles/css/main.css">
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

        dialog {
            border: none;
            padding: 20px;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            width: 50%;
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
    </style>
</head>

<body>
    <?php include ('./addFiles/header.php'); ?>
    <main class="main" id="main">
        <nav class="fixed top left-0 w-full bg-gray-600 font-bold text-white px-2" style="top:10vh; z-index: 9999">
            <button id="createNewTimeTable" type="button" class="hover:text-blue-600">Create New Time Table</button>
        </nav>

        <dialog id="addTimeTableModal">
            <div class="modal-header">
                <button class="close" onclick="closeModal()">&times;</button>
                <h5 class="modal-title">Add TimeTable</h5>
            </div><br>

            <div class="">

                <div class="">
                    <label for="">Course</label>
                    <select name="" id="">
                        <option value="" selected disabled>Select Course</option>
                        <?php
                        $courses = $conn->query("SELECT * FROM courses");
                        while ($row = $courses->fetch_assoc()): ?>
                            <option value=""><?= htmlspecialchars($row['courseName']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="">
                    <label for="">Year</label>
                    <select name="" id="">
                        <option value="" selected disabled>Select Year</option>
                        <?php
                        $years = $conn->query("SELECT * FROM courseyears");
                        while ($row = $years->fetch_assoc()): ?>
                            <option value=""><?= htmlspecialchars($row['courseYear']) ?></option>
                        <?php endwhile; ?>


                    </select>
                </div>
            </div>
            <form action="add_timetable.php" method="post">
                <?php
                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                foreach ($days as $day) {
                    echo "<h3>$day</h3>";
                    for ($i = 0; $i < 5; $i++) { // Allow up to 5 time slots per day
                        echo "<div class='form-group'>";
                        echo "<label for='{$day}_subject_$i'>Subject:</label>";
                        echo "<input type='text' name='{$day}_subject[]' id='{$day}_subject_$i' required>";
                        echo "<label for='{$day}_start_time_$i'>Start Time:</label>";
                        echo "<input type='time' name='{$day}_start_time[]' id='{$day}_start_time_$i' required>";
                        echo "<label for='{$day}_end_time_$i'>End Time:</label>";
                        echo "<input type='time' name='{$day}_end_time[]' id='{$day}_end_time_$i' required>";
                        echo "</div>";
                    }
                }
                ?>
                <button type="submit">Submit</button>
            </form>
        </dialog>
    </main>

    <script>
        const addTimeTableModal = document.getElementById("addTimeTableModal");
        const createNewTimeTable = document.getElementById("createNewTimeTable");
        createNewTimeTable.addEventListener("click", function () {
            addTimeTableModal.showModal();
        });

        function closeModal() {
            addTimeTableModal.close();
        }
    </script>
</body>

</html>
<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

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
    <title>Students</title>
    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="./addFiles/css/main.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }

        header {
            background-color: #333;
            color: #fff;
            padding: 1rem;
            text-align: center;
        }

        .main {
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }

        .filter-container div {
            flex: 1;
            min-width: 200px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        select,
        input[type="number"] {
            width: 100%;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        #getResult {
            width: 100%;
            margin-top: 20px;
            /* Adjust as needed */
        }

        .msg {
            z-index: 2900900;
            position: fixed;
            width: 100%;
            height: 60px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            font-size: 1.4rem;
            color: #fff;
            background-color: #4CAF50;
            transition: opacity 0.5s ease-in-out;
        }

        .hidden {
            display: none;
        }

        @media (max-width: 768px) {
            .filter-container {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>

<body>
    <?php include ('./addFiles/header.php'); ?>
    <main class="main">
        <h1>View Student Results</h1>
        <div class="filter-container">
            <div>
                <label for="course">Course</label>
                <select name="course" id="course">
                    <option value="" selected disabled>Select Course</option>
                    <?php
                    $sql = "SELECT id, courseName FROM courses";
                    $stmt = $conn->prepare($sql);
                    if ($stmt) {
                        $stmt->execute();
                        $result = $stmt->get_result();
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['courseName']) . "</option>";
                        }
                        $stmt->close();
                    } else {
                        echo "<option value='' disabled>Error loading courses</option>";
                    }
                    ?>
                </select>
            </div>
            <div>
                <label for="courseYear">Course Year</label>
                <select name="courseYear" id="courseYear">
                    <option value="" selected disabled>Select Year</option>
                    <?php
                    $sql = "SELECT id, courseYear FROM courseyears";
                    $stmt = $conn->prepare($sql);
                    if ($stmt) {
                        $stmt->execute();
                        $result = $stmt->get_result();
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['courseYear']) . "</option>";
                        }
                        $stmt->close();
                    } else {
                        echo "<option value='' disabled>Error loading years</option>";
                    }
                    ?>
                </select>
            </div>
            <div>
                <label for="resultType">Exam Name</label>
                <select name="resultType" id="resultType">
                    <option value="" selected disabled>Select Exam Name</option>
                    <?php
                    $sql = "SELECT id, examName FROM examnames";
                    $stmt = $conn->prepare($sql);
                    if ($stmt) {
                        $stmt->execute();
                        $result = $stmt->get_result();
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['examName']) . "</option>";
                        }
                        $stmt->close();
                    } else {
                        echo "<option value='' disabled>Error loading exam names</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="hidden" id="month-container">
                <label for="month">Month</label>
                <select name="month" id="month">
                    <option value="" selected disabled>Select Month</option>
                    <?php
                    // Use PHP to dynamically generate options if needed
                    $months = [
                        "January",
                        "February",
                        "March",
                        "April",
                        "May",
                        "June",
                        "July",
                        "August",
                        "September",
                        "October",
                        "November",
                        "December"
                    ];
                    foreach ($months as $month) {
                        echo "<option value='" . $month . "'>" . $month . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="hidden" id="year-container">
                <label for="year">Year</label>
                <input type="number" name="year" id="year" placeholder="Enter Year">
            </div>
        </div>

        <div id="getResult">
            <!-- Result Here -->
        </div>
    </main>

    <script src="https://cdn.datatables.net/2.0.7/js/dataTables.js"></script>

    <script>
        $(document).ready(function () {

            $('#resultType').change(function () {
                let examNameText = $('#resultType option:selected').text().trim(); // Get selected text and trim any extra whitespace
                if (examNameText === "Unit Test") {
                    $('#month-container').removeClass('hidden');
                    $('#year-container').removeClass('hidden');
                } else {
                    $('#month-container').addClass('hidden');
                    $('#year-container').removeClass('hidden');
                }
            });


            // Function to handle AJAX request
            function getResult() {
                // Gather selected data
                let courseId = $('#course').val();
                let courseYearId = $('#courseYear').val();
                let examNameId = $('#resultType').val();
                let year = $('#year').val();
                let month = $('#month').val(); // Initialize month to null or empty string

                // Check if examNameId is "Unit Test" to include month
                if (examNameId === "Unit Test") {
                    month = $('#month').val();
                }

                // AJAX data object
                let data = {
                    courseId: courseId,
                    courseYearId: courseYearId,
                    examNameId: examNameId,
                    year: year,
                    month: month
                };

                // AJAX request
                $.ajax({
                    url: './addFiles/getResult.php',
                    type: 'POST',
                    data: data,
                    success: function (response) {
                        // Update getResult div with response
                        $('#getResult').html(response);
                    },
                    error: function (xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            }

            // Call getResult initially or on change in select boxes
            $('#course, #courseYear, #resultType, #year, #month').change(function () {
                getResult();
            });

            // Optional: Fade out the message after 5 seconds
            setTimeout(() => {
                $(".msg").fadeOut('slow', function () {
                    $(this).addClass('hidden');
                });
            }, 5000);

        });

    </script>


</body>

</html>
<?php
session_start();
include ('../dbconnection/dbconn.php');

$emp_id = $_SESSION['employee_id'] ?? null;

if (!$emp_id) {
    die("No employee ID found in session. Please log in.");
}

// Fetch employee name
$sql = "SELECT * FROM employee WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $emp_name = htmlspecialchars($row["firstName"] . ' ' . $row['lastName']);
} else {
    die("Employee not found.");
}

$date = date("Y-m-d");
$dDate = "<div class='flex gap-5'><div><strong>Date: </strong>" . date("d-m-Y") . '</div> ' . '<div><strong>Month: </strong>' . date('F') . '</div> ' . '<div><strong>Day: </strong>' . date('l') . '</div> ' . "</div>";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['takeAttendance'])) {
        $courseId = $_POST['courseId'];
        $subjectId = $_POST['subject'];
        $lecture = $_POST['lecture'];
        $date = $_POST['date'];
        $day = $_POST['day'];
        $month = $_POST['month'];
        $year = $_POST['year'];
        $studentIds = $_POST['studentId'] ?? [];
        $statuses = $_POST['status'] ?? [];

        // Check if attendance already exists
        $checkSql = "SELECT COUNT(*) as count FROM attendance WHERE emp_id = ? AND course_id = ? AND subject_id = ? AND lecture = ? AND date = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("iiiss", $emp_id, $courseId, $subjectId, $lecture, $date);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $attendanceCount = $checkResult->fetch_assoc()['count'];

        if ($attendanceCount > 0) {
            echo "<script>alert('Attendance for this subject has already been submitted for today.');</script>";
        } else {
            // Insert attendance records
            $sql = "INSERT INTO attendance (student_id, emp_id, course_id, subject_id, lecture, date, day, month, year, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            $duplicateDetected = false;
            foreach ($studentIds as $studentId) {
                $status = in_array($studentId, $statuses) ? 'present' : 'absent';
                $stmt->bind_param("iiiissssss", $studentId, $emp_id, $courseId, $subjectId, $lecture, $date, $day, $month, $year, $status);
                try {
                    $stmt->execute();
                } catch (mysqli_sql_exception $e) {
                    if ($e->getCode() == 1062 && !$duplicateDetected) {
                        echo "<script>alert('Duplicate entry detected.');</script>";
                        $duplicateDetected = true;
                    } else {
                        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
                    }
                }
            }
            echo "<script>alert('Attendance stored successfully');</script>";
        }
    }

    if (isset($_POST['updateAttendance'])) {
        $newStatus = $_POST['attStatus'];
        $attendanceId = $_POST['AttendanceId'];

        $updateSql = "UPDATE attendance SET status = ? WHERE att_id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("si", $newStatus, $attendanceId);

        if ($stmt->execute()) {
            echo '<script>alert("Attendance Status Updated Successfully")</script>';
        } else {
            echo "Error updating attendance status: " . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.7.1.js"
        integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.2/css/dataTables.dataTables.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../src/output.css">
    <title>Attendance</title>
    <style>
       /* Add custom fonts for better appearance */
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');

/* Apply the custom font to the entire body */
body {
    font-family: 'Roboto', sans-serif;
    background-color: #f4f4f9;
    color: #333;
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Main container styling */
.main {
    margin-bottom: 10px;
    height: 90vh;
    position: fixed;
    top: 10vh;
    right: 0;
    width: 100%;
    padding: 20px;
    transition: all 0.2s linear;
    overflow-y: auto;
    background: #ffffff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Style for dialog modal */
dialog {
    border: none;
    padding: 20px;
    border-radius: 0.5rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    width: 50%;
    background: #fff;
    max-width: 600px;
    margin: auto;
    z-index: 1000;
    position: relative;
}

/* Hide scrollbar in modal */
dialog::-webkit-scrollbar {
    display: none;
}

/* Style for close button in modal */
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

/* Responsive modal styling */
@media screen and (max-width: 760px) {
    dialog {
        width: 90%;
    }
}

/* Modal header styling */
.modal-header {
    background-color: #f0f0f0;
    border-bottom: 2px solid #ccc;
    padding: 1rem;
}

/* Modal title styling */
.modal-title {
    font-size: 1.5rem;
    font-weight: bold;
    color: #333;
}

/* Button styling */
button {
    display: inline-block;
    font-size: 16px;
    padding: 10px 20px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s ease, color 0.3s ease;
}

button:hover {
    opacity: 0.9;
}

/* Specific button styles */
#takeAttendanceBtn,
#fullAttDetailsBtn {
    background-color: #28a745;
    color: white;
}

#takeAttendanceBtn:hover,
#fullAttDetailsBtn:hover {
    background-color: #218838;
}

button[type="submit"] {
    background-color: #007bff;
    color: white;
}

button[type="submit"]:hover {
    background-color: #0056b3;
}

/* Input and select styling */
input,
select {
    padding: 8px;
    margin: 5px 0;
    border-radius: 5px;
    border: 1px solid #ccc;
    /* width: 100%; */
}

input:focus,
select:focus {
    border-color: #28a745;
    outline: none;
    box-shadow: 0 0 5px rgba(40, 167, 69, 0.5);
}

/* Table styling */
table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    font-size: 16px;
    text-align: left;
}

table thead tr {
    background-color: #f2f2f2;
}

table th,
table td {
    padding: 12px 15px;
    border: 1px solid #ddd;
}

table tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

table tbody tr:hover {
    background-color: #f1f1f1;
}

/* Flexbox for form layout */
.flex {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.flex div {
    flex: 1;
}

/* Custom scrollbar styling */
body::-webkit-scrollbar {
    width: 10px;
}

body::-webkit-scrollbar-track {
    background: #f4f4f9;
}

body::-webkit-scrollbar-thumb {
    background-color: #888;
    border-radius: 10px;
    border: 2px solid #f4f4f9;
}

body::-webkit-scrollbar-thumb:hover {
    background-color: #555;
}

    </style>
</head>

<body>
    <?php include ('./addFiles/header.php') ?>

    <main class="main">
        <div class="font-bold text-center">Welcome - <?php echo $emp_name; ?></div>
        <button type="button" id="takeAttendanceBtn"
            class="bg-green-500 m-4 px-4 py-2 rounded hover:bg-green-700 font-bold hover:text-white">Take Attendance
        </button>
        <a href="./addFiles/fullAttendanceDetails.php" id="fullAttDetailsBtn"
            class="bg-green-500 m-4 px-4 py-2 rounded hover:bg-green-700 font-bold hover:text-white">Show Details
        </a>

        <dialog id="takeAttendanceModal" class="modal">
            <div class="modal-header">
                <button class="close" onclick="closeModal()">&times;</button>
                <h5 class="modal-title">Take Attendance</h5>
            </div><br>
            <div>
                <form id="attendanceForm" action="" method="POST">
                    <input type="hidden" name="takeAttendance">

                    <div class="flex flex-wrap justify-between">
                        <div>
                            <label class="font-bold">Select Course:</label>
                            <select name="courseId" class="border-2" id="course" required>
                                <option selected disabled hidden>Select Course</option>
                                <?php
                                $sql = "SELECT DISTINCT c.courseName, c.id FROM allotsubjects sa
                                        JOIN subjects s ON s.id = sa.subjectId
                                        JOIN coursebranches b ON b.id = s.branch
                                        JOIN courses c ON c.id = b.courseId
                                        WHERE sa.empId = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $emp_id);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                while ($row = $result->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($row['id']); ?>">
                                        <?php echo htmlspecialchars($row['courseName']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div>
                            <label for="year">Year:</label>
                            <select name="year" id="year" required>
                                <option value="" selected hidden disabled>Select Year</option>
                                <?php
                                $sql = "SELECT * FROM courseyears";
                                $result = $conn->query($sql);
                                while ($row = $result->fetch_assoc()):
                                    ?>
                                    <option value="<?php echo htmlspecialchars($row['id']) ?>">
                                        <?php echo htmlspecialchars($row['courseYear']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div>
                            <label for="subject">Subject:</label>
                            <select name="subject" id="subject" required>
                                <option value="" selected hidden disabled>Select Subject</option>
                                <?php
                                $sql = "SELECT s.id, s.subjectName FROM allotsubjects a
                                        JOIN subjects s ON s.id = a.subjectId
                                        WHERE a.empId = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $emp_id);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                while ($row = $result->fetch_assoc()):
                                    ?>
                                    <option value="<?php echo htmlspecialchars($row['id']) ?>">
                                        <?php echo htmlspecialchars($row['subjectName']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div>
                            <label for="lecture">Lecture:</label>
                            <select name="lecture" id="lecture" required>
                                <option value="" selected hidden disabled>Select Lecture</option>
                                <?php
                                $sql = "SELECT * FROM lectures";
                                $result1 = $conn->query($sql);
                                while ($row = $result1->fetch_assoc()):
                                    ?>
                                    <option value="<?php echo htmlspecialchars($row['id']); ?>">
                                        <?php echo htmlspecialchars($row['lecture_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div>
                            <label for="date">Date:</label>
                            <input type="date" name="date" id="date" required
                                class="bg-gray-100 text-gray-700 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-green-500">
                            <input type="hidden" name="day" id="dayOfWeek" readonly>
                            <input type="hidden" name="month" id="month" readonly>
                            <input type="hidden" name="year" id="yearHidden" readonly>
                        </div>
                    </div>


                    <div class="">
                        <div class="details">

                        </div>
                    </div>

                    <button type="submit"
                        class="bg-green-500 text-center flex justify-center w-full p-2 text-2xl font-bold text-white rounded mt-4">
                        Submit Attendance
                    </button>
                </form>
            </div>
        </dialog>

        <div>
            <label for="" class="font-bold text-2xl">Today Attendance</label><br>
            <label for="" class="font-bold"><?php echo $dDate ?></label>
        </div><br>

        <div class="overflow-x-auto">
            <table id="myTable" class="overflow-x-auto">
                <thead>
                    <tr class="border-2 border-black p-4 text-center">
                        <th>#</th>
                        <th>Roll No</th>
                        <th>Name</th>
                        <th>Subject</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Lecture</th>
                        <th>Status</th>
                        <th class="text-center">Update</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    $sql = "SELECT a.*, y.courseYear, s.rollNo, s.firstName, s.lastName, sb.subjectName, cs.courseName, l.lecture_name AS lecture 
                    FROM attendance a
                    JOIN students s ON s.id = a.student_id
                    JOIN courses cs ON cs.id = a.course_id
                    JOIN subjects sb ON sb.id = a.subject_id
                    JOIN courseyears y ON y.id = s.courseYear
                    JOIN lectures l ON l.id = a.lecture
                    WHERE a.date='$date' AND a.emp_id = $emp_id
                    ORDER BY s.firstName, s.lastName ASC";

                    $result = mysqli_query($conn, $sql);
                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td class="border">' . $i . '</td>';
                        echo '<td class="border">' . ucfirst($row['rollNo']) . '</td>';
                        echo '<td class="border">' . ucfirst($row['firstName']) . ' ' . ucfirst($row['lastName']) . '</td>';
                        echo '<td class="border">' . ucfirst($row['subjectName']) . '</td>';
                        echo '<td class="border">' . ucfirst($row['courseName']) . '</td>';
                        echo '<td class="border">' . ucfirst($row['courseYear']) . '</td>';
                        echo '<td class="border">' . ucfirst($row['lecture']) . '</td>';
                        echo '<td class="attendanceStatuses border">';
                        echo '<label class="p-1 text-center flex justify-center items-center w-9/12 rounded-lg text-white" style="background-color: ' . ($row['status'] === 'present' ? 'green' : 'red') . '">';
                        echo ucfirst($row['status']);
                        echo '</label>';
                        echo '</td>';
                        echo '<td class="flex justify-center border">';
                        echo '<button type="button" data-attId="' . $row['att_id'] . '" class="updateBtn font-bold text-2xl text-blue-600 hover:text-blue-700"><i class="fa-solid fa-pen"></i></button>';
                        echo '</td>';
                        echo '</tr>';
                        $i++;
                    }
                    ?>
                </tbody>
            </table>
        </div>


        <dialog id="updateAttendanceModal" class="modal">
            <div class="modal-header">
                <button class="close" onclick="closeModal()">&times;</button>
                <h5 class="modal-title">Take Attendance</h5>
            </div><br>
            <form id="updateAttendanceForm" method="post">

            </form>
        </dialog>
    </main>

    <script>
        const takeAttendanceBtn = document.getElementById('takeAttendanceBtn');
        takeAttendanceBtn.addEventListener('click', () => {
            document.getElementById('takeAttendanceModal').showModal();
        });



        function closeModal() {
            document.getElementById('takeAttendanceModal').close();
            document.getElementById('updateAttendanceModal').close();
        }

        function handleCourseChange() {
            const courseVal = course.value;
            const yearVal = year.value;

            fetchStudentDetails(courseVal, yearVal);
        }

        function handleYearChange() {
            const courseVal = course.value;
            const yearVal = year.value;
            fetchStudentDetails(courseVal, yearVal);
        }

        course.addEventListener("change", handleCourseChange);
        year.addEventListener("change", handleYearChange);

        function fetchStudentDetails(course, year) {
            const xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        document.querySelector('.details').innerHTML = xhr.responseText;
                    } else {
                        console.error('Error fetching student details:', xhr.status);
                    }
                }
            };

            xhr.open('GET', './addFiles/fetch_student_details.php?course=' + course + '&year=' + year, true);
            xhr.send();
        }

        document.addEventListener("DOMContentLoaded", function () {
            const updateButtons = document.querySelectorAll("#myTable .updateBtn");

            const updateAttendanceForm = document.getElementById("updateAttendanceForm");

            const updateModal = document.getElementById("updateAttendanceModal");
            updateButtons.forEach(function (button) {
                button.addEventListener("click", function () {
                    const attendanceId = button.getAttribute('data-attId');
                    document.getElementById('updateAttendanceModal').showModal();
                    const xhr = new XMLHttpRequest();
                    xhr.onreadystatechange = function () {
                        if (xhr.readyState === XMLHttpRequest.DONE) {
                            if (xhr.status === 200) {
                                updateAttendanceForm.innerHTML = xhr.responseText;

                            } else {
                                console.error('Error fetching student attendance details:', xhr.status);
                            }
                        }
                    };
                    xhr.open('GET', './addFiles/updateAttendance.php?attId=' + attendanceId, true);
                    xhr.send();
                });
            });

            closeButton.addEventListener("click", closeUpdateModal);
        });


        $(document).ready(function () {
            $('#myTable').DataTable();
        });

        const dateInput = document.getElementById('date');
        dateInput.addEventListener('change', function () {
            const selectedDate = new Date(this.value);
            document.getElementById('dayOfWeek').value = selectedDate.toLocaleString('default', { weekday: 'long' });
            document.getElementById('month').value = selectedDate.toLocaleString('default', { month: 'long' });
            document.getElementById('yearHidden').value = selectedDate.getFullYear();
        });



    </script>
    <script src="https://cdn.datatables.net/2.0.7/js/dataTables.js"></script>
    <script>
        let table = new DataTable('#myTable');
    </script>
</body>

</html>
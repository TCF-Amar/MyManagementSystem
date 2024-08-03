<?php
session_start();
require_once ('../dbconnection/dbconn.php');

// Utility function to handle errors
function handleError($message)
{
    echo "<script>alert('Error: $message');</script>";
}

// Utility function to fetch data from the database
function fetchData($conn, $sql, $params = [], $types = '')
{
    $stmt = $conn->prepare($sql);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result;
}

// Utility function to execute a database query
function executeQuery($conn, $sql, $params = [], $types = '')
{
    $stmt = $conn->prepare($sql);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $stmt->close();
}

// Define access permissions
$checkboxes = [
    'addStudent' => 'Add Student',
    'addEmployee' => 'Add Employee',
    'viewEmpDetails' => 'View Employee Details',
    'addCourse' => 'Add Course',
    'addSubjects' => 'Add Subjects',
    'updateStudent' => 'Update Student',
    'deleteStudent' => 'Delete Student',
    'updateEmployee' => 'Update Employee',
    'deleteEmployee' => 'Delete Employee',
    'updateCourses' => 'Update Courses',
    'removeCourses' => 'Remove Courses',
    'addCourseBranches' => 'Add Course Branches',
    'updateSubjects' => 'Update Subjects',
    'removeSubjects' => 'Remove Subjects',
    'giveAccessesAccess' => 'Give Accesses Employee',
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $empId = $_GET['empId'] ?? null;
    if (isset($_POST['accessGive'])) {
        $selectedAccesses = [];

        foreach ($checkboxes as $key => $value) {
            if (isset($_POST[$key])) {
                $selectedAccesses[] = $value;
            }
        }

        try {
            executeQuery($conn, "DELETE FROM accesses WHERE emp_id = ?", [$empId], 'i');
            $insertStmt = $conn->prepare("INSERT INTO accesses (emp_id, accesses) VALUES (?, ?)");
            foreach ($selectedAccesses as $access) {
                $insertStmt->bind_param("is", $empId, $access);
                $insertStmt->execute();
            }
            $insertStmt->close();
        } catch (mysqli_sql_exception $e) {
            handleError('Unable to save access permissions. Please try again later.');
        }
    }

    if (isset($_POST['subjectAllot'])) {
        $empId = intval($_POST['subjectAllot']);
        $courseId = intval($_POST['course']);
        $yearId = intval($_POST['yearForSubjects']);
        $selectedSubjects = isset($_POST['subjects']) ? $_POST['subjects'] : [];

        if (!empty($courseId) && !empty($yearId) && !empty($selectedSubjects)) {
            try {
                // Check if empId exists in the employee table
                $empCheck = $conn->prepare("SELECT id FROM employee WHERE id = ?");
                $empCheck->bind_param("i", $empId);
                $empCheck->execute();
                $empCheck->store_result();

                if ($empCheck->num_rows > 0) {
                    // Prepare the statement for inserting into allotSubjects table
                    $stmt = $conn->prepare("INSERT INTO allotSubjects (subjectId, empId) VALUES (?, ?)");
                    $stmt->bind_param("ii", $subjectId, $empId);

                    foreach ($selectedSubjects as $subjectId) {
                        $subjectId = intval($subjectId);

                        // Check if subjectId exists in the subjects table
                        $subjectCheck = $conn->prepare("SELECT id FROM subjects WHERE id = ?");
                        $subjectCheck->bind_param("i", $subjectId);
                        $subjectCheck->execute();
                        $subjectCheck->store_result();

                        if ($subjectCheck->num_rows > 0) {
                            // Insert the valid subjectId and empId into allotSubjects
                            $stmt->execute();
                        } else {
                            echo "<script>alert('Subject ID {$subjectId} does not exist in the subjects table.');</script>";
                        }

                        $subjectCheck->close();
                    }

                    $stmt->close();
                    echo "<script>alert('Subjects allotted successfully!');</script>";
                } else {
                    echo "<script>alert('Employee ID does not exist in the employee table.');</script>";
                }

                $empCheck->close();
            } catch (mysqli_sql_exception $e) {
                echo "<script>alert('Database error: " . $e->getMessage() . "');</script>";
            }
        } else {
            echo "<script>alert('Please select course, year, and at least one subject.');</script>";
        }
    }

}



$employeeId = $_GET['empId'] ?? null;
$name = $role = '';
$grantedAccesses = [];

if ($employeeId) {
    $result = fetchData($conn, "SELECT e.firstName, e.lastName, r.role FROM employee e 
                                JOIN empRole r ON e.role = r.id WHERE e.id = ?", [$employeeId], 'i');
    if ($row = $result->fetch_assoc()) {
        $name = $row['firstName'] . ' ' . $row['lastName'];
        $role = $row['role'];
    }

    $result = fetchData($conn, "SELECT accesses FROM accesses WHERE emp_id = ?", [$employeeId], 'i');
    while ($row = $result->fetch_assoc()) {
        $grantedAccesses[] = $row['accesses'];
    }
}

$courses = fetchData($conn, "SELECT id, courseName FROM courses")->fetch_all(MYSQLI_ASSOC);
$years = fetchData($conn, "SELECT id, courseYear FROM courseYears")->fetch_all(MYSQLI_ASSOC);

$conn->close();



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Give Access</title>
    <link rel="stylesheet" href="../src/output.css">
    <style>
        .main {
            margin-bottom: 10px;
            height: 90vh;
            position: fixed;
            top: 10vh;
            right: 0;
            width: 100%;
            padding: 20px;
            overflow-y: auto;
        }

        .main::-webkit-scrollbar {
            display: none;
        }

        .input {
            display: none;
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

        .accessBox {
            width: 100px;
            height: 50px;
            border-radius: 5px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 10px;
            font-weight: 700;
            font-size: .8rem;
            color: #fff;
            text-align: center;
        }

        .Box {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            width: 100%;
        }

        @media screen and (max-width:760px) {
            .modal-content {
                width: 90%;
            }
        }
    </style>
</head>

<body>
    <?php include ('./addFiles/header.php'); ?>
    <main class="main" id="main">
        <div class="modal-header">
            <a href="./employee.php" class="close" onclick="closeModal()">&times;</a>
            <h5 class="modal-title">Accesses</h5>
        </div>
        <div class=" bg-gray-600 text-white px-2 flex gap-4">
            <button class=" hover:text-blue-600 font-bold text-white rounded" id="allotSubjectBtn">Allot
                Subject</button>
            <button class=" hover:text-blue-600 font-bold text-white rounded" id="">Alloted
                Subjects</button>
        </div>
        <div>
            <span class="w-full flex justify-center"><strong>Name:</strong>
                <?php echo htmlspecialchars($name) . " (" . htmlspecialchars($role) . ")"; ?></span><br>
        </div><br>

        <br>
        <form action="" method="post">
            <input type="hidden" name="accessGive">
            <label class="font-bold text-2xl underline">Accesses:</label><br>
            <div class="Box">
                <?php
                foreach ($checkboxes as $key => $value) {
                    $isChecked = in_array($value, $grantedAccesses);
                    $bgColorClass = $isChecked ? 'bg-green-500' : 'bg-red-500';

                    echo "<button type='button' class='accessBox $bgColorClass' onclick='toggleCheckbox(this)'>";
                    echo "<input type='checkbox' class='input' value='$value' name='$key' id='$key' " . ($isChecked ? 'checked' : '') . ">";
                    echo "<label for='$key'>" . htmlspecialchars($value) . "</label><br>";
                    echo "</button>";
                }
                ?>
            </div>
            <button type="submit"
                class="bg-blue-500 px-6 py-2 rounded hover:bg-blue-700 font-bold text-2xl text-white w-full md:w-fit">Save
                Access</button>
        </form>

        <dialog id="allotSubjectModal">
            <div class="modal-header">
                <span class="close" onclick="closeModal()">&times;</span>
                <h5 class="modal-title">Subject Allot</h5>
            </div>
            <div>
                <form action="" method="post">
                    <input type="hidden" value="<?= htmlspecialchars($_GET['empId']); ?>" name="subjectAllot">
                    <label for="course" class="font-bold">Select Course:</label>
                    <select name="course" id="courseForSubject"
                        class="border w-full px-3 py-2 rounded-md mt-1 focus:outline-none focus:ring focus:border-blue-300">
                        <option value="" hidden selected disabled>Select Course</option>
                        <?php
                        foreach ($courses as $course) {
                            echo "<option value='" . htmlspecialchars($course['id']) . "'>" . htmlspecialchars($course['courseName']) . "</option>";
                        }
                        ?>
                    </select>

                    <label for="yearForSubjects" class="font-bold">Select Year:</label>
                    <select name="yearForSubjects" id="yearForSubjects"
                        class="border w-full px-3 py-2 rounded-md mt-1 focus:outline-none focus:ring focus:border-blue-300">
                        <option value="" disabled hidden selected>Select Year</option>
                        <?php
                        foreach ($years as $year) {
                            echo "<option value='" . htmlspecialchars($year['id']) . "'>" . htmlspecialchars($year['courseYear']) . "</option>";
                        }
                        ?>
                    </select>

                    <label for="subject" class="font-bold">Select Subjects:</label>
                    <div id="subjectList">
                    </div>
                    <button class="w-full bg-blue-500 py-2 px-4 font-bold text-white hover:bg-blue-700 rounded">Allot
                        Subject</button>
                </form>
            </div>
        </dialog>


        <dialog id="allotedSubjectModal">
            <div class="modal-header">
                <span class="close" onclick="closeModal()">&times;</span>
                <h5 class="modal-title">Subject Allot</h5>
            </div>
        </dialog>
    </main>
    <script>
        function toggleCheckbox(button) {
            const checkbox = button.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
            button.style.backgroundColor = checkbox.checked ? '#4CAF50' : '#f44336';
        }

        document.querySelectorAll('.accessBox').forEach(button => {
            button.addEventListener('click', function () {
                toggleCheckbox(this);
            });
        });

        const allotSubjectModal = document.getElementById("allotSubjectModal");
        document.getElementById("allotSubjectBtn").onclick = function () {
            allotSubjectModal.showModal();
        };

        function closeModal() {
            allotSubjectModal.close();
        }

        function fetchSubjects(courseVal, yearValue) {
            const xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        document.getElementById("subjectList").innerHTML = xhr.responseText;
                    } else {
                        console.error("Error Fetching subjects: ", xhr.status);
                    }
                }
            };
            xhr.open("GET", `./addFiles/fetch_subjects_option.php?courseId=${courseVal}&yearId=${yearValue}`);
            xhr.send();
        }

        document.getElementById('courseForSubject').addEventListener('change', function () {
            fetchSubjects(this.value, document.getElementById('yearForSubjects').value);
        });

        document.getElementById('yearForSubjects').addEventListener('change', function () {
            fetchSubjects(document.getElementById('courseForSubject').value, this.value);
        });
    </script>
</body>

</html>
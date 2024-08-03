<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['employee_id'])) {
    header('Location: login.php');
    exit();
}

$empId = $_SESSION['employee_id'];
include ('../dbconnection/dbconn.php');

function validateAndSanitize($data, $type)
{
    switch ($type) {
        case 'int':
            return filter_var($data, FILTER_VALIDATE_INT);
        case 'string':
            return filter_var($data, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        case 'array':
            return filter_var($data, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        default:
            return null;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['addResult'])) {
        // Validate and sanitize inputs
        $subject = validateAndSanitize($_POST['subject'], 'int');
        $examName = validateAndSanitize($_POST['examName'], 'int');
        $examDate = validateAndSanitize($_POST['examDate'], 'string');
        $examMonth = validateAndSanitize($_POST['examMonth'], 'string');
        $examYear = validateAndSanitize($_POST['examYear'], 'int');
        $maxMark = validateAndSanitize($_POST['maxMark'], 'int');
        $students = validateAndSanitize($_POST['students'], 'array');
        $marks = validateAndSanitize($_POST['marks'], 'array');

        if ($subject && $examName && $examDate && $examMonth && $examYear && $maxMark && !empty($students) && !empty($marks) && count($students) === count($marks)) {
            $insertSql = "INSERT INTO results (studentId, subjectId, examNameId, examDate, examMonth, examYear, maxMark, obtainedMark) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            if ($insertStmt = $conn->prepare($insertSql)) {
                foreach ($students as $key => $studentId) {
                    $studentId = validateAndSanitize($studentId, 'int');
                    $mark = validateAndSanitize($marks[$key], 'int');

                    if ($studentId !== false && $mark !== false) {
                        try {
                            // Try to insert new record
                            $insertStmt->bind_param("iiissiii", $studentId, $subject, $examName, $examDate, $examMonth, $examYear, $maxMark, $mark);
                            $insertStmt->execute();
                        } catch (mysqli_sql_exception $e) {
                            // Check for duplicate entry error
                            if ($e->getCode() === 1062) {
                                echo "<div id='msg' class='msg bg-red-500'>Duplicate entry detected!  $</div>";
                            } else {
                                echo "<div id='msg' class='msg bg-red-500'>Error inserting result for studentId: $studentId. Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                            }
                        }
                    }
                }
                $insertStmt->close();
            }
        }
    }

    if (isset($_POST['updateResult'])) {
        $editMarks = validateAndSanitize($_POST['obtainedMark'], 'int'); // Corrected from $_POST['editObtainedMark'] to $_POST['obtainedMark']
        $resultId = intval($_POST['updateResult']);

        if ($editMarks !== false && $resultId !== false) {
            $updateSql = "UPDATE results SET obtainedMark = ? WHERE id = ?";
            if ($stmt = $conn->prepare($updateSql)) {
                $stmt->bind_param('ii', $editMarks, $resultId);
                if ($stmt->execute()) {
                    echo " <div id='msg' class='msg bg-green-500'>Update Success</div>";
                } else {
                    echo "<div id='msg' class='msg gg-red-500'> Error: " . htmlspecialchars($stmt->error) . "</div>";
                }
                $stmt->close();
            } else {
                echo "<div id='msg' class='msg gg-red-500'> Prepare statement failed: " . htmlspecialchars($stmt->error) . "</div>";
            }
        } else {
            echo "<div id='msg' class='msg gg-red-500'> Invalid input</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.2/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="./addFiles/css/main.css">
    <style>
        /* Styling */
        dialog {
            border: none;
            padding: 20px;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            width: 50%;
        }

        #addResultDialog {
            width: 80%;
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
            #addResultDialog {
                width: 100%;
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

        .goToResultBox {
            margin: 20px;
            max-width: 300px;
            height: 100px;
            background: red;
        }

        .pass {
            color: green;
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
            transition: opacity 0.5s ease-in-out;
        }
    </style>
</head>

<body class="bg-gray-100">
    <?php include ('./addFiles/header.php'); ?>
    <main class="main" id="main">
        <nav class="bg-gray-600 w-full font-bold text-white flex gap-4 p-4">
            <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" id="addResultBtn">Add
                New Results</button>
        </nav>

        <dialog id="addResultDialog" class="p-4">
            <div class="modal-header">
                <button class="close" onclick="closeModal()">&times;</button>
                <h5 class="modal-title">Add Results</h5>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="addResult">
                <div class="flex flex-col md:flex-row gap-4 justify-between">
                    <div>
                        <label for="selectSubject" class="block mb-2 text-lg font-medium text-gray-700">Subject</label>
                        <select name="subject" id="selectSubject" class="block p-2 border border-gray-300 rounded mb-4"
                            required>
                            <option value="" disabled selected>Select Subject</option>
                            <?php
                            $SQL = "SELECT s.id AS subjectId, s.subjectName, c.id AS courseId, c.courseName, b.branchName, y.id AS yearId, y.courseYear 
                                    FROM allotsubjects a
                                    JOIN subjects s ON s.id = a.subjectId
                                    JOIN coursebranches b ON b.id = s.branch
                                    JOIN courses c ON c.id = b.courseId
                                    JOIN courseyears y ON y.id = s.year 
                                    WHERE a.empId = ?";
                            if ($stmt = $conn->prepare($SQL)) {
                                $stmt->bind_param("i", $empId);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo '<option value="' . htmlspecialchars($row['subjectId']) . '" data-course="' . htmlspecialchars($row['courseId']) . '" data-year="' . htmlspecialchars($row['yearId']) . '">' .
                                            htmlspecialchars($row['subjectName']) . ' (' . htmlspecialchars($row['courseName']) . ' - ' . htmlspecialchars($row['courseYear']) . ')</option>';
                                    }
                                }
                                $stmt->close();
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label for="examName" class="block mb-2 text-lg font-medium text-gray-700">Exam Name:</label>
                        <select name="examName" id="examName" class="block p-2 border border-gray-300 rounded mb-4"
                            required>
                            <option value="" disabled selected>Select Exam Name</option>
                            <?php
                            $SQL = "SELECT * FROM examnames";
                            $result = $conn->query($SQL);
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['examName']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label for="examDate" class="block mb-2 text-lg font-medium text-gray-700">Enter Exam
                            Date:</label>
                        <input type="date" id="examDate" class="block w-full p-2 border border-gray-300 rounded mb-4"
                            name="examDate" required>
                        <label for="examMonth" class="block mb-2 text-lg font-medium text-gray-700">Exam Month:</label>
                        <input type="text" id="examMonth" class="block w-full p-2 border border-gray-300 rounded mb-4"
                            name="examMonth" placeholder="Enter exam month">
                        <label for="examYear" class="block mb-2 text-lg font-medium text-gray-700">Exam Year:</label>
                        <input type="text" id="examYear" class="block w-full p-2 border border-gray-300 rounded mb-4"
                            name="examYear" placeholder="Enter exam year">
                    </div>
                    <div>
                        <label for="maxMark" class="block mb-2 text-lg font-medium text-gray-700">Enter Maximum
                            Marks</label>
                        <input type="number" class="block w-full p-2 border border-gray-300 rounded mb-4" name="maxMark"
                            required>
                    </div>
                </div>
                <div id="studentTable" class="bg-white p-4 rounded shadow"></div>
                <button type="submit" class="w-full bg-blue-500 font-bold text-white p-2">Save Results</button>
            </form>
        </dialog>

        <dialog id="editResultDialog" class="p-4">
            <div class="modal-header">
                <button class="close" onclick="closeModal()">&times;</button>
                <h5 class="modal-title">Edit Results</h5>
            </div>
            <form i method="POST" action="">
                <input type="hiden" name="updateResult" id="resultId">
                <div class="flex flex-col md:flex-row gap-4 justify-between" id="resultData">
                    <div class="flex justify-center flex-col w-full">
                        <div>
                            <label for="editSubject"
                                class="block mb-2 text-lg font-medium text-gray-700">Subject</label>
                            <input name="subject" id="editSubject"
                                class="block p-2 border border-gray-300 rounded mb-4 w-full" readonly>
                        </div>
                        <div>
                            <label for="editExamName" class="block mb-2 text-lg font-medium text-gray-700">Exam
                                Name:</label>
                            <input name="examName" id="editExamName"
                                class="block p-2 border border-gray-300 rounded mb-4 w-full" readonly>
                        </div>
                        <div>
                            <label for="editExamDate" class="block mb-2 text-lg font-medium text-gray-700">Enter Exam
                                Date:</label>
                            <input type="text" id="editExamDate"
                                class="block w-full p-2 border border-gray-300 rounded mb-4" name="examDate" readonly>
                        </div>
                        <div class="">
                            <label for="">Mont / Year</label>
                            <input type="text" class="block w-full p-2 border border-gray-300 rounded mb-4" readonly
                                id="monthYear">
                        </div>
                        <div>
                            <label for="editMaxMark" class="block mb-2 text-lg font-medium text-gray-700">Enter Maximum
                                Marks</label>
                            <input type="number" id="editMaxMark"
                                class="block w-full p-2 border border-gray-300 rounded mb-4" name="maxMark" readonly
                                required>
                        </div>
                        <div>
                            <label for="editObtainedMark" class="block mb-2 text-lg font-medium text-gray-700">Enter
                                Obtained Marks</label>
                            <input type="number" id="editObtainedMark"
                                class="block w-full p-2 border border-gray-300 rounded mb-4" name="obtainedMark"
                                required>
                        </div>
                    </div>
                </div>
                <button type="submit" class="w-full bg-blue-500 font-bold text-white p-2">Update Results</button>
            </form>
        </dialog>

        <table id="myTable">
            <thead>
                <tr>
                    <th>S.NO.</th>
                    <th>Roll NO.</th>
                    <th>Name</th>
                    <th>Exam Name</th>
                    <th>Subject</th>
                    <th>Course</th>
                    <th>Year</th>
                    <th>Exam Date</th>
                    <th>Max Mark</th>
                    <th>Obtain Mark</th>
                    <th>Result (P/F)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Perform SQL query and fetch results
                $SQL = "SELECT 
                    r.id AS resultId,
                    s.rollNo, 
                    CONCAT(s.firstName, ' ', s.lastName) AS studentName, 
                    en.examName, 
                    sub.subjectName, 
                    c.courseName, 
                    y.courseYear AS courseYear, 
                    r.examDate, 
                    r.maxMark, 
                    r.obtainedMark 
                FROM 
                    results r
                JOIN 
                    examnames en ON en.id = r.examNameId 
                JOIN 
                    subjects sub ON sub.id = r.subjectId
                JOIN 
                    students s ON s.id = r.studentId
                JOIN 
                    coursebranches cb ON cb.id = sub.branch
                JOIN 
                    courses c ON c.id = cb.courseId
                JOIN 
                    courseYears y ON y.id = sub.year";

                $i = 1;
                $result = $conn->query($SQL);

                // Check if there are rows returned
                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td class="border-b border-black text-wrap"><?php echo $i++ ?></td>
                            <td class="border-b border-black text-wrap"><?= $row['rollNo']; ?></td>
                            <td class="border-b border-black text-wrap"><?= $row['studentName']; ?></td>
                            <td class="border-b border-black text-wrap"><?= $row['examName']; ?></td>
                            <td class="border-b border-black text-wrap"><?= $row['subjectName']; ?></td>
                            <td class="border-b border-black text-wrap"><?= $row['courseName']; ?></td>
                            <td class="border-b border-black text-wrap"><?= $row['courseYear']; ?></td>
                            <td class="border-b border-black text-wrap"><?= $row['examDate']; ?></td>
                            <td class="border-b border-black text-wrap"><?= $row['maxMark']; ?></td>
                            <td class="border-b border-black text-wrap"><?= $row['obtainedMark']; ?></td>
                            <td class="border-b border-black text-wrap text-center">
                                <?php
                                // Determine pass or fail based on conditions
                                if (($row['maxMark'] == 75 && $row['obtainedMark'] >= 25) || ($row['maxMark'] == 100 && $row['obtainedMark'] >= 33) || ($row['maxMark'] == 20 && $row['obtainedMark'] >= 7) || ($row['maxMark'] == 70 && $row['obtainedMark'] >= 22)) {
                                    echo '<p class="pass font-bold">Pass</p>';
                                } else {
                                    echo '<p class="font-bold text-red-500">Fail</p>';
                                }
                                ?>
                            </td>
                            <td class="border-b border-black font-bold text-blue-500">
                                <button id="result_edit_btn" data-result-id="<?= $row['resultId'] ?>">Edit</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>

    </main>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.0.7/js/dataTables.js"></script>
    <script>
        const addResultBtn = document.getElementById('addResultBtn');
        addResultBtn.onclick = () => {
            document.getElementById('addResultDialog').showModal();
        }
        function closeModal() {
            document.getElementById('addResultDialog').close();
            editResultDialog.close();
        }
        document.getElementById('selectSubject').addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const subjectId = selectedOption.value;
            const courseId = selectedOption.getAttribute('data-course');
            const yearId = selectedOption.getAttribute('data-year');
            fetch(`./addFiles/getStudents.php?courseId=${courseId}&yearId=${yearId}`)
                .then(response => {
                    if (response.ok) {
                        return response.text();
                    } else {
                        throw new Error('Network response was not ok.');
                    }
                })
                .then(data => {
                    document.getElementById('studentTable').innerHTML = data;
                })
                .catch(error => {
                    console.error('There was a problem with the fetch operation:', error);
                    document.getElementById('studentTable').innerHTML = '<p class="text-red-500">Failed to load student data.</p>';
                });
        });
        // Fetch and Show Result Details in Edit Dialog
        const result_edit_btn = document.querySelectorAll('#result_edit_btn');
        result_edit_btn.forEach(btn => {
            btn.onclick = () => {
                const resultId = btn.getAttribute('data-result-id');
                editResultDialog.showModal();
                const resultData = document.getElementById('resultData');

                const xhr = new XMLHttpRequest();
                xhr.open('GET', `./addFiles/getResultDetails.php?resultId=${resultId}`, true);
                xhr.onload = function () {
                    if (this.status === 200) {
                        try {
                            const data = JSON.parse(this.responseText);
                            if (!data.error) {
                                document.getElementById('editSubject').value = data.subjectName;
                                document.getElementById('resultId').value = data.resultId;
                                document.getElementById('editExamName').value = data.examName;
                                document.getElementById('editExamDate').value = data.examDate;
                                document.getElementById('monthYear').value = data.examMonth + " - " + data.examYear;
                                document.getElementById('editMaxMark').value = data.maxMark;
                                document.getElementById('editObtainedMark').value = data.obtainedMark;
                            } else {
                                console.error(data.error);
                            }
                        } catch (e) {
                            console.error('Failed to parse JSON response');
                        }
                    } else {
                        console.error('Network response was not ok.');
                    }
                };
                xhr.onerror = function () {
                    console.error('There was a problem with the fetch operation.');
                };
                xhr.send();
            };
        });
        $(document).ready(function () {
            setTimeout(() => {
                $(".msg").fadeOut('slow', function () {
                    $(this).addClass('hidden');
                });
            }, 2000);
        });

        const examDateInput = document.getElementById('examDate');
        const examMonthInput = document.getElementById('examMonth');
        const examYearInput = document.getElementById('examYear');

        examDateInput.addEventListener('change', function () {
            const selectedDate = new Date(examDateInput.value);
            const month = selectedDate.toLocaleString('default', { month: 'long' });
            const year = selectedDate.getFullYear();

            examMonthInput.value = month;
            examYearInput.value = year;
        });

    </script>
    <script>
        let table = new DataTable('#myTable');
    </script>
</body>

</html>
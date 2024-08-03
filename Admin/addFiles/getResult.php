<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
    header('Location: ../../LandingPage/index.php');
}
include ('../../dbconnection/dbconn.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result</title>
    <script src="https://code.jquery.com/jquery-3.7.1.js"
        integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 18px;
            text-align: left;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        table thead th {
            background-color: #007bff;
            color: white;
            padding: 10px;
        }

        table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        table tbody td {
            padding: 10px;
        }

        dialog {
            border: none;
            padding: 20px;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            width: 50%;
        }

        .modal-content {
            background-color: #fefefe;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
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

        @media screen and (max-width: 600px) {
            dialog {
                width: 90%;
            }

            .modal-content {
                padding: 10px;
            }

            .modal-header,
            .modal-body {
                padding: 10px;
            }

            .modal-title {
                font-size: 1.2rem;
            }
        }
    </style>
</head>

<body>
    <div class="">


        <?php
        if (
            isset($_POST['courseId']) &&
            isset($_POST['courseYearId']) &&
            isset($_POST['examNameId']) &&
            isset($_POST['year'])
        ) {
            // Sanitize and validate inputs
            $courseId = intval($_POST['courseId']);
            $courseYearId = intval($_POST['courseYearId']);
            $examNameId = intval($_POST['examNameId']);
            $year = intval($_POST['year']);
            $month = isset($_POST['month']) ? intval($_POST['month']) : null;

            // Query to get subjects
            $sqlSubjects = "SELECT s.id AS subjectId, s.subjectName 
                        FROM subjects s
                        JOIN coursebranches b ON b.courseId = s.branch
                        WHERE b.courseId=? AND s.year=?";
            $stmtSubjects = $conn->prepare($sqlSubjects);
            $stmtSubjects->bind_param("ii", $courseId, $courseYearId);
            $stmtSubjects->execute();
            $subjectResult = $stmtSubjects->get_result();

            $subjects = [];
            if ($subjectResult->num_rows > 0) {
                while ($subjectRow = $subjectResult->fetch_assoc()) {
                    $subjects[$subjectRow['subjectId']] = $subjectRow['subjectName'];
                }
            }
            $stmtSubjects->close();

            // Prepare query based on examNameId condition
            if ($examNameId     == 1 && !empty($month)) {
                // Query for Unit Test with month and year
                $sql = "SELECT CONCAT(s.firstName, ' ', s.lastName) AS studentName, s.id AS studentId, s.rollNo, r.subjectId, r.obtainedMark, r.maxmark, r.examDate
                   FROM results r
                   JOIN students s ON s.id = r.studentId
                   WHERE s.courseId=? AND s.courseYear=? AND r.examNameId=? AND r.examMonth=? AND r.examYear=?
                    order by studentName";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iiisi", $courseId, $courseYearId, $examNameId, $month, $year);
            } else {
                // Query for other exams with only year
                $sql = "SELECT CONCAT(s.firstName, ' ', s.lastName) AS studentName, s.id AS studentId, s.rollNo, r.subjectId, r.obtainedMark, r.maxmark, r.examDate
                   FROM results r
                   JOIN students s ON s.id = r.studentId
                   WHERE s.courseId=? AND s.courseYear=? AND r.examNameId=? AND r.examYear=?
                   order by studentName";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iiii", $courseId, $courseYearId, $examNameId, $year);
            }

            // Execute query
            if ($stmt->execute()) {
                $result = $stmt->get_result();

                // Initialize an array to hold student data
                $students = [];

                // Process the query results
                while ($row = $result->fetch_assoc()) {
                    $studentId = $row['studentId'];
                    if (!isset($students[$studentId])) {
                        $students[$studentId] = [
                            'name' => $row['studentName'],
                            'rollNo' => $row['rollNo'],
                            'subjects' => []
                        ];
                    }
                    $students[$studentId]['subjects'][$row['subjectId']] = [
                        'obtainedMark' => $row['obtainedMark'],
                        'maxmark' => $row['maxmark']
                    ];
                }
                $i = 1;

                // Output results as HTML
                if (count($students) > 0) {
                    echo "<table id='myTable' class='display'>";
                    echo '<div class="float-right p-2">

            <label for="" class="float-right">Search:</label><br>
            <input class="border-2 rounded p-2" type="text" id="searchOption">
        </div><br>';
                    echo "<thead><tr><th>S.No.</th><th>ID</th><th>Roll No</th><th>Name</th>";

                    foreach ($subjects as $subjectName) {
                        echo "<th>" . htmlspecialchars($subjectName) . "</th>";
                    }

                    echo "<th>Percentage %</th><th>Details</th></tr></thead><tbody>";

                    foreach ($students as $studentId => $studentData) {
                        $totalMarks = 0;
                        $totalObtained = 0;
                        echo "<tr>";
                        echo "<td>" . $i++ . "</td>";
                        echo "<td>" . htmlspecialchars($studentId) . "</td>";
                        echo "<td>" . htmlspecialchars($studentData['rollNo']) . "</td>";
                        echo "<td>" . htmlspecialchars($studentData['name']) . "</td>";

                        foreach ($subjects as $subjectId => $subjectName) {
                            if (isset($studentData['subjects'][$subjectId])) {
                                $obtainedMark = $studentData['subjects'][$subjectId]['obtainedMark'];
                                $maxmark = $studentData['subjects'][$subjectId]['maxmark'];
                                echo "<td>" . htmlspecialchars($obtainedMark) . "</td>";
                                $totalMarks += $maxmark;
                                $totalObtained += $obtainedMark;
                            } else {
                                echo "<td>N/A</td>";
                            }
                        }

                        $percentage = $totalMarks > 0 ? ($totalObtained / $totalMarks) * 100 : 0;
                        echo "<td>" . htmlspecialchars(number_format($percentage, 2)) . "%</td>";
                        echo '<td><button class="show-details bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
         data-student="' . htmlspecialchars(json_encode($studentData)) . '"
         data-maxmark="' . htmlspecialchars($totalMarks) . '">Show</button></td>';

                        echo "</tr>";
                    }

                    echo "</tbody></table>";
                } else {
                    echo "No results found.";
                }

                $stmt->close();
            } else {
                echo "Error executing query: " . $stmt->error;
            }

            $conn->close();
        } else {
            // Handle case where required POST parameters are not set
            echo "Error: Required parameters not set.";
        }
        ?>
    </div>
    <!-- The Modal -->
    <dialog id="ShowDetailsDialog" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close">&times;</span>
                <h5 class="modal-title">Student Result Details</h5>
            </div>
            <div id="studentInfo" class="modal-body"></div>
            <div id="studentDetails" class="modal-body"></div>
        </div>
    </dialog>

    <script>
        $(document).ready(function () {

            var modal = document.getElementById("ShowDetailsDialog");
            var span = document.getElementsByClassName("close")[0];
            var subjects = <?php echo json_encode($subjects); ?>;

            $(".show-details").on("click", function () {
                var studentData = JSON.parse($(this).attr("data-student"));
                var maxMark = $(this).attr("data-maxmark");

                var studentInfoHtml = '<p>Name: ' + studentData.name + '</p>';
                studentInfoHtml += '<p>Roll No: ' + studentData.rollNo + '</p>';
                studentInfoHtml += '<p>Total Max Mark: ' + maxMark + '</p>';
                document.getElementById('studentInfo').innerHTML = studentInfoHtml;

                var studentDetailsHtml = '<table><thead><tr><th>Subject</th><th>Obtained Mark</th><th>Max Mark</th><th>Status</th></tr></thead><tbody>';
                var totalObtained = 0;
                var totalMax = 0;
                var failCount = 0;
                var passed = true;

                for (var subjectId in studentData.subjects) {
                    var obtainedMark = studentData.subjects[subjectId].obtainedMark;
                    var maxmark = studentData.subjects[subjectId].maxmark;
                    var status = "Fail";
                    if (maxmark === 100) {
                        status = obtainedMark >= 33 ? "Pass" : "Fail";
                    } else if (maxmark === 75) {
                        status = obtainedMark >= 25 ? "Pass" : "Fail";
                    } else if (maxmark === 20) {
                        status = obtainedMark >= 7 ? "Pass" : "Fail";
                    } else {
                        status = obtainedMark >= (maxmark / 3) ? "Pass" : "Fail";
                    }

                    if (status === "Fail") {
                        failCount++;
                        passed = false;
                    }

                    studentDetailsHtml += '<tr>';
                    studentDetailsHtml += '<td>' + subjects[subjectId] + '</td>';
                    studentDetailsHtml += '<td>' + obtainedMark + '</td>';
                    studentDetailsHtml += '<td>' + maxmark + '</td>';
                    studentDetailsHtml += '<td>' + status + '</td>';
                    studentDetailsHtml += '</tr>';

                    totalObtained += obtainedMark;
                    totalMax += maxmark;
                }

                var percentage = (totalObtained / totalMax) * 100;
                studentDetailsHtml += '</tbody></table>';
                studentDetailsHtml += '<p>Total Percentage: ' + percentage.toFixed(2) + '%</p>';
                studentDetailsHtml += '<p>Overall Result: ' + (passed ? 'Pass' : 'Fail') + '</p>';
                studentDetailsHtml += '<p>Failed Subjects: ' + failCount + '</p>';

                document.getElementById('studentDetails').innerHTML = studentDetailsHtml;

                modal.showModal();
            });

            span.onclick = function () {
                modal.close();
            }

            window.onclick = function (event) {
                if (event.target == modal) {
                    modal.close();
                }
            }

            // Search functionality
            $('#searchOption').keyup(function () {
                var searchText = $(this).val().toLowerCase();

                $('#myTable tbody tr').each(function () {
                    var found = false;
                    $(this).find('td').each(function () {
                        if ($(this).text().toLowerCase().indexOf(searchText) >= 0) {
                            found = true;
                            return false; // Break out of the loop
                        }
                    });
                    if (found) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
        });
    </script>
</body>

</html>
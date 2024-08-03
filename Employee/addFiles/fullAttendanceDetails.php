<?php
session_start();
include ('../../dbconnection/dbconn.php');
$empId = $_SESSION['employee_id'];


$query = "SELECT 
    s.rollNo,
        a.student_id,
        a.subject_id,
        cs.courseName as courseName,
        y.courseYear,
        a.month,
        a.year ,
        a.date,
        sub.subjectName AS subjectName,
        CONCAT(s.firstName, ' ', s.lastName) AS studentName,
        COUNT(a.att_id) AS total_lectures,
        SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) AS present_count,
        SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) AS absent_count,
        CONCAT(ROUND((SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) / COUNT(a.att_id)) * 100, 2), ' %') AS attendance_percentage
    FROM
        attendance a
    JOIN
        students s ON s.id = a.student_id
    JOIN
        subjects sub ON sub.id = a.subject_id
    JOIN
        courseYears y ON y.id = s.courseYear
    JOIN
        courses cs ON cs.id = a.course_id
    WHERE a.emp_id = ?
    ";


$query .= " GROUP BY courseYear, a.subject_id, studentName";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $empId);

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Full Attendance Details</title>
    <link rel="stylesheet" href="../../src/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://code.jquery.com/jquery-3.7.1.js"
        integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.2/css/dataTables.dataTables.css" />
    <style>
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
        }

        header {
            position: fixed;
            top: 0;
            right: 0;
            width: 100%;
            height: 10vh;
            background-color: #c3c3c3;
            color: rgb(16, 41, 110);
            display: flex;
            align-items: center;
            justify-content: start;
            gap: 30px;
            padding: 10px;
            box-shadow: 2px 2px 2px #000;
            z-index: 10;
        }

        .arrow {
            transform: rotate(180deg);
        }
    </style>
</head>

<body>
    <header>
        <a href="../attendance.php" class="bg-blue-500 hover:bg-blue-700 px-3 font-bold text-white text-2xl">
            <i class="fa-solid fa-right-to-bracket arrow"></i> <button>Back</button>
        </a>

    </header>
    <main class="main" id="main">
        <table class="w-full mt-4" id="myTable">
            <thead>
                <tr class="border-black">
                    <th class="border-2 p-4 text-left">Roll No</th>
                    <th class="border-2 p-4 text-left">Name</th>
                    <th class="border-2 p-4">Course</th>
                    <th class="border-2 p-4">CourseYear</th>
                    <th class="border-2 p-4">Subject</th>
                    <th class="border-2 p-4">Month</th>
                    <th class="border-2 p-4">Year</th>
                    <th class="border-2 p-4">Total Attendance</th>
                    <th class="border-2 p-4">Present</th>
                    <th class="border-2 p-4">Absent</th>
                    <th class="border-2 p-4">Attendance Percentage</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="border">
                            <td class="border p-4"><?= htmlspecialchars($row["rollNo"]); ?></td>
                            <td class="border p-4"><?= htmlspecialchars($row["studentName"]); ?></td>
                            <td class="border text-center"><?= htmlspecialchars($row["courseName"]); ?></td>
                            <td class="border text-center"><?= htmlspecialchars($row["courseYear"]); ?></td>
                            <td class="border text-center"><?= htmlspecialchars($row["subjectName"]); ?></td>
                            <td class="border text-center"><?= htmlspecialchars($row["month"]); ?></td>
                            <td class="border text-center"><?= htmlspecialchars($row["year"]); ?></td>

                            <td class="border text-center"><?= htmlspecialchars($row["total_lectures"]); ?></td>
                            <td class="border text-center"><?= htmlspecialchars($row["present_count"]); ?></td>
                            <td class="border text-center"><?= htmlspecialchars($row["absent_count"]); ?></td>
                            <td class="border text-center"><?= htmlspecialchars($row["attendance_percentage"]); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>

    </main>


    <script src="https://cdn.datatables.net/2.0.7/js/dataTables.js"></script>
    <script>
        let table = new DataTable('#myTable');
    </script>
</body>

</html>
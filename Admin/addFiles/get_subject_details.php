<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
    header('Location: ../../LandingPage/index.php');
}
include ('../../dbconnection/dbconn.php');

// Initialize variables to avoid undefined variable warnings
$subId = $subjectCode = $subjectName = $branchName = $courseName = $year = $courseId = "";

if (isset($_GET['subjectId'])) {
    $subId = $_GET['subjectId'];

    $sql = "SELECT s.id as subId, s.subjectCode, s.subjectName, b.branchName, c.courseName, y.courseYear ,c.id as courseId
            FROM subjects s 
            JOIN courseBranches b ON b.id = s.branch
            JOIN courses c ON c.id = b.courseId
            JOIN courseYears y On y.id = s.year  
            WHERE s.id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $subId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $subjectId, $subjectCode, $subjectName, $branchName, $courseName, $year, $courseId);
    mysqli_stmt_fetch($stmt);

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Subject</title>
</head>

<body>
    <div>
        <input type="hidden" value="<?= htmlspecialchars($subId) ?>" name="updateSubjects" id="">


        <label for="branch" class="font-bold">Select Bourse Branch:</label>
        <select name="branch" id="branch"
            class="border w-full px-3 py-2 rounded-md mt-1 focus:outline-none focus:ring focus:border-blue-300">
            <option value="" hidden selected>Select Branch</option>
            <?php
            $sql = "SELECT b.id, b.branchName, c.courseName FROM courseBranches b JOIN courses c ON b.courseId = c.id";
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $selected = ($branchName == $row['branchName']) ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($row['id']) . "' " . $selected . ">" . htmlspecialchars($row['courseName']) . " (" . htmlspecialchars($row['branchName']) . ")</option>";
                }
            }
            ?>
        </select>

        <label for="" class="font-bold">Year:</label>
        <select name="yearForSubjects" id=""
            class="border w-full px-3 py-2 rounded-md mt-1 focus:outline-none focus:ring focus:border-blue-300">
            <option value="" disabled hidden selected>Select Year</option>
            <?php
            $sql = "SELECT * FROM courseYears";
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $selected = ($year == $row['courseYear']) ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($row['id']) . "' " . $selected . ">" . htmlspecialchars($row['courseYear']) . "</option>";
                }
            }
            ?>
        </select>
    </div>
    <div id="subjectFields" class="flex flex-col gap-2">
        <div class="subjectField flex justify-between">
            <div class="">
                <label for="subjectCode" class="font-bold">Subject Code:</label>
                <input type="text" name="subjectCode" placeholder="Enter Subject Code"
                    value="<?= htmlspecialchars($subjectCode) ?>"
                    class="border w-full px-3 py-2 rounded-md mt-1 focus:outline-none focus:ring focus:border-blue-300">
            </div>
            <div class="">
                <label for="subjectName" class="font-bold">Subject Name:</label>
                <input type="text" name="subjectName" placeholder="Enter Subject Name"
                    value="<?= htmlspecialchars($subjectName) ?>"
                    class="border w-full px-3 py-2 rounded-md mt-1 focus:outline-none focus:ring focus:border-blue-300">
            </div>
        </div>
    </div><br><br>
    <!-- Your JavaScript code can go here -->
</body>

</html>
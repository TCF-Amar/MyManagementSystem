<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
    header('Location: ../../LandingPage/index.php');
}
include ('../../dbconnection/dbconn.php');

if ($_GET['courseId']) {
    $courseId = $_GET['courseId'];
    $SQL = "SELECT * FROM courses Where id = $courseId";
    $result = $conn->query($SQL);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $courseName = $row['courseName'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <label for="" class="font-bold"><?= $courseName ?> Branches</label> <input type="hidden" name="addBranch"
        value="<?= $courseId ?>">
    <p><strong>Note:</strong>If a course does not have any <i class="font-bold">branches</i> then add that course to its branches. </p>
</body>

</html>
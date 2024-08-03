<?php
// fetch_branches.php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
    header('Location: ../../LandingPage/index.php');
}
include ('../../dbconnection/dbconn.php');

if (isset($_GET['courseId'])) {
    $courseId = $_GET['courseId'];
    $query = "SELECT * FROM courseBranches WHERE courseId = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $courseId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $branches = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $branches[] = $row;
    }
    echo json_encode($branches);
}
?>
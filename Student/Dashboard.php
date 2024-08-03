<?php
session_start();
if (!isset($_SESSION['student']) || $_SESSION['student'] !== true) {
    header('Location: ../LandingPage/index.php');
    exit(); // Ensure the script stops executing after the redirect
}

include ('../dbconnection/dbconn.php');

$studentId = $_SESSION['student_id'];

$sql = "SELECT * FROM students WHERE id= ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        echo $student['firstName'];
    } else {
        echo "No student found with ID: $studentId";
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    echo "Failed to prepare the SQL statement.";
}
?>
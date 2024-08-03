<?php
// Include your database connection file
include ('../../dbconnection/dbconn.php');
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
    header('Location: ../../LandingPage/index.php');
}
// Initialize variables
$studentName = '';
$studentDetailsHtml = '';

if (isset($_GET['studentId'])) {
    // Sanitize the input to prevent SQL injection
    $studentId = mysqli_real_escape_string($conn, $_GET['studentId']);

    // Query to fetch student details based on ID
    $sql = "SELECT s.*, cs.courseName AS courseName, cy.courseYear AS courseYear 
            FROM students s 
            JOIN courses cs ON cs.id = s.courseId
            JOIN courseYears cy ON cy.id = s.courseYear 
            WHERE s.id = $studentId";

    $result = mysqli_query($conn, $sql);

    // Check if query was successful
    if ($result) {
        // Check if student with the provided ID exists
        if (mysqli_num_rows($result) > 0) {
            // Fetch student details
            $student = mysqli_fetch_assoc($result);
            // $studentName = htmlspecialchars($student['firstName'] . ' ' . $student['lastName']);
            $studentDetailsHtml = "
                <h5><strong>Name: </strong>" . htmlspecialchars($student['firstName'] . ' ' . $student['lastName']) . "</h5>
                <p><strong>Father's Name:</strong> " . htmlspecialchars($student['fatherName'] . ' ' . $student['lastName']) . "</p>
                <p><strong>Roll No: </strong>" . htmlspecialchars($student['rollNo']) . "</p>
                <p><strong>Enrollment No: </strong>" . htmlspecialchars($student['enrollNo']) . "</p>
                <p><strong>Course: </strong>" . htmlspecialchars($student['courseName']) . "</p>
                <p><strong>Year: </strong>" . htmlspecialchars($student['courseYear']) . "</p>
                <p><strong>Contact No: </strong>" . htmlspecialchars($student['contact']) . "</p>
                <p><strong>Email: </strong>" . htmlspecialchars($student['email']) . "</p>
                <p><strong>Gender: </strong>" . htmlspecialchars($student['gender']) . "</p>
                <p><strong>Date of Birth: </strong>" . htmlspecialchars($student['dob']) . "</p>
                <p><strong>Address: </strong>" . htmlspecialchars($student['address']) . "</p>
            ";
        } else {
            $studentDetailsHtml = "<p>No student found with ID: " . htmlspecialchars($studentId) . "</p>";
        }
    } else {
        $studentDetailsHtml = "<p>Error fetching student details.</p>";
    }
} else {
    $studentDetailsHtml = "<p>No student ID provided.</p>";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Details</title>
</head>

<body>
    <?= $studentDetailsHtml ?>
</body>

</html>
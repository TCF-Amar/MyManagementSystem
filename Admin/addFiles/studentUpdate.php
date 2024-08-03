<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
    header('Location: ../../LandingPage/index.php');
}
include ('../../dbconnection/dbconn.php');


if (isset($_GET['studentId'])) {
    $studentId = intval($_GET['studentId']);

    // Fetch student details
    $sql = "SELECT * FROM students WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();

    // Fetch courses
    $coursesSql = "SELECT * FROM courses";
    $coursesResult = $conn->query($coursesSql);
    $courses = [];
    while ($courseRow = $coursesResult->fetch_assoc()) {
        $courses[] = $courseRow;
    }

    // Fetch course years
    $yearsSql = "SELECT * FROM courseYears";
    $yearsResult = $conn->query($yearsSql);
    $years = [];
    while ($yearRow = $yearsResult->fetch_assoc()) {
        $years[] = $yearRow;
    }

    echo json_encode([
        'student' => $student,
        'courses' => $courses,
        'years' => $years
    ]);
}

?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $studentId = intval($_POST['studentId']);
        $newRollNo = $_POST['rollNo'];
        $enrollNo = $_POST['enrollNo'];
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];
        $fatherName = $_POST['fatherName'];
        $contactNo = $_POST['contactNo'];
        $emailId = $_POST['emailId'];
        $gender = $_POST['gender'];
        $dob = $_POST['dob'];
        $address = $_POST['address'];
        $courseId = $_POST['course'];
        $yearId = $_POST['year'];


        // Update student details
        $sql = "UPDATE students SET
                rollNo = ?,
                enrollNo = ?,
                firstName = ?,
                lastName = ?,
                fatherName = ?,
                contact = ?,
                email = ?,
                gender = ?,
                dob = ?,
                address = ?,
                courseId = ?,
                courseYear = ?
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }

        $stmt->bind_param(
            "ssssssssssiii",
            $newRollNo,
            $enrollNo,
            $firstName,
            $lastName,
            $fatherName,
            $contactNo,
            $emailId,
            $gender,
            $dob,
            $address,
            $courseId,
            $yearId,
            $studentId
        );

        if (!$stmt->execute()) {
            throw new Exception("Failed to execute statement: " . $stmt->error);
        }

        echo "
<script>alert('Student details updated successfully!'); window.location.href = '../student.php';</script>";

    } catch (Exception $e) {
        echo "
<script>alert('Error updating student details: " . addslashes($e->getMessage()) . "'); window.location.href = 'studentUpdateForm.php?studentId=$studentId';</script>
";
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        $conn->close();
    }
}
?>
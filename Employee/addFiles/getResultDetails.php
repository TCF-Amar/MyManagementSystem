<?php
session_start();

if (!isset($_SESSION['employee_id'])) {
    header('Location: login.php');
    exit();
}

include ('../../dbconnection/dbconn.php');

if (isset($_GET['resultId'])) {
    $resultId = $_GET['resultId'];

    $sql = "SELECT r.id AS resultId, s.subjectName, en.examName, r.examDate,r.examMonth,r.examYear, r.maxMark, r.obtainedMark
            FROM results r
            JOIN subjects s ON s.id = r.subjectId
            JOIN examnames en ON en.id = r.examNameId
            WHERE r.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $resultId);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'No data found']);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>
<?php
session_start();
include ('../../dbconnection/dbconn.php');

$courseId = isset($_GET['courseId']) ? intval($_GET['courseId']) : 0;
$yearId = isset($_GET['yearId']) ? intval($_GET['yearId']) : 0;


if ($courseId && $yearId) {
    $sql = "SELECT s.id, s.subjectName 
            FROM courses c 
            JOIN coursebranches b ON b.courseId = c.id
            JOIN subjects s ON s.branch = b.id
            WHERE c.id = ? AND s.year = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $courseId, $yearId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {

        while ($row = $result->fetch_assoc()) {
            echo "<input type='checkbox'  name='subjects[]' id='" . htmlspecialchars($row['subjectName']) . "' value='" . htmlspecialchars($row['id']) . "'><label for='" . htmlspecialchars($row['subjectName']) . "'>" . htmlspecialchars($row['subjectName']) . "</label><br>";
        }
    } else {
        echo "No subjects found";

    }
} else {
    echo "No subjects found";
}
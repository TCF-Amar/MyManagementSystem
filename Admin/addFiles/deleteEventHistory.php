<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
    header('Location: ../../LandingPage/index.php');
}
include ('../../dbconnection/dbconn.php');

function deleteHistory($conn)
{
    date_default_timezone_set('Asia/Kolkata');

    $currentDate = strtotime(date('Y-m-d'));
    $currentTime = strtotime(date('H:i:s'));

    $sql = "SELECT id, date, endTime, image FROM events";
    $result = $conn->query($sql);

    $deletedCount = 0;
    $errorCount = 0;

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $eventDate = strtotime($row['date']);
            $eventEndTime = strtotime($row['endTime']);

            if ($eventDate < $currentDate || ($eventDate === $currentDate && $eventEndTime < $currentTime)) {
                $eventId = $row['id'];
                $imagePath = $row['image'];

                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }

                $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
                $stmt->bind_param("i", $eventId);
                if ($stmt->execute()) {
                    $deletedCount++;
                } else {
                    $errorCount++;
                }
                $stmt->close();
            }
        }
        $message = $deletedCount > 0 ? "Successfully deleted $deletedCount events." : "No events deleted.";
        $message .= $errorCount > 0 ? " $errorCount errors occurred during deletion." : "";
    } else {
        $message = "No events found.";
    }

    return $message;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo deleteHistory($conn);
}
?>
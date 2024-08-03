<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
    header('Location: ../../LandingPage/index.php');
}
include ('../../dbconnection/dbconn.php');

// Query to fetch events from the database
$query = "SELECT * FROM events ORDER BY date ASC";
$result = mysqli_query($conn, $query);

$events = [];
while ($row = mysqli_fetch_assoc($result)) {
    $event = [
        'name' => $row['title'],
        'date' => $row['date'],
        'startTime' => $row['startTime'],
        'endTime' => $row['endTime'],
        'description' => $row['description'],
        'status' => getStatus($row['date'], $row['startTime'], $row['endTime']),
        'images'=>$row['image'],
    ];

    $events[] = $event;
}

// Function to determine event status (upcoming, live, past)
function getStatus($eventDate, $startTimeEvent, $endTimeEvent)
{
    date_default_timezone_set('Asia/Kolkata'); // Set timezone to Asia/Kolkata
    $eventDateTime = strtotime($eventDate); // Convert event date to timestamp
    $startTimeEventTime = strtotime($startTimeEvent); // Convert event start time to timestamp
    $endTimeEventTime = strtotime($endTimeEvent); // Convert event end time to timestamp
    $currentDate = strtotime(date('Y-m-d')); // Get current date and convert to timestamp
    $currentTime = strtotime(date('H:i:s')); // Get current time and convert to timestamp

    // Determine event status
    if ($eventDateTime == $currentDate && $startTimeEventTime <= $currentTime && $endTimeEventTime >= $currentTime) {
        return 'Live';
    } elseif ($eventDateTime > $currentDate || ($eventDateTime == $currentDate && $startTimeEventTime > $currentTime)) {
        return 'Upcoming';
    } elseif ($eventDateTime < $currentDate || ($eventDateTime == $currentDate && $endTimeEventTime < $currentTime)) {
        return 'Past';
    }
}

header('Content-Type: application/json');
echo json_encode($events);
?>
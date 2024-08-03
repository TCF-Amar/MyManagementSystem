<?php
session_start();
include ('../../dbconnection/dbconn.php');

$course = $_GET['course'];
$year = $_GET['year'];

$sql = "SELECT * FROM students WHERE courseId = '$course' AND courseYear = '$year' ORDER BY firstName, lastName ASC";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    echo '<table class="w-full">';
    echo '<thead><tr class="border-2 border-black"><th>#</th><th>Name</th><th class"text-center">Attendance</th></tr></thead>';
    echo '<tbody>';
    $i = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<tr class="border-2 text-center">';
        echo '<td>' . $i . '</td>';
        echo '<td>' . $row['firstName'] . ' ' . $row['lastName'] . '</td>';
        echo '<td class="border-2 flex justify-center">';
        echo '<label class="Attendance">';
        echo '<input type="hidden" name="studentId[]" value="' . $row['id'] . '">';
        echo '<input id="' . $row['rollNo'] . '" type="checkbox" value="' . $row['id'] . '" name="status[]" ';
        echo isset($_POST['status']) && in_array($row['id'], $_POST['status']) ? 'checked' : '';
        echo '>';
        echo '</label>';
        echo '</td>';
        echo '</tr>';
        $i++;
    }
    echo '</tbody>';
    echo '</table>';
} else {
    echo "<div class='bg-red-500 flex justify-center font-bold text-white'>No students found for this course and year.</div>";
}

mysqli_close($conn);
?>
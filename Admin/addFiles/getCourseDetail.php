<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
    header('Location: ../../LandingPage/index.php');
}
include ('../../dbconnection/dbconn.php');

if ($_GET['courseId']) {
    $courseId = $_GET['courseId'];
    // echo $courseId;
    $sql = "SELECT * FROM courses WHERE id = $courseId";

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $courseName = $row['courseName'];
        $department = $row['department'];
        $duration = $row['courseDuration'];
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Update</title>
</head>

<body>
    <div class="">
        <input type="hidden" name="courseUpdate">
        <input type="hidden" name="courseId" value="<?= $courseId ?>">

        <label for="courseName" class="font-bold">Course Name:</label>
        <input type="text" name="updateCourseName" id="courseName" value="<?= htmlspecialchars($courseName) ?>"
            class="border w-full px-3 py-2 rounded-md mt-1 focus:outline-none focus:ring focus:border-blue-300"
            placeholder="Enter course name.." required autofocus>

        <label for="department" class="block mt-4">Department:</label>
        <select name="updateDepartment" id="department"
            class="border w-full px-3 py-2 rounded-md mt-1 focus:outline-none focus:ring focus:border-blue-300">
            <option value="VIMS" <?= ($department === "VIMS") ? 'selected' : "" ?>>VIMS</option>
            <option value="VITS" <?= ($department === "VITS") ? 'selected' : "" ?>>VITS</option>
            <option value="VIMR" <?= ($department === "VIMR") ? 'selected' : "" ?>>VIMR</option>
        </select>

        <label for="duration" class="font-bold block mt-4">Duration (in Years):</label>
        <select name="updateDuration" id="courseDuration" required
            class="border w-full px-3 py-2 rounded-md mt-1 focus:outline-none focus:ring focus:border-blue-300">
            <option value="1 Year" <?= ($duration === "1 Year") ? 'selected' : "" ?>>1 Year</option>
            <option value="2 Year's" <?= ($duration === "2 Year's") ? 'selected' : "" ?>>2 Year's</option>
            <option value="3 Year's" <?= ($duration === "3 Year's") ? 'selected' : "" ?>>3 Year's</option>
            <option value="4 Year's" <?= ($duration === "4 Year's") ? 'selected' : "" ?>>4 Year's</option>
            <option value="5 Year's" <?= ($duration === "5 Year's") ? 'selected' : "" ?>>5 Year's</option>
        </select>

        <button type="submit"
            class="bg-blue-500 text-white px-4 py-2 mt-4 w-full rounded-md hover:bg-blue-600 focus:outline-none focus:bg-blue-600">
            Update
        </button>
    </div>
</body>

</html>
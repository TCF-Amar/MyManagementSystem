<?php
session_start();
include ('../../dbconnection/dbconn.php');

// Check if the attendance ID is provided in the query string
if (isset($_GET['attId'])) {
    // Retrieve the attendance ID from the query string
    $attId = $_GET['attId'];


    $sql = "SELECT * FROM attendance a
    JOIN students s ON s.id = a.student_id
    JOIN subjects sub On sub.id = subject_id
    JOIN courses cs On cs.ID = a.course_id
    WHERE att_id = '$attId'";
    $result = mysqli_query($conn, $sql);
    // Check if the attendance ID exists in the database
    if (mysqli_num_rows($result) > 0) {
        // If the attendance ID exists, retrieve the attendance data
        $row = mysqli_fetch_assoc($result);
        $rollNo = $row['rollNo'];
        $name = $row['firstName'] . ' ' . $row['lastName'];
        $lecture = $row['lecture'];
        $date = $row['date'];
        $subject = $row['subjectName'];
        $status = $row['status'];
        $course = $row['courseName'];
        $year = $row['year'];




    }


    // Encode the data as JSON and output it
} else {
    // If attendance ID is not provided, return an error message
    echo json_encode(array('error' => 'Attendance ID not provided'));
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>attUpdate</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
        }

        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: auto;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .form-group span {
            display: block;
            padding: 8px;
            background-color: #e0e0e0;
            border-radius: 4px;
        }

        .form-group input[type="radio"] {
            margin-right: 10px;
        }

        .form-group button {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        .form-group button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>


    <label for="" class="font-bold flex justify-center">Attendance Status Update: <?= $name ?></label>
    <div>
        <div>
            <label for="rollNo" class="font-bold">Roll No.:</label>
            <span id="rollNo"><?= $rollNo ?></span>
        </div>

    </div>
    <div>
        <div>
            <label for="date" class="font-bold">Date: </label>
            <span id="date"><?= $date ?></span>
        </div>
        <div>
            <label for="course" class="font-bold">Course: </label>
            <span id="course"><?= $course ?></span>
        </div>
        <div>
            <label for="year" class="font-bold">Year: </label>
            <span id="year"><?= $year ?></span>
        </div>
        <div>
            <label for="subject" class="font-bold">Subject: </label>
            <span id="subject"><?= $subject ?></span>
        </div>
        <div>
            <label for="lecture" class="font-bold">Lecture:</label>
            <span id="lecture"><?= $lecture ?></span>
        </div>
    </div>
    <input type="hidden" name="updateAttendance">
    <input type="hidden" name="AttendanceId" value="<?php echo $attId ?>">
    <input type="hidden" name="student_name" value="<?php $name ?>">
    <div>
        <label for="attStatus" class="font-bold">Attendance Status:</label><br>
        <input type="radio" id="presentStatus" name="attStatus" <?= isset($status) && $status == 'present' ? 'checked' : ""; ?> value="present">
        <label for="presentStatus">Present</label>
        <input type="radio" id="absentStatus" name="attStatus" <?= isset($status) && $status == 'absent' ? 'checked' : ""; ?> value="absent">
        <label for="absentStatus">Absent</label>
    </div>
    <button type="submit"
        class="bg-blue-500 w-full rounded-lg mt-4 font-bold text-white hover:bg-blue-700">Submit</button>


</body>

</html>
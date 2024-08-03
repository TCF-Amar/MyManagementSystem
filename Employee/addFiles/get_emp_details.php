<?php
include ('../../dbconnection/dbconn.php');

$employeeDetailsHtml = '';

if (isset($_GET['empId'])) {
    $empId = $_GET['empId'];

    function allotSubject($empId)
    {
        global $conn;
        $subjectNames = [];
        $sql = "SELECT s.subjectName FROM allotsubjects a 
                JOIN subjects s On s.id = a.subjectId 
                WHERE a.empId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $empId);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $subjectNames[] = $row['subjectName'];
        }

        return implode(', ', $subjectNames);
    }

    $sql = "SELECT e.firstName, e.lastName, e.gender, er.role, e.contact, e.email, e.address 
            FROM employee e 
            JOIN empRole er On e.role = er.id 
            WHERE e.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $empId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $employeeDetailsHtml = "
            <h5><strong>Name: </strong>" . htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) . "</h5>
            <p><strong>Contact No: </strong>" . htmlspecialchars($row['contact']) . "</p>
            <p><strong>Email: </strong>" . htmlspecialchars($row['email']) . "</p>
            <p><strong>Gender: </strong>" . htmlspecialchars($row['gender']) . "</p>
            <p><strong>Address: </strong>" . htmlspecialchars($row['address']) . "</p>
            <p><strong>Role: </strong>" . htmlspecialchars($row['role']) . "</p>
            <p><strong>Allot Subjects: </strong>" . htmlspecialchars(allotSubject($empId)) . "</p>
        ";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Details</title>
</head>

<body>
    <?= $employeeDetailsHtml ?>
</body>

</html>
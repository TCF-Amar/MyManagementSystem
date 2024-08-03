<?php
include ('../../dbconnection/dbconn.php');

$courseId = isset($_GET['courseId']) ? intval($_GET['courseId']) : 0;
$yearId = isset($_GET['yearId']) ? intval($_GET['yearId']) : 0;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get Students</title>
</head>

<body>
    <div class="container mx-auto p-4">
        <table class="w-full bg-white shadow-md rounded my-6">
            <thead class="bg-gray-200">
                <tr>
                    <th class="py-2  border-b">S.NO.</th>
                    <th class="py-2  border-b">Roll NO.</th>
                    <th class="py-2  border-b">Name</th>
                    <th class="py-2  border-b">Marks</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($courseId > 0 && $yearId > 0):
                    $SQL = "SELECT id, rollNo, CONCAT(firstName, ' ', lastName) AS name
                            FROM students
                            WHERE courseId = ? AND courseYear = ?";
                    if ($stmt = $conn->prepare($SQL)):
                        $stmt->bind_param("ii", $courseId, $yearId);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $i = 1;

                        while ($row = $result->fetch_assoc()): ?>
                            <tr class="border-b hover:bg-gray-100">
                                <td class="py-2 px-4 text-center"><?php echo htmlspecialchars($i++) ?></td>
                                <td class="py-2 px-4 text-center"><?php echo htmlspecialchars($row['rollNo']); ?></td>
                                <td class="py-2 px-4 text-center">
                                    <?php echo htmlspecialchars($row['name']); ?>
                                    <input type="hidden" name="students[]" value="<?php echo htmlspecialchars($row['id']); ?>">
                                </td>
                                <td class="py-2 px-4 text-center">
                                    <input class="border-2 border-gray-300 rounded p-1" value="0" name="marks[]" type="number"
                                        min="0" max="100">
                                </td>
                            </tr>
                        <?php endwhile;

                        $stmt->close();
                    else: ?>
                        <tr>
                            <td colspan="4" class="py-2 px-4 text-red-500">Failed to prepare the SQL statement.</td>
                        </tr>
                    <?php endif;
                else: ?>
                    <tr>
                        <td colspan="4" class="py-2 px-4 text-red-500">Invalid course or year ID.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>
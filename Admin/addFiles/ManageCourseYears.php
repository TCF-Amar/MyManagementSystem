<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
    header('Location: ../../LandingPage/index.php');
    exit();
}
include ('../../dbconnection/dbconn.php');

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    if (isset($_POST['addNewCourseYear'])) {
        $courseYear = mysqli_real_escape_string($conn, $_POST['addNewCourseYear']);

        $checkSql = "SELECT * FROM courseyears WHERE courseYear = '$courseYear'";
        $checkResult = mysqli_query($conn, $checkSql);

        if (mysqli_num_rows($checkResult) > 0) {
            echo "<div class='msg bg-red-500'>Course Year Already Exists</div>";
        } else {
            $sql = "INSERT INTO courseyears (courseYear) VALUES ('$courseYear')";
            $result = mysqli_query($conn, $sql);
            if ($result) {
                echo "<div class='msg bg-green-500'>Course Year Added Successfully</div>";
            } else {
                echo "<div class='msg bg-red-500'>Error Occur</div>";
            }
        }
    }
    if (isset($_POST['EditCourseYear'])) {
        $courseYearId = $_POST['EditCourseYear'];
        $courseYearName = $_POST['courseYear'];
        $sql = "UPDATE courseyears SET courseYear = ? WHERE id=?";
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            echo "<div class='msg bg-red-500'>Error Occur</div>";
        } else {
            mysqli_stmt_bind_param(
                $stmt,
                "ss",
                $courseYearName,
                $courseYearId
            );
            mysqli_stmt_execute($stmt);
            echo "<div class='msg bg-green-500'>Course Year Updated Successfully</div>";
        }



    }
    if (isset($_POST['courseYearDelete'])) {
        $courseYearId = mysqli_real_escape_string($conn, $_POST['courseYearDelete']);
        $sql = "DELETE FROM courseyears WHERE id='$courseYearId'";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            echo "<div class='msg bg-green-500'>Course Year Deleted Successfully</div>";
        } else {
            echo "<div class='msg bg-red-500'>Error Occur</div>";
        }
    }

}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Course Years</title>
    <link rel="stylesheet" href="../../src/output.css">
    <link rel="stylesheet" href="./css/main.css">
    <link rel="stylesheet" href="./css/msg.css">
    <script src="../../src/jQuery.js"></script>
</head>

<body>
    <header>
        <nav>
            <button class="font-bold text-2xl" id="courseYearManageClose">Close</button> |
            <button class="font-bold" id="addCourseYearFormBtn">Add More Years</button> |
        </nav>
    </header>
    <main class="main" id="main">
        <div class="container mx-auto p-4">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200 divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Course Year</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        $query = "SELECT * FROM courseyears";
                        $result = mysqli_query($conn, $query);
                        if (mysqli_num_rows($result) > 0):
                            while ($row = mysqli_fetch_assoc($result)):
                                ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($row['courseYear']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 flex gap-2 items-baseline">
                                        <button class="text-blue-500 hover:text-blue-700 font-bold editCourseYearBtn"
                                            data-courseYear="<?php echo htmlspecialchars($row['courseYear']) ?>"
                                            data-courseYearId="<?php echo htmlspecialchars($row['id']) ?>"
                                            id="editCourseYearBtn">Edit</button>

                                        <form action="" method="post">
                                            <input type="hidden" value="<?php echo $row['id']; ?>" name="courseYearDelete">
                                            <button type="submit"
                                                class="text-red-600 hover:text-red-900 font-bold courseYearDeleteBtn"
                                                id="courseYearDeleteBtn">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php
                            endwhile;
                        endif;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <dialog id="addMoreCourseYearForm">
        <div class="modal-header">
            <span class="modal-title">Add More Course Year</span>
            <button class="close" id="closeAddMoreCourseYearForm">&times;</button>
        </div>
        <div class="modal-body">
            <form action="" method="post" class="flex flex-col " id="newCorseAddYearForm">
                <input type="text" name="addNewCourseYear" class="border-2 rounded border-black "
                    placeholder='Enter Course Year If Course Year Not Added (EX. "1st Year")' required><br>
                <button class="bg-blue-500 text-white font-bold m-2 p-2 rounded hover:bg-blue-700" type="submit"
                    name="addNewCourseYearBtn">Add</button>
            </form>
        </div>
    </dialog>
    <dialog id="CourseYearEditForm">
        <div class="modal-header">
            <span class="modal-title">Edit Course Year</span>
            <button class="close" id="closeEditCourseYearForm">&times;</button>
        </div>
        <div class="modal-body">
            <form action="" method="post" class="flex flex-col " id="editCourseYearForm">
                <input type="hidden" name="EditCourseYear" id="courseYearId">
                <input type="text" name="courseYear" id="courseYear" class="border-2 rounded border-black "
                    placeholder='Enter Course Year' required><br>
                <button class="bg-blue-500 text-white font-bold m-2 p-2 rounded hover:bg-blue-700" type="submit"
                    name="editCourseYearBtn">Save</button>
            </form>
        </div>
    </dialog>

    <script src="./js/script.js"></script>

    <script>
        $(document).ready(function () {
            $("#courseYearManageClose").click(() => {
                window.location.href = '../moreFeatures.php';
            });

            $("#addCourseYearFormBtn").click(function () {
                $("#addMoreCourseYearForm").show();
            });

            $("#closeAddMoreCourseYearForm").click(function () {
                $("#addMoreCourseYearForm").hide();
            });

            $(".courseYearDeleteBtn").click(function () {
                return confirm("Are you sure you want to delete this course year?");
            });

            $(".editCourseYearBtn").click(function () {
                let courseYear = $(this).data('courseyear');
                let courseYearId = $(this).data('courseyearid');

                $("#courseYear").val(courseYear);
                $("#courseYearId").val(courseYearId);
                $("#CourseYearEditForm").show();
            });

            $("#closeEditCourseYearForm").click(function () {
                $("#CourseYearEditForm").hide();
            });
        });
    </script>
</body>

</html>
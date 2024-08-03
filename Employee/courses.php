<?php
session_start();
$empId = $_SESSION['employee_id'];

include ('../dbconnection/dbconn.php');


$AccessArray[] = array();

$getAccessSqlQuery = "SELECT accesses FROM accesses WHERE emp_id = $empId";

$result = $conn->query($getAccessSqlQuery);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $accesses = explode(',', $row['accesses']);

        $AccessArray = array_merge($AccessArray, $accesses);
    }
}



// Add new course
function addNewCourse($conn)
{
    $courseName = $_POST['courseName'];
    $department = $_POST['department'];
    $duration = $_POST['duration'];

    if (empty($courseName) || empty($department) || empty($duration)) {
        echo '<script>alert("All fields are required!");</script>';
        return;
    }

    $selectQuery = "SELECT * FROM courses WHERE courseName = ?";
    $stmt = mysqli_prepare($conn, $selectQuery);
    mysqli_stmt_bind_param($stmt, "s", $courseName);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        echo '<script>alert("Course already exists!");</script>';
    } else {
        $insertQuery = "INSERT INTO courses (courseName, Department, courseDuration) VALUES (?, ?, ?)";
        $insertStmt = mysqli_prepare($conn, $insertQuery);
        mysqli_stmt_bind_param($insertStmt, "sss", $courseName, $department, $duration);
        $success = mysqli_stmt_execute($insertStmt);

        echo $success ? '<script>alert("Course added successfully!");</script>' : '<script>alert("Error adding course!");</script>';
        mysqli_stmt_close($insertStmt);
    }
    mysqli_stmt_close($stmt);
}

// Update course
function updateCourse($conn)
{
    $updateCourseName = $_POST['updateCourseName'];
    $updateDepartment = $_POST['updateDepartment'];
    $updateDuration = $_POST['updateDuration'];
    $updateId = $_POST['courseId'];

    if (empty($updateDuration) || empty($updateCourseName) || empty($updateDepartment)) {
        echo '<script>alert("All fields are required!");</script>';
        return;
    }

    $courseUpdateQuery = "UPDATE courses SET courseName=?, department=?, courseDuration=? WHERE id=?";
    $updateStmt = mysqli_prepare($conn, $courseUpdateQuery);
    mysqli_stmt_bind_param($updateStmt, 'sssi', $updateCourseName, $updateDepartment, $updateDuration, $updateId);
    $success = mysqli_stmt_execute($updateStmt);

    echo $success ? '<script>alert("Course updated successfully!");</script>' : '<script>alert("Error updating course!");</script>';
    mysqli_stmt_close($updateStmt);
}

// Delete course
function deleteCourse($conn)
{
    $courseId = $_POST['deleteCourse'];
    $deleteQuery = "DELETE FROM courses WHERE id = ?";
    $stmt = mysqli_prepare($conn, $deleteQuery);
    mysqli_stmt_bind_param($stmt, "i", $courseId);
    $success = mysqli_stmt_execute($stmt);

    echo $success ? '<script>alert("Course deleted successfully!");</script>' : '<script>alert("Error deleting course!");</script>';
    mysqli_stmt_close($stmt);
}

// Delete subject
function deleteSubject($conn)
{
    $subjectID = $_POST['deleteSubject'];
    $deleteQuery = "DELETE FROM subjects WHERE id = ?";
    $stmt = mysqli_prepare($conn, $deleteQuery);
    mysqli_stmt_bind_param($stmt, "i", $subjectID);
    $success = mysqli_stmt_execute($stmt);

    echo $success ? '<script>alert("Subject deleted successfully!");</script>' : '<script>alert("Error deleting subject!");</script>';

    mysqli_stmt_close($stmt);
}

// Add branch
function addBranch($conn)
{
    $addBranchCourseId = $_POST['addBranch'];
    $branchNames = $_POST['branchName'];

    $courseQuery = "SELECT courseName FROM courses WHERE id = ?";
    $stmt = mysqli_prepare($conn, $courseQuery);
    mysqli_stmt_bind_param($stmt, "i", $addBranchCourseId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $courseName);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    $successMessages = [];
    $errorMessages = [];

    foreach ($branchNames as $branchName) {
        $checkQuery = "SELECT * FROM courseBranches WHERE courseId = ? AND branchName = ?";
        $stmt = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($stmt, "is", $addBranchCourseId, $branchName);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errorMessages[] = "$courseName '$branchName' already exists!";
        } else {
            $addBranchQuery = "INSERT INTO courseBranches (courseId, branchName) VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $addBranchQuery);
            mysqli_stmt_bind_param($stmt, "is", $addBranchCourseId, $branchName);
            $success = mysqli_stmt_execute($stmt);

            if ($success) {
                $successMessages[] = "$courseName '$branchName' added successfully!";
            } else {
                $errorMessages[] = "Error adding $courseName '$branchName'!";
            }
            mysqli_stmt_close($stmt);
        }
    }

    if (!empty($successMessages)) {
        echo '<script>alert("' . implode('\n', $successMessages) . '");</script>';
    }

    if (!empty($errorMessages)) {
        echo '<script>alert("' . implode('\n', $errorMessages) . '");</script>';
    }
}

// Add new subject
function addNewSubject($conn)
{
    $branchId = $_POST['branch'];
    $yearId = $_POST['yearForSubjects'];
    $subjectCodes = $_POST['subjectCode'];
    $subjectNames = $_POST['subjectName'];

    $success = true;
    $errorMessage = '';

    try {
        foreach ($subjectCodes as $key => $subjectCode) {
            $subjectName = $subjectNames[$key];
            $insertQuery = "INSERT INTO subjects (subjectName, subjectCode, branch, year) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insertQuery);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssii", $subjectName, $subjectCode, $branchId, $yearId);

                if (!mysqli_stmt_execute($stmt)) {
                    $success = false;
                    $errorMessage = 'Error executing statement: ' . mysqli_stmt_error($stmt);
                    throw new Exception($errorMessage);
                }

                mysqli_stmt_close($stmt);
            } else {
                $success = false;
                $errorMessage = 'Error preparing statement: ' . mysqli_error($conn);
                throw new Exception($errorMessage);
            }
        }

        if ($success) {
            echo '<script>alert("Subjects added successfully!");</script>';
        }
    } catch (Exception $e) {
        echo '<script>alert("Duplicate Entry ' . $subjectCode . ' (' . $subjectName . ')");</script>';
    }
}

function updateSubjects($conn)
{
    // Sanitize input to prevent SQL injection
    $subjectId = mysqli_real_escape_string($conn, $_POST['updateSubjects']);
    $branchId = mysqli_real_escape_string($conn, $_POST['branch']);
    $yearId = mysqli_real_escape_string($conn, $_POST['yearForSubjects']);
    $subjectCode = mysqli_real_escape_string($conn, $_POST['subjectCode']);
    $subjectName = mysqli_real_escape_string($conn, $_POST['subjectName']);

    // Update query with placeholders for security and readability
    $SQL = "UPDATE subjects SET 
             subjectName = ?,
             subjectCode = ?,
             branch = ?,
             year = ?
             WHERE id = ?";

    // Prepare and bind parameters
    $stmt = mysqli_prepare($conn, $SQL);
    mysqli_stmt_bind_param($stmt, 'ssiii', $subjectName, $subjectCode, $branchId, $yearId, $subjectId);

    // Execute the statement
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        echo '<script>alert("Subject updated successfully!");</script>';
    } else {
        echo '<script>alert("Subject not updated!");</script>';
    }

    // Close statement
    mysqli_stmt_close($stmt);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['addNewCourse'])) {
        addNewCourse($conn);
    }
    if (isset($_POST['courseUpdate'])) {
        updateCourse($conn);
    }
    if (isset($_POST['deleteCourse'])) {
        deleteCourse($conn);
    }
    if (isset($_POST['deleteSubject'])) {
        if (isset($_POST['confirmed']) && $_POST['confirmed'] === 'true') {
            deleteSubject($conn);
        } else {
            // User cancelled the deletion
            // You can redirect or display a message here
        }
    }
    if (isset($_POST['addBranch'])) {
        addBranch($conn);
    }
    if (isset($_POST['addNewSubject'])) {
        addNewSubject($conn);
    }
    if (isset($_POST['updateSubjects'])) {
        updateSubjects($conn);
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="./addFiles/css/main.css">
    <script src="https://code.jquery.com/jquery-3.7.1.js"
        integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.2/css/dataTables.dataTables.css" />

    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0vh;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 7% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
            z-index: 999;
            border-radius: 5px;
        }


        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        dialog {
            padding: 20px;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            width: 50%;
        }

        dialog::-webkit-scrollbar {
            display: none;
        }

        .modal-header {
            background-color: #f0f0f0;
            border-bottom: 2px solid #ccc;
            padding: 1rem;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }

        @media screen and (max-width:760px) {
            .modal-content {
                margin: 25% auto;
                width: 90%;
            }
        }
    </style>

</head>

<body>
    <?php include ('./addFiles/header.php');
    ?>
    <main class="main" id="main">

        <nav class=" fixed top left-0 w-full bg-gray-600 font-bold text-white px-2" style="top:10vh ; z-index: 
        9999">
            <button id="courseAddBtn" type="button"
                class=" hover:text-blue-600  <?php echo (in_array('Add Course', $AccessArray) ? '' : 'hidden') ?>">Add
                Courses</button>
           
            <button id="addSubjectBtn"
                class=" hover:text-blue-600 <?php echo (in_array('Add Subjects', $AccessArray) ? '' : 'hidden') ?>">Add
                Subjects</button>
        </nav>

        <!-- <div class=" bg-red-500 absolute  w-full p-4 left-0 font-bold text-white">Error </div> -->



        <!-- add course -->
        <div id="courseAddModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <span class="flex justify-center text-3xl font-bold">Add New Course</span><br>

                <form action="" method="post" id="courseForm" class="">
                    <input type="hidden" name="addNewCourse">
                    <label for="courseName" class="font-bold">Course Name:</label>
                    <input type="text" name="courseName" id="addCourseName"
                        class="border w-full px-3 py-2 rounded-md mt-1 focus:outline-none focus:ring focus:border-blue-300"
                        placeholder="Enter course name.. " required autofocus>

                    <label for="department" class="block mt-4">AOP:</label>
                    <select name="department" id="department"
                        class="border w-full px-3 py-2 rounded-md mt-1 focus:outline-none focus:ring focus:border-blue-300">
                        <option value="VIMS" selected>VIMS</option>
                        <option value="VITS">VITS</option>
                        <option value="VIMR">VIMR</option>
                    </select>

                    <label for="duration" class="font-bold block mt-4">Duration(in Years):</label>
                    <select name="duration" id="courseDuration" required
                        class="border w-full px-3 py-2 rounded-md mt-1 focus:outline-none focus:ring focus:border-blue-300">
                        <option value="" selected disabled hidden>Select Duration</option>
                        <option value="1 Year">1 Year</option>
                        <option value="2 Year's">2 Year's</option>
                        <option value="3 Year's">3 Year's</option>
                        <option value="4 Year's">4 Year's</option>
                        <option value="5 Year's">5 Year's</option>
                    </select>

                    <button type="submit"
                        class="bg-blue-500 text-white px-4 py-2 mt-4 w-full rounded-md hover:bg-blue-600 focus:outline-none focus:bg-blue-600">Add
                        Course</button>
                </form>
            </div>
        </div>
        <!-- update course -->

        <div id="courseUpdateModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <span class="flex justify-center text-3xl font-bold">Update Course</span><br>


                <form action="" method="post" id="courseUpdateFormData">

                </form>
            </div>
        </div>

        <!-- Add Branches -->
        <div id="branchAddModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <span class="flex justify-center text-3xl font-bold">Add Branches</span><br>

                <form action="" method="post" id="branchAddFormData">

                    <div id="addBranchCourse"></div>

                    <div id="newBranchInputField">
                    </div>
                    <div id="branchInputFields" class="mt-4">
                        <input type="text" value="" name="branchName[]" placeholder="Enter Branch Name"
                            class="border w-full px-3 py-2 rounded-md mt-1 focus:outline-none focus:ring focus:border-blue-300">
                        <!-- Dynamic input fields will be added here -->
                    </div>
                    <button type="button"
                        class="btn  float-right px-6 py-1 rounded bg-blue-500 hover:bg-blue-700 m-2 text-white flex justify-center items-center gap-2"
                        onclick="addBranchInput()"><Span>Add Another Brach</Span><i
                            class="fa-solid fa-plus"></i></button>

                    <div class="">

                        <button
                            class="bg-blue-500 text-white px-4 py- mt-4 w-full rounded-md hover:bg-blue-600 focus:outline-none focus:bg-blue-600">Add
                            Branches</button>
                    </div>

                </form>
            </div>
        </div>


        <!-- Add Subjects -->
        <div id="subjectAddModal" class="modal   ">
            <div class="modal-content">
                <span class="close">&times;</span>
                <span class="flex justify-center text-3xl font-bold">Add Subjects</span><br>

                <form action="" method="post">
                    <input type="hidden" name="addNewSubject" id="">
                    <div>

                        <label for="courseForBranch" class="font-bold">Select Course:</label>
                        <select name="courseForBranch" id="courseForBranch" onchange="getBranches()"
                            class="border w-full px-3 py-2 rounded-md mt-1 focus:outline-none focus:ring focus:border-blue-300">
                            <option value="" disabled hidden selected>Select Course</option>
                            <?php
                            $sql = "SELECT * FROM courses";
                            $result = $conn->query($sql);
                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<option value='" . $row['id'] . "'>" . $row['courseName'] . "</option>";
                                }
                            }
                            ?>
                        </select>

                        <label for="branch" class="font-bold">Select Branch:</label>
                        <select name="branch" id="branch"
                            class="border w-full px-3 py-2 rounded-md mt-1 focus:outline-none focus:ring focus:border-blue-300">
                            <option value="" hidden selected>Select Branch</option>
                        </select>

                        <label for="" class="font-bold">Year:</label>
                        <select name="yearForSubjects" id=""
                            class="border w-full px-3 py-2 rounded-md mt-1 focus:outline-none focus:ring focus:border-blue-300">
                            <option value="" disabled hidden selected>Select Year</option>
                            <?php
                            $sql = "SELECT * FROM courseYears";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='" . $row['id'] . "'>" . $row['courseYear'] . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div id="subjectFields" class="flex flex-col gap-2">
                        <div class="subjectField flex justify-between">
                            <div class="">

                                <label for="subjectName" class="font-bold">Subject Code:</label>
                                <input type="text" name="subjectCode[]" placeholder="Enter Subject Code"
                                    class="border w-full px-3 py-2 rounded-md mt-1 focus:outline-none focus:ring focus:border-blue-300">
                            </div>
                            <div class="">
                                <label for="subjectName" class="font-bold">Subject Name:</label>
                                <input type="text" name="subjectName[]" placeholder="Enter Subject Name"
                                    class="border w-full px-3 py-2 rounded-md mt-1 focus:outline-none focus:ring focus:border-blue-300">
                            </div>


                        </div>
                    </div><br>
                    <div class=" w-full flex justify-center">

                        <button type="button" class="   bg-green-600 px-4 font-bold text-white rounded"
                            onclick="addSubjectField()">Add Another Subject</button>
                    </div>
                    <br>

                    <button class=" w-full bg-blue-500 py-2 px-4 font-bold text-white hover:bg-blue-700 rounded">Add New
                        Subjects</button>

                </form>

            </div>
        </div>

        <dialog class="dialog" id="subjectUpdateModal">
            <div class="modal-header">
                <button class="close" onclick="modalClose()">&times;</button>
                <h5 class="modal-title" id="">Update Subjects</h5>
            </div>

            <form id="" method="post">
                <div class="" id="subjectUpdateFormData"></div>
                <button class=" w-full bg-blue-500 py-2 px-4 font-bold text-white hover:bg-blue-700 rounded">Change
                    Save</button>
            </form>

        </dialog>
        <!-- Courses Table -->
        <div class="courseTable mt-3 pb-8">
            <h2 class="text-2xl font-bold text-center underline">Available Courses</h2><br>
            <table class="courseTables border-2 border-black border-collapse table-fixed w-full">
                <thead>
                    <tr class="">
                        <th class="border border-black p-4 overflow-hidden text-nowrap text-ellipsis hover:text-clip">
                            Courses Name
                        </th>
                        <th class="border border-black overflow-hidden text-nowrap text-ellipsis hover:text-clip">AOP
                        </th>
                        <th class="border border-black overflow-hidden text-nowrap text-ellipsis hover:text-clip">
                            Duration</th>
                        <th
                            class="border border-black overflow-hidden text-nowrap text-ellipsis hover:text-clip <?php echo (in_array('Add Course Branches', $AccessArray) ? '' : 'hidden'); ?>">
                            Add Branches
                        </th>
                        <th
                            class="border border-black overflow-hidden text-nowrap text-ellipsis hover:text-clip <?php echo (in_array('Update Courses', $AccessArray) || in_array('Remove Courses', $AccessArray) ? '' : 'hidden'); ?>">
                            Action
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM courses";
                    $result = $conn->query($sql);
                    $issUpdateCourseAccess = in_array('Update Courses', $AccessArray) ? '' : 'hidden';
                    $issDeleteCourseAccess = in_array('Remove Courses', $AccessArray) ? '' : 'hidden';

                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td class='border hover:text-clip courseName px-4 text-center overflow-hidden text-nowrap text-ellipsis font-bold'>{$row['courseName']}</td>";
                            echo "<td class='border hover:text-clip courseDepartment px-4 text-center overflow-hidden text-nowrap text-ellipsis font-bold'>{$row['department']}</td>";
                            echo "<td class='border hover:text-clip courseDuration px-4 text-center overflow-hidden text-nowrap text-ellipsis font-bold'>{$row['courseDuration']}</td>";

                            // Conditionally show the Add Branches column
                            if (in_array('Add Course Branches', $AccessArray)) {
                                echo '<td class="text-center p-2 border">
                                <button id="branchAddBtn" data-courseId="' . $row['id'] . '" class="flex justify-center items-center gap-2 w-full">
                                <i class="fa fa-plus"></i><span>Add</span></button>
                              </td>';
                            } else {
                                echo '<td class="hidden"></td>';
                            }

                            // Conditionally show the Action column
                            if (in_array('Update Courses', $AccessArray) || in_array('Remove Courses', $AccessArray)) {
                                echo '<td class="border hover:text-clip flex justify-center items-baseline gap-4 font-bold">';
                                if (in_array('Update Courses', $AccessArray)) {
                                    echo '<div>
                                    <button id="" data-courseId="' . $row['id'] . '" class="courseUpdateBtn text-blue-500 hover:text-blue-700">
                                        <i class="fa-solid fa-pen"></i><span class="hidden md:inline-block">Update</span>
                                    </button>
                                  </div>';
                                }
                                if (in_array('Remove Courses', $AccessArray)) {
                                    echo '<div>
                                    <form method="post" onsubmit="return confirmDeleteCourse(\'' . $row['courseName'] . '\')" class="">
                                        <input type="hidden" name="deleteCourse" value="' . $row['id'] . '">
                                        <button type="submit" class="text-red-500 hover:text-red-700">
                                            <i class="fa-solid fa-trash-can"></i><span class="hidden md:inline-block">Delete</span>
                                        </button>
                                    </form>
                                  </div>';
                                }
                                echo '</td>';
                            } else {
                                echo '<td class="hidden"></td>';
                            }

                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No records found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            <br>
            <hr>
        </div>





        <!-- subject table -->
        <div class="">
            <h1 class="text-2xl font-bold text-center underline">Subjects</h1><br>
            <table class="table table-fixed w-full border-collapse border-2 border-black" id="myTable">
                <thead>
                    <tr>
                        <th class="border border-black text-ellipsis text-nowrap overflow-hidden py-4">Subject Code</th>
                        <th class="border border-black text-ellipsis text-nowrap overflow-hidden py-4">Subject Name</th>
                        <th class="border border-black text-ellipsis text-nowrap overflow-hidden py-4">Course Name</th>
                        <th class="border border-black text-ellipsis text-nowrap overflow-hidden py-4">Year</th>

                        <?php
                        if (in_array('Update Subjects', $AccessArray) || in_array('Remove Subjects', $AccessArray)) {
                            echo ' <th
                            class="border border-black text-ellipsis text-nowrap overflow-hidden py-4 ">
                            Action
                        </th>';
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $SQL = "SELECT s.id as subjectId,
                        s.subjectCode, 
                        s.subjectName, 
                        c.courseName, 
                        b.branchName,
                        y.courseYear
                    FROM subjects s
                    JOIN courseBranches b ON s.branch = b.id 
                    JOIN courses c ON c.id = b.courseId
                    JOIN courseYears y ON y.id = s.year"; // Assuming y.subjectId is the foreign key linking courseYear to subjects
                    $isUpdateSubject = in_array('Update Subjects', $AccessArray) ? '' : 'hidden';
                    $isRemoveSubject = in_array('Remove Subjects', $AccessArray) ? '' : 'hidden';

                    $result = mysqli_query($conn, $SQL);
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td class='border border-black hover:text-clip courseName px-4 text-center overflow-hidden text-nowrap text-ellipsis font-bold'>{$row['subjectCode']}</td>";
                            echo "<td class='border border-black hover:text-clip courseName px-4 text-center overflow-hidden text-nowrap text-ellipsis font-bold'>{$row['subjectName']}</td>";
                            echo "<td class='border border-black hover:text-clip courseName px-4 text-center overflow-hidden text-nowrap text-ellipsis font-bold'>{$row['courseName']} ({$row['branchName']})</td>";
                            echo "<td class='border border-black hover:text-clip courseName px-4 text-center overflow-hidden text-nowrap text-ellipsis font-bold'>{$row['courseYear']}</td>";

                            if (in_array('Update Subjects', $AccessArray) || in_array('Remove Subjects', $AccessArray)) {
                                echo "<td class='border hover:text-clip flex justify-center items-baseline gap-4 font-bold '>";

                                if (in_array('Update Subjects', $AccessArray)) {
                                    echo "<div>
                                <button class='subjectEditBtn text-blue-500' data-subjectId='{$row['subjectId']}'><i class='fa fa-pen'></i><span class='hidden md:inline-block'> Edit</span></button>
                              </div>";
                                }

                                if (in_array('Remove Subjects', $AccessArray)) {
                                    echo "<div>
                                <form action='' method='post' class='delete-subject-form'>
                                    <input type='hidden' name='deleteSubject' value='" . htmlspecialchars($row['subjectId']) . "'>
                                    <input type='hidden' name='confirmed' id='confirmation-input' value='false'>
                                    <button class='text-red-500' onclick='confirmDeleteSubject(event);'><i class='fa-regular fa-trash-can'></i><span class='hidden md:inline-block'>Delete</span></button>
                                </form>
                              </div>";
                                }

                                echo "</td>";
                            }
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No records found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>


    </main>


    <script>
        // Function to send AJAX requests
        function sendAjaxRequest(method, url, callback) {
            const xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        callback(null, xhr.responseText);
                    } else {
                        callback(`Error: ${xhr.status}`);
                    }
                }
            };
            xhr.open(method, url, true);
            xhr.send();
        }

        // Get elements
        const courseAddForm = document.getElementById("courseAddModal");
        const courseAddBtn = document.getElementById("courseAddBtn");
        const closeButtons = document.querySelectorAll(".close");

        // Show course add form
        courseAddBtn.addEventListener('click', () => {
            courseAddForm.style.display = "block";
        });

        // Close modals
        function closeModal(modal) {
            modal.style.display = "none";
        }

        closeButtons.forEach(button => {
            button.addEventListener('click', () => {
                closeModal(button.closest('.modal'));
            });
        });

        // Course update modal handling
        const courseUpdateModal = document.getElementById("courseUpdateModal");
        const courseUpdateButtons = document.querySelectorAll(".courseUpdateBtn");

        courseUpdateButtons.forEach(button => {
            button.addEventListener('click', () => {
                courseUpdateModal.style.display = "block";
                const courseId = button.getAttribute('data-courseId');
                sendAjaxRequest('GET', `./addFiles/getCourseDetail.php?courseId=${courseId}`, (err, response) => {
                    if (err) {
                        console.error(err);
                    } else {
                        document.getElementById('courseUpdateFormData').innerHTML = response;
                    }
                });
            });
        });

        // Branch add modal handling
        const branchAddModal = document.getElementById("branchAddModal");
        const branchAddButtons = document.querySelectorAll("#branchAddBtn");

        branchAddButtons.forEach(button => {
            button.addEventListener('click', () => {
                branchAddModal.style.display = "block";
                const courseId = button.getAttribute('data-courseId');
                sendAjaxRequest('GET', `./addFiles/getCourseForAddBranch.php?courseId=${courseId}`, (err, response) => {
                    if (err) {
                        console.error(err);
                    } else {
                        document.getElementById('addBranchCourse').innerHTML = response;
                    }
                });
            });
        });

        // Subject add modal handling
        const addSubjectModal = document.getElementById("subjectAddModal");
        const addSubjectBtn = document.getElementById("addSubjectBtn");

        addSubjectBtn.addEventListener('click', () => {
            addSubjectModal.style.display = "block";
        });

        // Confirm delete course
        function confirmDeleteCourse(courseName) {
            return confirm(`Are you sure you want to remove ${courseName}?`);
        }

        function confirmDeleteSubject(event) {
            event.preventDefault(); // Prevent default form submission
            const confirmed = confirm("Are you sure you want to delete this subject?");
            if (confirmed) {
                event.target.closest('form').elements['confirmed'].value = 'true';
                event.target.closest('form').submit(); // Submit the form
            }
        }

        // Add branch input field
        function addBranchInput() {
            const branchInputFields = document.getElementById('branchInputFields');
            const newInput = document.createElement('div');
            newInput.classList.add('mt-2');
            newInput.innerHTML = `
        <input type="text" name="branchName[]" placeholder="Enter Branch Name" class="border w-full px-3 py-2 rounded-md mt-1 focus:outline-none focus:ring focus:border-blue-300">
        <button type="button" class="btn px-6 py-1 rounded bg-red-500 hover:bg-red-700 m-2 text-white" onclick="removeBranchInput(this)">Remove</button>
    `;
            branchInputFields.appendChild(newInput);
        }

        // Remove branch input field
        function removeBranchInput(button) {
            button.parentElement.remove();
        }

        // Get branches for a course
        function getBranches() {
            const courseId = document.getElementById("courseForBranch").value;
            const branchSelect = document.getElementById("branch");

            // Clear existing options
            branchSelect.innerHTML = '';

            if (courseId) {
                sendAjaxRequest('GET', `./addFiles/fetch_branches.php?courseId=${courseId}`, (err, response) => {
                    if (err) {
                        console.error(err);
                    } else {
                        const branches = JSON.parse(response);
                        if (branches.length > 0) {
                            branches.forEach(branch => {
                                const option = document.createElement('option');
                                option.value = branch.id;
                                option.text = branch.branchName;
                                branchSelect.appendChild(option);
                            });
                        } else {
                            const option = document.createElement('option');
                            option.value = "";
                            option.text = 'No branches available';
                            option.selected = true;
                            branchSelect.appendChild(option);
                        }
                    }
                });
            }
        }

        // Add subject input field
        function addSubjectField() {
            const subjectFields = document.getElementById('subjectFields');
            const newField = document.createElement('div');
            newField.classList.add('subjectField', 'flex', 'justify-between', 'items-end');
            newField.innerHTML = `
        <div>
            <label for="subjectCode" class="font-bold">Subject Code:</label>
            <input type="text" name="subjectCode[]" placeholder="Enter Subject Code" class="border w-full px-3 py-2 rounded-md mt-1 focus:outline-none focus:ring focus:border-blue-300">
        </div>
        <div>
            <label for="subjectName" class="font-bold">Subject Name:</label>
            <input type="text" name="subjectName[]" placeholder="Enter Subject Name" class="border w-full px-3 py-2 rounded-md mt-1 focus:outline-none focus:ring focus:border-blue-300">
        </div>
        <button type="button" class="text-red-500 text-2xl font-bold rounded float-right" onclick="removeSubjectField(this)"><i class="fa fa-xmark"></i></button>
    `;
            subjectFields.appendChild(newField);
        }

        // Remove subject input field
        function removeSubjectField(button) {
            button.parentElement.remove();
        }

        // Subject edit modal handling
        const subjectEditBtns = document.querySelectorAll(".subjectEditBtn");

        subjectEditBtns.forEach(btn => {
            btn.addEventListener("click", function () {
                const subjectUpdateModal = document.getElementById("subjectUpdateModal");
                subjectUpdateModal.showModal();
                const subId = btn.getAttribute('data-subjectId');
                sendAjaxRequest('GET', `./addFiles/get_subject_details.php?subjectId=${subId}`, (err, response) => {
                    if (err) {
                        console.log(err);
                    } else {
                        document.getElementById('subjectUpdateFormData').innerHTML = response;
                    }
                });
            });
        });

        // Close subject update modal
        function modalClose() {
            document.getElementById('subjectUpdateModal').close();
        }

    </script>
    <script src="https://cdn.datatables.net/2.0.7/js/dataTables.js"></script>
    <script>
        let table = new DataTable('#myTable');
    </script>

</body>

</html>
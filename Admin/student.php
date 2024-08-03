<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
    header('Location: ../LandingPage/index.php');
    exit();
}
$adminId = $_SESSION['admin_id'];
include ('../dbconnection/dbconn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['registerNewStudent'])) {
        // Sanitize and validate input data
        $rollNo = mysqli_real_escape_string($conn, $_POST['rollNo']);
        $enrollNo = mysqli_real_escape_string($conn, $_POST['enrollNo']);
        $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
        $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
        $fatherName = mysqli_real_escape_string($conn, $_POST['fatherName']);
        $contactNo = mysqli_real_escape_string($conn, $_POST['contactNo']);
        $emailId = mysqli_real_escape_string($conn, $_POST['emailId']);
        $gender = mysqli_real_escape_string($conn, $_POST['gender']);
        $dob = mysqli_real_escape_string($conn, $_POST['dob']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        $course = mysqli_real_escape_string($conn, $_POST['course']);
        $year = mysqli_real_escape_string($conn, $_POST['year']);

        // Validate required fields
        if (empty($rollNo) || empty($enrollNo) || empty($firstName) || empty($lastName) || empty($fatherName) || empty($contactNo) || empty($emailId) || empty($gender) || empty($dob) || empty($address) || empty($course) || empty($year)) {
            echo "<script>alert('All fields are required');</script>";
        } else {
            // Hash the roll number to use as the password

            try {
                // Insert data into the database
                $sql = "INSERT INTO students (rollNo, enrollNo, firstName, lastName, fatherName, contact, email, gender, dob, address, courseId, courseYear) VALUES ('$rollNo', '$enrollNo', '$firstName', '$lastName', '$fatherName', '$contactNo', '$emailId', '$gender', '$dob', '$address', '$course', '$year')";

                if (mysqli_query($conn, $sql)) {
                    echo "<script>alert('Student Registered Successfully');</script>";
                } else {
                    throw new Exception("Error Occurred: " . mysqli_error($conn));
                }
            } catch (Exception $e) {
                echo "<script>alert('" . $e->getMessage() . "');</script>";
            }
        }
    }
    if (isset($_POST['deleteStudent'])) {
        $studentId = intval($_POST['deleteStudent']);

        try {
            // Prepare and execute the delete query
            $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
            if (!$stmt) {
                throw new Exception("Prepare statement failed: " . $conn->error);
            }

            $stmt->bind_param("i", $studentId);

            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            echo "<script>alert('Student has been successfully deleted.');</script>";

            $stmt->close();
        } catch (Exception $e) {
            echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
        }

    }

}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.7.1.js"
        integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.2/css/dataTables.dataTables.css" />
    <title>Students</title>
    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="./addFiles/css/main.css">

    <style>
        .main {
            margin-bottom: 10px;
            height: 90vh;
            position: fixed;
            top: 10vh;
            right: 0;
            width: 100%;
            padding: 20px;
            /* background: #e0e0e0; */
            transition: all 0.2s linear;
            overflow-y: auto;

        }

        .main::-webkit-scrollbar {
            display: none;

        }

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
            margin: 7% auto;
            padding: 20px;
            width: 50%;
            z-index: 999;
            background-color: #fff;
            border: 1px solid rgba(0, 0, 0, 0.2);
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
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



        @media screen and (max-width:760px) {
            .modal-content {
                margin: 25% auto;
                width: 90%;
            }
        }


        /* Modal header */
        .modal-header {
            background-color: #f0f0f0;
            border-bottom: 2px solid #ccc;
            padding: 1rem;
        }

        /* Modal title */
        .modal-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }

        /* Modal body */

        /* Close button hover effect */
        .btn-close:hover {
            color: #333;
        }

        /* Override Bootstrap modal fade effect */
        .modal.fade .modal-dialog {
            transform: none;
        }

        /* Optional: Scrollbar styles */
        .modal-dialog-scrollable {
            max-height: calc(100vh - 100px);
            /* Adjust as needed */
            overflow-y: auto;
        }
    </style>
</head>

<body>
    <?php include ('./addFiles/header.php'); ?>

    <main class="main" id="main">
        <nav class="fixed top left-0 w-full bg-gray-600 font-bold text-white px-2" style="top:10vh; z-index: 9999">
            <button id="registerNewStudentBtn" type="button" class="hover:text-blue-600">Register New Student</button>
        </nav>

        <!-- Add Student Modal -->
        <div id="addStudentModal" class="modal">
            <div class="modal-content">
                <div class="modal-header border-b-2 border-gray-200">
                    <span class="close">&times;</span>
                    <h5 class="modal-title" id="studentDetailsModalLabel">Register New Student</h5>
                </div>
                <br>
                <div class="modal-body">


                    <form action="" method="post">
                        <input type="hidden" name="registerNewStudent">
                        <div class="flex flex-col justify-center">
                            <div class="flex justify-between gap-4 md:flex-nowrap flex-wrap">
                                <div class="w-full">
                                    <label for="rollNo" class="font-bold">Roll No:</label>
                                    <input type="text" name="rollNo" class="border-2 border-gray-400 p-2 w-full"
                                        placeholder="Enter Roll No" required>
                                </div>
                                <div class="w-full">
                                    <label for="enrollNo" class="font-bold">Enrollment No:</label>
                                    <input type="text" name="enrollNo" class="border-2 border-gray-400 p-2 w-full"
                                        placeholder="Enter Enrollment No" required>
                                </div>
                            </div>
                            <div class="flex justify-between gap-4 md:flex-nowrap flex-wrap">
                                <div class="w-full">
                                    <label for="firstName" class="font-bold">First Name:</label>
                                    <input type="text" name="firstName" class="border-2 border-gray-400 p-2 w-full"
                                        placeholder="Enter Student First Name" required>
                                </div>
                                <div class="w-full">
                                    <label for="lastName" class="font-bold">Last Name:</label>
                                    <input type="text" name="lastName" class="border-2 border-gray-400 p-2 w-full"
                                        placeholder="Enter Student Last Name" required>
                                </div>
                            </div>
                            <div class="flex justify-between gap-4 md:flex-nowrap flex-wrap">
                                <div class="w-full">
                                    <label for="fatherName" class="font-bold">Father's Name:</label>
                                    <input type="text" name="fatherName" class="border-2 border-gray-400 p-2 w-full"
                                        placeholder="Enter Father's Name" required>
                                </div>
                                <div class="w-full">
                                    <label for="contactNo" class="font-bold">Contact Number:</label>
                                    <input type="tel" name="contactNo" class="border-2 border-gray-400 p-2 w-full"
                                        placeholder="Enter Contact Number" required>
                                </div>
                            </div>
                            <div class="flex justify-between gap-4 md:flex-nowrap flex-wrap">

                                <div class=" w-full">

                                    <label for="emailId" class="font-bold">Email Address:</label>
                                    <input type="email" name="emailId" class="border-2 border-gray-400 p-2 w-full"
                                        placeholder="Enter Email Address" required>
                                </div>

                                <div class=" w-full flex flex-col">
                                    <label for="gender" class="font-bold">Gender:</label>
                                    <select name="gender" id="" class="border-2 border-gray-400 p-2 w-full">
                                        <option value="" disabled hidden selected required>Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Others">Others</option>

                                    </select>

                                </div>
                            </div><br>
                            <div class="flex justify-between gap-4 md:flex-nowrap flex-wrap">
                                <div class="w-full">
                                    <label for="dob" class="font-bold">Date of Birth:</label>
                                    <input type="date" name="dob" class="border-2 border-gray-400 p-2 w-full"
                                        placeholder="Enter Date of Birth" required>
                                </div>
                                <div class="w-full">
                                    <label for="address" class="font-bold">Address:</label>
                                    <input type="text" class="border-2 border-gray-400 p-2 w-full" name="address"
                                        placeholder="Enter Address" required>
                                </div>
                            </div>
                            <div class="flex justify-between gap-4 md:flex-nowrap flex-wrap">
                                <div class="w-full">
                                    <label for="course" class="font-bold">Course:</label>
                                    <select class="border-2 border-gray-400 p-2 w-full" name="course" id="course"
                                        required>
                                        <option value="" selected hidden disabled>Select Course</option>
                                        <?php
                                        $sql = "SELECT * FROM courses";
                                        $result = $conn->query($sql);
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<option value='" . $row['id'] . "'>" . $row['courseName'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="w-full">
                                    <label for="year" class="font-bold">Year:</label>
                                    <select class="border-2 border-gray-400 p-2 w-full" name="year" id="year" required>
                                        <option value="" selected hidden disabled>Select Year</option>
                                        <?php
                                        $sql = "SELECT * FROM courseYears";
                                        $result = $conn->query($sql);
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<option value='" . $row['id'] . "'>" . $row['courseYear'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <br>
                            <button type="submit"
                                class="border-2 border-gray-400 p-2 w-full bg-blue-500 hover:bg-blue-700 text-white font-bold">Register</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Update Student Modal -->
        <div id="studentUpdateModal" class="modal">
            <div class="modal-content">
                <div class="modal-header border-b-2 border-gray-200">
                    <span class="close">&times;</span>
                    <span class="modal-title">Update Student</span><br>
                </div><br>
                <div id="studentUpdateForm">
                    <!-- The form content will be dynamically loaded here -->
                </div>
            </div>
        </div>
        <!-- Student Table -->
        <div class="overflow-hidden overflow-x-auto mt-6">
            <span class="font-bold text-2xl underline">Student Table</span>

            <table id="myTable" class="border-2 border-collapse table-fixed overflow-hidden overflow-x-auto">
                <thead>
                    <tr class="border-2 border-black">
                        <th
                            class="border-2 border-black overflow-hidden text-ellipsis hover:text-clip hover:overflow-x-auto">
                            Roll No</th>
                        <th
                            class="border-2 border-black overflow-hidden text-ellipsis hover:text-clip hover:overflow-x-auto">
                            Enrollment No</th>
                        <th
                            class="border-2 border-black overflow-hidden text-ellipsis hover:text-clip hover:overflow-x-auto">
                            First Name</th>
                        <th
                            class="border-2 border-black overflow-hidden text-ellipsis hover:text-clip hover:overflow-x-auto">
                            Last Name</th>
                        <th
                            class="border-2 border-black overflow-hidden text-ellipsis hover:text-clip hover:overflow-x-auto">
                            Course</th>
                        <th
                            class="border-2 border-black overflow-hidden text-ellipsis hover:text-clip hover:overflow-x-auto">
                            Year</th>
                        <th
                            class="border-2 border-black overflow-hidden text-ellipsis hover:text-clip hover:overflow-x-auto">
                            Details</th>
                        <th
                            class="border-2 border-black overflow-hidden text-ellipsis hover:text-clip hover:overflow-x-auto">
                            <span class="p-2">Action</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Fetch and display student data from the database -->
                    <?php
                    $sql = "SELECT s.id, s.rollNo, s.enrollNo, s.firstName, s.lastName, c.courseName, cy.courseYear 
        FROM students s 
        JOIN courses c ON s.courseId = c.id 
        JOIN courseYears cy ON cy.id = s.courseYear 
        order by rollNo ASC";
                    $result = $conn->query($sql);

                    while ($row = $result->fetch_assoc()) {
                        echo "<tr class='border-2 border-black'>
            <td class='border border-black overflow-hidden text-ellipsis hover:text-clip hover:overflow-x-auto'>" . $row['rollNo'] . "</td>
            <td class='border border-black overflow-hidden text-ellipsis hover:text-clip hover:overflow-x-auto'>" . $row['enrollNo'] . "</td>
            <td class='border border-black overflow-hidden text-ellipsis hover:text-clip hover:overflow-x-auto'>" . $row['firstName'] . "</td>
            <td class='border border-black overflow-hidden text-ellipsis hover:text-clip hover:overflow-x-auto'>" . $row['lastName'] . "</td>
            <td class='border border-black overflow-hidden text-ellipsis hover:text-clip hover:overflow-x-auto'>" . $row['courseName'] . "</td>
            <td class='border border-black overflow-hidden text-ellipsis hover:text-clip hover:overflow-x-auto'>" . $row['courseYear'] . "</td>
            <td class='border border-black overflow-hidden text-ellipsis hover:text-clip hover:overflow-x-auto'>
                <button class='w-full text-center text-blue-600 font-bold' id='showStudentDetailsBtn' data-studentId = '" . $row['id'] . "'>
                    <i class='fa-solid fa-eye'></i><span>Show</span>
                </button>
            </td>
            <td class='border border-black overflow-hidden text-ellipsis font-bold hover:text-clip hover:overflow-x-auto'>
                <div class='w-full flex justify-center  items-baseline gap-4 md:text-white'>
                 <div class=''>
                 <button class='w-full text-center  text-blue-500 hover:text-blue-700' id='studentUpdateBtn' data-studentId ='" . $row['id'] . "'>
                 <i class='fa fa-pen'></i>
                 <span class='md:inline-block hidden'>Edit</span>
                 </button> 
                 </div>
                 <div class=''>
                 <form method='post' onsubmit='return confirmDelete(\"" . $row['firstName'] . " " . $row['lastName'] . " (" . $row['courseName'] . " " . $row['courseYear'] . ")\")'>
                 <input type='hidden' name='deleteStudent' value='" . $row['id'] . "'>
                 <button type='submit' class='w-full text-center text-nowrap  text-red-500 hover:text-red-700'>
                 <i class='fa-solid fa-trash'></i>
                 <span class='md:inline-block hidden'>Delete</span>
                 </button>
                 </form>
                 </div>
                </div>
            </td>
          </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <!-- Student Details Modal -->
        <div class="modal fade" id="studentDetailsModal" tabindex="-1" aria-labelledby="studentDetailsModalLabel"
            aria-hidden="true">
            <div class="modal-content">
                <div class="modal-header border-b-2 border-gray-200">
                    <span type="button" class="close">&times;</span>
                    <h5 class="modal-title" id="studentDetailsModalLabel">Student Details</h5>
                </div><br>

                <div id="studentDetailsContent">
                </div>
            </div>
        </div>

    </main>

    <script>
        const addStudentModal = document.getElementById("addStudentModal");
        const addStudentBtn = document.getElementById("registerNewStudentBtn");
        const closeAddStudentBtn = document.getElementsByClassName("close")[0];

        addStudentBtn.onclick = function () {
            addStudentModal.style.display = "block";
        }
        closeAddStudentBtn.onclick = function () {
            addStudentModal.style.display = "none";
        }
        function confirmDelete(studentInfo) {
            if (confirm("Are you sure you want to delete, " + studentInfo + "?")) {
                return true;
            } else {
                alert("Removed Canceled!");
                return false;
            }
        }
        const studentUpdateModal = document.getElementById("studentUpdateModal");
        const studentUpdateBtns = document.querySelectorAll("#studentUpdateBtn");
        const closeUpdateStudentBtn = document.getElementsByClassName("close")[1];

        // Add event listeners to all update buttons
        studentUpdateBtns.forEach(function (btn) {
            btn.onclick = function () {
                studentUpdateModal.style.display = "block";

                const studentId = btn.getAttribute('data-studentId');

                const xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        if (xhr.status === 200) {
                            const responseData = JSON.parse(xhr.responseText);
                            const studentData = responseData.student;
                            const courses = responseData.courses;
                            const years = responseData.years;
                            populateUpdateForm(studentData, courses, years);
                        }
                    }
                };
                xhr.open('GET', './addFiles/studentUpdate.php?studentId=' + studentId, true);
                xhr.send();
            };
        });

        function populateUpdateForm(data, courses, years) {
            const formHtml = `<form action="./addFiles/studentUpdate.php" method="post">
        <input type="hidden" name="studentId" value="${data.id}">
        <div class="flex flex-col justify-center">
            <div class="flex justify-between gap-4 md:flex-nowrap flex-wrap">
                <div class="w-full">
                    <label for="rollNo" class="font-bold">Roll No:</label>
                    <input type="text" name="rollNo" value="${data.rollNo}" class="border-2 border-gray-400 p-2 w-full" placeholder="Enter Roll No" required>
                </div>
                <div class="w-full">
                    <label for="enrollNo" class="font-bold">Enrollment No:</label>
                    <input type="text" name="enrollNo" value="${data.enrollNo}" class="border-2 border-gray-400 p-2 w-full" placeholder="Enter Enrollment No" required>
                </div>
            </div>
            <div class="flex justify-between gap-4 md:flex-nowrap flex-wrap">
                <div class="w-full">
                    <label for="firstName" class="font-bold">First Name:</label>
                    <input type="text" name="firstName" value="${data.firstName}" class="border-2 border-gray-400 p-2 w-full" placeholder="Enter Student First Name" required>
                </div>
                <div class="w-full">
                    <label for="lastName" class="font-bold">Last Name:</label>
                    <input type="text" name="lastName" value="${data.lastName}" class="border-2 border-gray-400 p-2 w-full" placeholder="Enter Student Last Name" required>
                </div>
            </div>
            <div class="flex justify-between gap-4 md:flex-nowrap flex-wrap">
                <div class="w-full">
                    <label for="fatherName" class="font-bold">Father's Name:</label>
                    <input type="text" name="fatherName" value="${data.fatherName}" class="border-2 border-gray-400 p-2 w-full" placeholder="Enter Father's Name" required>
                </div>
                <div class="w-full">
                    <label for="contactNo" class="font-bold">Contact Number:</label>
                    <input type="tel" name="contactNo" value="${data.contact}" class="border-2 border-gray-400 p-2 w-full" placeholder="Enter Contact Number" required>
                </div>
            </div>
   <div class="flex justify-between gap-4 md:flex-nowrap flex-wrap">

                            <div class=" w-full">

                                <label for="emailId" class="font-bold">Email Address:</label>
                                <input type="email" name="emailId" value="${data.email}" class="border-2 border-gray-400 p-2 w-full"
                                    placeholder="Enter Email Address" required>
                            </div>

                            <div class=" w-full flex flex-col">
                                <label for="gender" class="font-bold">Gender:</label>
                                <select name="gender" id="" class="border-2 border-gray-400 p-2 w-full">
                                    <option value="" disabled hidden selected>Select Gender</option>
                                    <option value="Male" ${data.gender === 'Male' ? 'selected' : ''}>Male</option>
                                    <option value="Female" ${data.gender === 'Female' ? 'selected' : ''}>Female</option>
                                    <option value="Others" ${data.gender === 'Others' ? 'selected' : ''}>Others</option>

                                </select>

                            </div>
                        </div><br>
            <div class="flex justify-between gap-4 md:flex-nowrap flex-wrap">
                <div class="w-full">
                    <label for="dob" class="font-bold">Date of Birth:</label>
                    <input type="date" name="dob" value="${data.dob}" class="border-2 border-gray-400 p-2 w-full" placeholder="Enter Date of Birth" required>
                </div>
                <div class="w-full">
                    <label for="address" class="font-bold">Address:</label>
                    <input type="text" value="${data.address}" class="border-2 border-gray-400 p-2 w-full" name="address" placeholder="Enter Address" required>
                </div>
            </div>
            <div class="flex justify-between gap-4 md:flex-nowrap flex-wrap">
                <div class="w-full">
                    <label for="course" class="font-bold">Course:</label>
                    <select class="border-2 border-gray-400 p-2 w-full" name="course" id="course" required>
                        <option value="" selected hidden disabled>Select Course</option>
                        ${generateCourseOptions(courses, data.courseId)}
                    </select>
                </div>
                <div class="w-full">
                    <label for="year" class="font-bold">Year:</label>
                    <select class="border-2 border-gray-400 p-2 w-full" name="year" id="year" required>
                        <option value="" selected hidden disabled>Select Year</option>
                        ${generateYearOptions(years, data.courseYear)}
                    </select>
                </div>
            </div>
            <br>
            <button type="submit" class="border-2 border-gray-400 p-2 w-full bg-blue-500 hover:bg-blue-700 text-white font-bold">Update</button>
            <button type="button" id="studentUpdateBtnCancel" class="border-2 border-gray-400 p-2 w-full bg-red-500 hover:bg-red-700 text-white font-bold">Cancel</button>
        </div>
    </form>`;

            document.getElementById("studentUpdateForm").innerHTML = formHtml;

            // Add event listener to the cancel button
            document.getElementById("studentUpdateBtnCancel").onclick = function () {
                studentUpdateModal.style.display = "none";
            };
        }

        // Close the update student modal
        closeUpdateStudentBtn.onclick = function () {
            studentUpdateModal.style.display = "none";
        };

        // Optional: Close modals when clicking outside of them
        window.onclick = function (event) {

            // if (event.target == addStudentBtn) {
            //     addStudentModal.style.display = "none";
            // }
            if (event.target == studentUpdateModal) {
                studentUpdateModal.style.display = "none";
            }
            if (event.target == studentDetailsModal) {
                studentDetailsModal.style.display = "none";
            }
        };

        // Function to generate options for select elements
        function generateCourseOptions(courses, selectedCourseId) {
            return courses.map(course => `<option value="${course.id}" ${course.id == selectedCourseId ? 'selected' : ''}>${course.courseName}</option>`).join('');
        }
        // Function to generate year options
        function generateYearOptions(years, selectedYearId) {
            return years.map(year => `<option value="${year.id}" ${year.id == selectedYearId ? 'selected' : ''}>${year.courseYear}</option>`).join('');
        }



        const studentDetailsModal = document.getElementById("studentDetailsModal");
        const studentDetailsBtns = document.querySelectorAll("#showStudentDetailsBtn");
        const studentDetailsCloseBtn = document.getElementsByClassName("close")[2]; // Select the close button

        studentDetailsBtns.forEach(function (btn) {
            btn.onclick = function () {
                studentDetailsModal.style.display = "block";
                let studentId = btn.getAttribute("data-studentId");


                const xhr = new XMLHttpRequest();

                xhr.onreadystatechange = function () {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        if (xhr.status === 200) {
                            document.getElementById("studentDetailsContent").innerHTML = xhr.responseText;

                        }
                    }
                };
                xhr.open('GET', './addFiles/et_student_details.php?studentId=' + studentId);
                xhr.send();


            };
        });

        studentDetailsCloseBtn.onclick = function () {
            studentDetailsModal.style.display = "none";
        };

//cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css

    </script>
        <script src="https://cdn.datatables.net/2.0.7/js/dataTables.js"></script>
        <script>
            let table = new DataTable('#myTable');
        </script>
</body>

</html>
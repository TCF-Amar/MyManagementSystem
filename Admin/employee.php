<?php
session_start();



$adminId = $_SESSION['admin_id'];
include ('../dbconnection/dbconn.php');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['registerNewEmp'])) {
        // Process registration form
        try {
            // Collect and sanitize form data
            $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
            $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
            $gender = mysqli_real_escape_string($conn, $_POST['gender']);
            $role = mysqli_real_escape_string($conn, $_POST['role']);
            $address = mysqli_real_escape_string($conn, $_POST['emp_current_address']);
            $contactNumber = mysqli_real_escape_string($conn, $_POST['contactNumber']);
            $email = mysqli_real_escape_string($conn, $_POST['email']);
            $password = mysqli_real_escape_string($conn, $_POST['emp_password']);
            $confirmPassword = mysqli_real_escape_string($conn, $_POST['cnfPass']);

            // Validate inputs
            if ($password !== $confirmPassword) {
                echo "<script>alert('Passwords do not match!');</script>";
                exit();
            }

            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert into the database
            $sql = "INSERT INTO employee (firstName, lastName, gender, role, address, contact, email, password) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssissss", $firstName, $lastName, $gender, $role, $address, $contactNumber, $email, $hashedPassword);

            if ($stmt->execute()) {
                echo "<script>alert('New employee registered successfully!');</script>";
            } else {
                throw new Exception($stmt->error);
            }

            $stmt->close();
        } catch (Exception $e) {
            echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
        } finally {
            $conn->close();
        }
    }
    if (isset($_POST['deleteEmp'])) {
        $empId = $_POST['deleteEmp'];

        $stmt = $conn->prepare("DELETE FROM employee WHERE id = ?");
        $stmt->bind_param("i", $empId);
        if (!$stmt->execute()) {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }
        echo "<script>alert('Employee Delete SuccessFully');</script>";


    }
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee</title>
    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="./addFiles/css/main.css">
    <script src="https://code.jquery.com/jquery-3.7.1.js"
        integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.2/css/dataTables.dataTables.css" />
    <style>
        dialog {
            border: none;
            padding: 20px;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            width: 50%;
        }

        dialog::-webkit-scrollbar {
            display: none;
        }



        .close {
            background: none;
            border: none;
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            float: right;
        }

        .close:hover,
        .close:focus {
            color: black;
        }

        @media screen and (max-width: 760px) {
            dialog {
                width: 90%;
            }
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
    </style>
</head>

<body>
    <?php include ('./addFiles/header.php'); ?>
    <main class="main" id="main">
        <nav class="fixed top left-0 w-full bg-gray-600 font-bold text-white px-2" style="top:10vh; z-index: 9999">
            <button id="registerNewEmployeeBtn" onclick="openModal()" type="button" class="hover:text-blue-600">
                Register New Employee
            </button>
        </nav>



        <!-- Register new employee -->
        <dialog id="empRegisterDialog">
            <div class="modal-header">
                <button class="close" onclick="closeModal()">&times;</button>
                <h5 class="modal-title" id="">Register New Employee</h5>
            </div>
            <div id="registerEmpForm">
                <form action="" method="post" class="max-w-lg mx-auto mt-8" onsubmit=" return validateForm()">
                    <input type="hidden" name="registerNewEmp">
                    <!-- Form fields -->
                    <div>
                        <div>
                            <label for="fname">First Name:</label><br>
                            <input type="text" id="fname" name="firstName" placeholder="Enter First Name" required
                                class="w-full px-3 py-2 border rounded-md">
                        </div>
                        <div class="mt-4">
                            <label for="lname">Last Name:</label><br>
                            <input type="text" id="lname" name="lastName" placeholder="Enter Last Name" required
                                class="w-full px-3 py-2 border rounded-md">
                        </div>
                        <div class="mt-4">
                            <label>Gender:</label><br>
                            <select name="gender" required class="w-full px-3 py-2 border rounded-md">
                                <option value="" disabled hidden selected>Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>
                        <div class="mt-4">
                            <label for="emp_role">Role</label>
                            <select name="role" id="emp_role" required class="w-full px-3 py-2 border rounded-md">
                                <option value="" hidden selected disabled>Select Employee Role</option>
                                <?php
                                $sql = "SELECT * FROM emprole ORDER BY id ASC";
                                $result = mysqli_query($conn, $sql);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<option value='" . $row['id'] . "'>" . $row['role'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label for="emp_currentAddress">Address:</label><br>
                        <input type="text" name="emp_current_address" id="emp_currentAddress"
                            placeholder="Enter Current Address" required class="w-full px-3 py-2 border rounded-md">
                    </div>
                    <div>
                        <div class="mt-4">
                            <label for="emp_contactNumber">Contact No.</label>
                            <input type="tel" name="contactNumber" required placeholder="Enter Employee Contact Number"
                                class="w-full px-3 py-2 border rounded-md">
                        </div>
                        <div class="mt-4">
                            <label for="emp_email">Email:</label><br>
                            <input type="email" name="email" pattern="[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}$"
                                placeholder="Enter Employee Email Id" required
                                class="w-full px-3 py-2 border rounded-md">
                        </div>
                        <div class="mt-4">
                            <label for="emp_password">Password</label><br>
                            <input type="password" id="emp_password" name="emp_password" placeholder="Enter Password"
                                required class="w-full px-3 py-2 border rounded-md">
                        </div>
                        <div class="mt-4">
                            <label for="cnf_pass">Confirm Password</label><br>
                            <input type="password" id="cnf_pass" name="cnfPass" placeholder="Confirm Password" required
                                class="w-full px-3 py-2 border rounded-md">
                        </div>
                    </div>
                    <button type="submit"
                        class="w-full mt-8 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Submit</button>
                </form>
            </div>
        </dialog>


        <!-- update employee  -->
        <dialog id="empUpdateDialog">
            <div class="modal-header">
                <button class="close" onclick="closeModal()">&times;</button>
                <h5 class="modal-title" id="">Update Employee</h5>

            </div>
            <form method="post" action="./addFiles/empUpdate.php"
                class="max-w-lg mx-auto mt-8 bg-white p-6 rounded-lg shadow-md" id="updateForm">


            </form>
        </dialog>

        <!-- employee details  -->
        <dialog id="empDetailsModal">
            <div class="modal-header">
                <button class="close" onclick="closeModal()">&times;</button>
                <h5 class="modal-title" id="">Employee Details</h5>
            </div>
            <div class="" id="empDetails">

            </div>


        </dialog>
        <!-- emp table -->
        <div class="overflow-hidden overflow-x-auto mt-6">
            <span class="font-bold text-2xl underline">Employee Table</span>

            <table id="myTable" class="border-2 border-collapse table-fixed overflow-hidden overflow-x-auto">
                <thead>
                    <tr class="border-2 border-black">
                        <th class="border-2 border-black overflow-hidden text-ellipsis">Name</th>
                        <th class="border-2 border-black overflow-hidden text-ellipsis">Role</th>
                        <th class="border-2 border-black overflow-hidden text-ellipsis">Contact</th>
                        <th class="border-2 border-black overflow-hidden text-ellipsis">Email</th>
                        <th class="border-2 border-black overflow-hidden text-ellipsis">Details</th>
                        <th class="border-2 border-black overflow-hidden text-ellipsis">Access</th>
                        <th class="border-2 border-black overflow-hidden text-ellipsis"><span class="p-2">Action</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT e.id, e.firstName, e.lastName, r.role, e.contact, e.email
                    FROM employee e
                    JOIN empRole r ON e.role = r.id
                    ORDER BY firstName ASC";
                    $result = mysqli_query($conn, $sql);

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $empId = htmlspecialchars($row['id']);
                            $fullName = htmlspecialchars($row['firstName']) . ' ' . htmlspecialchars($row['lastName']);
                            $role = htmlspecialchars($row['role']);
                            $contact = htmlspecialchars($row['contact']);
                            $email = htmlspecialchars($row['email']);

                            echo "<tr class='border-2 border-black'>";
                            echo "<td class='border border-black overflow-hidden text-nowrap text-ellipsis hover:text-clip'>$fullName</td>";
                            echo "<td class='border border-black overflow-hidden text-ellipsis hover:text-clip'>$role</td>";
                            echo "<td class='border border-black overflow-hidden text-ellipsis hover:text-clip'>$contact</td>";
                            echo "<td class='border border-black overflow-hidden text-ellipsis hover:text-clip '>$email</td>";
                            echo "<td class='border border-black overflow-hidden text-center text-ellipsis'>
                            <button data-empId='" . $row['id'] . "' id='employeeDetailsBtn' class='text-blue-500  font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline'>
                                <i class='fa-solid fa-eye inline-block md:hidden'></i>
                                <span class='md:inline-block hidden'>Details</span>
                            </button>
                          </td>";
                            echo "<td class='border border-black text-center overflow-hidden text-ellipsis'>
                            <button id='empAccessBtn'  class='text-blue-500 font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline' data-empId='" . $row['id'] . "'>
                                <span class='md:inline-block hidden'>Access</span>
                                <i class='fa-solid fa-hand inline-block md:hidden'></i>
                            </button>
                          </td>";
                            echo "<td class='border  text-center border-black overflow-hidden text-ellipsis '>
                            <div>
                            <form action='' method='post' onsubmit='return confirmEmpDelete(\"" . $row['firstName'] . " " . $row['lastName'] . "\")'>
                                <input type='hidden' name='deleteEmp' value='" . $row['id'] . "'>
                                <button type='submit' class=' text-red-500 font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline' onclick='deleteEmployee($empId)'>
                                <i class='fa-solid fa-trash-can'></i>
                                <span class='md:inline-block hidden'>Delete</span>
                                
                                </button>
                            </form>
                            </div>
                            <div >
                            <button data-empId='$empId' class='   text-blue-500 font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline' id='empUpdateBtn'>
                            <i class='fa-solid fa-pen'></i>
                            <span class='md:inline-block hidden'>Edit</span>
                            </button>
                            </div>
                          </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center'>No records found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>




    </main>

    <script>
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


        // Open modal
        function openModal() {
            document.getElementById("empRegisterDialog").showModal();
        }

        // Close all modals
        function closeModal() {
            document.getElementById("empRegisterDialog").close();
            document.getElementById("empDetailsModal").close();
            document.getElementById("empUpdateDialog").close();
            document.getElementById('empAccesses').close();
        }

        // Employee details button handling
        const employeeDetailsBtn = document.querySelectorAll("#employeeDetailsBtn");
        employeeDetailsBtn.forEach(btn => {
            btn.onclick = function () {
                document.getElementById("empDetailsModal").showModal();
                let empId = btn.getAttribute("data-empId");

                sendAjaxRequest('GET', "./addFiles/get_emp_details.php?empId=" + empId, (err, response) => {
                    if (err) {
                        console.error(err);
                    } else {
                        document.getElementById("empDetails").innerHTML = response;
                    }
                });
            };
        });

        // Employee update button handling
        const empUpdateBtn = document.querySelectorAll("#empUpdateBtn");
        empUpdateBtn.forEach(btn => {
            btn.addEventListener("click", function () {
                document.getElementById("empUpdateDialog").showModal();
                let empId = btn.getAttribute("data-empId");

                sendAjaxRequest('GET', "./addFiles/empUpdate.php?empId=" + empId, (err, response) => {
                    if (err) {
                        console.error(err);
                    } else {
                        document.getElementById("updateForm").innerHTML = response;
                    }
                });
            });
        });

        function confirmEmpDelete(empName) {
            if (confirm("Kya aap " + empName + " ko delete karna chahte hain?")) {
                // Agar user OK par click karta hai
                return true; // Delete karne ke liye true return karenge
            }
            else {
                alert("Delete Cancel")
                return false;
            }
        }



        function validateForm() {
            const password = document.getElementById('emp_password').value;
            const confirmPassword = document.getElementById('cnf_pass').value;
            const passwordError = document.getElementById('passwordError');

            if (password !== confirmPassword) {
                passwordError.textContent = 'Passwords do not match!';
                return false;
            }

            return true;
        }

        // Employee access button handling
        const empAccessBtn = document.querySelectorAll("#empAccessBtn");
        empAccessBtn.forEach(btn => {
            btn.addEventListener("click", function () {
                const empId = btn.getAttribute('data-empId');
                location.href = "./empAccess.php?empId=" + empId;
            });
        });

    </script>
    <script src="https://cdn.datatables.net/2.0.7/js/dataTables.js"></script>
    <script>
        let table = new DataTable('#myTable');
    </script>
</body>

</html>
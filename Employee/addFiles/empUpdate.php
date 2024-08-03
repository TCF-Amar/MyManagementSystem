<?php
session_start();
include ('../../dbconnection/dbconn.php');

if (isset($_GET['empId'])) {
    $id = $_GET['empId'];

    $stmt = $conn->prepare("SELECT e.*, r.id as roleId
                                    FROM employee e 
                                    JOIN empRole r ON e.role = r.id 
                                    WHERE e.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();

    if ($employee) {
        $name = htmlspecialchars($employee['firstName']);
        $email = htmlspecialchars($employee['email']);
        $role = htmlspecialchars($employee['role']);

        echo '
                            <input type="hidden" value="' . htmlspecialchars($employee['id']) . '" name="empUpdateForm">

                    <div>
                        <div>
                            <label for="fname" class="block text-gray-700">First Name:</label>
                            <input type="text" id="fname" name="firstName" value="' . $name . '" required class="w-full px-3 py-2 border rounded-md">
                        </div>
                        <div class="mt-4">
                            <label for="lname" class="block text-gray-700">Last Name:</label>
                            <input type="text" id="lname" name="lastName" value="' . htmlspecialchars($employee['lastName']) . '" required class="w-full px-3 py-2 border rounded-md">
                        </div>
                        <div class="mt-4">
                            <label class="block text-gray-700">Gender:</label>
                            <select name="gender" required class="w-full px-3 py-2 border rounded-md">
                                <option value="" disabled hidden>Select Gender</option>
                                <option value="Male" ' . ($employee['gender'] == 'Male' ? 'selected' : '') . '>Male</option>
                                <option value="Female" ' . ($employee['gender'] == 'Female' ? 'selected' : '') . '>Female</option>
                                <option value="Others" ' . ($employee['gender'] == 'Others' ? 'selected' : '') . '>Others</option>
                            </select>
                        </div>
                        <div class="mt-4">
                            <label for="emp_role" class="block text-gray-700">Role</label>
                            <select name="role" id="emp_role" required class="w-full px-3 py-2 border rounded-md">
                                <option value="" hidden disabled>Select Employee Role</option>';

        $sql = "SELECT * FROM empRole";
        $result = mysqli_query($conn, $sql);
        if ($result->num_rows > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<option value="' . $row['id'] . '" ' . ($row['id'] == $employee['roleId'] ? 'selected' : '') . '>' . $row['role'] . '</option>';
            }
        }

        echo '
                            </select>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label for="emp_currentAddress" class="block text-gray-700">Address:</label>
                        <input type="text" name="emp_address" id="emp_currentAddress" value="' . htmlspecialchars($employee['address']) . '" required class="w-full px-3 py-2 border rounded-md">
                    </div>
                    <div>
                        <div class="mt-4">
                            <label for="emp_contactNumber" class="block text-gray-700">Contact No.</label>
                            <input type="tel" name="contactNumber" value="' . htmlspecialchars($employee['contact']) . '" required class="w-full px-3 py-2 border rounded-md">
                        </div>
                        <div class="mt-4">
                            <label for="emp_email" class="block text-gray-700">Email:</label>
                            <input type="email" name="email" value="' . $email . '" pattern="[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}$" required class="w-full px-3 py-2 border rounded-md">
                        </div>
                    </div>
                    <button type="submit" class="w-full mt-8 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Submit</button>
                ';
    } else {
        echo "<p class='text-red-500 text-center mt-8'>Employee not found.</p>";
    }

    # code...
}
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    try {
        if (isset($_POST['empUpdateForm'])) {
            $empId = $_POST['empUpdateForm'];
            $firstName = $_POST['firstName'];
            $lastName = $_POST['lastName'];
            $gender = $_POST['gender'];
            $role = $_POST['role'];
            $emp_address = $_POST['emp_address'];
            $contactNumber = $_POST['contactNumber'];
            $email = $_POST['email'];

            $stmt = $conn->prepare("UPDATE employee SET
                                    firstName = ?, lastName = ?, gender = ?, role = ?, address = ?, contact = ?, email = ?
                                    WHERE id = ?");
            $stmt->bind_param("sssisssi", $firstName, $lastName, $gender, $role, $emp_address, $contactNumber, $email, $empId);
            if ($stmt->execute()) {
                echo "<script>alert('Employee updated successfully.');window.location.href='../employee.php'</script>";
            } else {
                echo "<script>alert('Employee not updated.')</script>";
            }
        }
    } catch (Exception $e) {
        echo "<script>alert('Error Updating Employee: " . $e->getMessage() . "')</script>";
    }
}
?>
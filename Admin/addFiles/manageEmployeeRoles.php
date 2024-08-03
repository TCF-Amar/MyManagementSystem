<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
    header('Location: ../../LandingPage/index.php');
    exit();
}

include ('../../dbconnection/dbconn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['newRole'])) {
        $role = mysqli_real_escape_string($conn, $_POST['newRole']);

        $checkSql = "SELECT * FROM emprole WHERE role = '$role'";
        $checkResult = mysqli_query($conn, $checkSql);

        if (mysqli_num_rows($checkResult) > 0) {
            echo "<div class='msg bg-red-500'>Role Already Exists</div>";
        } else {
            $insertSql = "INSERT INTO emprole (role) VALUES ('$role')";
            if (mysqli_query($conn, $insertSql)) {
                echo "<div class='msg bg-green-500'>Role Added Successfully</div>";
            } else {
                echo "<div class='msg bg-red-500'>Error Occured</div>";
            }
        }
    }

    if (isset($_POST['editRoleId']) && isset($_POST['role'])) {
        $roleId = mysqli_real_escape_string($conn, $_POST['editRoleId']);
        $role = mysqli_real_escape_string($conn, $_POST['role']);

        $updateSql = "UPDATE emprole SET role = '$role' WHERE id = '$roleId'";
        if (mysqli_query($conn, $updateSql)) {
            echo "<div class='msg bg-green-500'>Role Updated Successfully</div>";
        } else {
            echo "<div class='msg bg-red-500'>Error Occured</div>";
        }
    }
    if (isset($_POST['deleteRole'])) {
        $roleId = mysqli_real_escape_string($conn, $_POST['deleteRole']);
        $sql = "DELETE FROM emprole where id = $roleId ";
        if (mysqli_query($conn, $sql)) {
            echo "<div class='msg bg-green-500'>Role Deleted Successfully</div>";
        } else {
            echo "<div class='msg bg-red-500'>Error Occured</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Manage</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../../src/output.css">
    <link rel="stylesheet" href="./css/msg.css">
    <link rel="stylesheet" href="./css/dataTables.dataTables.css">
    <script src="../../src/jQuery.js"></script>
    <link rel="stylesheet" href="./css/main.css">
</head>

<body>
    <header>
        <nav>
            <button class="font-bold text-2xl" id="manageEmpRoleCloseBtn">Close</button> |
            <button class="font-bold" id="addNewEmpRoleBtn">Add More Employee Role's</button> |
        </nav>
    </header>

    <main id="main" class="main">
        <label for="" class="font-bold ">Employee Roles: </label>
        <div class="container mx-auto p-4">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200 divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Role's</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        $SQL = "SELECT * FROM emprole";
                        $result = mysqli_query($conn, $SQL);
                        while ($row = mysqli_fetch_assoc($result)):
                            ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($row['role']); ?>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 flex gap-2 justify-center items-baseline">
                                    <button class="text-blue-500 hover:text-blue-700 font-bold"
                                        data-roleName="<?php echo htmlspecialchars($row['role']); ?>"
                                        data-roleId="<?php echo htmlspecialchars($row['id']); ?>"
                                        onclick="openEditDialog(this)">Edit</button>
                                    <form action="" method="post">
                                        <input type="hidden" name="deleteRole"
                                            value="<?php echo htmlspecialchars($row['id']) ?>">
                                        <button class="text-red-500 hover:text-red-700 font-bold"
                                            id="roleDeleteBtn">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <dialog id="addMoreEmpRoleFormDialog">
        <div class="modal-header">
            <span class="modal-title">Add More Employee Role's</span>
            <button class="close" id="closeAddMoreEmpRoleForm">&times;</button>
        </div>
        <div class="modal-body">
            <form action="" method="post" id="addMoreEmpRoleForm">
                <div class="form-group">
                    <label for="role">Role: </label>
                    <input type="text" name="newRole" id="role" class="form-control border-2 rounded"
                        placeholder="Enter Role's" required>
                </div>
                <br>
                <button type="submit"
                    class="w-full bg-blue-500 hover:bg-blue-700 p-2 font-bold text-white">Save</button>
            </form>
        </div>
    </dialog>

    <dialog id="editEmpRoleFormDialog">
        <div class="modal-header">
            <span class="modal-title">Edit Employee Role's</span>
            <button class="close" id="closeEditEmpRoleForm">&times;</button>
        </div>
        <div class="modal-body">
            <form action="" method="post" id="editEmpRoleForm">
                <input type="hidden" name="editRoleId" id="editRoleId">
                <div class="form-group">
                    <label for="editRole">Role: </label>
                    <input type="text" name="role" id="editRole" class="form-control border-2 rounded"
                        placeholder="Enter Role's" required>
                </div>
                <br>
                <button type="submit"
                    class="w-full bg-blue-500 hover:bg-blue-700 p-2 font-bold text-white">Save</button>
            </form>
        </div>
    </dialog>
    <script src="./js/script.js"></script>
    <script>
        $(document).ready(function () {
            $('#manageEmpRoleCloseBtn').click(function () {
                window.location.href = '../moreFeatures.php';
            });
            $("#addNewEmpRoleBtn").click(function () {
                $("#addMoreEmpRoleFormDialog").show();
            });
            $("#closeAddMoreEmpRoleForm").click(function () {
                $("#addMoreEmpRoleFormDialog").hide();
            });
            $("#closeEditEmpRoleForm").click(function () {
                $("#editEmpRoleFormDialog").hide();
            });
        });

        function openEditDialog(button) {
            const roleName = $(button).data('rolename');
            const roleId = $(button).data('roleid');

            $("#editRoleId").val(roleId);
            $("#editRole").val(roleName);

            $("#editEmpRoleFormDialog").show();
        }

        $("#roleDeleteBtn").click(function () {
            return confirm('Are you sure you want to delete this role?')

        })
    </script>
</body>

</html>
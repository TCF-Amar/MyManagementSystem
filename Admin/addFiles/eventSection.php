<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
    header('Location: ../../LandingPage/index.php');
}
include ('../../dbconnection/dbconn.php');


function generateUniqueFileName($title, $date, $startTime, $endTime, $extension)
{
    // Replace spaces with underscores and remove special characters from the title
    $title = preg_replace('/[^a-zA-Z0-9_]/', '_', $title);
    // Format the date and time
    $date = date('Ymd', strtotime($date));
    $startTime = str_replace(':', '', $startTime);
    $endTime = str_replace(':', '', $endTime);

    return $title . '_' . $date . '_' . $startTime . '_' . $endTime . '.' . $extension;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['NewEvent'])) {
        // Escape and validate inputs
        $eventTitle = mysqli_real_escape_string($conn, $_POST['event-title']);
        $eventDate = mysqli_real_escape_string($conn, $_POST['event-date']);
        $eventStartTime = mysqli_real_escape_string($conn, $_POST['event-start-time']);
        $eventEndTime = mysqli_real_escape_string($conn, $_POST['event-end-time']);
        $eventLocation = mysqli_real_escape_string($conn, $_POST['event-location']);
        $eventDescription = mysqli_real_escape_string($conn, $_POST['event-description']);

        // Handling the image upload
        if (isset($_FILES['event-image']) && $_FILES['event-image']['error'] == 0) {
            $imageFileType = pathinfo($_FILES['event-image']['name'], PATHINFO_EXTENSION);
            $imageFileType = strtolower($imageFileType);

            // Validate file type
            if (in_array($imageFileType, ['jpg', 'jpeg', 'png'])) {
                $newFileName = generateUniqueFileName($eventTitle, $eventDate, $eventStartTime, $eventEndTime, $imageFileType);
                $targetDirectory = '../Events/EventsImages/';
                $targetFile = $targetDirectory . $newFileName;

                // Ensure the uploads directory exists and is writable
                if (!file_exists($targetDirectory)) {
                    mkdir($targetDirectory, 0777, true);
                }

                // Check if the event already exists
                $stmt_check = $conn->prepare("SELECT id FROM events WHERE title = ? AND date = ? AND startTime =? AND endTime=? AND location = ?");
                $stmt_check->bind_param("sssss", $eventTitle, $eventDate, $eventStartTime, $eventEndTime, $eventLocation);
                $stmt_check->execute();
                $stmt_check->store_result();

                if ($stmt_check->num_rows > 0) {
                    echo "<div class='msg bg-red-500'>Event already exists with the same details.</div>";
                } else {
                    // Move the uploaded file to the target directory
                    if (move_uploaded_file($_FILES['event-image']['tmp_name'], $targetFile)) {
                        // Insert event details into the database
                        $stmt = $conn->prepare("INSERT INTO events (title, date, startTime, endTime, location, description, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("sssssss", $eventTitle, $eventDate, $eventStartTime, $eventEndTime, $eventLocation, $eventDescription, $targetFile);

                        if ($stmt->execute()) {
                            echo "<div class='msg bg-green-500'>Event added successfully!</div>";

                        } else {
                            echo "<div class='msg bg-red-500'>Error: " . $stmt->error . "</div>";
                        }

                        $stmt->close();
                    } else {
                        echo "<div class='msg bg-red-500'>Error uploading file.</div>";
                    }
                }
            } else {
                echo "<div class='msg bg-red-500'>Invalid file type. Only JPG, JPEG, & PNG files are allowed.</div>";
            }
        } else {
            echo "<div class='msg bg-red-500'>No file was uploaded or there was an error uploading the file.</div>";
        }
    }
    // event Update
    if (isset($_POST['updateEvent'])) {
        $eventId = $_POST['event-id'];
        $eventTitle = mysqli_real_escape_string($conn, $_POST['event-title']);
        $eventDate = mysqli_real_escape_string($conn, $_POST['event-date']);
        $eventStartTime = mysqli_real_escape_string($conn, $_POST['event-start-time']);
        $eventEndTime = mysqli_real_escape_string($conn, $_POST['event-end-time']);
        $eventLocation = mysqli_real_escape_string($conn, $_POST['event-location']);
        $eventDescription = mysqli_real_escape_string($conn, $_POST['event-description']);

        // Retrieve current event details including image path
        $stmt = $conn->prepare("SELECT title, date, startTime, endTime, image FROM events WHERE id = ?");
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        $stmt->bind_result($currentTitle, $currentDate, $currentStartTime, $currentEndTime, $currentImagePath);
        $stmt->fetch();
        $stmt->close();

        // Handling the image upload
        if (isset($_FILES['edit-event-image']) && $_FILES['edit-event-image']['error'] == 0) {
            $imageFileType = strtolower(pathinfo($_FILES['edit-event-image']['name'], PATHINFO_EXTENSION));

            if (in_array($imageFileType, ['jpg', 'jpeg', 'png'])) {
                // If title, date, and times are unchanged, retain the existing image name
                if ($eventTitle == $currentTitle && $eventDate == $currentDate && $eventStartTime == $currentStartTime && $eventEndTime == $currentEndTime) {
                    $newFileName = basename($currentImagePath); // retain existing image name
                } else {
                    $newFileName = generateUniqueFileName($eventTitle, $eventDate, $eventStartTime, $eventEndTime, $imageFileType);
                }

                $targetDirectory = '../Events/EventsImages/';
                $targetFile = $targetDirectory . $newFileName;

                if (!file_exists($targetDirectory)) {
                    mkdir($targetDirectory, 0777, true);
                }

                if (move_uploaded_file($_FILES['edit-event-image']['tmp_name'], $targetFile)) {
                    if (file_exists($currentImagePath) && $currentImagePath != $targetFile) {
                        unlink($currentImagePath);
                    }

                    $stmt = $conn->prepare("UPDATE events SET title=?, date=?, startTime=?, endTime=?, location=?, description=?, image=? WHERE id=?");
                    $stmt->bind_param("sssssssi", $eventTitle, $eventDate, $eventStartTime, $eventEndTime, $eventLocation, $eventDescription, $targetFile, $eventId);
                    if ($stmt->execute()) {
                        echo "<div class='msg bg-green-500'>Event updated successfully with new image!</div>";
                    } else {
                        echo "<div class='msg bg-red-500'>Error updating event: " . $stmt->error . "</div>";
                    }
                    $stmt->close();
                } else {
                    echo "<div class='msg bg-red-500'>Error uploading new image.</div>";
                }
            } else {
                echo "<div class='msg bg-red-500'>Invalid file type. Only JPG, JPEG, & PNG files are allowed.</div>";
            }
        } else {
            // No new image uploaded, rename existing image if necessary
            if ($currentImagePath) {
                $currentImageExtension = pathinfo($currentImagePath, PATHINFO_EXTENSION);
                $newFileName = generateUniqueFileName($eventTitle, $eventDate, $eventStartTime, $eventEndTime, $currentImageExtension);
                $newFilePath = '../Events/EventsImages/' . $newFileName;

                if ($currentImagePath != $newFilePath && rename($currentImagePath, $newFilePath)) {
                    $stmt = $conn->prepare("UPDATE events SET title=?, date=?, startTime=?, endTime=?, location=?, description=?, image=? WHERE id=?");
                    $stmt->bind_param("sssssssi", $eventTitle, $eventDate, $eventStartTime, $eventEndTime, $eventLocation, $eventDescription, $newFilePath, $eventId);
                    if ($stmt->execute()) {
                        echo "<div class='msg bg-green-500'>Event updated successfully with renamed image!</div>";
                    } else {
                        echo "<div class='msg bg-red-500'>Error updating event: " . $stmt->error . "</div>";
                    }
                    $stmt->close();
                } else {
                    // Update event details without changing the image path
                    $stmt = $conn->prepare("UPDATE events SET title=?, date=?, startTime=?, endTime=?, location=?, description=? WHERE id=?");
                    $stmt->bind_param("ssssssi", $eventTitle, $eventDate, $eventStartTime, $eventEndTime, $eventLocation, $eventDescription, $eventId);
                    if ($stmt->execute()) {
                        echo "<div class='msg bg-green-500'>Event updated successfully!</div>";
                    } else {
                        echo "<div class='msg bg-red-500'>Error updating event: " . $stmt->error . "</div>";
                    }
                    $stmt->close();
                }
            }
        }
    }
    if (isset($_POST['deleteEvent'])) {
        $eventId = $_POST['deleteEvent'];

        // Retrieve the event's image file path
        $stmt = $conn->prepare("SELECT image FROM events WHERE id = ?");
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        $stmt->bind_result($imagePath);
        $stmt->fetch();
        $stmt->close();

        // Delete the image file from the server
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        // Delete the event from the database
        $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
        $stmt->bind_param("i", $eventId);

        if ($stmt->execute()) {
            echo "<div class='msg bg-green-500'>Delete Success.</div>";
            echo "<script>$('#eventSection').removeClass('hidden');</script>";
        } else {

            echo "<div class='msg bg-red-500'>Error Deleting Event.</div>";
        }

        $stmt->close();
    }

}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Manage</title>
    <link rel="stylesheet" href="../../src/output.css">
    <link rel="stylesheet" href="./css/main.css">
    <link rel="stylesheet" href="./css/dataTables.dataTables.css">
    <script src="../../src/jQuery.js"></script>
    <style>
        .msg {
            z-index: 2900900;
            position: fixed;
            width: 100%;
            height: 60px;
            display: flex;
            padding-left: 20px;
            align-items: center;
            font-weight: bold;
            font-size: 1.4rem;
            color: #fff;
            transition: opacity 0.5s ease-in-out;
        }

        header {
            position: fixed;
            top: 0;
            right: 0;
            width: 100%;
            height: 10vh;
            background-color: #fff;
            color: rgb(16, 41, 110);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            font-size: 1.2rem;
            transition: all 0.2s ease-in-out;
            z-index: 10;
            box-shadow: 2px 2px 2px #000;
        }

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

        .modal-body {
            padding: 1rem;
            background-color: #fff;
        }

        .modal-body label {
            display: block;
            margin: 0.5rem 0 0.2rem;
            font-weight: bold;
        }

        .modal-body input,
        .modal-body textarea {
            width: calc(100% - 2rem);
            margin: 0 1rem;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 0.25rem;
            font-size: 1rem;
        }

        .modal-body textarea {
            resize: vertical;
        }
    </style>


</head>

<body>
    <header>
        <nav>
            <button class="font-bold text-2xl" id="eventSectionCloseBtn">Close</button> |
            <button class="font-bold" id="addEventsBtn">Add New Event</button> |
            <button class="font-bold" id="historyDelete">Delete History</button> |

        </nav>
    </header>

    <main id="main" class="main ">

        <h2 class="text-2xl font-bold">Events Manage</h2>
        <div class="overflow-hidden overflow-x-auto">
            <table id="myTable" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            S.No</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Events</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Time</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Location</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Details</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php
                    $i = 1;
                    $sql = "SELECT * FROM events";
                    $result = mysqli_query($conn, $sql);
                    if (mysqli_num_rows($result) > 0):
                        while ($row = mysqli_fetch_assoc($result)):
                            ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap border"><?php echo $i++ ?></td>
                                <td class="px-6 py-4 whitespace-nowrap border">
                                    <?php echo htmlspecialchars($row['title']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap border"><?php echo htmlspecialchars($row['date']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap border">
                                    <?php
                                    // Assuming $row['startTime'] and $row['endTime'] are in 'H:i' format (24-hour format)
                                    $startTime = date('h:i A', strtotime($row['startTime']));
                                    $endTime = date('h:i A', strtotime($row['endTime']));
                                    echo htmlspecialchars($startTime . " To " . $endTime);
                                    ?>
                                </td>


                                <td class="px-6 py-4 whitespace-nowrap border">
                                    <?php echo htmlspecialchars($row['location']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap border">
                                    <?php
                                    date_default_timezone_set('Asia/Kolkata'); // Set timezone to Asia/Kolkata
                                    $eventDate = strtotime($row['date']); // Convert event date to timestamp
                                    $startTimeEvent = strtotime($row['startTime']); // Convert event start time to timestamp
                                    $endTimeEvent = strtotime($row['endTime']); // Convert event end time to timestamp
                                    $currentDate = strtotime(date('Y-m-d')); // Get current date and convert to timestamp
                                    $currentTime = strtotime(date('H:i:s')); // Get current time and convert to timestamp
                            
                                    // Determine event status
                                    if ($eventDate == $currentDate && $startTimeEvent <= $currentTime && $endTimeEvent >= $currentTime) {
                                        echo '<span class="font-bold text-red-500">Live</span>';
                                    } elseif ($eventDate > $currentDate || ($eventDate == $currentDate && $startTimeEvent > $currentTime)) {
                                        echo '<span class="font-bold text-blue-500">Upcoming</span>';
                                    } elseif ($eventDate < $currentDate || ($eventDate == $currentDate && $endTimeEvent < $currentTime)) {
                                        echo '<span class="font-bold text-green-500">Outgoing</span>';
                                    }
                                    ?>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap border">
                                    <button class="text-blue-600 hover:text-blue-900 font-bold  showEvent-btn"
                                        data-id="<?php echo $row['id']; ?>"
                                        data-title="<?php echo htmlspecialchars($row['title']); ?>"
                                        data-date="<?php echo htmlspecialchars($row['date']); ?>"
                                        data-startTime="<?php echo htmlspecialchars($row['startTime']) ?>"
                                        data-endTime="<?php echo htmlspecialchars($row['endTime']) ?>"
                                        data-location="<?php echo htmlspecialchars($row['location']); ?>"
                                        data-description="<?php echo htmlspecialchars($row['description']); ?>"
                                        data-image="<?php echo htmlspecialchars($row['image']) ?>">Show</button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap border flex items-baseline   justify-center">
                                    <div class="flex items-baseline h-full gap-3">

                                        <button class="edit-btn font-bold text-blue-500" data-id="<?php echo $row['id']; ?>"
                                            data-title="<?php echo htmlspecialchars($row['title']); ?>"
                                            data-date="<?php echo htmlspecialchars($row['date']); ?>"
                                            data-startTime="<?php echo htmlspecialchars($row['startTime']) ?>"
                                            data-endTime="<?php echo htmlspecialchars($row['endTime']) ?>"
                                            data-location="<?php echo htmlspecialchars($row['location']); ?>"
                                            data-description="<?php echo htmlspecialchars($row['description']); ?>"
                                            data-image="<?php echo htmlspecialchars($row['image']) ?>">Update</button>

                                        <form action="" method="post" class="flex">
                                            <input type="hidden" name="deleteEvent" value="<?php echo $row['id'] ?>">
                                            <button type="BUTTON" class="text-red-600 hover:text-red-900 font-bold delete-btn"
                                                onclick=" confirmDeleteEvent(event)">Delete</button>
                                        </form>
                                    </div>

                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- add new event  -->
        <dialog id="addNewEventDialog">
            <div class="modal-header">
                <button class="close" aria-label="Close modal" onclick="closeModal()">&times;</button>
                <h2 class="modal-title">Add New Event</h2>
            </div>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="NewEvent">
                <div class="modal-body">
                    <label for="event-image">Event Image:</label>
                    <input type="file" id="event-image" name="event-image" accept="image/*" required>
                    <br>

                    <label for="event-title">Event Title:</label>
                    <input type="text" id="event-title" name="event-title" placeholder="Event Title" required>
                    <br>

                    <label for="event-date">Event Date:</label>
                    <input type="date" id="event-date" name="event-date" required>
                    <br>
                    <label for="event-start-time">Event Start Time:</label>
                    <input type="time" id="event-start-time" name="event-start-time" required>
                    <br>
                    <label for="event-end-time">Event End Time:</label>
                    <input type="time" id="event-end-time" name="event-end-time" required>
                    <br>

                    <label for="event-location">Event Location:</label>
                    <input type="text" id="event-location" name="event-location" placeholder="Event Location" required>
                    <br>

                    <label for="event-description">Event Description:</label>
                    <textarea id="event-description" name="event-description"
                        placeholder="Event Description Like Start Time End Time And Others Details" rows="4"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btns bg-blue-500 w-full p-2 text-2xl font-bold text-white hover:bg-blue-700 rounded">Submit</button>
                </div>
            </form>
        </dialog>
        <!-- Show event details -->
        <dialog id="showEventDialog">
            <form method="dialog">
                <div class="modal-header">
                    <button class="close" aria-label="Close modal">&times;</button>
                    <h2 class="modal-title">Event Details</h2>
                </div>
                <div class=" p-2 flex justify-center w-full h-auto">
                    <img id="show-event-image" class=" w-full h-auto" src="" alt="Event Image">

                </div>
                <div>
                    <label for="show-event-title" class="font-bold text-2xl">Event Title:</label>
                    <span id="show-event-title" class="text-2xl"></span>
                </div>
                <div>
                    <label for="show-event-date" class="font-bold text-2xl">Date:</label>
                    <span id="show-event-date" class=""></span>

                </div>
                <div>
                    <label for="show-event-start-time" class="font-bold text-2xl">Time:</label>
                    <span id="show-event-start-time"></span> To <span id="show-event-end-time"></span>
                </div>

                <div>
                    <label for="show-event-location" class="font-bold text-2xl">Location:</label>
                    <span id="show-event-location"></span>
                </div>
                <div>
                    <label for="show-event-description" class="font-bold text-2xl">Description:</label>
                    <p id="show-event-description"> </p>
                </div>


            </form>
        </dialog>
        <!-- event Update -->

        <dialog id="editEventDialog">
            <div class="modal-header">
                <button class="close" aria-label="Close modal" onclick="closeEditModal()">&times;</button>
                <h2 class="modal-title">Update Event</h2>
            </div>
            <form method="POST" action="" enctype="multipart/form-data" id="editEventForm">
                <input type="hidden" name="updateEvent">
                <div class="modal-body">
                    <input type="hidden" id="edit-event-id" name="event-id">

                    <label for="current-event-image">Current Event Image:</label>
                    <img id="current-event-image" src="" alt="Event Image" style="max-width: 50%; height: auto;">
                    <br>

                    <label for="edit-event-image">Change Event Image:</label>
                    <input type="file" id="edit-event-image" name="edit-event-image" accept="image/*">
                    <br>

                    <label for="edit-event-title">Event Title:</label>
                    <input type="text" id="edit-event-title" name="event-title" placeholder="Event Title" required>
                    <br>

                    <label for="edit-event-date">Event Date:</label>
                    <input type="date" id="edit-event-date" name="event-date" required>
                    <br>

                    <label for="edit-event-start-time">Event Start Time:</label>
                    <input type="time" id="edit-event-start-time" name="event-start-time" required>
                    <br>

                    <label for="edit-event-end-time">Event End Time:</label>
                    <input type="time" id="edit-event-end-time" name="event-end-time" required>
                    <br>

                    <label for="edit-event-location">Event Location:</label>
                    <input type="text" id="edit-event-location" name="event-location" placeholder="Event Location"
                        required>
                    <br>

                    <label for="edit-event-description">Event Description:</label>
                    <textarea id="edit-event-description" name="event-description" class="text-start"
                        placeholder="Event Description Like Start Time End Time And Others Details" rows="4"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="submit"
                        class="bg-blue-500 font-bold text-2xl text-white px-3 py-2 rounded w-full">Save Changes</button>
                </div>
            </form>
        </dialog>



    </main>

    <script src="https://cdn.datatables.net/2.0.7/js/dataTables.js"></script>
    <script>
        let table = new DataTable('#myTable');
    </script>
    <script>
        setTimeout(function () {
            $(".msg").fadeOut('slow', function () {
                $(this).addClass('hidden');
            });
        }, 1000);
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const addNewEventDialog = document.getElementById('addNewEventDialog');
            const showEventDialog = document.getElementById('showEventDialog');
            const editEventDialog = document.getElementById('editEventDialog')

            document.getElementById('eventSectionCloseBtn').addEventListener('click', () => {
                window.location.href = '../moreFeatures.php';
            });

            document.getElementById('addEventsBtn').addEventListener('click', () => {
                addNewEventDialog.showModal();
            });

            document.querySelectorAll('.showEvent-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const eventDetails = {
                        id: this.dataset.id,
                        title: this.dataset.title,
                        date: this.dataset.date,
                        startTime: this.dataset.starttime,
                        endTime: this.dataset.endtime,
                        location: this.dataset.location,
                        description: this.dataset.description,
                        image: this.dataset.image,
                    };
                    openShowModal(eventDetails);
                });




                document.querySelectorAll('.edit-btn').forEach(button => {
                    button.addEventListener('click', function () {
                        const eventDetails = {
                            id: this.dataset.id,
                            title: this.dataset.title,
                            date: this.dataset.date,
                            startTime: this.dataset.starttime,
                            endTime: this.dataset.endtime,
                            location: this.dataset.location,
                            description: this.dataset.description,
                            image: this.dataset.image,
                        };
                        openEditModal(eventDetails);
                    })

                });


                document.querySelectorAll('.close').forEach(button => {
                    button.addEventListener('click', () => {
                        addNewEventDialog.close();
                        showEventDialog.close();
                        editEventDialog.close();

                    });
                });
                window.onclick = function (e) {
                    if (e.target == addNewEventDialog) {
                        addNewEventDialog.close();
                    }
                    if (e.target == showEventDialog) {
                        showEventDialog.close();
                    }
                    if (e.target == editEventDialog) {
                        editEventDialog.close();
                    }

                }

                function openShowModal(details) {
                    let startTime = formatAMPM(details.startTime);
                    // Convert end time to AM/PM format
                    let endTime = formatAMPM(details.endTime);

                    document.getElementById('show-event-title').textContent = details.title;
                    document.getElementById('show-event-date').textContent = details.date;
                    document.getElementById('show-event-start-time').textContent = startTime;
                    document.getElementById('show-event-end-time').textContent = endTime;
                    document.getElementById('show-event-location').textContent = details.location;
                    document.getElementById('show-event-description').textContent = details.description;
                    document.getElementById('show-event-image').src = details.image;
                    showEventDialog.showModal();
                }


                function openEditModal(details) {
                    document.getElementById('edit-event-id').value = details.id;
                    document.getElementById('edit-event-title').value = details.title;
                    document.getElementById('edit-event-date').value = details.date;
                    document.getElementById('edit-event-start-time').value = details.startTime;
                    document.getElementById('edit-event-end-time').value = details.endTime;
                    document.getElementById('edit-event-location').value = details.location;
                    document.getElementById('edit-event-description').value = details.description;
                    document.getElementById('current-event-image').src = details.image;
                    editEventDialog.showModal();

                }

                function formatAMPM(time) {
                    let hours = parseInt(time.substr(0, 2));
                    let minutes = time.substr(3, 2);
                    let ampm = hours >= 12 ? 'PM' : 'AM';
                    hours = hours % 12;
                    hours = hours ? hours : 12; // The hour '0' should be '12'
                    let strTime = hours + ':' + minutes + ' ' + ampm;
                    return strTime;
                }
            });




        })
        const historyDeleteBtn = document.getElementById("historyDelete");
        historyDeleteBtn.addEventListener("click", () => {
            const deleteConfirm = confirm('Kya App Puri History Delete Karna Chahte Hai');
            if (deleteConfirm) {
                fetch('deleteEventHistory.php', {
                    method: 'POST',
                })
                    .then(response => response.text())
                    .then(data => {
                        alert(data)
                        window.location.href = '../addFiles/eventSection.php';

                    })
                    .catch(error => {
                        // alert("Error: ", error)
                        console.log("Error: ", error);

                    });
            }
        });


        function confirmDeleteEvent(event) {
            event.preventDefault();
            const confirmed = confirm('Kya aap is event ko delete Karna Chahte Hain?');
            if (confirmed) {
                $(event.target).closest('form').submit();
            }
        }
    </script>


</body>

</html>
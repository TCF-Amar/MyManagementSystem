<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != true) {
    header('Location: ../LandingPage/index.php');
}
$adminId = $_SESSION['admin_id'];
include ('../dbconnection/dbconn.php');
include ('../Tables/tables.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="./addFiles/css/main.css">
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        /* Main Section */
        .main {
            margin-bottom: 10px;
            height: 90vh;
            position: fixed;
            top: 10vh;
            right: 0;
            width: 100%;
            padding: 20px;
            overflow-y: auto;
            transition: all 0.2s linear;
        }

        /* Hide Scrollbar */
        .main::-webkit-scrollbar {
            display: none;
        }

        /* Tables */
        table {
            border: 2px solid #000;
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        /* Flexbox Container */
        .container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            margin-bottom: 20px;
        }

        /* Box Styling */
        .box {
            padding: 10px;
            background-color: #fff;
            height: 100px;
            border-radius: 5px;
            box-shadow: 2px 2px 4px #5a5a5a;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all .2s linear;
        }

        .box:hover {
            transform: translateY(-5px);
        }

        /* Responsive Layout */
        @media (max-width: 768px) {
            .container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Calendar Container */
        .calendar-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        #month-year {
            font-size: 20px;
            font-weight: bold;
        }

        .nav-button {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
        }


        /* Navbar Inner Box */
        .navBarInnerBox {
            width: 100%;
            padding: 10px;
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            box-shadow: 2px 2px 18px #5a5a5a, -2px -2px 18px #ffffff;
            border-radius: 5px;
            gap: 5px;
            overflow-x: auto;
            white-space: nowrap;
            scroll-behavior: smooth;
        }

        .navBarInnerBox button {
            padding: 10px;
            border-radius: 5px;
        }

        .navBarInnerBox button:hover {
            transition: all .3s linear;
            background-color: rgb(16, 41, 110);
            color: #fff;
        }

        .navBarInnerBox button.active {
            background-color: rgb(16, 41, 110);
            color: #fff;
        }

        /* Scrollbar Styles */
        .navBarInnerBox::-webkit-scrollbar {
            display: none;
        }

        .navBarInnerBox {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Calendar Styling */
        .calendar-weekdays,
        .calendar-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }

        .calendar-weekdays div,
        .calendar-days div {
            text-align: center;
            padding: 10px;
        }

        .calendar-days div:hover {
            background-color: #f0f0f0;
        }

        .calendar-days .prev-date,
        .calendar-days .next-date {
            color: #ccc;
        }

        .calendar-days .today {
            background-color: #ffcccc;
        }

        .calendar-days .sunday,
        .calendar-days .holiday {
            color: red;
        }

        /* Event Boxes Container */
        .event-boxes {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin: 20px;
        }

        /* Event Box Styling */
        .event-box {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            height: 60vh;
            overflow: hidden;
            /* overflow-y: auto; */

        }

        /* 
        .event-box:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        } */


        .event-box h2 {
            margin: 0;
            font-size: 1.3rem;
            padding: 10px 2px;
            margin-bottom: 10px;
            text-align: center;
            background-color: rgba(0, 0, 0, .7);
            color: #fff;
            font-weight: 700;
            width: 100%;
        }

        /* Event Content */
        .event-content {
            margin-top: 20px;
            height: 50vh;
            overflow: hidden;
            overflow-y: auto;
        }

        .event-content::-webkit-scrollbar,
        .description::-webkit-scrollbar {
            display: none;
        }

        .event-card {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .event-poster img {
            width: 100%;
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .event-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .event-date {
            font-size: 1rem;
            color: #555;
            margin-bottom: 10px;
        }

        .description {
            font-size: 0.9rem;
            line-height: 1.5;
            color: #666;
            height: 100px;
            padding: 5px;
            overflow: hidden;
            overflow-y: auto;
        }



        /* Media Queries for Responsiveness */
        @media (max-width: 768px) {
            .event-boxes {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }

            .event-box {
                padding: 15px;
            }

            .event-card {
                padding: 10px;
            }
        }
    </style>
</head>

<body>
    <?php include ('./addFiles/header.php') ?>
    <main class="main" id="main">
        <section class="section">
            <div class="container">
                <div class="box">
                    <div class="text-4xl">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                    <i class="text-4xl text-orange-400">|</i>
                    <div class="flex flex-col items-center">
                        <span>Students</span>
                        <span class="font-bold"><?php
                        $SQL = "SELECT count(*) as totalStudent FROM students";
                        $result = mysqli_query($conn, $SQL);
                        $row = mysqli_fetch_assoc($result);
                        echo $row['totalStudent'];
                        ?></span>
                    </div>
                </div>
                <div class="box">
                    <div class="text-4xl">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <i class="text-4xl text-orange-400">|</i>
                    <div class="flex flex-col items-center">
                        <span>Employees</span>
                        <span class="font-bold"><?php
                        $SQL = "SELECT count(*) as totalEmployee FROM employee";
                        $result = mysqli_query($conn, $SQL);
                        $row = mysqli_fetch_assoc($result);
                        echo $row['totalEmployee'];
                        ?></span>
                    </div>
                </div>
            </div>
        </section>
        <div class=" eventAndCal grid md:grid-cols-2 grid-cols-1 m-2">

            <div class="">
                <label for="">Events:</label>
                <div class="event-boxes">
                    <div class="event-box upcoming">
                        <h2 class="">Upcoming Events</h2>
                        <div class="event-content" id="upcoming-event-content">

                        </div>
                    </div>
                    <div class="event-box live relative">
                        <h2 class=" relative">Live Events</h2>
                        <div class="event-content" id="live-event-content">

                        </div>
                    </div>
                    <div class="event-box history">
                        <h2 class="">History</h2>
                        <div class="event-content" id="past-event-content">

                        </div>
                    </div>
                </div>




            </div>
            <!-- calender -->
            <div class="calendar-container">
                <div class="calendar-header">
                    <button id="prev" class="nav-button">❮</button>
                    <div id="month-year"></div>
                    <button id="next" class="nav-button">❯</button>
                </div>
                <div class="calendar-body">
                    <div class="calendar-weekdays">
                        <div>Sun</div>
                        <div>Mon</div>
                        <div>Tue</div>
                        <div>Wed</div>
                        <div>Thu</div>
                        <div>Fri</div>
                        <div>Sat</div>
                    </div>
                    <div class="calendar-days" id="calendar-days">
                        <!-- Days will be dynamically generated here -->
                    </div>
                </div>
            </div>
        </div>

        <template id="event-template">
            <div class="event-card">
                <div class="event-poster">
                    <img src="=" alt="poster">
                </div>
                <div class="event-title"></div>
                <div class="event-date"></div>
                <div class="event-time"></div>
                <span class="font-bold underline" naive. management system.>Description:</span><br>
                <div class="description">
                    <p></p>
                </div>
            </div>
        </template>
    </main>
    <script>

        document.addEventListener("DOMContentLoaded", function () {
            fetchEvents(); // Fetch events when the page loads

            // Function to fetch events from server
            function fetchEvents() {
                fetch('./addFiles/fetch-events.php')
                    .then(response => response.json())
                    .then(data => {
                        // Process the data and render events in respective sections
                        renderEvents(data);
                    })
                    .catch(error => console.error('Error fetching events:', error));
            }

            // Function to render events into respective sections
            function renderEvents(events) {
                const upcomingEventsContainer = document.getElementById('upcoming-event-content');
                const liveEventsContainer = document.getElementById('live-event-content');
                const pastEventsContainer = document.getElementById('past-event-content');
                const eventTemplate = document.getElementById('event-template');

                // Clear previous content
                upcomingEventsContainer.innerHTML = '';
                liveEventsContainer.innerHTML = '';
                pastEventsContainer.innerHTML = '';
                // Check if events array is empty for each category
                if (events.filter(event => event.status.toLowerCase() === 'upcoming').length === 0) {
                    upcomingEventsContainer.innerHTML = '<p>No upcoming events available</p>';
                }

                if (events.filter(event => event.status.toLowerCase() === 'live').length === 0) {
                    liveEventsContainer.innerHTML = '<p>No live events available</p>';
                }

                if (events.filter(event => event.status.toLowerCase() === 'past').length === 0) {
                    pastEventsContainer.innerHTML = '<p>No History events available</p>';
                }


                events.forEach(event => {
                    const eventCard = eventTemplate.content.cloneNode(true).querySelector('.event-card');

                    const existingImgSrc = event.images;
                    const newImgSrc = existingImgSrc.replace('../Events/EventsImages', './Events/EventsImages');
                    eventCard.querySelector('.event-poster img').src = newImgSrc;
                    eventCard.querySelector('.event-title').textContent = event.name;
                    eventCard.querySelector('.event-date').textContent = `Date: ${event.date}`;
                    eventCard.querySelector('.event-time').textContent = convertTo12HourFormat(event.startTime) + ' - ' + convertTo12HourFormat(event.endTime);
                    eventCard.querySelector('.description p').textContent = event.description;

                    // Determine which section to append the event card
                    if (event.status.toLowerCase() === 'upcoming') {
                        upcomingEventsContainer.appendChild(eventCard);
                    } else if (event.status.toLowerCase() === 'live') {
                        liveEventsContainer.appendChild(eventCard);
                    } else if (event.status.toLowerCase() === 'past') {
                        pastEventsContainer.appendChild(eventCard);
                    }
                });
            }

            // Function to convert 24-hour time format to 12-hour format
            function convertTo12HourFormat(time) {
                let [hours, minutes, seconds] = time.split(':');
                let period = 'AM';

                hours = parseInt(hours);

                if (hours >= 12) {
                    period = 'PM';
                    if (hours > 12) {
                        hours -= 12;
                    }
                } else if (hours === 0) {
                    hours = 12;
                }

                return `${hours}:${minutes} ${period}`;
            }
        });
        // Calendar rendering
        document.addEventListener('DOMContentLoaded', () => {
            const monthYearDisplay = document.getElementById('month-year');
            const calendarDays = document.getElementById('calendar-days');
            const prevButton = document.getElementById('prev');
            const nextButton = document.getElementById('next');

            let currentDate = new Date();

            const renderCalendar = (date) => {
                const year = date.getFullYear();
                const month = date.getMonth();

                monthYearDisplay.innerText = `${date.toLocaleString('default', { month: 'long' })} ${year}`;

                const firstDayOfMonth = new Date(year, month, 1).getDay();
                const daysInMonth = new Date(year, month + 1, 0).getDate();
                const daysInPrevMonth = new Date(year, month, 0).getDate();

                calendarDays.innerHTML = '';

                for (let i = firstDayOfMonth; i > 0; i--) {
                    const day = document.createElement('div');
                    day.classList.add('prev-date');
                    day.innerText = daysInPrevMonth - i + 1;
                    calendarDays.appendChild(day);
                }

                for (let i = 1; i <= daysInMonth; i++) {
                    const day = document.createElement('div');
                    const today = new Date();
                    if (i === today.getDate() && year === today.getFullYear() && month === today.getMonth()) {
                        day.classList.add('today');
                    }
                    day.innerText = i;
                    calendarDays.appendChild(day);
                }

                const totalDaysDisplayed = firstDayOfMonth + daysInMonth;
                const nextDays = 7 - (totalDaysDisplayed % 7);
                if (nextDays < 7) {
                    for (let i = 1; i <= nextDays; i++) {
                        const day = document.createElement('div');
                        day.classList.add('next-date');
                        day.innerText = i;
                        calendarDays.appendChild(day);
                    }
                }
            };

            prevButton.addEventListener('click', () => {
                currentDate.setMonth(currentDate.getMonth() - 1);
                renderCalendar(currentDate);
            });

            nextButton.addEventListener('click', () => {
                currentDate.setMonth(currentDate.getMonth() + 1);
                renderCalendar(currentDate);
            });

            renderCalendar(currentDate);
        });
    </script>
</body>

</html>
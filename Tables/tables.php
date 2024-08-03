<?php
include ('../dbconnection/dbconn.php');


$sqlCourseYears = "CREATE TABLE IF NOT EXISTS courseYears (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    courseYear VARCHAR(255) NOT NULL UNIQUE)";
$conn->query($sqlCourseYears);


$defYearInsert = "INSERT IGNORE INTO courseYears (courseYear) VALUES 
    ('1st Year'), 
    ('2nd Year'), 
    ('3rd Year'), 
    ('4th Year')";

$conn->query($defYearInsert);
$lecSql = "CREATE TABLE IF NOT EXISTS lectures (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    lecture_name VARCHAR(255) NOT NULL UNIQUE
)";
$conn->query($lecSql);

$lecVal = "INSERT IGNORE INTO lectures (lecture_name) VALUES
    ('1 Lecture'),
    ('2 Lecture'),
    ('3 Lecture'),
    ('4 Lecture'),
    ('5 Lecture'),
    ('6 Lecture'),
    ('Lab')
";
$conn->query($lecVal);


$empRole = "CREATE TABLE IF NOT EXISTS empRole (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role VARCHAR(255) NOT NULL  UNIQUE
)";
$conn->query($empRole);

// Insert default roles into the empRole table
$defaultRoles = "INSERT IGNORE INTO empRole (role) VALUES
('Principal'),
('HOD'),
('Teacher')";
$conn->query($defaultRoles);

$courseTableSql = "CREATE TABLE IF NOT EXISTS courses (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    courseName VARCHAR(255) NOT NULL UNIQUE,
    department VARCHAR(255) not NULL ,
    courseDuration varchar(255) not null
)";
$conn->query($courseTableSql);

$branchTable = "CREATE TABLE IF NOT EXISTS courseBranches(
    id int not null auto_increment primary Key,
    courseId int not null,
    branchName varchar(255) not null,
        FOREIGN KEY (courseId) REFERENCES courses(id) ON DELETE CASCADE
)  ";
$conn->query($branchTable);



$student = "CREATE TABLE IF NOT EXISTS students (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    rollNo VARCHAR(255) NOT NULL UNIQUE,
    enrollNo VARCHAR(255) NOT NULL UNIQUE,
    firstName VARCHAR(255) NOT NULL,
    lastName VARCHAR(255) NOT NULL,
    fatherName VARCHAR(255) NOT NULL,
    contact BIGINT NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    gender ENUM('Male', 'Female', 'Others') NOT NULL,
    dob DATE NOT NULL,
    address VARCHAR(255) NOT NULL,
    courseId INT NOT NULL,
    courseYear INT NOT NULL,
    FOREIGN KEY (courseId) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (courseYear) REFERENCES courseYears(id) ON DELETE CASCADE
)";
$conn->query($student);

$employeeTable = "CREATE TABLE IF NOT EXISTS employee (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    firstName VARCHAR(255) NOT NULL,
    lastName VARCHAR(255) NOT NULL,
    gender ENUM('Male', 'Female', 'Others') NOT NULL,
    role INT NOT NULL,
    address VARCHAR(255) NOT NULL,
    contact BIGINT NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    FOREIGN KEY (role) REFERENCES empRole(id) On DELETE CASCADE
)";
$conn->query($employeeTable);
// Create accesses table
$createAccessesTable = "CREATE TABLE IF NOT EXISTS accesses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    emp_id INT,
    accesses VARCHAR(30),
    FOREIGN KEY (emp_id) REFERENCES employee(id) ON DELETE CASCADE
)";
$conn->query($createAccessesTable);

$subjectTable = "CREATE TABLE IF NOT EXISTS subjects (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        subjectName VARCHAR(255) NOT NULL,
        subjectCode VARCHAR(255) NOT NULL UNIQUE,
        branch INT NOT NULL,
        year INT NOT NULL,
        FOREIGN KEY (branch) REFERENCES courseBranches(id),
        FOREIGN KEY (year) REFERENCES courseYears(id)
    )";
$conn->query($subjectTable);

$subjectAllotTable = "CREATE TABLE IF NOT EXISTS allotSubjects (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    subjectId INT NOT NULL,
    empId INT NOT NULL,
    unique key (subjectId, empId),
    FOREIGN KEY (subjectId) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (empId) REFERENCES employee(id) ON DELETE CASCADE
)";
$conn->query($subjectAllotTable);

$createAttendanceTable = "CREATE TABLE IF NOT EXISTS attendance (
    att_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL REFERENCES students(id) ON DELETE CASCADE,
    emp_id INT NOT NULL REFERENCES employee(emp_id) ON DELETE CASCADE,
    course_id INT NOT NULL REFERENCES courses(id) ON DELETE CASCADE,
    subject_id INT NOT NULL REFERENCES subjects(id) ON DELETE CASCADE,
    lecture VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    day VARCHAR(255) NOT NULL,
    month VARCHAR(255) NOT NULL,
    year VARCHAR(255) NOT NULL,
    status ENUM('present', 'absent', 'leave') NOT NULL,
    UNIQUE KEY lecture_date_unique (lecture, date, student_id, course_id)
)";
$conn->query($createAttendanceTable);


$examNameSql = "CREATE TABLE IF NOT EXISTS examNames(
    id INT NOT NULL AUTO_INCREMENT  PRIMARY KEY,
    examName VARCHAR(255) NOT NULL UNIQUE
)";
$conn->query($examNameSql);
$defaultExamNameSql = "INSERT IGNORE INTO examNames(examName)
VALUE('Unit Test'), ('Mid Semester')";
$conn->query($defaultExamNameSql);

$studentResultSql = "CREATE TABLE IF NOT EXISTS results (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    studentId INT NOT NULL,
    subjectId INT NOT NULL,
    examNameId INT NOT NULL,
    examDate DATE NOT NULL,
    examMonth varchar(255) NOT NULL,
    examYear INT NOT NULL,
    maxMark INT NOT NULL,
    obtainedMark INT NOT NULL,
    FOREIGN KEY (studentId) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subjectId) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (examNameId) REFERENCES examNames(id) ON DELETE CASCADE
)";
$conn->query($studentResultSql);


$SQLTIMETABLE = 'CREATE TABLE IF NOT EXISTS timetable (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    day VARCHAR(255) NOT NULL,
    startTime TIME NOT NULL,
    endTime TIME NOT NULL ,
    subjectId INT NOT NULL,
    UNIQUE KEY dayTimeSubject (day, startTime, subjectId),
    UNIQUE KEY  (day, startTime, endTime),
    FOREIGN KEY (subjectId) REFERENCES subjects(id) ON DELETE CASCADE
)
';
$conn->query($SQLTIMETABLE);

$eventSql = "CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    startTime time not null,
    endTime time not null,
    location VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_event (title, date,startTime,endTime,location)
)
";
$conn->query($eventSql);
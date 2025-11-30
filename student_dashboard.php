<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'student') {
    header("Location: college_login.php");
    exit;
}

// Get student data
$student = $_SESSION['user'];
$joiningDate = $student['joining_date'] ?? null;
if (!$joiningDate) {
    die("Joining date not found for student.");
}

// Calculate semester
$join = new DateTime($joiningDate);
$now = new DateTime();

// Calculate total months passed
$interval = $join->diff($now);
$monthsPassed = ($interval->y * 12) + $interval->m;

// Each semester = 6 months
$currentSem = intdiv($monthsPassed, 6) + 1;

// Determine semester type based on calendar
$monthNow = (int)$now->format('m');
if ($monthNow >= 7 && $monthNow <= 12) {
    // Jul-Dec → odd semester
    $semType = ($currentSem % 2 === 1) ? 'Odd' : 'Even';
} else {
    // Jan-May → even semester
    $semType = ($currentSem % 2 === 0) ? 'Even' : 'Odd';
}

// Display college info
$collegeName = $_SESSION['college'] ?? 'Unknown College';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
</head>
<body>
<h2>Welcome, <?= htmlspecialchars($student['name'] ?? $student['id']); ?> 🎓</h2>
<p>College: <?= htmlspecialchars($collegeName); ?></p>
<p>Current Semester: <?= $currentSem ?> (<?= $semType ?> Semester)</p>

<h3>Student Info</h3>
<ul>
    <li>ID: <?= htmlspecialchars($student['id']); ?></li>
    <li>Name: <?= htmlspecialchars($student['name']); ?></li>
    <li>Date of Joining: <?= htmlspecialchars($student['joining_date']); ?></li>
    <li>Birthday: <?= htmlspecialchars($student['dob']); ?></li>
    <li>Address: <?= htmlspecialchars($student['address']); ?></li>
</ul>

<p><a href="logout.php">Logout</a></p>
</body>
</html>
 

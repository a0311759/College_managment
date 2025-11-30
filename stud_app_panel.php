<?php
session_start();

// Ensure admin is logged in
if (!isset($_SESSION['college'])) {
    header("Location: college_login.php");
    exit;
}

$collegeFolder = __DIR__ . "/dbs/colleges/";
$collegeName = strtolower($_SESSION['college']); // normalize to lowercase

$studentsFile = null;

// Find folder matching college name (case-insensitive)
foreach (array_diff(scandir($collegeFolder), ['.', '..']) as $folder) {
    if (strtolower($folder) === $collegeName) {
        $studentsFile = $collegeFolder . $folder . "/students.json";
        break;
    }
}

// Load students
$students = [];
if ($studentsFile && file_exists($studentsFile)) {
    $students = json_decode(file_get_contents($studentsFile), true) ?? [];
}

// Filtering by search and approval
$search = $_GET['search'] ?? '';
$filterApproved = $_GET['filter'] ?? '';

$filteredStudents = array_filter($students, function($s) use ($search, $filterApproved) {
    $matchSearch = $search === '' || stripos($s['id'] ?? '', $search) !== false || stripos($s['name'] ?? '', $search) !== false;
    $matchApproved = $filterApproved === '' || ($filterApproved === 'approved' && !empty($s['approved'])) || ($filterApproved === 'not_approved' && empty($s['approved']));
    return $matchSearch && $matchApproved;
});
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Approval Panel</title>
    <!-- External stylesheet (same as teacher panel) -->
    <link rel="stylesheet" type="text/css" href="teacher_panel.css">
</head>
<body>
    <h2>Student Approval Panel - <?= htmlspecialchars($_SESSION['college'] ?? ''); ?></h2>

    <a href="college_dashboard.php" class="back-btn">⬅ Back</a>

    <form method="get" class="filter-form">
        <input type="text" name="search" placeholder="Search by ID or Name" value="<?= htmlspecialchars($search); ?>">
        <select name="filter">
            <option value="">All</option>
            <option value="approved" <?= $filterApproved === 'approved' ? 'selected' : ''; ?>>Approved</option>
            <option value="not_approved" <?= $filterApproved === 'not_approved' ? 'selected' : ''; ?>>Not Approved</option>
        </select>
        <button type="submit">Filter</button>
    </form>

    <table class="teacher-table">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Approved</th>
            <th>Action</th>
        </tr>
        <?php foreach ($filteredStudents as $student): ?>
        <tr>
            <td><?= htmlspecialchars($student['id'] ?? ''); ?></td>
            <td><?= htmlspecialchars($student['name'] ?? ''); ?></td>
            <td><?= !empty($student['approved']) ? '✅' : '❌'; ?></td>
            <td><a class="button" href="edit_student.php?id=<?= urlencode($student['id'] ?? ''); ?>">Edit</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>

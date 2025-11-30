<?php
session_start();
if (!isset($_SESSION['college'])) {
    header("Location: college_login.php");
    exit;
}

$collegeFolder = __DIR__ . "/dbs/colleges/";
$collegeName = strtolower($_SESSION['college']);

$teachersFile = null;
foreach (array_diff(scandir($collegeFolder), ['.', '..']) as $folder) {
    if (strtolower($folder) === $collegeName) {
        $teachersFile = $collegeFolder . $folder . "/teachers.json";
        break;
    }
}

$teachers = [];
if ($teachersFile && file_exists($teachersFile)) {
    $teachers = json_decode(file_get_contents($teachersFile), true) ?? [];
}

$search = $_GET['search'] ?? '';
$filterApproved = $_GET['filter'] ?? '';

$filteredTeachers = array_filter($teachers, function($t) use ($search, $filterApproved) {
    $matchSearch = $search === '' || stripos($t['id'] ?? '', $search) !== false || stripos($t['name'] ?? '', $search) !== false;
    $matchApproved = $filterApproved === '' || ($filterApproved === 'approved' && !empty($t['approved'])) || ($filterApproved === 'not_approved' && empty($t['approved']));
    return $matchSearch && $matchApproved;
});
?>
<!DOCTYPE html>
<html>
<head>
    <title>Teacher Approval Panel</title>
    <!-- External stylesheet -->
    <link rel="stylesheet" type="text/css" href="teacher_panel.css">
</head>
<body>
    <h2>Teacher Approval Panel - <?= htmlspecialchars($_SESSION['college'] ?? ''); ?></h2>

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
        <?php foreach ($filteredTeachers as $teacher): ?>
        <tr>
            <td><?= htmlspecialchars($teacher['id'] ?? ''); ?></td>
            <td><?= htmlspecialchars($teacher['name'] ?? ''); ?></td>
            <td><?= !empty($teacher['approved']) ? '✅' : '❌'; ?></td>
            <td><a class="button" href="edit_teacher.php?id=<?= urlencode($teacher['id'] ?? ''); ?>">Edit</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>

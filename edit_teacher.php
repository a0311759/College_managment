<?php
session_start();
if (!isset($_SESSION['college'])) {
    header("Location: college_login.php");
    exit;
}

$collegeFolder = __DIR__ . "/dbs/colleges/";
$collegeName = strtolower($_SESSION['college']);

// find teacher and course files
$teachersFile = null;
$coursesFile = null;

foreach (array_diff(scandir($collegeFolder), ['.', '..']) as $folder) {
    if (strtolower($folder) === $collegeName) {
        $teachersFile = $collegeFolder . $folder . "/teachers.json";
        $coursesFile = $collegeFolder . $folder . "/courses.json";
        break;
    }
}

if (!$teachersFile || !file_exists($teachersFile)) {
    die("teachers file not found.");
}

// load teachers
$teachers = json_decode(file_get_contents($teachersFile), true) ?? [];
$teacherId = $_GET['id'] ?? '';
$teacher = null;

// find the teacher
foreach ($teachers as &$t) {
    if (($t['id'] ?? '') === $teacherId) {
        $teacher = &$t;
        break;
    }
}

if (!$teacher) die("teacher not found.");

// load available courses
$courses = [];
if ($coursesFile && file_exists($coursesFile)) {
    $courses = json_decode(file_get_contents($coursesFile), true) ?? [];
}
if (!is_array($courses)) $courses = [];

// ensure teacher['courses'] is an array of mappings
if (!isset($teacher['courses']) || !is_array($teacher['courses'])) {
    $teacher['courses'] = [];
}

// handle post update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher['name'] = $_POST['name'] ?? $teacher['name'];
    $teacher['father_name'] = $_POST['father_name'] ?? $teacher['father_name'];
    $teacher['mother_name'] = $_POST['mother_name'] ?? $teacher['mother_name'];
    $teacher['dob'] = $_POST['dob'] ?? $teacher['dob'];
    $teacher['joining_date'] = $_POST['joining_date'] ?? $teacher['joining_date'];
    $teacher['address'] = $_POST['address'] ?? $teacher['address'];
    $teacher['approved'] = isset($_POST['approved']) ? true : false;

    // handle multiple selected courses + semesters
    $teacher['courses'] = [];
    if (!empty($_POST['courses'])) {
        foreach ($_POST['courses'] as $courseName => $semesters) {
            $teacher['courses'][$courseName] = $semesters;
        }
    }

    if (!empty($_POST['password'])) {
        $teacher['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    file_put_contents($teachersFile, json_encode($teachers, JSON_PRETTY_PRINT));
    $success = "teacher updated successfully.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Teacher - <?= htmlspecialchars($teacher['id'] ?? ''); ?></title>
    <link rel="stylesheet" type="text/css" href="styles.css"> <!-- external stylesheet -->
</head>
<body>
    <div class="container">
        <h2>Edit Teacher <?= htmlspecialchars($teacher['id'] ?? ''); ?></h2>
        <?php if(isset($success)) echo "<p class='success'>$success</p>"; ?>

        <form method="POST" class="form-container">
            <div class="form-group">
                <label>ID:</label>
                <b><?= htmlspecialchars($teacher['id'] ?? ''); ?></b>
            </div>

            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" value="<?= htmlspecialchars($teacher['name'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label>Father Name:</label>
                <input type="text" name="father_name" value="<?= htmlspecialchars($teacher['father_name'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Mother Name:</label>
                <input type="text" name="mother_name" value="<?= htmlspecialchars($teacher['mother_name'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Birthday:</label>
                <input type="date" name="dob" value="<?= htmlspecialchars($teacher['dob'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Date of Joining:</label>
                <input type="date" name="joining_date" value="<?= htmlspecialchars($teacher['joining_date'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Address:</label>
                <input type="text" name="address" value="<?= htmlspecialchars($teacher['address'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label><b>Courses & Semesters Taught:</b></label>
                <?php if (!empty($courses)): ?>
                    <?php foreach ($courses as $courseName => $semesters): ?>
                        <fieldset class="form-group">
                            <legend><?= htmlspecialchars($courseName); ?></legend>
                            <?php foreach ($semesters as $semKey => $subjects):
                                $semNum = preg_replace('/[^0-9]/', '', $semKey); ?>
                                <label>
                                    <input type="checkbox" name="courses[<?= htmlspecialchars($courseName); ?>][]"
                                           value="<?= htmlspecialchars($semNum); ?>"
                                           <?= (isset($teacher['courses'][$courseName]) && in_array($semNum, $teacher['courses'][$courseName])) ? 'checked' : ''; ?>>
                                    Semester <?= $semNum; ?>
                                </label><br>
                            <?php endforeach; ?>
                        </fieldset>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="error">No courses available in this college yet.</p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Password (leave blank to keep same):</label>
                <input type="password" name="password">
            </div>

            <div class="form-group">
                <label>Approved:</label>
                <input type="checkbox" name="approved" <?= !empty($teacher['approved']) ? 'checked' : ''; ?>>
            </div>

            <button type="submit" class="btn">Update Teacher</button>
        </form>

        <p><a href="teacher_app_panel.php" class="back-link">Back to Teacher Panel</a></p>
    </div>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['college'])) {
    header("Location: college_login.php");
    exit;
}

$collegeFolder = __DIR__ . "/dbs/colleges/";
$collegeName = strtolower($_SESSION['college']);

// find student file
$studentsFile = null;
foreach (array_diff(scandir($collegeFolder), ['.', '..']) as $folder) {
    if (strtolower($folder) === $collegeName) {
        $studentsFile = $collegeFolder . $folder . "/students.json";
        $coursesFile = $collegeFolder . $folder . "/courses.json";
        break;
    }
}

if (!$studentsFile || !file_exists($studentsFile)) {
    die("students file not found.");
}

// load students
$students = json_decode(file_get_contents($studentsFile), true) ?? [];
$studentId = $_GET['id'] ?? '';
$student = null;

// find the student
foreach ($students as &$s) {
    if (($s['id'] ?? '') === $studentId) {
        $student = &$s;
        break;
    }
}

if (!$student) die("student not found.");

// load available courses
$courses = [];
if (isset($coursesFile) && file_exists($coursesFile)) {
    $coursesData = json_decode(file_get_contents($coursesFile), true) ?? [];
    if (is_array($coursesData)) {
        $courses = $coursesData; // full course data with semesters
    }
}

// handle post update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student['name'] = $_POST['name'] ?? $student['name'];
    $student['father_name'] = $_POST['father_name'] ?? $student['father_name'];
    $student['mother_name'] = $_POST['mother_name'] ?? $student['mother_name'];
    $student['dob'] = $_POST['dob'] ?? $student['dob'];
    $student['address'] = $_POST['address'] ?? $student['address'];
    $student['course'] = $_POST['course'] ?? $student['course'];
    $student['current_semester'] = $_POST['current_semester'] ?? $student['current_semester'];
    $student['approved'] = isset($_POST['approved']) ? true : false;

    if (!empty($_POST['password'])) {
        $student['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    file_put_contents($studentsFile, json_encode($students, JSON_PRETTY_PRINT));
    $success = "student updated successfully.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Student - <?= htmlspecialchars($student['id'] ?? ''); ?></title>
    <link rel="stylesheet" type="text/css" href="styles.css"> <!-- external CSS attached -->
    <script>
        function updateSemesters() {
            const courseSelect = document.getElementById('course');
            const semesterSelect = document.getElementById('current_semester');
            const selectedCourse = courseSelect.value;

            const coursesData = <?= json_encode($courses); ?>;

            semesterSelect.innerHTML = '<option value="">-- select semester --</option>';

            if (selectedCourse && coursesData[selectedCourse]) {
                const semesters = Object.keys(coursesData[selectedCourse]);
                semesters.forEach((sem, index) => {
                    const semNum = index + 1;
                    const opt = document.createElement('option');
                    opt.value = semNum;
                    opt.textContent = "Semester " + semNum;
                    semesterSelect.appendChild(opt);
                });
            }
        }

        window.onload = function() {
            updateSemesters();
            const savedSemester = "<?= htmlspecialchars($student['current_semester'] ?? ''); ?>";
            if (savedSemester) {
                document.getElementById('current_semester').value = savedSemester;
            }
        };
    </script>
</head>
<body>
    <div class="container"> <!-- containerised structure -->
        <h2>Edit Student <?= htmlspecialchars($student['id'] ?? ''); ?></h2>
        <?php if(isset($success)) echo "<p class='success'>$success</p>"; ?>
        
        <form method="POST" class="form-container">
            <div class="form-group">
                <label>ID:</label>
                <b><?= htmlspecialchars($student['id'] ?? ''); ?></b>
            </div>

            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" value="<?= htmlspecialchars($student['name'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label>Father Name:</label>
                <input type="text" name="father_name" value="<?= htmlspecialchars($student['father_name'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Mother Name:</label>
                <input type="text" name="mother_name" value="<?= htmlspecialchars($student['mother_name'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Birthday:</label>
                <input type="date" name="dob" value="<?= htmlspecialchars($student['dob'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Address:</label>
                <input type="text" name="address" value="<?= htmlspecialchars($student['address'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Course:</label>
                <select name="course" id="course" onchange="updateSemesters()" required>
                    <option value="">-- select course --</option>
                    <?php foreach (array_keys($courses) as $course): ?>
                        <option value="<?= htmlspecialchars($course); ?>" <?= (isset($student['course']) && $student['course'] == $course) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($course); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Current Semester:</label>
                <select name="current_semester" id="current_semester" required>
                    <option value="">-- select semester --</option>
                </select>
            </div>

            <div class="form-group">
                <label>Password (leave blank to keep same):</label>
                <input type="password" name="password">
            </div>

            <div class="form-group">
                <label>Approved:</label>
                <input type="checkbox" name="approved" <?= !empty($student['approved']) ? 'checked' : ''; ?>>
            </div>

            <button type="submit" class="btn">Update Student</button>
        </form>

        <p><a href="stud_app_panel.php" class="back-link">Back to Student Panel</a></p>
    </div>
</body>
</html>

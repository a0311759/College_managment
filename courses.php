<?php
session_start();
if (!isset($_SESSION['college'])) {
    header("Location: college_login.php");
    exit;
}

$collegeName = $_SESSION['college'];
$collegeDir = __DIR__ . "/dbs/colleges/" . $collegeName;
if (!is_dir($collegeDir)) {
    mkdir($collegeDir, 0777, true);
}

$jsonFile = $collegeDir . "/courses.json";

// Load existing courses
$courses = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];

// Ensure file exists
if (!file_exists($jsonFile)) {
    file_put_contents($jsonFile, "{}");
}

// Handle form submission safely
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $courseName = isset($_POST['courseName']) ? trim($_POST['courseName']) : '';

    if ($action === 'save' && $courseName !== '') {
        // Initialize course with 12 semesters if not exists
        if (!isset($courses[$courseName])) {
            for ($i = 1; $i <= 12; $i++) {
                $courses[$courseName]["sem$i"] = [];
            }
        }

        // Update subjects for each semester safely
        for ($i = 1; $i <= 12; $i++) {
            $field = "sem$i";
            if (!empty($_POST[$field])) {
                $subjects = array_filter(array_map('trim', explode(',', $_POST[$field])));
                $courses[$courseName]["sem$i"] = $subjects;
            }
        }

        // Save back to JSON
        file_put_contents($jsonFile, json_encode($courses, JSON_PRETTY_PRINT));

        // Redirect before any HTML output
        header("Location: courses.php?course=" . urlencode($courseName));
        exit;
    }

    if ($action === 'delete' && isset($_POST['deleteCourse'])) {
        $deleteCourse = $_POST['deleteCourse'];
        $captchaAnswer = $_POST['captchaAnswer'] ?? '';
        $captchaExpected = $_POST['captchaExpected'] ?? '';

        if ($captchaAnswer === $captchaExpected && isset($courses[$deleteCourse])) {
            unset($courses[$deleteCourse]);
            file_put_contents($jsonFile, json_encode($courses, JSON_PRETTY_PRINT));
            header("Location: courses.php");
            exit;
        } else {
            $error = "❌ Captcha failed. Course not deleted.";
        }
    }
}

// Selected course (if any)
$selectedCourse = isset($_GET['course']) ? $_GET['course'] : null;

// Generate captcha
$captchaNum = rand(1000, 9999);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Courses Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f9f9f9; }
        h2 { text-align: center; color: #333; }
        .form-container { width: 80%; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .semester { margin-bottom: 15px; }
        label { font-weight: bold; }
        input[type=text] { width: 100%; padding: 8px; margin-top: 5px; }
        button { margin-top: 10px; padding: 8px 15px; }
        .course-list { margin: 20px auto; width: 80%; }
        .course-item { margin: 8px 0; padding: 8px; background: #e3f2fd; border-radius: 5px; }
        .course-item a { margin-right: 15px; text-decoration: none; color: #333; font-weight: bold; }
        .nav-link { display: block; text-align: center; margin-top: 30px; }
        .error { color: red; text-align: center; }
    </style>
</head>
<body>
    <h2>📚 Manage Courses - <?php echo htmlspecialchars($collegeName); ?></h2>

    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

    <?php if (!$selectedCourse): ?>
        <!-- Course list screen -->
        <div class="course-list">
            <h3>Available Courses:</h3>
            <?php foreach ($courses as $course => $data): ?>
                <div class="course-item">
                    <a href="courses.php?course=<?php echo urlencode($course); ?>"><?php echo htmlspecialchars($course); ?></a>
                    <!-- Delete form with captcha -->
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="deleteCourse" value="<?php echo htmlspecialchars($course); ?>">
                        <input type="hidden" name="captchaExpected" value="<?php echo $captchaNum; ?>">
                        <label>Captcha: <?php echo $captchaNum; ?></label>
                        <input type="text" name="captchaAnswer" required>
                        <button type="submit" name="action" value="delete">🗑️ Delete</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="form-container">
            <form method="post">
                <label>New Course Name:</label>
                <input type="text" name="courseName" required>
                <button type="submit" name="action" value="save">➕ Create</button>
            </form>
        </div>
        <p class="nav-link"><a href="college_dashboard.php">⬅️ Back to College Dashboard</a></p>
    <?php else: ?>
        <!-- Course editing screen -->
        <div class="form-container">
            <form method="post">
                <label>Course Name:</label>
                <input type="text" name="courseName" value="<?php echo htmlspecialchars($selectedCourse); ?>" required>

                <?php for ($i = 1; $i <= 12; $i++): 
                    $subjects = isset($courses[$selectedCourse]["sem$i"]) ? implode(", ", $courses[$selectedCourse]["sem$i"]) : "";
                ?>
                    <div class="semester">
                        <label>Semester <?php echo $i; ?> Subjects (comma separated):</label>
                        <input type="text" name="sem<?php echo $i; ?>" value="<?php echo htmlspecialchars($subjects); ?>">
                    </div>
                <?php endfor; ?>

                <button type="submit" name="action" value="save">💾 Save</button>
            </form>
        </div>
        <p class="nav-link"><a href="courses.php">⬅️ Back to Courses List</a></p>
    <?php endif; ?>
</body>
</html>

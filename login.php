<?php
session_start();

$collegesDir = __DIR__ . "/dbs/colleges";

// Ensure folder exists
if (!is_dir($collegesDir)) {
    mkdir($collegesDir, 0777, true);
}

// Load college folders
$colleges = array_values(
    array_filter(scandir($collegesDir), fn($i) =>
        $i !== '.' && $i !== '..' && is_dir("$collegesDir/$i")
    )
);

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $college = $_POST['college'] ?? '';
    $id = $_POST['id'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    $filePath = "$collegesDir/$college/{$role}s.json";

    if (file_exists($filePath)) {
        $users = json_decode(file_get_contents($filePath), true) ?? [];

        foreach ($users as $user) {
            if ($user['id'] === $id && password_verify($password, $user['password'])) {

                $_SESSION['user'] = $user;
                $_SESSION['college'] = $college;
                $_SESSION['role'] = $role;

                // approval safe-check
                $isApproved = false;
                if (isset($user['approved'])) {
                    $v = $user['approved'];
                    $isApproved = ($v === true || $v === "true" || $v === 1 || $v === "1");
                }

                if ($isApproved) {
                    if ($role === "student") {
                        header("Location: student_dashboard.php");
                    } else {
                        header("Location: teacher_dashboard.php");
                    }
                } else {
                    header("Location: not_approved.php");
                }
                exit;
            }
        }
        $error = "Invalid ID or password.";
    } else {
        $error = "No records found for this college.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>College Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="form.css">
</head>

<body>

<div class="form-wrapper">
    <h2>College Login</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="form-box">

      <label>ID Number</label>
      <input type="text" name="id" required>

      <label>Password</label>
      <input type="password" name="password" required>

      <label>Role</label>
      <select name="role" required>
        <option value="student">Student</option>
        <option value="teacher">Teacher</option>
      </select>

      <label>Search College</label>
      <input type="text" id="collegeSearch" placeholder="Start typing...">

      <select name="college" id="collegeSelect" size="5" required></select>

      <button type="submit">Login</button>
    </form>
</div>

<script>
    const colleges = <?= json_encode($colleges) ?>;
    const searchInput = document.getElementById('collegeSearch');
    const selectBox = document.getElementById('collegeSelect');

    function populate(list) {
        selectBox.innerHTML = '';
        list.forEach(college => {
            const opt = document.createElement('option');
            opt.value = college;
            opt.text = college;
            selectBox.appendChild(opt);
        });
        if (selectBox.options.length > 0)
            selectBox.options[0].selected = true;
    }

    populate(colleges);

    searchInput.addEventListener('input', () => {
        const q = searchInput.value.toLowerCase();
        const filtered = colleges.filter(c => c.toLowerCase().includes(q));
        populate(filtered);
    });
</script>

</body>
</html>


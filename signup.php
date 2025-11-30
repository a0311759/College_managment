<?php
session_start();

/**
 * signup.php
 * - Session-based rate limiting
 * - Creates student/teacher accounts per college
 */

$collegesDir = __DIR__ . "/dbs/colleges";
if (!is_dir($collegesDir)) mkdir($collegesDir, 0777, true);

if (!isset($_SESSION['signup_attempts'])) {
    $_SESSION['signup_attempts'] = [];
}

// Remove attempts older than 1 hour
$_SESSION['signup_attempts'] = array_filter(
    $_SESSION['signup_attempts'],
    fn($t) => $t > time() - 3600
);

$colleges = array_values(
    array_filter(scandir($collegesDir), fn($i) =>
        $i !== '.' && $i !== '..' && is_dir("$collegesDir/$i")
    )
);

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (count($_SESSION['signup_attempts']) >= 4) {
        $error = "Too many signup attempts in the last hour.";
    } else {
        $id = trim($_POST['id'] ?? '');
        $password = $_POST['password'] ?? '';
        $college = $_POST['college'] ?? '';
        $role = $_POST['role'] ?? '';

        if ($id === '' || $password === '' || $college === '' || $role === '') {
            $error = "All fields are required.";
        } elseif (!in_array($college, $colleges)) {
            $error = "Selected college not found.";
        } elseif (!in_array($role, ['student', 'teacher'])) {
            $error = "Invalid role.";
        } else {
            $collegeDir = "$collegesDir/$college";
            if (!is_dir($collegeDir)) mkdir($collegeDir, 0777, true);

            $file = "$collegeDir/" . ($role === 'student' ? "students.json" : "teachers.json");
            if (!file_exists($file)) file_put_contents($file, "[]");

            $users = json_decode(file_get_contents($file), true) ?? [];

            foreach ($users as $u) {
                if (isset($u['id']) && $u['id'] === $id) {
                    $error = "This ID is already registered in this college.";
                    break;
                }
            }

            if (!$error) {
                $newUser = [
                    'id'         => $id,
                    'password'   => password_hash($password, PASSWORD_DEFAULT),
                    'role'       => $role,
                    'college'    => $college,
                    'approved'   => false,
                    'created_at' => date("Y-m-d H:i:s")
                ];

                $users[] = $newUser;
                file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));

                $_SESSION['signup_attempts'][] = time();
                $_SESSION['user'] = $newUser;

                header("Location: not_approved.php");
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Sign Up</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="form.css">
</head>

<body>
<div class="form-wrapper">
    <h2>Sign Up</h2>

    <?php if ($error): ?>
    <div class="error" style="color:#b00020;margin-bottom:10px;">
        <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>

    <form method="post" autocomplete="off" class="form-box">

        <label>ID Number</label>
        <input name="id" type="text" required>

        <label>Password</label>
        <input name="password" type="password" required>

        <label>Role</label>
        <select name="role" required>
            <option value="student">Student</option>
            <option value="teacher">Teacher</option>
        </select>

        <label>Search College</label>
        <input id="collegeSearch" type="text" placeholder="Start typing...">

        <select id="collegeSelect" name="college" size="6" required>
            <?php foreach ($colleges as $c): ?>
                <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Sign Up</button>

        <p class="note">
            New accounts require admin approval.  
            You will be redirected to a waiting page after signup.
        </p>

    </form>
</div>

<script>
    const colleges = <?php echo json_encode($colleges); ?>;
    const searchInput = document.getElementById('collegeSearch');
    const selectBox = document.getElementById('collegeSelect');

    function populate(list) {
        selectBox.innerHTML = '';
        list.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c;
            opt.text = c;
            selectBox.appendChild(opt);
        });
        if (selectBox.options.length) selectBox.options[0].selected = true;
    }

    populate(colleges);

    searchInput.addEventListener('input', () => {
        const q = searchInput.value.toLowerCase();
        populate(colleges.filter(c => c.toLowerCase().includes(q)));
    });
</script>

</body>
</html>


<?php
session_start();
$db_file = __DIR__ . "/dbs/colleges.json";

// Ensure dbs folder exists
if (!is_dir(__DIR__ . "/dbs/colleges")) {
    mkdir(__DIR__ . "/dbs/colleges", 0777, true);
}

// Ensure JSON exists
if (!file_exists($db_file)) {
    file_put_contents($db_file, "[]");
}

// Generate captcha
if (!isset($_SESSION['captcha_a']) || !isset($_SESSION['captcha_b'])) {
    $_SESSION['captcha_a'] = rand(1, 9);
    $_SESSION['captcha_b'] = rand(1, 9);
}

// Rate-limit setup
if (!isset($_SESSION['register_attempts'])) {
    $_SESSION['register_attempts'] = [];
}

$_SESSION['register_attempts'] = array_filter(
    $_SESSION['register_attempts'],
    fn($t) => $t > time() - 3600
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (count($_SESSION['register_attempts']) >= 4) {
        die("❌ Too many attempts. Try again later.");
    }

    $college   = trim($_POST['college']);
    $applicant = trim($_POST['applicant']);
    $post      = trim($_POST['post']);
    $phone     = trim($_POST['phone']);
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $captcha_ok = ((int)$_POST['captcha'] === ($_SESSION['captcha_a'] + $_SESSION['captcha_b']));
    if (!$captcha_ok) {
        die("❌ Invalid captcha. <a href='register.php'>Try again</a>");
    }

    $_SESSION['register_attempts'][] = time();

    $data = json_decode(file_get_contents($db_file), true) ?? [];
    foreach ($data as $r) {
        if (strtolower($r['college']) === strtolower($college)) {
            die("❌ This college is already registered. <a href='register.php'>Go back</a>");
        }
    }

    $data[] = [
        'college' => $college,
        'applicant' => $applicant,
        'post' => $post,
        'phone' => $phone,
        'password' => $password,
        'approval' => false,
        'created_at' => date("Y-m-d H:i:s")
    ];

    file_put_contents($db_file, json_encode($data, JSON_PRETTY_PRINT));

    $safe = preg_replace("/[^a-zA-Z0-9_-]/", "_", strtolower($college));
    $folder = __DIR__ . "/dbs/colleges/" . $safe;

    if (!is_dir($folder)) mkdir($folder, 0777, true);

    $_SESSION['college'] = $college;
    unset($_SESSION['captcha_a'], $_SESSION['captcha_b']);
    header("Location: approval.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register College</title>
    <link rel="stylesheet" href="form.css">
</head>

<body>
<div class="form-wrapper">
    <h2>College Registration</h2>

    <form method="POST" class="form-box">

        <label>College Name</label>
        <input type="text" name="college" required>

        <label>Applicant Name</label>
        <input type="text" name="applicant" required>

        <label>Post in College</label>
        <input type="text" name="post" required>

        <label>Phone Number</label>
        <input type="text" name="phone" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>
            Captcha: What is <?php echo $_SESSION['captcha_a'] . " + " . $_SESSION['captcha_b']; ?> ?
        </label>
        <input type="text" name="captcha" required>

        <button type="submit">Register</button>

        <p class="note">
            Applicant will be called for manual verification.  
            You will see a waiting page until approval.
        </p>

    </form>
</div>
</body>
</html>


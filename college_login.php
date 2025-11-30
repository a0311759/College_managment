<?php
session_start();
$db_file = __DIR__ . "/dbs/colleges.json";
if (!file_exists($db_file)) file_put_contents($db_file, "[]");

// Load registered colleges
$registrations = json_decode(file_get_contents($db_file), true) ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $college = trim($_POST['college']);
    $password = $_POST['password'];

    foreach ($registrations as $r) {
        if (strtolower($r['college']) === strtolower($college) && password_verify($password, $r['password'])) {
            $_SESSION['college'] = $college;
            if (!empty($r['approval']) && $r['approval'] === true) {
                header("Location: college_dashboard.php");
            } else {
                header("Location: approval.php");
            }
            exit;
        }
    }
    $error = "Invalid college name or password.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>College Login</title>
    <!-- ✅ External CSS file -->
    <link rel="stylesheet" href="page.css">
</head>

<body>

<div class="container">
    <h2>College Login</h2>

    <form method="POST">

        <label>Search College</label>
        <input type="text" id="collegeSearch" placeholder="Start typing...">

        <label>Select College</label>
        <select name="college" id="collegeSelect" size="5" required>
            <?php foreach ($registrations as $r): ?>
                <option value="<?= htmlspecialchars($r['college']) ?>">
                    <?= htmlspecialchars($r['college']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Password</label>
        <input type="password" name="password" placeholder="Enter password" required>

        <button type="submit">Login</button>
    </form>

    <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>

    <p class="link"><a href="register.php">Register a new college</a></p>
</div>

<!-- 🔍 College Search Filter Logic -->
<script>
    const searchInput = document.getElementById("collegeSearch");
    const selectBox = document.getElementById("collegeSelect");

    searchInput.addEventListener("keyup", function () {
        const filter = searchInput.value.toLowerCase();
        let firstMatch = null;

        for (let i = 0; i < selectBox.options.length; i++) {
            const option = selectBox.options[i];
            const text = option.text.toLowerCase();

            option.style.display = text.includes(filter) ? "" : "none";

            if (text.includes(filter) && !firstMatch) {
                firstMatch = option;
            }
        }

        if (firstMatch) firstMatch.selected = true;
    });
</script>

</body>
</html>

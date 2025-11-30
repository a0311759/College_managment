<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Waiting for Approval</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f9f9f9; text-align: center; padding: 50px; }
    .box {
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0px 0px 10px #aaa;
      display: inline-block;
    }
    h2 { color: #cc0000; }
  </style>
</head>
<body>
  <div class="box">
    <h2>⏳ Waiting for Approval</h2>
    <p>Dear <strong><?php echo htmlspecialchars($user['id']); ?></strong>,</p>
    <p>Your account is not yet approved by the admin.</p>
    <p>Please wait until your details are verified.</p>
    <br>
    <a href="logout.php">Logout</a>
  </div>
</body>
</html>


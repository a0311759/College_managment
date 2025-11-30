<?php
session_start();
session_destroy();
header("Location: college_login.php");
exit;


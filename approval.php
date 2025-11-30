<?php
session_start();
if (!isset($_SESSION['college'])) {
    header("Location: college_login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Approval Pending</title>
    <style>
        body {
            font-family: "Segoe UI", Roboto, Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #f4f6f9, #e9ecef);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }

        .card {
            max-width: 500px;
            width: 90%;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.1);
            text-align: center;
        }

        h2 {
            margin-top: 0;
            color: #2c3e50;
            font-size: 24px;
        }

        p {
            font-size: 16px;
            line-height: 1.5;
            margin: 15px 0;
            color: #555;
        }

        a.logout {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 18px;
            background: #c62828;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s ease;
        }

        a.logout:hover {
            background: #a61d1d;
        }

        /* Responsive tweaks */
        @media (max-width: 600px) {
            .card {
                padding: 20px;
            }
            h2 {
                font-size: 20px;
            }
            p {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>⏳ Waiting for Approval</h2>
        <p>Your registration is under review. An admin will call you to verify authenticity.</p>
        <p>Once approved, you will have access to the College Dashboard.</p>
        <a href="logout.php" class="logout">Logout</a>
    </div>
</body>
</html>

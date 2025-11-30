<?php
session_start();
if (!isset($_SESSION['college'])) {
    header("Location: college_login.php");
    exit;
}

$collegeName = $_SESSION['college'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>College Dashboard</title>
    <style>
        /* General page styling */
        body {
            font-family: "Segoe UI", Roboto, Arial, sans-serif;
            margin: 0;
            padding: 40px 20px;
            text-align: center;
            background: linear-gradient(135deg, #f4f6f9, #e9ecef);
            color: #333;
        }

        h2 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 28px;
        }

        p {
            font-size: 16px;
            color: #555;
        }

        /* Card container */
        .card-container {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        /* Card styling */
        .card {
            width: 260px;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: #fff;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .card h3 {
            margin-bottom: 15px;
            font-size: 20px;
            color: #2c3e50;
        }

        /* Panel colors */
        .teacher { background: #fce4ec; }
        .student { background: #e3f2fd; }
        .course { background: #e8f5e9; }

        /* Links */
        a {
            text-decoration: none;
            font-weight: 600;
            color: #0078d7;
            font-size: 15px;
            display: inline-block;
            margin-top: 10px;
            padding: 8px 14px;
            border-radius: 6px;
            background: #fff;
            border: 1px solid #0078d7;
            transition: all 0.3s ease;
        }

        a:hover {
            background: #0078d7;
            color: #fff;
        }

        /* Logout link */
        .logout {
            margin-top: 50px;
            display: inline-block;
            color: #c62828;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid #c62828;
            padding: 8px 14px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .logout:hover {
            background: #c62828;
            color: #fff;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .card-container {
                flex-direction: column;
                align-items: center;
            }
            .card {
                width: 90%;
            }
        }
    </style>
</head>
<body>
    <h2>🎓 College Dashboard - <?= htmlspecialchars($collegeName); ?></h2>
    <p>Select a panel to manage approvals or view courses:</p>

    <div class="card-container">
        <div class="card teacher">
            <h3>👩‍🏫 Teacher Approval Panel</h3>
            <a href="teacher_app_panel.php">Go ➡️</a>
        </div>
        <div class="card student">
            <h3>👨‍🎓 Student Approval Panel</h3>
            <a href="stud_app_panel.php">Go ➡️</a>
        </div>
        <div class="card course">
            <h3>📚 Available Courses</h3>
            <a href="courses.php">View ➡️</a>
        </div>
    </div>

    <p><a href="logout.php" class="logout">Logout</a></p>
</body>
</html>

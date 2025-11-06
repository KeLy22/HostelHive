<?php
session_start();
require 'config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}

$student_id = $_SESSION['student_id'];

// Fetch student info
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

// Fetch current room info
$room = null;
if ($student && $student['room_id']) {
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$student['room_id']]);
    $room = $stmt->fetch();
}

// Fetch latest room change request
$stmt = $pdo->prepare("SELECT * FROM room_change_requests WHERE student_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$student_id]);
$change_request = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard - HostelHive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f9fafb;
            font-family: 'Segoe UI', sans-serif;
        }
        .sidebar {
            width: 230px;
            background-color: #2c3e50;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            color: white;
            padding-top: 30px;
        }
        .sidebar h4 {
            text-align: center;
            margin-bottom: 30px;
        }
        .sidebar a {
            display: block;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: #34495e;
        }
        .main-content {
            margin-left: 240px;
            padding: 30px;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h4>HostelHive Student</h4>
        <a href="student_dashboard.php">🏠 Dashboard</a>
        <a href="my_room.php">🏘️ My Room</a>
        <a href="student_profile.php">⚙️ Profile Settings</a>
        <a href="logout.php">🚪 Logout</a>
    </div>

    <div class="main-content">
        <h3>Welcome, <?= htmlspecialchars($student['name']); ?> 👋</h3>

        <!-- Room Status Card -->
        <div class="card p-3 my-3">
            <h5>🏠 Room Status</h5>
            <hr>
            <?php if ($room): ?>
                <p>🏘️ <strong>Room Number:</strong> <?= htmlspecialchars($room['room_number']); ?></p>
                <p>🛏️ <strong>Room Type:</strong> <?= htmlspecialchars($room['room_type']); ?></p>
                <p>✅ <strong>Status:</strong> Assigned</p>
            <?php elseif ($change_request): ?>
                <p>🛏️ <strong>Requested Room ID:</strong> <?= htmlspecialchars($change_request['requested_room_id']); ?></p>
                <p>🛌 <strong>Requested Room Type:</strong> <?= htmlspecialchars($change_request['requested_room_type'] ?? 'N/A'); ?></p>
                <p>🕓 <strong>Status:</strong> Pending Approval</p>
                <p>📝 <strong>Reason:</strong> <?= htmlspecialchars($change_request['reason']); ?></p>
            <?php else: ?>
                <p>❌ You have not been assigned a room yet.</p>
            <?php endif; ?>
        </div>

        <!-- Fee Status Card -->
        <div class="card p-3 my-3">
            <h5>💰 Fee Status</h5>
            <hr>
            <?php if ($student['fee_status'] ?? false === 'paid'): ?>
                <p>✅ <strong>Status:</strong> Paid</p>
            <?php else: ?>
                <p>❌ <strong>Status:</strong> Unpaid</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

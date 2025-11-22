<?php
session_start();
require 'config.php';

// Check if student logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}

$student_id = $_SESSION['student_id'];

// Fetch student info
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

// Fetch assigned room info
$room = null;
if ($student && $student['room_id']) {
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$student['room_id']]);
    $room = $stmt->fetch();
}

// Fetch latest room change request
$request = null;
$stmt = $pdo->prepare("SELECT * FROM room_change_requests WHERE student_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$student_id]);
$request = $stmt->fetch();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_room_change'])) {
    $requested_room_id = $_POST['requested_room_id'];
    $reason = trim($_POST['reason']);

    // Insert room change request
    $stmt = $pdo->prepare("INSERT INTO room_change_requests (student_id, requested_room_id, reason, status) VALUES (?, ?, ?, 'pending')");
    $stmt->execute([$student_id, $requested_room_id, $reason]);

    $message = "Your room change request has been submitted successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Room - HostelHive</title>
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
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
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
        <h3>My Room Details 🏡</h3>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="card p-3 my-3">
            <?php if ($room): ?>
                <h5>Room Assigned</h5>
                <hr>
                <p><strong>Room Number:</strong> <?= htmlspecialchars($room['room_no'] ?? 'N/A'); ?></p>
                <p><strong>Room Type:</strong> <?= htmlspecialchars($room['room_type'] ?? 'N/A'); ?></p>
                <p><strong>Capacity:</strong> <?= htmlspecialchars($room['capacity'] ?? 'N/A'); ?></p>
                <p><strong>Fee:</strong> <?= htmlspecialchars($room['fee'] ?? 'N/A'); ?></p>
                <p><strong>Status:</strong> <span class="badge bg-success">Approved</span></p>

            <?php elseif ($request): ?>
                <h5>Room Request</h5>
                <hr>
                <p>Your request for Room ID <strong><?= htmlspecialchars($request['requested_room_id'] ?? 'N/A'); ?></strong> is
                    <?php if ($request['status'] == 'pending'): ?>
                        <span class="badge bg-warning text-dark">Pending</span>
                    <?php elseif ($request['status'] == 'approved'): ?>
                        <span class="badge bg-success">Approved</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Rejected</span>
                    <?php endif; ?>
                </p>
                <p><strong>Reason:</strong> <?= htmlspecialchars($request['reason'] ?? 'N/A'); ?></p>
            <?php else: ?>
                <p>You have not been assigned any room or requested yet.</p>
            <?php endif; ?>
        </div>

        <div class="card p-3 my-3">
            <h5>🔄 Request Room Change</h5>
            <hr>
            <form method="POST">
                <div class="mb-2">
                    <label>Requested Room</label>
                    <select name="requested_room_id" class="form-control" required>
                        <option value="">-- Select Room --</option>
                        <?php
                        $rooms_stmt = $pdo->query("SELECT id, room_no, room_type, capacity, occupied FROM rooms ORDER BY room_no ASC");
                        $rooms = $rooms_stmt->fetchAll();
                        foreach ($rooms as $r):
                            $available = ($r['occupied'] < $r['capacity']);
                        ?>
                            <option value="<?= $r['id'] ?>" <?= !$available ? 'disabled' : '' ?>>
                                <?= htmlspecialchars($r['room_no'] . " ({$r['room_type']})" . (!$available ? ' - Full' : '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label>Reason</label>
                    <textarea name="reason" class="form-control" rows="3" required></textarea>
                </div>
                <button type="submit" name="request_room_change" class="btn btn-primary">Submit Request</button>
            </form>
        </div>
    </div>

</body>

</html>
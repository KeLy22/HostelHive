<?php
session_start();
require 'config.php';

// Flash message
$flash_msg = '';
if (isset($_SESSION['flash_msg'])) {
    $flash_msg = $_SESSION['flash_msg'];
    unset($_SESSION['flash_msg']);
}

// Handle remove room assignment
if (isset($_GET['remove'])) {
    $student_id = intval($_GET['remove']);
    $stmt = $pdo->prepare("SELECT room_id FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $room = $stmt->fetch();

    if ($room && $room['room_id']) {
        $pdo->prepare("UPDATE rooms SET occupied = occupied - 1 WHERE id = ? AND occupied > 0")
            ->execute([$room['room_id']]);
        $pdo->prepare("UPDATE students SET room_id = NULL WHERE id = ?")
            ->execute([$student_id]);
    }
    header("Location: student_list.php");
    exit;
}

// Handle mark payment
if (isset($_GET['pay'])) {
    $student_id = intval($_GET['pay']);
    $pdo->prepare("UPDATE students SET fee_status='paid' WHERE id=?")->execute([$student_id]);
    header("Location: student_list.php");
    exit;
}

// Fetch students
$search = $_GET['search'] ?? '';
if ($search !== '') {
    $stmt = $pdo->prepare("
        SELECT s.*, r.room_no, r.room_type
        FROM students s
        LEFT JOIN rooms r ON s.room_id = r.id
        WHERE s.name LIKE ? OR s.email LIKE ? OR r.room_no LIKE ?
        ORDER BY s.id DESC
    ");
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("
        SELECT s.*, r.room_no, r.room_type
        FROM students s
        LEFT JOIN rooms r ON s.room_id = r.id
        ORDER BY s.id DESC
    ");
}
$students = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Student List - HostelHive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f3f4f6;
            display: flex;
        }

        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            height: 100vh;
            position: fixed;
            display: flex;
            flex-direction: column;
        }

        .sidebar h2 {
            text-align: center;
            background: #1a252f;
            padding: 15px 0;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 15px 20px;
            display: block;
        }

        .sidebar a:hover {
            background: #34495e;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            flex-grow: 1;
        }

        .action-btn {
            display: inline-block;
            width: 110px;
            padding: 7px 0;
            border-radius: 6px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            text-align: center;
            margin-bottom: 3px;
        }

        .assign-room {
            background: #28a745;
        }

        .remove-room {
            background: #dc3545;
        }

        .mark-paid {
            background: #17a2b8;
        }

        .assigned-tag {
            background: #28a745;
            color: white;
            display: inline-block;
            padding: 7px 0;
            border-radius: 6px;
            width: 110px;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <h2>HostelHive Admin</h2>
        <a href="admin_dashboard.php">🏠 Home</a>
        <a href="student_requests.php">📥 Student Requests</a>
        <a href="room_change_requests.php">🔄 Room Change Requests</a>
        <a href="manage_rooms.php">🏘️ Manage Rooms</a>
        <a href="student_list.php">👩‍🎓 Hostel Students</a>
        <a href="profile.php">⚙️ Profile Settings</a>
        <a href="logout.php">🚪 Logout</a>
    </div>

    <div class="main-content">
        <h2>All Students</h2>

        <?php if ($flash_msg): ?>
            <div class="alert alert-info"><?= htmlspecialchars($flash_msg) ?></div>
        <?php endif; ?>

        <form method="get" class="mb-3 d-flex gap-2">
            <input type="text" name="search" class="form-control" placeholder="Search by Name, Email or Room No" value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-primary">Search</button>
            <button type="button" onclick="window.location='student_list.php'" class="btn btn-secondary">Reset</button>
        </form>

        <table class="table table-bordered table-striped bg-white">
            <thead class="table-primary">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Room No</th>
                    <th>Room Type</th>
                    <th>Payment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($students): foreach ($students as $s): ?>
                        <tr>
                            <td><?= $s['id'] ?></td>
                            <td><?= htmlspecialchars($s['name']) ?></td>
                            <td><?= htmlspecialchars($s['email']) ?></td>
                            <td><?= htmlspecialchars($s['phone']) ?></td>
                            <td><?= ucfirst($s['status']) ?></td>
                            <td><?= $s['room_no'] ?? 'Not assigned' ?></td>
                            <td><?= $s['room_type'] ?? 'Not assigned' ?></td>
                            <td>
                                <?php if ($s['fee_status'] === 'paid'): ?>
                                    ✅ Paid
                                <?php else: ?>
                                    ❌ Unpaid<br>
                                    <a href="student_list.php?pay=<?= $s['id'] ?>" class="action-btn mark-paid" onclick="return confirm('Mark payment as paid?')">Mark Paid</a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$s['room_no']): ?>
                                    <a href="assign_room.php?id=<?= $s['id'] ?>" class="action-btn assign-room">Assign Room</a>
                                <?php else: ?>
                                    <span class="assigned-tag">Assigned</span><br>
                                    <a href="student_list.php?remove=<?= $s['id'] ?>" class="action-btn remove-room" onclick="return confirm('Remove room assignment?')">Remove</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach;
                else: ?>
                    <tr>
                        <td colspan="9">No students found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>
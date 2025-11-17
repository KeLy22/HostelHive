<?php
session_start();
require 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

// Handle remove room assignment
if (isset($_GET['remove'])) {
    $student_id = intval($_GET['remove']);

    // Get current room ID
    $stmt = $pdo->prepare("SELECT room_id FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $room = $stmt->fetch();

    if ($room && $room['room_id']) {
        // Decrease occupied count
        $pdo->prepare("UPDATE rooms SET occupied = occupied - 1 WHERE id = ? AND occupied > 0")
            ->execute([$room['room_id']]);

        // Remove room assignment from student
        $pdo->prepare("UPDATE students SET room_id = NULL, room_no = NULL WHERE id = ?")
            ->execute([$student_id]);
    }

    header("Location: student_list.php");
    exit;
}

// Handle mark payment as paid
if (isset($_GET['pay'])) {
    $student_id = intval($_GET['pay']);
    $pdo->prepare("UPDATE students SET fee_status='paid' WHERE id=?")
        ->execute([$student_id]);
    header("Location: student_list.php");
    exit;
}

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search !== '') {
    $stmt = $pdo->prepare("
        SELECT s.id, s.name, s.email, s.phone, s.status, s.fee_status, r.room_no, r.room_type
        FROM students s
        LEFT JOIN rooms r ON s.room_id = r.id
        WHERE s.name LIKE ? OR s.email LIKE ? OR r.room_no LIKE ?
        ORDER BY s.id DESC
    ");
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("
        SELECT s.id, s.name, s.email, s.phone, s.status, s.fee_status, r.room_no, r.room_type
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

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
            display: flex;
        }

        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            height: 100vh;
            position: fixed;
            display: flex;
            flex-direction: column;
        }

        .sidebar h2 {
            text-align: center;
            background-color: #1a252f;
            padding: 15px 0;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 15px 20px;
            display: block;
        }

        .sidebar a:hover {
            background-color: #34495e;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            flex-grow: 1;
        }

        h2 {
            margin-bottom: 20px;
        }

        /* Search bar */
        form.search-form {
            margin-bottom: 20px;
            display: flex;
            gap: 5px;
        }

        form.search-form input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        form.search-form button,
        form.search-form .reset-btn {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
        }

        form.search-form button {
            background-color: #007bff;
        }

        form.search-form button:hover {
            background-color: #0056b3;
        }

        form.search-form .reset-btn {
            background-color: #6c757d;
        }

        form.search-form .reset-btn:hover {
            background-color: #5a6268;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
        }

        table th,
        table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }

        table th {
            background-color: #007bff;
            color: white;
        }

        /* ==== FIXED BUTTON BOXES ==== */
        .action-btn {
            display: inline-block;
            width: 110px;
            /* Same size for all buttons */
            padding: 7px 0;
            border-radius: 6px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            text-align: center;
        }

        .assign-room {
            background-color: #28a745;
        }

        .remove-room {
            background-color: #dc3545;
        }

        .mark-paid {
            background-color: #17a2b8;
        }

        .assigned-tag {
            background-color: #28a745;
            color: white;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <h2>HostelHive Admin</h2>
        <a href="admin_dashboard.php">🏠 Home</a>
        <a href="student_requests.php">📥 Student Requests</a>
        <a href="manage_rooms.php">🏘️ Manage Rooms</a>
        <a href="student_list.php">👩‍🎓 Hostel Students</a>
        <a href="profile.php">⚙️ Profile Settings</a>
        <a href="logout.php">🚪 Logout</a>
    </div>

    <div class="main-content">
        <h2>All Students</h2>

        <!-- Search Form -->
        <form method="get" class="search-form">
            <input type="text" name="search" placeholder="Search by Name, Email or Room No" value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
            <button type="button" onclick="window.location='student_list.php'" class="reset-btn">Reset</button>
        </form>

        <table>
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

            <?php if ($students): ?>
                <?php foreach ($students as $s): ?>
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
                                ❌ Unpaid
                                <br>
                                <a href="student_list.php?pay=<?= $s['id'] ?>" class="action-btn mark-paid" onclick="return confirm('Mark payment as paid?')">Mark Paid</a>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if (!$s['room_no']): ?>
                                <a href="assign_room.php?id=<?= $s['id'] ?>" class="action-btn assign-room">Assign Room</a>
                            <?php else: ?>
                                <span class="action-btn assigned-tag">Assigned</span>
                                <br>
                                <a href="student_list.php?remove=<?= $s['id'] ?>" class="action-btn remove-room" onclick="return confirm('Remove room assignment?')">Remove</a>
                            <?php endif; ?>
                        </td>

                    </tr>
                <?php endforeach; ?>

            <?php else: ?>
                <tr>
                    <td colspan="9">No students found.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</body>

</html>
<?php
session_start();
require 'config.php';

// Check if admin logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

// Handle Approve/Reject actions
if(isset($_GET['action']) && isset($_GET['id'])){
    $id = (int)$_GET['id'];
    $action = $_GET['action'];

    if($action === 'approve'){
        $stmt = $pdo->prepare("UPDATE students SET status = 'approved' WHERE id = ?");
        $stmt->execute([$id]);
    } elseif($action === 'reject'){
        $stmt = $pdo->prepare("UPDATE students SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$id]);
    }
    header("Location: student_requests.php");
    exit;
}

// Fetch pending student requests
$stmt = $pdo->prepare("SELECT * FROM students WHERE status = 'pending' ORDER BY created_at DESC");
$stmt->execute();
$students = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Requests - HostelHive</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f3f4f6;
}
.container {
    margin-top: 40px;
}
.table th, .table td {
    vertical-align: middle;
}
a.btn {
    text-decoration: none;
}
.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
</style>
</head>
<body>

<div class="container">
    <div class="top-bar">
        <h2>Pending Student Requests</h2>
        <a href="admin_dashboard.php" class="btn btn-secondary btn-sm">⬅️ Back to Dashboard</a>
    </div>

    <?php if(count($students) === 0): ?>
        <div class="alert alert-info">No pending requests at the moment.</div>
    <?php else: ?>
        <table class="table table-bordered table-striped bg-white">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Room Preference</th>
                    <th>Applied At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($students as $index => $student): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($student['name']) ?></td>
                    <td><?= htmlspecialchars($student['email']) ?></td>
                    <td><?= htmlspecialchars($student['phone']) ?></td>
                    <td><?= htmlspecialchars($student['room_pref']) ?></td>
                    <td><?= $student['created_at'] ?></td>
                    <td>
                        <a href="student_requests.php?action=approve&id=<?= $student['id'] ?>" class="btn btn-success btn-sm" onclick="return confirm('Approve this student?');">Approve</a>
                        <a href="student_requests.php?action=reject&id=<?= $student['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Reject this student?');">Reject</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>

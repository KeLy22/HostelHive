<?php
session_start();
require 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

// Handle Approve Request
if (isset($_GET['approve'])) {
    $req_id = intval($_GET['approve']);

    // Fetch request info
    $query = $pdo->prepare("
        SELECT rcr.*, s.room_id AS student_current_room, 
               r_req.room_no AS req_room_no, r_req.capacity, r_req.occupied,
               s.name AS student_name
        FROM room_change_requests rcr
        JOIN students s ON rcr.student_id = s.id
        JOIN rooms r_req ON rcr.requested_room_id = r_req.id
        WHERE rcr.id = ?
    ");
    $query->execute([$req_id]);
    $request = $query->fetch();

    if ($request) {
        // Check room capacity
        if ($request['occupied'] < $request['capacity']) {

            // Reduce occupied count from old room
            if ($request['student_current_room']) {
                $pdo->prepare("UPDATE rooms SET occupied = occupied - 1 WHERE id = ?")
                    ->execute([$request['student_current_room']]);
            }

            // Increase occupied count in new room
            $pdo->prepare("UPDATE rooms SET occupied = occupied + 1 WHERE id = ?")
                ->execute([$request['requested_room_id']]);

            // Update student's assigned room
            $pdo->prepare("UPDATE students SET room_id = ? WHERE id = ?")
                ->execute([$request['requested_room_id'], $request['student_id']]);

            // Mark request as approved
            $pdo->prepare("UPDATE room_change_requests SET status='approved' WHERE id = ?")
                ->execute([$req_id]);

            $_SESSION['flash_msg'] = "✅ Room change approved for {$request['student_name']} (Room {$request['req_room_no']})!";
        } else {
            $_SESSION['flash_msg'] = "⚠ Cannot approve! Room {$request['req_room_no']} is full.";
        }
    }

    header("Location: student_list.php");
    exit;
}

// Handle Reject Request
if (isset($_GET['reject'])) {
    $req_id = intval($_GET['reject']);
    $pdo->prepare("UPDATE room_change_requests SET status='rejected' WHERE id = ?")
        ->execute([$req_id]);
    $_SESSION['flash_msg'] = "❌ Room change request rejected.";
    header("Location: student_list.php");
    exit;
}

// Fetch all room change requests
$stmt = $pdo->prepare("
    SELECT rcr.*, s.name AS student_name, s.email AS student_email,
           r1.room_no AS current_room_no,
           r2.room_no AS requested_room_no,
           r2.room_type AS requested_room_type
    FROM room_change_requests rcr
    JOIN students s ON rcr.student_id = s.id
    LEFT JOIN rooms r1 ON s.room_id = r1.id
    LEFT JOIN rooms r2 ON rcr.requested_room_id = r2.id
    ORDER BY rcr.created_at DESC
");
$stmt->execute();
$requests = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Room Change Requests - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f1f3f6;
            font-family: 'Segoe UI', sans-serif;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        table {
            background: white;
        }

        th {
            background-color: #2c3e50 !important;
            color: white !important;
        }

        .btn-custom {
            border-radius: 8px;
            padding: 5px 12px;
        }

        .btn-success {
            background-color: #27ae60 !important;
        }

        .btn-danger {
            background-color: #c0392b !important;
        }

        .back-btn {
            background-color: #34495e !important;
            color: white !important;
            border-radius: 8px;
        }

        .back-btn:hover {
            background-color: #2c3e50 !important;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <a href="admin_dashboard.php" class="btn btn-secondary btn-sm">⬅️ Back to Dashboard</a>

        <div class="card p-3">
            <h3 class="mb-3">All Room Change Requests</h3>

            <?php if (!empty($_SESSION['flash_msg'])): ?>
                <div class="alert alert-info"><?= htmlspecialchars($_SESSION['flash_msg']);
                                                unset($_SESSION['flash_msg']); ?></div>
            <?php endif; ?>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Email</th>
                        <th>Current Room</th>
                        <th>Requested Room</th>
                        <th>Requested Room Type</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Requested At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $req): ?>
                        <tr>
                            <td><?= htmlspecialchars($req['student_name']) ?></td>
                            <td><?= htmlspecialchars($req['student_email']) ?></td>
                            <td><?= $req['current_room_no'] ?? 'No Room Assigned' ?></td>
                            <td><?= htmlspecialchars($req['requested_room_no']) ?></td>
                            <td><?= htmlspecialchars($req['requested_room_type']) ?></td>
                            <td><?= htmlspecialchars($req['reason']) ?></td>
                            <td>
                                <?php if ($req['status'] == 'pending'): ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php elseif ($req['status'] == 'approved'): ?>
                                    <span class="badge bg-success">Approved</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $req['created_at'] ?></td>
                            <td>
                                <?php if ($req['status'] == 'pending'): ?>
                                    <a href="room_change_requests.php?approve=<?= $req['id'] ?>" class="btn btn-success btn-sm btn-custom">Approve</a>
                                    <a href="room_change_requests.php?reject=<?= $req['id'] ?>" class="btn btn-danger btn-sm btn-custom">Reject</a>
                                <?php else: ?>
                                    <span class="text-muted">No Action</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
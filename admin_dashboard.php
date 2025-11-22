<?php
session_start();
require 'config.php';

// Check if admin logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

// Fetch admin name
$stmt = $pdo->prepare("SELECT name FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();
$admin_name = $admin ? $admin['name'] : 'Admin';

// Real counts from database
$pending = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'pending'")->fetchColumn();
$total_students = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'approved'")->fetchColumn();
$total_rooms = $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn() ?? 0;

// Pending room change requests
$pending_room_change = $pdo->query("SELECT COUNT(*) FROM room_change_requests WHERE status = 'pending'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>HostelHive Admin Dashboard</title>
<style>
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f3f4f6;
    display: flex;
}

/* Sidebar */
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

/* Main Content */
.main-content {
    margin-left: 250px;
    padding: 20px;
    flex-grow: 1;
}

/* Header */
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #fff;
    padding: 15px 25px;
    border-radius: 10px;
    box-shadow: 0 0 5px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

/* Stats Cards */
.stats {
    display: flex;
    gap: 20px;
    margin-top: 25px;
}
.card {
    flex: 1;
    background-color: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    text-align: center;
}
.card h3 {
    margin: 0;
    color: #333;
}
.card p {
    font-size: 22px;
    color: #007bff;
    margin-top: 8px;
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
    <div class="header">
        <h2>Welcome, <?php echo htmlspecialchars($admin_name); ?>!</h2>
    </div>

    <div class="stats">
        <div class="card">
            <h3>Pending Requests</h3>
            <p><?php echo $pending; ?> new registrations</p>
        </div>
        <div class="card">
            <h3>Pending Room Change</h3>
            <p><?php echo $pending_room_change; ?> requests</p>
        </div>
        <div class="card">
            <h3>Total Students</h3>
            <p><?php echo $total_students; ?> approved students</p>
        </div>
        <div class="card">
            <h3>Rooms</h3>
            <p><?php echo $total_rooms; ?> total rooms</p>
        </div>
    </div>
</div>

</body>
</html>

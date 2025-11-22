<?php 
session_start();
require 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

// Handle delete room
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM rooms WHERE id = ?")->execute([$del_id]);
    header("Location: manage_rooms.php");
    exit;
}

// Handle add room
if (isset($_POST['add_room'])) {
    $room_no = isset($_POST['room_no']) ? trim($_POST['room_no']) : '';
    $room_type = isset($_POST['room_type']) ? $_POST['room_type'] : 'Shared';
    $capacity = isset($_POST['capacity']) ? intval($_POST['capacity']) : 2;
    $fee = isset($_POST['fee']) ? floatval($_POST['fee']) : 3000.00;

    if (!empty($room_no)) {
        $stmt = $pdo->prepare("INSERT INTO rooms (room_no, room_type, capacity, fee, occupied) VALUES (?, ?, ?, ?, 0)");
        $stmt->execute([$room_no, $room_type, $capacity, $fee]);
    }
    header("Location: manage_rooms.php");
    exit;
}

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search !== '') {
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE room_no LIKE ? OR room_type LIKE ? ORDER BY id DESC");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM rooms ORDER BY id DESC");
}

// Fetch rooms and calculate available seats
$rooms = [];
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $row['occupied'] = $row['occupied'] ?? 0; 
    $row['available'] = $row['capacity'] - $row['occupied'];
    $rooms[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Rooms - HostelHive</title>
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
h2 { margin-bottom: 20px; }

/* Table Styles */
table {
    width: 100%;
    border-collapse: collapse;
    background-color: white;
}
table th, table td {
    padding: 12px;
    border: 1px solid #ddd;
    text-align: center;
}
table th {
    background-color: #007bff;
    color: white;
}

/* Form Styles */
form {
    margin-bottom: 20px;
    background: white;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
form input, form select {
    padding: 10px;
    margin-right: 10px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}
form button {
    padding: 10px 20px;
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 5px;
}
form button:hover {
    background-color: #0056b3;
}

/* Search Form */
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
    font-size: 16px;
    box-sizing: border-box;
}
form.search-form button,
form.search-form .reset-btn {
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    font-size: 16px;
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

/* Edit/Delete Buttons */
a.edit, a.delete {
    padding: 5px 10px;
    border-radius: 5px;
    color: white;
    text-decoration: none;
}
a.edit { background-color: #28a745; }
a.delete { background-color: #dc3545; }
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
    <h2>Manage Rooms</h2>

    <!-- Add Room Form -->
    <form method="post">
        <input type="text" name="room_no" placeholder="Room No" required>
        <select name="room_type" required>
            <option value="Shared">Shared</option>
            <option value="Single">Single</option>
            <option value="Double">Double</option>
        </select>
        <input type="number" name="capacity" placeholder="Capacity" value="1" required>
        <input type="number" step="0.01" name="fee" placeholder="Fee" value="3000.00" required>
        <button type="submit" name="add_room">Add Room</button>
    </form>

    <!-- Search Form -->
    <form method="get" class="search-form">
        <input type="text" name="search" placeholder="Search by Room No or Type" value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
        <button type="button" onclick="window.location='manage_rooms.php'" class="reset-btn">Reset</button>
    </form>

    <!-- Rooms Table -->
    <table>
        <tr>
            <th>ID</th>
            <th>Room No</th>
            <th>Type</th>
            <th>Capacity</th>
            <th>Occupied</th>
            <th>Available</th>
            <th>Fee</th>
            <th>Actions</th>
        </tr>
        <?php if($rooms): ?>
            <?php foreach($rooms as $room): ?>
            <tr>
                <td><?= $room['id'] ?></td>
                <td><?= htmlspecialchars($room['room_no']) ?></td>
                <td><?= htmlspecialchars($room['room_type']) ?></td>
                <td><?= $room['capacity'] ?></td>
                <td><?= $room['occupied'] ?></td>
                <td><?= $room['available'] ?></td>
                <td><?= $room['fee'] ?></td>
                <td>
                    <a href="edit_room.php?id=<?= $room['id'] ?>" class="edit">Edit</a>
                    <a href="manage_rooms.php?delete=<?= $room['id'] ?>" class="delete" onclick="return confirm('Are you sure to delete this room?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="8">No rooms found.</td></tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>

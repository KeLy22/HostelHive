<?php
session_start();
require 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

// Get room ID from URL
$room_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch room details
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id=?");
$stmt->execute([$room_id]);
$room = $stmt->fetch();

if (!$room) {
    echo "Room not found!";
    exit;
}

// Handle form submission
if (isset($_POST['update_room'])) {
    $room_no = isset($_POST['room_no']) ? trim($_POST['room_no']) : '';
    $room_type = isset($_POST['room_type']) ? $_POST['room_type'] : 'Shared';
    $capacity = isset($_POST['capacity']) ? intval($_POST['capacity']) : 2;
    $fee = isset($_POST['fee']) ? floatval($_POST['fee']) : 3000.00;

    if (!empty($room_no)) {
        $stmt = $pdo->prepare("UPDATE rooms SET room_no=?, room_type=?, capacity=?, fee=? WHERE id=?");
        $stmt->execute([$room_no, $room_type, $capacity, $fee, $room_id]);
        header("Location: manage_rooms.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Room - HostelHive</title>
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f3f4f6;
    padding: 50px;
}
form {
    background: white;
    padding: 20px;
    border-radius: 10px;
    max-width: 400px;
    margin: auto;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

/* Make inputs and selects the same size */
form input,
form select {
    width: 100%;
    padding: 12px;           
    margin-bottom: 15px;
    box-sizing: border-box;   
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
}

/* Button styling */
form button {
    width: 100%;
    padding: 12px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
}

form button:hover {
    background-color: #0056b3;
}

/* Back link styling */
a.back {
    display: block;
    text-align: center;
    margin-bottom: 20px;
    color: #007bff;
    text-decoration: none;
    font-size: 16px;
}

a.back:hover {
    text-decoration: underline;
}
</style>

</head>
<body>

<a href="manage_rooms.php" class="back">⬅️ Back to Rooms</a>

<h2 style="text-align:center;">Edit Room: <?= htmlspecialchars($room['room_no']) ?></h2>

<form method="post">
    <input type="text" name="room_no" value="<?= htmlspecialchars($room['room_no']) ?>" placeholder="Room No" required>
    <select name="room_type" required>
        <option value="Shared" <?= $room['room_type']=='Shared'?'selected':'' ?>>Shared</option>
        <option value="Single" <?= $room['room_type']=='Single'?'selected':'' ?>>Single</option>
        <option value="Double" <?= $room['room_type']=='Double'?'selected':'' ?>>Double</option>
    </select>
    <input type="number" name="capacity" value="<?= $room['capacity'] ?>" placeholder="Capacity" required>
    <input type="number" step="0.01" name="fee" value="<?= $room['fee'] ?>" placeholder="Fee" required>
    <button type="submit" name="update_room">Update Room</button>
</form>

</body>
</html>

<?php
session_start();
require 'config.php';

// Check if admin logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

// Get student ID
$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch student info
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    echo "Student not found!";
    exit;
}

// Fetch available preferred rooms
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE room_type = ? AND occupied < capacity");
$stmt->execute([$student['room_pref']]);
$preferred_rooms = $stmt->fetchAll();

// If no preferred rooms available, get alternative ones
$alt_rooms = [];
if (empty($preferred_rooms)) {
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE occupied < capacity");
    $stmt->execute();
    $alt_rooms = $stmt->fetchAll();
}

// Handle room assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = intval($_POST['room_id']);

    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();

    if ($room && $room['occupied'] < $room['capacity']) {
        try {
            $pdo->beginTransaction();

            // Update student info
            $update_student = $pdo->prepare("UPDATE students SET room_id = ?, room_no = ?, status = 'approved' WHERE id = ?");
            $update_student->execute([$room['id'], $room['room_no'], $student_id]);

            // Update room occupied count
            $update_room = $pdo->prepare("UPDATE rooms SET occupied = occupied + 1 WHERE id = ?");
            $update_room->execute([$room['id']]);

            $pdo->commit();

            header("Location: student_list.php");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "<script>alert('Selected room is not available.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Assign Room - HostelHive</title>
<style>
body {
    font-family: 'Segoe UI', sans-serif;
    background-color: #f3f4f6;
    padding: 50px;
}
.container {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    max-width: 550px;
    margin: auto;
}
h2 { text-align: center; color: #333; }
select, button {
    width: 100%;
    padding: 10px;
    margin-top: 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
}
button {
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
}
button:hover { background-color: #0056b3; }
section { margin-top: 20px; }
.note {
    color: red;
    text-align: center;
    margin-top: 10px;
}
a.back {
    display: block;
    text-align: center;
    margin-bottom: 15px;
    color: #007bff;
    text-decoration: none;
}
a.back:hover { text-decoration: underline; }
</style>
</head>
<body>

<a href="student_list.php" class="back">⬅️ Back to Student List</a>

<div class="container">
    <h2>Assign Room to <?= htmlspecialchars($student['name']) ?></h2>
    <p><strong>Preferred Room Type:</strong> <?= htmlspecialchars($student['room_pref']) ?></p>

    <form method="post">
        <label>Select Room:</label>
        <select name="room_id" required>
            <option value="">-- Select Room --</option>

            <?php if (!empty($preferred_rooms)): ?>
                <optgroup label="Preferred Rooms (<?= htmlspecialchars($student['room_pref']) ?>)">
                    <?php foreach ($preferred_rooms as $room): ?>
                        <option value="<?= $room['id'] ?>">
                            <?= htmlspecialchars($room['room_no']) ?> - <?= htmlspecialchars($room['room_type']) ?> (<?= $room['capacity'] - $room['occupied'] ?> left)
                        </option>
                    <?php endforeach; ?>
                </optgroup>
            <?php endif; ?>

            <?php if (!empty($alt_rooms)): ?>
                <optgroup label="Alternative Available Rooms">
                    <?php foreach ($alt_rooms as $room): ?>
                        <option value="<?= $room['id'] ?>">
                            <?= htmlspecialchars($room['room_no']) ?> - <?= htmlspecialchars($room['room_type']) ?> (<?= $room['capacity'] - $room['occupied'] ?> left)
                        </option>
                    <?php endforeach; ?>
                </optgroup>
            <?php endif; ?>
        </select>

        <button type="submit">Assign Room</button>
    </form>

    <?php if (empty($preferred_rooms) && empty($alt_rooms)): ?>
        <p class="note">❌ No rooms available at the moment.</p>
    <?php endif; ?>
</div>

</body>
</html>

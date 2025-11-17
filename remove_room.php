<?php
session_start();
require 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

// Get student ID
$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch student info
$stmt = $pdo->prepare("SELECT room_no FROM students WHERE id=?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if ($student && $student['room_no']) {
    $room_no = $student['room_no'];

    // Decrease the room occupancy by 1 (if not already 0)
    $stmt = $pdo->prepare("UPDATE rooms SET occupied = GREATEST(occupied - 1, 0) WHERE room_no = ?");
    $stmt->execute([$room_no]);

    // Remove room assignment from student
    $stmt = $pdo->prepare("UPDATE students SET room_no=NULL, room_id=NULL WHERE id=?");
    $stmt->execute([$student_id]);
}

// Redirect back to student list
header("Location: student_list.php");
exit;
?>

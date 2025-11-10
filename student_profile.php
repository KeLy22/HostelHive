<?php
session_start();
require 'config.php';

// Check if student logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}

$student_id = $_SESSION['student_id'];

// Fetch student details
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

// Handle profile update
$profile_msg = '';
if (isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];

    $stmt = $pdo->prepare("UPDATE students SET name = ?, email = ? WHERE id = ?");
    if ($stmt->execute([$name, $email, $student_id])) {
        $profile_msg = "Profile updated successfully!";
        $student['name'] = $name;
        $student['email'] = $email;
    } else {
        $profile_msg = "Failed to update profile.";
    }
}

// Handle password update
$pass_msg = '';
if (isset($_POST['update_password'])) {
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if (password_verify($current_pass, $student['password'])) {
        if ($new_pass === $confirm_pass) {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE students SET password = ? WHERE id = ?");
            if ($stmt->execute([$hashed, $student_id])) {
                $pass_msg = "Password updated successfully!";
            } else {
                $pass_msg = "Failed to update password.";
            }
        } else {
            $pass_msg = "New passwords do not match.";
        }
    } else {
        $pass_msg = "Current password is incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Profile Settings - HostelHive</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f3f4f6;
            display: flex;
        }

        .sidebar {
            width: 230px;
            background-color: #2c3e50;
            color: white;
            height: 100vh;
            position: fixed;
            display: flex;
            flex-direction: column;
        }

        .sidebar h2 {
            text-align: center;
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
            margin-left: 230px;
            padding: 20px;
            flex-grow: 1;
        }

        .section {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .section h3 {
            margin-top: 0;
        }

        .section form {
            display: flex;
            flex-direction: column;
        }

        .section form input {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .section form button {
            padding: 10px;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }

        .section form button:hover {
            background-color: #0056b3;
        }

        .msg {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            color: white;
        }

        .success {
            background-color: #28a745;
        }

        .error {
            background-color: #dc3545;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <h2>HostelHive Student</h2>
        <a href="student_dashboard.php">🏠 Dashboard</a>
        <a href="my_room.php">🏘️ My Room</a>
        <a href="profile.php">⚙️ Profile Settings</a>
        <a href="logout.php">🚪 Logout</a>
    </div>

    <div class="main-content">
        <h2>Profile Settings</h2>

        <div class="section">
            <h3>Update Profile</h3>
            <?php if ($profile_msg): ?>
                <div class="msg <?= strpos($profile_msg, 'success') !== false ? 'success' : 'error'; ?>">
                    <?= $profile_msg ?>
                </div>
            <?php endif; ?>
            <form method="post">
                <input type="text" name="name" placeholder="Full Name" value="<?= htmlspecialchars($student['name']); ?>" required>
                <input type="email" name="email" placeholder="Email Address" value="<?= htmlspecialchars($student['email']); ?>" required>
                <button type="submit" name="update_profile">Update Profile</button>
            </form>
        </div>

        <div class="section">
            <h3>Change Password</h3>
            <?php if ($pass_msg): ?>
                <div class="msg <?= strpos($pass_msg, 'success') !== false ? 'success' : 'error'; ?>">
                    <?= $pass_msg ?>
                </div>
            <?php endif; ?>
            <form method="post">
                <input type="password" name="current_password" placeholder="Current Password" required>
                <input type="password" name="new_password" placeholder="New Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
                <button type="submit" name="update_password">Update Password</button>
            </form>
        </div>
    </div>
</body>

</html>
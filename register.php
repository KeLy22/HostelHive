<?php
require 'config.php';

$errors = [];
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $room_pref = trim($_POST['room_pref'] ?? '');

    if(!$name || !$email || !$password){
        $errors[] = 'Name, email and password are required.';
    }

    $stmt = $pdo->prepare('SELECT id FROM students WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    if($stmt->fetch()){
        $errors[] = 'Email already registered as a student.';
    }

    $stmt = $pdo->prepare('SELECT id FROM admins WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    if($stmt->fetch()){
        $errors[] = 'This email is reserved (admin). Use another email.';
    }

    if(empty($errors)){
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO students (name, email, password, phone, room_pref) VALUES (?,?,?,?,?)');
        $stmt->execute([$name, $email, $hash, $phone, $room_pref]);
        $success = 'Registration submitted. Wait for admin approval.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Student Registration - HostelHive</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
body {
  background: linear-gradient(135deg, #007bff, #6610f2);
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  margin: 0;
  color: #333;
}
.register-box {
  background: #fff;
  padding: 18px 25px; /* compact vertical padding */
  border-radius: 15px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.2);
  width: 100%;
  max-width: 360px;
}
.register-box h2 {
  text-align: center;
  margin-bottom: 12px; /* smaller spacing */
  font-weight: bold;
  color: #007bff;
}
.form-control:focus {
  box-shadow: none;
  border-color: #007bff;
}
.mb-3 {
  margin-bottom: 8px; /* smaller spacing between fields */
}
.btn-primary {
  width: 100%;
  background: #007bff;
  border: none;
}
.links {
  text-align: center;
  margin-top: 8px; /* smaller spacing above links */
}
a {
  color: #007bff;
  text-decoration: none;
}
a:hover {
  text-decoration: underline;
}
.position-relative .bi {
  position: absolute;
  top: 30px; /* adjust eye icon position */
  right: 12px;
  cursor: pointer;
}
</style>
</head>
<body>

<div class="register-box">
  <h2>Student Registration</h2>

  <?php if($errors): ?>
      <?php foreach($errors as $e) echo '<div class="alert alert-danger">'.htmlspecialchars($e).'</div>'; ?>
  <?php endif; ?>

  <?php if($success): ?>
      <div class="alert alert-success"><?=htmlspecialchars($success)?></div>
  <?php endif; ?>

  <form method="post">
    <div class="mb-3">
      <label>Full Name</label>
      <input type="text" class="form-control" name="name" required>
    </div>

    <div class="mb-3">
      <label>Email</label>
      <input type="email" class="form-control" name="email" required>
    </div>

    <div class="mb-3 position-relative">
      <label>Password</label>
      <input type="password" class="form-control" name="password" id="reg_password" required>
      <i class="bi bi-eye-fill" id="toggleRegPass"></i>
    </div>

    <div class="mb-3">
      <label>Phone</label>
      <input type="text" class="form-control" name="phone">
    </div>

    <div class="mb-3">
      <label>Room preference</label>
      <select class="form-control" name="room_pref" required>
        <option value="">Select Room Preference</option>
        <option value="single">Single</option>
        <option value="double">Double</option>
      </select>
    </div>

    <button type="submit" class="btn btn-primary">Register</button>
  </form>

  <div class="links mt-2">
    <p><a href="index.php">Back to Login</a></p>
  </div>
</div>

<script>
const toggleRegPass = document.getElementById('toggleRegPass');
const regPassword = document.getElementById('reg_password');
toggleRegPass.addEventListener('click', () => {
  if(regPassword.type === "password"){
    regPassword.type = "text";
    toggleRegPass.classList.replace('bi-eye-fill','bi-eye-slash-fill');
  } else {
    regPassword.type = "password";
    toggleRegPass.classList.replace('bi-eye-slash-fill','bi-eye-fill');
  }
});
</script>

</body>
</html>

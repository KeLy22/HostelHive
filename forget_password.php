<?php
require 'config.php';

$error = '';
$show_reset_form = false;
$email = '';
$username = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(isset($_POST['check_email'])){
        $email = trim($_POST['email'] ?? '');
        if(!$email){
            $error = 'Please enter your email.';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            $user_type = '';
            
            if($user){
                $user_type = 'student';
            } else {
                $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                if($user){
                    $user_type = 'admin';
                } else {
                    $error = 'No account found with this email.';
                }
            }

            if($user){
                $username = $user['name'];
                $show_reset_form = true;
            }
        }
    }

    if(isset($_POST['reset_password'])){
        $email = $_POST['email'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if(!$new_password || !$confirm_password){
            $error = 'Enter both password fields.';
            $show_reset_form = true;
        } elseif($new_password !== $confirm_password){
            $error = 'Passwords do not match.';
            $show_reset_form = true;
        } else {
            $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if($user){
                $stmt = $pdo->prepare("UPDATE students SET password = ? WHERE email = ?");
                $stmt->execute([password_hash($new_password, PASSWORD_DEFAULT), $email]);
            } else {
                $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE email = ?");
                $stmt->execute([password_hash($new_password, PASSWORD_DEFAULT), $email]);
            }

            $success = 'Password reset successful. You can now <a href="index.php">login</a>.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Forgot Password - HostelHive</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
body {
  background: linear-gradient(135deg, #007bff, #6610f2);
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  color: #333;
}
.reset-box {
  background: #fff;
  padding: 30px 40px;
  border-radius: 15px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.2);
  width: 100%;
  max-width: 400px;
}
.reset-box h2 {
  text-align: center;
  margin-bottom: 25px;
  font-weight: bold;
  color: #007bff;
}
.form-control:focus {
  box-shadow: none;
  border-color: #007bff;
}
.btn-primary {
  width: 100%;
  background: #007bff;
  border: none;
}
.links {
  text-align: center;
  margin-top: 15px;
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
  top: 38px;
  right: 15px;
  cursor: pointer;
}
</style>
</head>
<body>

<div class="reset-box">
  <h2>Forgot Password</h2>

  <?php if($error): ?>
      <div class="alert alert-danger"><?=htmlspecialchars($error)?></div>
  <?php endif; ?>

  <?php if($success): ?>
      <div class="alert alert-success"><?=$success?></div>
  <?php endif; ?>

  <?php if(!$show_reset_form && !$success): ?>
    <form method="post">
      <div class="mb-3">
        <label>Enter your registered email:</label>
        <input type="email" class="form-control" name="email" required>
      </div>
      <button type="submit" class="btn btn-primary" name="check_email">Next</button>
    </form>
  <?php endif; ?>

  <?php if($show_reset_form): ?>
    <p>Email found! Username: <b><?=htmlspecialchars($username)?></b></p>
    <form method="post">
      <input type="hidden" name="email" value="<?=htmlspecialchars($email)?>">

      <div class="mb-3 position-relative">
        <label>New Password:</label>
        <input type="password" class="form-control" name="new_password" id="new_password" required>
        <i class="bi bi-eye-fill" id="toggleNewPass"></i>
      </div>

      <div class="mb-3 position-relative">
        <label>Confirm Password:</label>
        <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
        <i class="bi bi-eye-fill" id="toggleConfirmPass"></i>
      </div>

      <button type="submit" class="btn btn-primary" name="reset_password">Reset Password</button>
    </form>
  <?php endif; ?>

  <div class="links mt-3">
    <p><a href="index.php">Back to Login</a></p>
  </div>
</div>

<script>
const toggleNewPass = document.getElementById('toggleNewPass');
const newPassword = document.getElementById('new_password');
toggleNewPass.addEventListener('click', () => {
  if(newPassword.type === "password"){
    newPassword.type = "text";
    toggleNewPass.classList.replace('bi-eye-fill','bi-eye-slash-fill');
  } else {
    newPassword.type = "password";
    toggleNewPass.classList.replace('bi-eye-slash-fill','bi-eye-fill');
  }
});

const toggleConfirmPass = document.getElementById('toggleConfirmPass');
const confirmPassword = document.getElementById('confirm_password');
toggleConfirmPass.addEventListener('click', () => {
  if(confirmPassword.type === "password"){
    confirmPassword.type = "text";
    toggleConfirmPass.classList.replace('bi-eye-fill','bi-eye-slash-fill');
  } else {
    confirmPassword.type = "password";
    toggleConfirmPass.classList.replace('bi-eye-slash-fill','bi-eye-fill');
  }
});
</script>

</body>
</html>

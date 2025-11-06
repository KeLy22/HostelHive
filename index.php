<?php
session_start();
require 'config.php';

$error = '';

// Handle login form submission
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check student first
    $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $student = $stmt->fetch();

    if($student){
        if($student['status'] !== 'approved'){
            $error = "Your account is not approved yet.";
        } elseif(password_verify($password, $student['password'])){
            $_SESSION['student_id'] = $student['id'];
            header("Location: student_dashboard.php");
            exit;
        } else {
            $error = "Invalid credentials.";
        }
    } else {
        // Check admin
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if($admin && password_verify($password, $admin['password'])){
            $_SESSION['admin_id'] = $admin['id'];
            header("Location: admin_dashboard.php");
            exit;
        } else {
            $error = "Invalid credentials.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>HostelHive - Login</title>
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
.login-box {
    background: #fff;
    padding: 30px 40px;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    width: 100%;
    max-width: 400px;
}
.login-box h2 {
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

<div class="login-box">
<h2>HostelHive Login</h2>

<?php if($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post">
    <div class="mb-3">
        <label>Email</label>
        <input type="email" class="form-control" name="email" required>
    </div>

    <div class="mb-3 position-relative">
        <label>Password</label>
        <input type="password" class="form-control" name="password" id="login_password" required>
        <i class="bi bi-eye-fill" id="toggleLoginPass"></i>
    </div>

    <button type="submit" class="btn btn-primary">Login</button>
</form>

<div class="links mt-3">
    <p><a href="register.php">New Student? Register here</a></p>
    <p><a href="forget_password.php">Forgot Password?</a></p> 
</div>
</div>

<script>
const toggleLoginPass = document.getElementById('toggleLoginPass');
const loginPassword = document.getElementById('login_password');
toggleLoginPass.addEventListener('click', () => {
    if(loginPassword.type === "password"){
        loginPassword.type = "text";
        toggleLoginPass.classList.replace('bi-eye-fill','bi-eye-slash-fill');
    } else {
        loginPassword.type = "password";
        toggleLoginPass.classList.replace('bi-eye-slash-fill','bi-eye-fill');
    }
});
</script>

</body>
</html>

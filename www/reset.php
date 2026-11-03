<?php
require 'db.php';
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $newPass = $_POST['new_password'];
    if (!empty($email) && !empty($newPass)) {
        $stmt = $pdo->prepare("SELECT userID FROM users WHERE Email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $newHash = password_hash($newPass, PASSWORD_BCRYPT);
            $update = $pdo->prepare("UPDATE users SET Password = ? WHERE Email = ?");
            $update->execute([$newHash, $email]);
            $message = "<div class='alert alert-success'>Password updated successfully! <a href='login.php' class='alert-link'>Login here</a></div>";
        } else {
            $message = "<div class='alert alert-danger'>No account found with that email address.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reset Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-5">
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <h2 class="h4 mb-4">Reset Account Password</h2>
          <?php echo $message; ?>
          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Enter Account Email</label>
              <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Enter New Password</label>
              <input type="password" name="new_password" class="form-control" required>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-secondary">Update Password Hash</button>
            </div>
          </form>
          <p class="small mt-3 mb-0"><a href="login.php">Return to Login</a></p>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
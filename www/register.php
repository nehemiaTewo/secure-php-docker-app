<?php
require 'db.php';
require 'mailer.php';
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = trim($_POST['firstname']);
    $last = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $pass = $_POST['password'];
    if (!empty($first) && !empty($last) && !empty($email) && !empty($pass)) {
        try {
            $stmt = $pdo->prepare("SELECT userID FROM users WHERE Email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $message = "<div class='alert alert-danger'>Email already registered!</div>";
            } else {
                $hashedPass = password_hash($pass, PASSWORD_BCRYPT);
                $verificationToken = bin2hex(random_bytes(16));
                $insert = $pdo->prepare("INSERT INTO users (FirstName, LastName, Email, Password, Status, EmailVerified, EmailVerificationToken, RegDatetime) VALUES (?, ?, ?, ?, 0, 0, ?, NOW())");
                $insert->execute([$first, $last, $email, $hashedPass, $verificationToken]);

                // EXTRA CREDIT: Construct and fire a real automated verification link
                $verifyLink = "http://localhost:8080/verify_email.php?token=" . $verificationToken;
                $emailSubject = "Activate Your Docker Core Account Profile";
                $emailContent = "
                    <h3>Welcome to the 3-Tier Laboratory Stack, " . htmlspecialchars($first) . "!</h3>
                    <p>Please click the verification link below to activate your account:</p>
                    <p><a href='{$verifyLink}' style='background:#0076ff; color:#fff; padding:10px; text-decoration:none; border-radius:4px; display:inline-block;'>Verify My Email Address</a></p>
                    <br>
                    <small>Token verification hash signature: <code>{$verificationToken}</code></small>
                ";
                if (sendSystemMail($email, "{$first} {$last}", $emailSubject, $emailContent)) {
                    $message = "<div class='alert alert-success'>Registration successful! A real verification link has been dispatched. Check your Mailtrap inbox to validate your access profile.</div>";
                } else {
                    $message = "<div class='alert alert-warning'>Profile built, but the system failed to send the verification email.</div>";
                }
            }
        } catch (\PDOException $e) {
            $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>All fields are required.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Register & Verify Stack</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-5">
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <h2 class="h4 mb-4">Step 1: Create Account Portal</h2>
          <?php echo $message; ?>
          <form method="POST">
            <div class="row g-3">
              <div class="col-sm-6">
                <label class="form-label">First Name</label>
                <input type="text" name="firstname" class="form-control" required>
              </div>
              <div class="col-sm-6">
                <label class="form-label">Last Name</label>
                <input type="text" name="lastname" class="form-control" required>
              </div>
              <div class="col-12">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required>
              </div>
              <div class="col-12">
                <label class="form-label">Password Asset</label>
                <input type="password" name="password" class="form-control" required>
              </div>
              <div class="col-12 d-grid">
                <button type="submit" class="btn btn-primary">Register Core Account</button>
              </div>
            </div>
          </form>
          <hr>
          <p class="small mb-1">Looking to verify an existing link? <a href="verify_email.php">Manual Verification Input Terminal</a></p>
          <p class="small mb-0">Need admin access? <a href="adminRegister.php">Apply as an admin</a> · Already registered? <a href="login.php">Log in</a></p>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
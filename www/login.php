<?php
require 'db.php';
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $pass = $_POST['password'];
    if (!empty($email) && !empty($pass)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE Email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($pass, $user['Password'])) {
            if ($user['EmailVerified'] == 0) {
                $message = "<div class='alert alert-danger'>&#10060; Login Blocked: You must complete your email verification processes before initializing logins. Check your Mailtrap inbox for the verification link.</div>";
            } else {
                // Hold the user in a pending state until the TOTP code is verified
                $_SESSION['2fa_pending_userID'] = $user['userID'];
                // First time: set up the authenticator app. Otherwise: ask for the code.
                header("Location: " . (empty($user['TotpSecret']) ? "setup_2fa.php" : "verify_2fa.php"));
                exit;
            }
        } else {
            $message = "<div class='alert alert-danger'>Invalid email or password parameters.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>System Login Proxy</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-5">
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <h2 class="h4 mb-4">Account Sign-In Terminal</h2>
          <?php echo $message; ?>
          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Registered Email Address</label>
              <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Secure Password Key</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary">Request Login Handshake</button>
            </div>
          </form>
          <hr>
          <div class="d-flex justify-content-between small">
            <a href="register.php">Create an account</a>
            <a href="reset.php">Forgot password?</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
<?php
require 'db.php';
$message = "";
// Auto-catch parameters if passed inside an active query link string
$tokenParam = $_GET['token'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' || !empty($tokenParam)) {
    $token = !empty($tokenParam) ? trim($tokenParam) : trim($_POST['token']);
    if (!empty($token)) {
        $stmt = $pdo->prepare("SELECT userID FROM users WHERE EmailVerificationToken = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        if ($user) {
            // Null out token value and update validation metrics flag
            $update = $pdo->prepare("UPDATE users SET EmailVerified = 1, EmailVerificationToken = NULL WHERE userID = ?");
            $update->execute([$user['userID']]);
            $message = "<div class='alert alert-success'>&#10004; Success: Email authenticated cleanly! You may now navigate to the <a href='login.php' class='alert-link'>Login Terminal</a>.</div>";
        } else {
            $message = "<div class='alert alert-danger'>&#10060; Error: Invalid, structural mismatch, or expired verification token profile.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Activation Core Terminal</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-5">
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <h2 class="h4 mb-4">Step 2: Email Authentication Verification</h2>
          <?php echo $message; ?>
          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Paste Simulated Email Token String</label>
              <input type="text" name="token" class="form-control font-monospace" value="<?php echo htmlspecialchars($tokenParam); ?>" required>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-success">Verify Email Authenticity</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
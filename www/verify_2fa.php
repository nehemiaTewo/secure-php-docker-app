<?php
require 'db.php';
require 'GoogleAuthenticator.php';

if (!isset($_SESSION['2fa_pending_userID'])) {
    header("Location: login.php");
    exit;
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userPin = trim($_POST['pin']);
    $userId  = $_SESSION['2fa_pending_userID'];

    $stmt = $pdo->prepare("SELECT TotpSecret, FirstName FROM users WHERE userID = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    $ga = new PHPGangsta_GoogleAuthenticator();

    // 1 = accept codes from one 30-second step before or after now (clock drift)
    $checkResult = $ga->verifyCode($user['TotpSecret'], $userPin, 1);

    if ($checkResult) {
        $update = $pdo->prepare("UPDATE users SET LoginDatetime = NOW(), Status = 1 WHERE userID = ?");
        $update->execute([$userId]);

        $_SESSION['userID']    = $userId;
        $_SESSION['FirstName'] = $user['FirstName'];
        unset($_SESSION['2fa_pending_userID']);

        header("Location: dashboard.php");
        exit;
    } else {
        $message = "<div class='alert alert-danger'>&#10060; Access Denied: Invalid or expired authenticator code.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>MFA Checkpoint</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-5">
      <div class="card shadow-sm text-center">
        <div class="card-body p-4">
          <h2 class="h4">&#128272; Device Authenticator Checkpoint</h2>
          <p>Open your authenticator app and enter the current 6-digit code:</p>
          <?php echo $message; ?>
          <form method="POST" class="mx-auto" style="max-width:320px;">
            <input type="text" name="pin" maxlength="6" required autocomplete="off" placeholder="000000"
                   class="form-control form-control-lg text-center font-monospace mb-3">
            <div class="d-grid">
              <button type="submit" class="btn btn-primary btn-lg">Verify Authenticator Code</button>
            </div>
          </form>
          <p class="small mt-3 mb-0"><a href="login.php">&laquo; Back to Login</a></p>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
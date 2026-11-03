<?php
require 'db.php';
require 'GoogleAuthenticator.php';

if (!isset($_SESSION['2fa_pending_userID'])) {
    header("Location: login.php");
    exit;
}

$userId  = $_SESSION['2fa_pending_userID'];
$ga      = new PHPGangsta_GoogleAuthenticator();
$message = "";

$stmt = $pdo->prepare("SELECT Email, FirstName FROM users WHERE userID = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Hold the new secret in the session until the user confirms a code
if (empty($_SESSION['2fa_setup_secret'])) {
    $_SESSION['2fa_setup_secret'] = $ga->createSecret();
}
$secretKey = $_SESSION['2fa_setup_secret'];

$appName    = "DockerLab-3Tier";
$otpauthUri = "otpauth://totp/" . rawurlencode($appName) . ":" . rawurlencode($user['Email'])
            . "?secret={$secretKey}&issuer=" . rawurlencode($appName);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codeAttempt = trim($_POST['code']);

    if ($ga->verifyCode($secretKey, $codeAttempt, 1)) {
        // Code matched: save the secret permanently and log in
        $update = $pdo->prepare("UPDATE users SET TotpSecret = ?, LoginDatetime = NOW(), Status = 1 WHERE userID = ?");
        $update->execute([$secretKey, $userId]);

        $_SESSION['userID']    = $userId;
        $_SESSION['FirstName'] = $user['FirstName'];
        unset($_SESSION['2fa_pending_userID'], $_SESSION['2fa_setup_secret']);

        header("Location: dashboard.php");
        exit;
    } else {
        $message = "<div class='alert alert-danger'>&#10060; Verification failed. Check your phone's time and try again.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Authenticator Setup</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-5">
      <div class="card shadow-sm text-center">
        <div class="card-body p-4">
          <h2 class="h4">Set Up Your Authenticator</h2>
          <p>Scan this with <strong>Google Authenticator</strong> or any TOTP app:</p>
          <div class="d-flex justify-content-center my-3 p-3 bg-light border rounded"><div id="qrcode"></div></div>
          <p class="small text-body-secondary">Or enter this key manually:<br>
            <code class="fs-6 fw-bold"><?php echo htmlspecialchars($secretKey); ?></code></p>
          <?php echo $message; ?>
          <form method="POST" class="mx-auto" style="max-width:320px;">
            <label class="form-label fw-semibold">Enter the 6-digit code from your app</label>
            <input type="text" name="code" maxlength="6" required autocomplete="off" placeholder="000000"
                   class="form-control form-control-lg text-center font-monospace mb-3">
            <div class="d-grid">
              <button type="submit" class="btn btn-success btn-lg">Verify and Finish Setup</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  new QRCode(document.getElementById("qrcode"), { text: <?php echo json_encode($otpauthUri); ?>, width: 200, height: 200 });
</script>
</body>
</html>
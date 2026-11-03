<?php
require 'db.php';
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit;
}
$stmt = $pdo->prepare("SELECT * FROM users WHERE userID = ?");
$stmt->execute([$_SESSION['userID']]);
$profile = $stmt->fetch();
$roleColors = ['admin' => 'danger', 'pending' => 'warning', 'user' => 'secondary'];
$roleColor = $roleColors[$profile['Role']] ?? 'secondary';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-10 col-lg-7">
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <h2 class="h4">Welcome, <?php echo htmlspecialchars($profile['FirstName'] . " " . $profile['LastName']); ?>!</h2>
          <p class="mb-4">
            <span class="badge text-bg-success">Session Authenticated (Status Code: <?php echo $profile['Status']; ?>)</span>
            <span class="badge text-bg-<?php echo $roleColor; ?>">Role: <?php echo htmlspecialchars($profile['Role']); ?></span>
          </p>

          <?php if ($profile['Role'] === 'admin'): ?>
            <div class="alert alert-info">You have admin access. <a href="admin.php" class="alert-link">Open the admin console</a>.</div>
          <?php elseif ($profile['Role'] === 'pending'): ?>
            <div class="alert alert-warning">Your admin application is waiting for approval by an existing admin.</div>
          <?php endif; ?>

          <h3 class="h6 text-uppercase text-body-secondary">System Diagnostics Metrics</h3>
          <ul class="list-group mb-4">
            <li class="list-group-item d-flex justify-content-between"><span>User Account ID</span><strong><?php echo $profile['userID']; ?></strong></li>
            <li class="list-group-item d-flex justify-content-between"><span>Registered On</span><span><?php echo $profile['RegDatetime']; ?></span></li>
            <li class="list-group-item d-flex justify-content-between"><span>Last Login Captured</span><span><?php echo $profile['LoginDatetime']; ?></span></li>
            <li class="list-group-item d-flex justify-content-between"><span>Previous Logout Captured</span><span><?php echo $profile['LogoutDatetime'] ?? 'Never Logged Out'; ?></span></li>
          </ul>
          <a href="logout.php" class="btn btn-danger">Secure Logout</a>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
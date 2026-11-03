<?php
require 'db.php';

// RBAC: only logged-in users whose Role is 'admin' may view this page
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit;
}
$roleCheck = $pdo->prepare("SELECT Role FROM users WHERE userID = ?");
$roleCheck->execute([$_SESSION['userID']]);
if ($roleCheck->fetchColumn() !== 'admin') {
    http_response_code(403);
    die("<!DOCTYPE html><html><head><meta name='viewport' content='width=device-width, initial-scale=1'><link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'></head><body class='bg-light'><div class='container py-5'><div class='alert alert-danger text-center'><h2 class='h4'>403: Access Denied</h2><p class='mb-0'>Only approved admins can view this page.</p></div></div></body></html>");
}

// Admin actions: approve / reject pending admins, revoke existing admins
$notice = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id'])) {
    $targetId = (int) $_POST['user_id'];
    $newRoles = ['approve' => 'admin', 'reject' => 'user', 'revoke' => 'user'];
    if ($targetId === (int) $_SESSION['userID']) {
        $notice = "<div class='alert alert-danger'>You can't change your own role.</div>";
    } elseif (isset($newRoles[$_POST['action']])) {
        $update = $pdo->prepare("UPDATE users SET Role = ? WHERE userID = ?");
        $update->execute([$newRoles[$_POST['action']], $targetId]);
        $notice = "<div class='alert alert-success'>User #{$targetId} updated: " . htmlspecialchars($_POST['action']) . ".</div>";
    }
}

try {
    $stmt = $pdo->query("SELECT userID, FirstName, LastName, Email, Role, Status, EmailVerified, EmailVerificationToken, RegDatetime, LoginDatetime, LogoutDatetime FROM users ORDER BY RegDatetime DESC");
    $users = $stmt->fetchAll();
} catch (\PDOException $e) {
    die("<p style='color:red;'>Error fetching metrics: " . $e->getMessage() . "</p>");
}

function roleButton($userId, $action, $label, $btnClass) {
    return "<form method='POST' class='d-inline'>
              <input type='hidden' name='user_id' value='{$userId}'>
              <input type='hidden' name='action' value='{$action}'>
              <button type='submit' class='btn btn-sm {$btnClass}'>{$label}</button>
            </form>";
}
$roleColors = ['admin' => 'danger', 'pending' => 'warning', 'user' => 'secondary'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Master Container Admin Console</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container-xl py-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom pb-3 mb-3">
    <div>
      <h1 class="h3 mb-1">Master Infrastructure Analytics Panel</h1>
      <p class="mb-0">Database Layer Status: <span class="text-primary fw-bold">RBAC Admin Control Active</span></p>
    </div>
    <a href="admin.php" class="btn btn-primary">&#128260; Refresh Metrics Engine</a>
  </div>

  <?php echo $notice; ?>

  <div class="card shadow-sm">
    <div class="card-header fw-semibold">Active Database Account Registry (Security Lifecycle Diagnostics)</div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small">
        <thead class="table-light">
          <tr>
            <th>User ID</th><th>Full Name</th><th>Email Address</th><th>Role</th><th>Verification State</th>
            <th>Live Security Token</th><th>Session State</th><th>Last Login</th><th>Last Logout</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if (count($users) > 0): ?>
        <?php foreach ($users as $row): ?>
          <tr>
            <td><strong>#<?php echo $row['userID']; ?></strong></td>
            <td><?php echo htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']); ?></td>
            <td><code><?php echo htmlspecialchars($row['Email']); ?></code></td>
            <td><span class="badge text-bg-<?php echo $roleColors[$row['Role']] ?? 'secondary'; ?>"><?php echo htmlspecialchars($row['Role']); ?></span></td>
            <td>
              <?php if ($row['EmailVerified'] == 1): ?>
                <span class="badge text-bg-success">&#10004; Verified (1)</span>
              <?php else: ?>
                <span class="badge text-bg-warning">&#8987; Pending (0)</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if (!empty($row['EmailVerificationToken'])): ?>
                <code class="small"><?php echo substr($row['EmailVerificationToken'], 0, 12); ?>…</code>
                <a href="verify_email.php?token=<?php echo $row['EmailVerificationToken']; ?>" target="_blank" class="small">[Auto Activate]</a>
              <?php else: ?>
                <span class="text-body-secondary fst-italic">None/Cleared</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($row['Status'] == 1): ?>
                <span class="badge text-bg-success">&#9679; Online</span>
              <?php else: ?>
                <span class="badge text-bg-light border">&#9675; Offline</span>
              <?php endif; ?>
            </td>
            <td class="text-nowrap"><?php echo htmlspecialchars($row['LoginDatetime'] ?? 'Never'); ?></td>
            <td class="text-nowrap"><?php echo htmlspecialchars($row['LogoutDatetime'] ?? 'Never'); ?></td>
            <td class="text-nowrap">
              <?php if ((int) $row['userID'] === (int) $_SESSION['userID']): ?>
                <span class="text-body-secondary">(you)</span>
              <?php elseif ($row['Role'] === 'pending'): ?>
                <?php echo roleButton($row['userID'], 'approve', 'Approve', 'btn-success'); ?>
                <?php echo roleButton($row['userID'], 'reject', 'Reject', 'btn-outline-secondary'); ?>
              <?php elseif ($row['Role'] === 'admin'): ?>
                <?php echo roleButton($row['userID'], 'revoke', 'Revoke', 'btn-outline-danger'); ?>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="10" class="text-center p-4">No container metrics available.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <p class="mt-3"><strong>Operational Testing Navigation:</strong> <a href="register.php">Register Suite</a> | <a href="login.php">Login Terminal</a> | <a href="dashboard.php">User Profile Area</a></p>
</div>
</body>
</html>
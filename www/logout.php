<?php
require 'db.php';
if (isset($_SESSION['userID'])) {
    $update = $pdo->prepare("UPDATE users SET LogoutDatetime = NOW(), Status = 0 WHERE userID = ?");
    $update->execute([$_SESSION['userID']]);
}
$_SESSION = array();
session_destroy();
header("Location: login.php");
exit;
?>

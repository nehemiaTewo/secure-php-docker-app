<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Credentials come from the container environment (.env via docker-compose), never from code
$host = getenv('DB_HOST') ?: 'database';
$db   = getenv('DB_NAME') ?: 'lab_db';
$user = getenv('DB_USER') ?: 'lab_user';
$pass = getenv('DB_PASSWORD') ?: '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (\PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Service temporarily unavailable. Please try again shortly.");
}
// Schema setup now lives in db/init.sql, so the old runtime ALTER TABLE is removed
?>
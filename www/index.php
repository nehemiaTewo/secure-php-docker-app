<?php
require 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>DockerLab Web Services</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-5">
      <div class="card shadow-sm text-center">
        <div class="card-body p-5">
          <h1 class="h3">Welcome</h1>
          <p class="text-body-secondary">DockerLab Web Services</p>
          <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
            <a href="login.php" class="btn btn-primary px-4">Login</a>
            <a href="register.php" class="btn btn-outline-primary px-4">Register</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
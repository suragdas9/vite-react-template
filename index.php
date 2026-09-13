<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: /dashboard.php');
    exit;
}
$error = '';
$timeout = isset($_GET['timeout']) ? 'Session expired. Please log in again.' : '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TruRides Admin – Login</title>
    <meta name="description" content="Sign in to TruRides Admin Dashboard to monitor drivers, rides and revenue.">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="login-body">

<div class="login-card">
    <div class="login-logo">
        <img src="/assets/img/trurides.png" alt="TruRides Logo" onerror="this.style.display='none'">
        <h2>TruRides Admin</h2>
        <p>Sign in to your dashboard</p>
    </div>

    <?php if (!empty($timeout)): ?>
        <div class="alert alert-warning"><?= htmlspecialchars($timeout) ?></div>
    <?php endif; ?>

    <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-danger">Invalid email or password. Please try again.</div>
    <?php endif; ?>

    <form action="/login.php" method="POST" id="loginForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? bin2hex(random_bytes(16))) ?>">

        <div class="form-group">
            <label class="form-label" for="employee_uid">Employee UID</label>
            <input
                type="text"
                id="employee_uid"
                name="employee_uid"
                class="form-control"
                placeholder="EMP-001"
                required
                autocomplete="username"
                value="<?= htmlspecialchars($_GET['employee_uid'] ?? '') ?>"
            >
        </div>

        <div class="form-group">
            <label class="form-label" for="email">Email Address</label>
            <input
                type="email"
                id="email"
                name="email"
                class="form-control"
                placeholder="admin@trurides.in"
                required
                autocomplete="email"
                value="<?= htmlspecialchars($_GET['email'] ?? '') ?>"
            >
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                class="form-control"
                placeholder="Enter your password"
                required
                autocomplete="current-password"
            >
        </div>

        <button type="submit" class="login-btn" id="loginBtn">Sign In</button>
    </form>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', function() {
    const btn = document.getElementById('loginBtn');
    btn.textContent = 'Signing in…';
    btn.disabled = true;
});
</script>
</body>
</html>

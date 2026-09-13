<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/config.php';

// Regenerate session ID on login
session_regenerate_id(true);

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /index.php');
    exit;
}

// CSRF check
$token = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'], $token)) {
    header('Location: /index.php?error=1');
    exit;
}

$employee_uid = trim($_POST['employee_uid'] ?? '');
$email        = trim($_POST['email'] ?? '');
$password     = trim($_POST['password'] ?? '');

if (empty($employee_uid) || empty($email) || empty($password)) {
    header('Location: /index.php?error=1');
    exit;
}

// Fetch admin from Supabase by employee_uid
$admins = supabase_query('admin_users', 'employee_uid=eq.' . urlencode($employee_uid) . '&select=*');

if (empty($admins)) {
    header('Location: /index.php?error=1&employee_uid=' . urlencode($employee_uid));
    exit;
}

$admin = $admins[0];

// Verify password
if (!password_verify($password, $admin['password_hash'] ?? '')) {
    header('Location: /index.php?error=1&employee_uid=' . urlencode($employee_uid));
    exit;
}

// Check if admin is active
if (($admin['is_active'] ?? true) === false) {
    header('Location: /index.php?error=1&employee_uid=' . urlencode($employee_uid));
    exit;
}

// Set session
$_SESSION['admin_logged_in']  = true;
$_SESSION['admin_id']         = $admin['id'];
$_SESSION['admin_email']      = $admin['email'];
$_SESSION['admin_name']       = $admin['full_name'] ?? 'Admin';
$_SESSION['admin_role']       = $admin['role'] ?? 'admin';
$_SESSION['admin_employee_uid'] = $admin['employee_uid'];
$_SESSION['last_activity']    = time();

// Rotate CSRF token after login
$_SESSION['csrf_token'] = bin2hex(random_bytes(16));

header('Location: /dashboard.php');
exit;
?>

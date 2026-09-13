<?php
/**
 * TruRides Admin Dashboard – Setup Helper
 * =========================================
 * Run this ONCE from browser: https://yourdomain.com/setup.php
 * It will output the hashed password to paste into includes/config.php
 * DELETE this file immediately after use!
 */

// Change this to your desired admin password
$plain_password = 'TruRides@2024';

$hash = password_hash($plain_password, PASSWORD_BCRYPT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>TruRides Setup</title>
<style>
body { font-family: Arial, sans-serif; max-width: 600px; margin: 60px auto; padding: 20px; background: #f8f9fa; }
.card { background: #fff; border-radius: 10px; padding: 28px; box-shadow: 0 2px 10px rgba(0,0,0,.1); }
h2 { color: #c0392b; margin-bottom: 4px; }
.hash { background: #f1f3f4; border: 1px solid #dadce0; padding: 14px; border-radius: 6px; font-family: monospace; font-size: 13px; word-break: break-all; margin: 16px 0; }
.warn { background: #fce8e6; color: #c5221f; border: 1px solid #f5c6c2; padding: 12px; border-radius: 6px; font-size: 13px; margin-top: 20px; }
ol { font-size: 14px; line-height: 2; }
code { background: #f1f3f4; padding: 2px 6px; border-radius: 4px; font-size: 13px; }
</style>
</head>
<body>
<div class="card">
    <h2>🔧 TruRides Setup</h2>
    <p>Password hash for: <strong><?= htmlspecialchars($plain_password) ?></strong></p>
    <div class="hash"><?= htmlspecialchars($hash) ?></div>

    <ol>
        <li>Open <code>includes/config.php</code></li>
        <li>Replace <code>ADMIN_EMAIL</code> with your email</li>
        <li>Replace <code>ADMIN_PASSWORD_HASH</code> with the hash above</li>
        <li>Replace <code>SUPABASE_URL</code> and <code>SUPABASE_KEY</code> with your Supabase credentials</li>
        <li><strong>Delete this <code>setup.php</code> file immediately!</strong></li>
    </ol>

    <div class="warn">
        ⚠️ <strong>Security Warning:</strong> Delete this file from your server immediately after setup. 
        Anyone with access to this URL can see your password hash generation.
    </div>
</div>
</body>
</html>

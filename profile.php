<?php
require_once 'includes/auth.php';
require_once 'includes/config.php';
require_once 'includes/header.php';

$admin_email   = $_SESSION['admin_email'] ?? 'Admin';
$admin_name    = $_SESSION['admin_name'] ?? 'Admin';
$employee_uid  = $_SESSION['admin_employee_uid'] ?? '—';
$admin_role    = $_SESSION['admin_role'] ?? 'admin';
$supabase_url  = SUPABASE_URL;

function initials($name) {
    $chunks = preg_split('/[\s._-]+/', $name);
    $initials = '';
    foreach ($chunks as $chunk) {
        if (!empty($chunk)) {
            $initials .= strtoupper(substr($chunk, 0, 1));
        }
        if (strlen($initials) >= 2) break;
    }
    return $initials ?: 'AD';
}

$init = initials($admin_name);
// Generate a beautiful, dynamic profile image based on the admin's name
$avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($admin_name) . "&background=c0392b&color=fff&size=128&bold=true";

$account_created = 'Sep 13, 2026';
$last_login      = date('d M Y, g:i A');
$password_algo   = 'bcrypt';
$account_status  = 'Active';
$role_label      = ucfirst($admin_role);
$two_fa          = 'Disabled';

$stats = [
    ['label' => 'Rides Managed (All-Time)', 'value' => '0', 'icon' => 'clipboard-list'],
    ['label' => 'Drivers Supervised',       'value' => '0', 'icon' => 'users'],
    ['label' => 'Account Age',              'value' => '0 days', 'icon' => 'calendar'],
    ['label' => 'Last IP Address',          'value' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1', 'icon' => 'map-pin'],
];
?>

<div class="profile-container">
    <div class="profile-header-bar">
        <h1>Profile</h1>
        <p class="profile-subtitle">Manage your account settings and preferences</p>
    </div>

    <div class="profile-grid">
        <!-- Main Profile Card -->
        <div class="profile-card">
            <div class="profile-card-bg"></div>
            <div class="profile-card-body">
                <div class="profile-avatar-wrap">
                    <img src="<?= $avatar_url ?>" alt="Profile Image" class="profile-avatar-large">
                    <div class="avatar-status" title="<?= $account_status ?>"></div>
                </div>

                <div class="profile-identity">
                    <h2 class="profile-name"><?= htmlspecialchars($admin_name) ?></h2>
                    <p class="profile-email"><?= htmlspecialchars($admin_email) ?></p>
                    <span class="profile-role-badge"><?= $role_label ?></span>
                </div>

                <div class="profile-meta-grid">
                    <div class="meta-item">
                        <i data-lucide="calendar" class="meta-icon"></i>
                        <span class="meta-label">Account Created</span>
                        <span class="meta-value"><?= $account_created ?></span>
                    </div>
                    <div class="meta-item">
                        <i data-lucide="clock" class="meta-icon"></i>
                        <span class="meta-label">Last Login</span>
                        <span class="meta-value"><?= $last_login ?></span>
                    </div>
                    <div class="meta-item">
                        <i data-lucide="badge" class="meta-icon"></i>
                        <span class="meta-label">Employee UID</span>
                        <span class="meta-value"><?= htmlspecialchars($employee_uid) ?></span>
                    </div>
                    <div class="meta-item">
                        <i data-lucide="shield" class="meta-icon"></i>
                        <span class="meta-label">Password Hash</span>
                        <span class="meta-value">bcrypt (PHP PASSWORD_BCRYPT)</span>
                    </div>
                    <div class="meta-item">
                        <i data-lucide="activity" class="meta-icon"></i>
                        <span class="meta-label">Two-Factor Auth</span>
                        <span class="meta-value"><?= $two_fa ?></span>
                    </div>
                    <div class="meta-item">
                        <i data-lucide="database" class="meta-icon"></i>
                        <span class="meta-label">Backend</span>
                        <span class="meta-value break-word" style="max-width:200px"><?= htmlspecialchars($supabase_url) ?></span>
                    </div>
                    <div class="meta-item">
                        <i data-lucide="check-circle" class="meta-icon"></i>
                        <span class="meta-label">Status</span>
                        <span class="meta-value status-active"><?= $account_status ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Card -->
        <div class="profile-stats-card">
            <h3 class="stats-card-title">Account Summary</h3>
            <div class="stats-card-list">
                <?php foreach ($stats as $stat): ?>
                <div class="stat-row">
                    <div class="stat-row-icon">
                        <i data-lucide="<?= $stat['icon'] ?>" style="width:18px;height:18px;stroke:#5f6368"></i>
                    </div>
                    <span class="stat-row-label"><?= $stat['label'] ?></span>
                    <span class="stat-row-value"><?= $stat['value'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Security Settings Card -->
    <div class="security-card">
        <h3 class="security-title">Security Settings</h3>
        <div class="security-list">
            <div class="security-item">
                <div class="security-item-info">
                    <span class="security-item-label">Employee UID</span>
                    <span class="security-item-desc">Your unique employee identifier</span>
                </div>
                <span class="security-item-value"><?= htmlspecialchars($employee_uid) ?></span>
            </div>
            <div class="security-item">
                <div class="security-item-info">
                    <span class="security-item-label">Email Address</span>
                    <span class="security-item-desc">Used for admin login and notifications</span>
                </div>
                <span class="security-item-value"><?= htmlspecialchars($admin_email) ?></span>
            </div>
            <div class="security-item">
                <div class="security-item-info">
                    <span class="security-item-label">Password Protection</span>
                    <span class="security-item-desc">Currently hashed with bcrypt</span>
                </div>
                <span class="badge badge-success">Secure</span>
            </div>
            <div class="security-item">
                <div class="security-item-info">
                    <span class="security-item-label">Two-Factor Authentication</span>
                    <span class="security-item-desc">Add an extra layer of security</span>
                </div>
                <span class="badge badge-secondary">Not Enabled</span>
            </div>
        </div>
        <div class="security-actions">
            <button class="btn btn-outline btn-sm" onclick="alert('Change password feature coming soon.')">
                <i data-lucide="edit-2" style="width:14px;height:14px;vertical-align:middle;margin-right:4px"></i>Change Password
            </button>
            <button class="btn btn-outline btn-sm" onclick="alert('2FA setup coming soon.')">
                <i data-lucide="smartphone" style="width:14px;height:14px;vertical-align:middle;margin-right:4px"></i>Enable 2FA
            </button>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

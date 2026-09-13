<?php
require_once 'includes/auth.php';
require_once 'includes/config.php';
require_once 'includes/header.php';

// Use pre-built views for performance
$stats = supabase_query('dashboard_stats', 'select=*');
$stat = $stats[0] ?? [];

$total_drivers   = $stat['total_drivers']   ?? 0;
$online_drivers  = $stat['online_drivers']  ?? 0;
$rides_today     = $stat['rides_today']     ?? 0;
$revenue_today   = $stat['revenue_today']   ?? 0;
$active_rides    = $stat['active_rides']    ?? 0;
$completed_today = $stat['completed_today'] ?? 0;
$cancelled_today = $stat['cancelled_today'] ?? 0;
$avg_rating      = $stat['avg_driver_rating'] ?? 0;

// Recent rides from view
$recent_rides = supabase_query('recent_rides', 'limit=10');

function status_badge(string $status): string {
    $map = [
        'completed' => 'badge-success',
        'started'   => 'badge-primary',
        'accepted'  => 'badge-primary',
        'arriving'  => 'badge-primary',
        'cancelled' => 'badge-danger',
        'pending'   => 'badge-warning',
        'requesting'=> 'badge-warning',
    ];
    $cls = $map[strtolower($status)] ?? 'badge-secondary';
    return '<span class="badge ' . $cls . '">' . htmlspecialchars(ucfirst($status ?: 'unknown')) . '</span>';
}

function fmt_time(string $dt): string {
    if (!$dt) return '—';
    try { return (new DateTime($dt))->format('d M, g:i A'); } catch (Exception $e) { return $dt; }
}

function get_greeting() {
    $h = (int) date('H');
    if ($h >= 5 && $h < 12) return 'Good morning';
    if ($h >= 12 && $h < 17) return 'Good afternoon';
    if ($h >= 17 && $h < 21) return 'Good evening';
    return 'Good evening';
}

$greeting = get_greeting();
$today_display = date('l, F j, Y');
?>

<!-- Welcome Header -->
<div class="dashboard-intro">
    <h1><?= $greeting ?>! TruRides Admin</h1>
    <p>Real-time overview of your platform operations.</p>
    <span class="date"><?= $today_display ?> <span id="realtimeClock" style="color:var(--primary); font-weight:700; margin-left:6px;"></span></span>
</div>

<!-- Stat Cards Row 1 -->
<div class="stats-grid" id="statsGrid">
    <div class="stat-card">
        <div class="stat-icon primary"><i data-lucide="users" class="stat-svg"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Drivers</div>
            <div class="stat-value" id="totalDrivers"><?= $total_drivers ?></div>
            <div class="stat-sub">Registered on platform</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i data-lucide="radio" class="stat-svg"></i></div>
        <div class="stat-info">
            <div class="stat-label">Online Drivers</div>
            <div class="stat-value" id="onlineDrivers"><?= $online_drivers ?></div>
            <div class="stat-sub">Currently active</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon yellow"><i data-lucide="clipboard-list" class="stat-svg"></i></div>
        <div class="stat-info">
            <div class="stat-label">Rides Today</div>
            <div class="stat-value" id="ridesToday"><?= $rides_today ?></div>
            <div class="stat-sub">All statuses</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i data-lucide="indian-rupee" class="stat-svg"></i></div>
        <div class="stat-info">
            <div class="stat-label">Revenue Today</div>
            <div class="stat-value" id="revenueToday">₹<?= number_format($revenue_today, 0) ?></div>
            <div class="stat-sub">5% commission</div>
        </div>
    </div>
</div>

<!-- Stat Cards Row 2 -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary"><i data-lucide="zap" class="stat-svg"></i></div>
        <div class="stat-info">
            <div class="stat-label">Active Rides</div>
            <div class="stat-value" id="activeRides"><?= $active_rides ?></div>
            <div class="stat-sub">In progress now</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i data-lucide="check-circle" class="stat-svg"></i></div>
        <div class="stat-info">
            <div class="stat-label">Completed Today</div>
            <div class="stat-value" id="completedToday"><?= $completed_today ?></div>
            <div class="stat-sub">Successfully finished</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i data-lucide="x-circle" class="stat-svg"></i></div>
        <div class="stat-info">
            <div class="stat-label">Cancelled Today</div>
            <div class="stat-value" id="cancelledToday"><?= $cancelled_today ?></div>
            <div class="stat-sub">By driver or passenger</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon yellow"><i data-lucide="star" class="stat-svg"></i></div>
        <div class="stat-info">
            <div class="stat-label">Avg. Driver Rating</div>
            <div class="stat-value" id="avgRating"><?= number_format($avg_rating, 1) ?></div>
            <div class="stat-sub">Out of 5.0</div>
        </div>
    </div>
</div>

<!-- Recent Rides Table -->
<div class="table-card">
    <div class="table-card-header">
        <h2>Recent Rides</h2>
        <a href="/rides.php" class="btn btn-outline btn-sm">
            <i data-lucide="arrow-right" style="width:14px;height:14px;vertical-align:middle;margin-right:4px"></i>View All
        </a>
    </div>
    <div class="table-wrapper">
        <table id="recentRidesTable">
            <thead>
                <tr>
                    <th>Ride ID</th>
                    <th>Passenger</th>
                    <th>Driver</th>
                    <th>Pickup</th>
                    <th>Dropoff</th>
                    <th>Fare</th>
                    <th>Status</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent_rides)): ?>
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <i data-lucide="car" style="width:48px;height:48px;stroke:#dadce0;margin-bottom:12px"></i>
                            <p>No rides recorded yet today.</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($recent_rides as $ride): ?>
                <tr>
                    <td><code><?= htmlspecialchars(substr($ride['id'] ?? '—', 0, 8)) ?></code></td>
                    <td><?= htmlspecialchars($ride['passenger_name'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($ride['driver_name'] ?? '—') ?></td>
                    <td title="<?= htmlspecialchars($ride['pickup_address'] ?? '') ?>"><?= htmlspecialchars(substr($ride['pickup_address'] ?? '—', 0, 28)) ?></td>
                    <td title="<?= htmlspecialchars($ride['dropoff_address'] ?? '') ?>"><?= htmlspecialchars(substr($ride['dropoff_address'] ?? '—', 0, 28)) ?></td>
                    <td>₹<?= number_format(floatval($ride['fare'] ?? 0), 2) ?></td>
                    <td><?= status_badge($ride['status'] ?? '') ?></td>
                    <td><?= fmt_time($ride['created_at'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Auto-refresh stats every 30 seconds
setInterval(function () {
    fetch('/api/get_stats.php')
        .then(r => r.json())
        .then(data => {
            if (!data) return;
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
            set('totalDrivers',   data.total_drivers   ?? '—');
            set('onlineDrivers',  data.online_drivers  ?? '—');
            set('ridesToday',     data.rides_today      ?? '—');
            set('revenueToday',   '₹' + (data.revenue_today  ?? '0'));
            set('activeRides',    data.active_rides     ?? '—');
            set('completedToday', data.completed_today  ?? '—');
            set('cancelledToday', data.cancelled_today  ?? '—');
            set('avgRating',      data.avg_rating       ?? '—');
        })
        .catch(() => {});
}, 30000);

// Realtime Clock
function updateClock() {
    const now = new Date();
    let hours = now.getHours();
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12;
    const mins = now.getMinutes().toString().padStart(2, '0');
    const secs = now.getSeconds().toString().padStart(2, '0');
    const el = document.getElementById('realtimeClock');
    if (el) el.textContent = `— ${hours}:${mins}:${secs} ${ampm}`;
}
setInterval(updateClock, 1000);
updateClock();
</script>

<?php require_once 'includes/footer.php'; ?>

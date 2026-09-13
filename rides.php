<?php
require_once 'includes/auth.php';
require_once 'includes/config.php';
require_once 'includes/header.php';

// Filters
$date_from  = $_GET['date_from'] ?? '';
$date_to    = $_GET['date_to']   ?? '';
$status_f   = $_GET['status']    ?? 'all';

$params = 'select=id,passenger_name,driver_name,pickup_address,dropoff_address,fare,status,created_at&order=created_at.desc';

if ($date_from) $params .= '&created_at=gte.' . urlencode($date_from . 'T00:00:00');
if ($date_to)   $params .= '&created_at=lte.' . urlencode($date_to   . 'T23:59:59');
if ($status_f && $status_f !== 'all') $params .= '&status=eq.' . urlencode($status_f);

$rides = supabase_query('rides', $params);

function status_badge_r($status) {
    $map = [
        'completed' => 'badge-success',
        'started'   => 'badge-primary',
        'cancelled' => 'badge-danger',
        'pending'   => 'badge-warning',
    ];
    $cls = $map[strtolower($status ?? '')] ?? 'badge-secondary';
    return '<span class="badge ' . $cls . '">' . htmlspecialchars(ucfirst($status ?? 'unknown')) . '</span>';
}
function fmt_dt($dt) {
    if (!$dt) return '—';
    try { return (new DateTime($dt))->format('d M Y, g:i A'); } catch(Exception $e) { return $dt; }
}
?>

<!-- Filters Bar -->
<form method="GET" action="/rides.php" class="filters-bar" id="ridesFilters">
    <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($date_from) ?>" title="From date">
    <input type="date" name="date_to"   class="form-control" value="<?= htmlspecialchars($date_to) ?>"   title="To date">
    <select name="status" class="form-control" id="ridesStatusFilter">
        <option value="all"       <?= $status_f === 'all'       ? 'selected' : '' ?>>All Status</option>
        <option value="completed" <?= $status_f === 'completed' ? 'selected' : '' ?>>Completed</option>
        <option value="started"   <?= $status_f === 'started'   ? 'selected' : '' ?>>Active</option>
        <option value="cancelled" <?= $status_f === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        <option value="pending"   <?= $status_f === 'pending'   ? 'selected' : '' ?>>Pending</option>
    </select>
    <button type="submit" class="btn btn-primary btn-sm"><i data-lucide="search" style="width:14px;height:14px;vertical-align:middle;margin-right:4px"></i>Filter</button>
    <a href="/rides.php" class="btn btn-outline btn-sm"><i data-lucide="x" style="width:14px;height:14px;vertical-align:middle;margin-right:4px"></i>Clear</a>
    <button type="button" class="btn btn-success btn-sm" onclick="exportCSV()"><i data-lucide="download" style="width:14px;height:14px;vertical-align:middle;margin-right:4px"></i>Export CSV</button>
</form>

<div class="table-card">
    <div class="table-card-header">
        <h2>Rides History <span class="badge badge-primary" id="rideCount"><?= count($rides) ?></span></h2>
    </div>
    <div class="table-wrapper">
        <table id="ridesTable">
            <thead>
                <tr>
                    <th>Ride ID</th>
                    <th>Date & Time</th>
                    <th>Passenger</th>
                    <th>Driver</th>
                    <th>Pickup</th>
                    <th>Dropoff</th>
                    <th>Fare</th>
                    <th>Commission</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="ridesBody">
                <?php if (empty($rides)): ?>
                <tr><td colspan="9"><div class="empty-state"><i data-lucide="clipboard-list" style="width:48px;height:48px;stroke:#dadce0;margin-bottom:12px"></i><p>No rides match the current filters.</p></div></td></tr>
                <?php else: ?>
                <?php foreach ($rides as $ride): ?>
                <?php $fare = floatval($ride['fare'] ?? 0); ?>
                <tr>
                    <td><code><?= htmlspecialchars(substr($ride['id'] ?? '—', 0, 8)) ?></code></td>
                    <td><?= fmt_dt($ride['created_at'] ?? '') ?></td>
                    <td><?= htmlspecialchars($ride['passenger_name'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($ride['driver_name'] ?? '—') ?></td>
                    <td title="<?= htmlspecialchars($ride['pickup_address'] ?? '') ?>"><?= htmlspecialchars(substr($ride['pickup_address'] ?? '—', 0, 28)) ?></td>
                    <td title="<?= htmlspecialchars($ride['dropoff_address'] ?? '') ?>"><?= htmlspecialchars(substr($ride['dropoff_address'] ?? '—', 0, 28)) ?></td>
                    <td>₹<?= number_format($fare, 2) ?></td>
                    <td>₹<?= number_format($fare * 0.05, 2) ?></td>
                    <td><?= status_badge_r($ride['status'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <!-- Pagination -->
    <div class="pagination" id="ridesPagination">
        <span class="pagination-info" id="ridesPageInfo">Showing all</span>
        <div class="pagination-controls" id="ridesPageControls"></div>
    </div>
</div>

<script>
// ---- Pagination ----
const ROWS_PER = 20;
let rCurrentPage = 1;
let rVisible = [];

function applyRidesPagination() {
    const total = rVisible.length;
    const pages = Math.max(1, Math.ceil(total / ROWS_PER));
    const start = (rCurrentPage - 1) * ROWS_PER;
    const end   = start + ROWS_PER;

    rVisible.forEach((row, i) => {
        row.style.display = (i >= start && i < end) ? '' : 'none';
    });

    document.getElementById('ridesPageInfo').textContent =
        total === 0
            ? 'No results'
            : `Showing ${Math.min(start+1, total)}–${Math.min(end, total)} of ${total}`;
    document.getElementById('rideCount').textContent = total;

    const ctrl = document.getElementById('ridesPageControls');
    ctrl.innerHTML = '';
    if (pages <= 1) return;

    const prev = document.createElement('button');
    prev.className = 'page-btn';
    prev.textContent = '← Prev';
    prev.disabled = rCurrentPage === 1;
    prev.onclick = () => { rCurrentPage--; applyRidesPagination(); };
    ctrl.appendChild(prev);

    for (let p = 1; p <= pages; p++) {
        const btn = document.createElement('button');
        btn.className = 'page-btn' + (p === rCurrentPage ? ' active' : '');
        btn.textContent = p;
        btn.onclick = (function(pg){ return function(){ rCurrentPage = pg; applyRidesPagination(); }; })(p);
        ctrl.appendChild(btn);
    }

    const next = document.createElement('button');
    next.className = 'page-btn';
    next.textContent = 'Next →';
    next.disabled = rCurrentPage === pages;
    next.onclick = () => { rCurrentPage++; applyRidesPagination(); };
    ctrl.appendChild(next);
}

// Export CSV
function exportCSV() {
    const table = document.getElementById('ridesTable');
    let csv = [];
    const rows = table.querySelectorAll('tr');
    rows.forEach(row => {
        const cells = row.querySelectorAll('th, td');
        const vals  = Array.from(cells).map(c => '"' + c.innerText.replace(/"/g, '""') + '"');
        if (vals.length > 0) csv.push(vals.join(','));
    });
    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = 'trurides_rides_' + new Date().toISOString().slice(0,10) + '.csv';
    a.click();
    URL.revokeObjectURL(url);
}

window.addEventListener('DOMContentLoaded', () => {
    rVisible = Array.from(document.querySelectorAll('#ridesBody tr:not(:only-child)'));
    // Filter out empty-state row
    rVisible = rVisible.filter(r => r.querySelector('td:first-child code'));
    applyRidesPagination();
});
</script>

<?php require_once 'includes/footer.php'; ?>

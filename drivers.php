<?php
require_once 'includes/auth.php';
require_once 'includes/config.php';
require_once 'includes/header.php';

// Handle Add Driver
$message = '';
$error = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_driver') {
    $newDriver = [
        'full_name'     => trim($_POST['full_name'] ?? ''),
        'phone'         => trim($_POST['phone'] ?? ''),
        'vehicle_model' => trim($_POST['vehicle_model'] ?? ''),
        'vehicle_plate' => trim($_POST['vehicle_plate'] ?? ''),
        'is_online'     => false,
        'total_trips'   => 0,
        'rating'        => 5.0
    ];

    if (!empty($newDriver['full_name']) && !empty($newDriver['phone'])) {
        $res = supabase_insert('drivers', $newDriver);
        if (isset($res['error'])) {
            $error = true;
            $message = 'Error adding driver. Please try again.';
        } else {
            $message = 'Driver added successfully!';
        }
    } else {
        $error = true;
        $message = 'Name and phone are required.';
    }
}

// Fetch all drivers
$drivers = supabase_query('drivers', 'select=id,full_name,phone,vehicle_model,vehicle_plate,is_online,total_trips,rating&order=full_name.asc');

function rating_stars($rating) {
    $r = floatval($rating ?? 0);
    $full = min(5, floor($r));
    $empty = 5 - $full;
    return str_repeat('★', $full) . str_repeat('☆', $empty) . ' ' . number_format($r, 1);
}
?>

<!-- Filters -->
<?php if ($message): ?>
<div class="alert <?= $error ? 'alert-error' : 'alert-success' ?>" style="padding:12px;margin-bottom:16px;border-radius:8px;background:<?= $error ? '#ffebee' : '#e8f5e9' ?>;color:<?= $error ? '#c62828' : '#2e7d32' ?>;">
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<div class="filters-bar">
    <input type="text" id="searchInput" class="form-control" placeholder="Search by name or phone…" oninput="filterTable()">
    <select id="statusFilter" class="form-control" onchange="filterTable()">
        <option value="all">All Drivers</option>
        <option value="online">Online Only</option>
        <option value="offline">Offline Only</option>
    </select>
    <a href="/map.php" class="btn btn-primary btn-sm"><i data-lucide="map" style="width:14px;height:14px;vertical-align:middle;margin-right:4px"></i>Live Map</a>
    <button class="btn btn-primary btn-sm" onclick="document.getElementById('addDriverModal').classList.add('active')"><i data-lucide="plus" style="width:14px;height:14px;vertical-align:middle;margin-right:4px"></i>Add Driver</button>
</div>

<div class="table-card">
    <div class="table-card-header">
        <h2>All Drivers <span id="driverCountBadge" class="badge badge-primary"><?= count($drivers) ?></span></h2>
    </div>
    <div class="table-wrapper">
        <table id="driversTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th class="sortable" onclick="sortTable(1)">Name <i data-lucide="chevrons-up-down" style="width:12px;height:12px;vertical-align:middle"></i></th>
                    <th>Phone</th>
                    <th>Vehicle</th>
                    <th class="sortable" onclick="sortTable(4)">Status <i data-lucide="chevrons-up-down" style="width:12px;height:12px;vertical-align:middle"></i></th>
                    <th class="sortable" onclick="sortTable(5)">Total Rides <i data-lucide="chevrons-up-down" style="width:12px;height:12px;vertical-align:middle"></i></th>
                    <th class="sortable" onclick="sortTable(6)">Rating <i data-lucide="chevrons-up-down" style="width:12px;height:12px;vertical-align:middle"></i></th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="driversBody">
                <?php if (empty($drivers)): ?>
                <tr><td colspan="8"><div class="empty-state"><i data-lucide="car" style="width:48px;height:48px;stroke:#dadce0;margin-bottom:12px"></i><p>No drivers registered yet.</p></div></td></tr>
                <?php else: ?>
                <?php foreach ($drivers as $d): ?>
                <?php $online = !empty($d['is_online']); ?>
                <tr data-status="<?= $online ? 'online' : 'offline' ?>"
                    data-name="<?= htmlspecialchars(strtolower($d['full_name'] ?? '')) ?>"
                    data-phone="<?= htmlspecialchars($d['phone'] ?? '') ?>">
                    <td><code><?= htmlspecialchars(substr($d['id'] ?? '—', 0, 8)) ?></code></td>
                    <td><?= htmlspecialchars($d['full_name'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($d['phone'] ?? '—') ?></td>
                    <td><?= htmlspecialchars(($d['vehicle_model'] ?? '') . ' · ' . ($d['vehicle_plate'] ?? '')) ?></td>
                    <td>
                        <span class="badge <?= $online ? 'badge-online' : 'badge-offline' ?>">
                            <?= $online ? 'Online' : 'Offline' ?>
                        </span>
                    </td>
                    <td><?= intval($d['total_trips'] ?? 0) ?></td>
                    <td><span class="rating-stars"><?= rating_stars($d['rating'] ?? 0) ?></span></td>
                    <td>
                        <button class="btn btn-outline btn-sm" onclick='showDriver(<?= json_encode($d) ?>)'>View</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <!-- Pagination -->
    <div class="pagination" id="paginationBar">
        <span class="pagination-info" id="pageInfo">Showing all</span>
        <div class="pagination-controls" id="pageControls"></div>
    </div>
</div>

<!-- Driver Detail Modal -->
<div class="modal-overlay" id="driverModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalDriverName">Driver Details</h3>
            <button class="modal-close" onclick="closeModal('driverModal')">×</button>
        </div>
        <div class="modal-body" id="modalDriverBody"></div>
    </div>
</div>

<!-- Add Driver Modal -->
<div class="modal-overlay" id="addDriverModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Add New Driver</h3>
            <button type="button" class="modal-close" onclick="closeModal('addDriverModal')">×</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="/drivers.php">
                <input type="hidden" name="action" value="add_driver">
                <div class="form-group" style="margin-bottom:12px">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" class="form-control" required style="width:100%;margin-top:4px;">
                </div>
                <div class="form-group" style="margin-bottom:12px">
                    <label>Phone *</label>
                    <input type="text" name="phone" class="form-control" required style="width:100%;margin-top:4px;">
                </div>
                <div class="form-group" style="margin-bottom:12px">
                    <label>Vehicle Model</label>
                    <input type="text" name="vehicle_model" class="form-control" placeholder="e.g. Toyota Prius" style="width:100%;margin-top:4px;">
                </div>
                <div class="form-group" style="margin-bottom:24px">
                    <label>Vehicle Plate</label>
                    <input type="text" name="vehicle_plate" class="form-control" placeholder="e.g. MH 01 AB 1234" style="width:100%;margin-top:4px;">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%">Save Driver</button>
            </form>
        </div>
    </div>
</div>

<script>
// ---- Pagination ----
const ROWS_PER_PAGE = 20;
let currentPage = 1;
let visibleRows = [];

function filterTable() {
    const search = document.getElementById('searchInput').value.toLowerCase().trim();
    const status = document.getElementById('statusFilter').value;
    const rows   = document.querySelectorAll('#driversBody tr[data-name]');
    visibleRows  = [];

    rows.forEach(row => {
        const name  = row.dataset.name  || '';
        const phone = row.dataset.phone || '';
        const rowStatus = row.dataset.status;
        const matchSearch = !search || name.includes(search) || phone.includes(search);
        const matchStatus = status === 'all' || rowStatus === status;

        if (matchSearch && matchStatus) {
            visibleRows.push(row);
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });

    currentPage = 1;
    applyPagination();
}

function applyPagination() {
    const total = visibleRows.length;
    const pages = Math.max(1, Math.ceil(total / ROWS_PER_PAGE));
    const start = (currentPage - 1) * ROWS_PER_PAGE;
    const end   = start + ROWS_PER_PAGE;

    visibleRows.forEach((row, i) => {
        row.style.display = (i >= start && i < end) ? '' : 'none';
    });

    document.getElementById('pageInfo').textContent =
        total === 0
            ? 'No results'
            : `Showing ${Math.min(start+1, total)}–${Math.min(end, total)} of ${total}`;

    document.getElementById('driverCountBadge').textContent = total;

    const ctrl = document.getElementById('pageControls');
    ctrl.innerHTML = '';

    if (pages <= 1) return;

    const prev = document.createElement('button');
    prev.className = 'page-btn';
    prev.textContent = '← Prev';
    prev.disabled = currentPage === 1;
    prev.onclick = () => { currentPage--; applyPagination(); };
    ctrl.appendChild(prev);

    for (let p = 1; p <= pages; p++) {
        const btn = document.createElement('button');
        btn.className = 'page-btn' + (p === currentPage ? ' active' : '');
        btn.textContent = p;
        btn.onclick = (function(pg) { return function() { currentPage = pg; applyPagination(); }; })(p);
        ctrl.appendChild(btn);
    }

    const next = document.createElement('button');
    next.className = 'page-btn';
    next.textContent = 'Next →';
    next.disabled = currentPage === pages;
    next.onclick = () => { currentPage++; applyPagination(); };
    ctrl.appendChild(next);
}

// ---- Sort ----
let sortDir = {};
function sortTable(colIndex) {
    const tbody = document.getElementById('driversBody');
    const rows  = Array.from(tbody.querySelectorAll('tr[data-name]'));
    sortDir[colIndex] = !sortDir[colIndex];
    const asc = sortDir[colIndex];

    rows.sort((a, b) => {
        const av = a.cells[colIndex]?.textContent.trim() || '';
        const bv = b.cells[colIndex]?.textContent.trim() || '';
        const an = parseFloat(av), bn = parseFloat(bv);
        if (!isNaN(an) && !isNaN(bn)) return asc ? an - bn : bn - an;
        return asc ? av.localeCompare(bv) : bv.localeCompare(av);
    });

    rows.forEach(r => tbody.appendChild(r));
    filterTable();
}

// ---- Modal ----
function showDriver(d) {
    document.getElementById('modalDriverName').textContent = d.full_name || 'Driver';
    const body = document.getElementById('modalDriverBody');
    const online = d.is_online ? '<span class="badge badge-online">Online</span>' : '<span class="badge badge-offline">Offline</span>';
    const rating = d.rating ? '★'.repeat(Math.round(d.rating)) + ' ' + parseFloat(d.rating).toFixed(1) : '—';
    body.innerHTML = `
        <div class="detail-row"><span class="detail-label">ID:</span><span class="detail-value">${(d.id||'').substring(0,8)}</span></div>
        <div class="detail-row"><span class="detail-label">Full Name:</span><span class="detail-value">${d.full_name||'—'}</span></div>
        <div class="detail-row"><span class="detail-label">Phone:</span><span class="detail-value">${d.phone||'—'}</span></div>
        <div class="detail-row"><span class="detail-label">Vehicle Model:</span><span class="detail-value">${d.vehicle_model||'—'}</span></div>
        <div class="detail-row"><span class="detail-label">Plate Number:</span><span class="detail-value">${d.vehicle_plate||'—'}</span></div>
        <div class="detail-row"><span class="detail-label">Status:</span><span class="detail-value">${online}</span></div>
        <div class="detail-row"><span class="detail-label">Total Rides:</span><span class="detail-value">${d.total_trips||0}</span></div>
        <div class="detail-row"><span class="detail-label">Rating:</span><span class="detail-value" style="color:#fbbc04">${rating}</span></div>
    `;
    document.getElementById('driverModal').classList.add('active');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

// Close modal on overlay click
document.getElementById('driverModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal('driverModal');
});
document.getElementById('addDriverModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal('addDriverModal');
});

// Initialize
window.addEventListener('DOMContentLoaded', () => {
    visibleRows = Array.from(document.querySelectorAll('#driversBody tr[data-name]'));
    applyPagination();
});
</script>

<?php require_once 'includes/footer.php'; ?>

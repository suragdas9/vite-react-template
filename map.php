<?php
require_once 'includes/auth.php';
require_once 'includes/config.php';
require_once 'includes/header.php';
?>

<div class="card map-container">
    <div id="map"></div>
</div>

<div class="map-info-bar">
    <div class="map-stat">
        <i data-lucide="circle" style="width:12px;height:12px;fill:#34a853;stroke:#34a853"></i>
        <strong id="onlineCount">—</strong>&nbsp;Online Drivers
    </div>
    <div class="map-stat">
        <i data-lucide="circle" style="width:12px;height:12px;fill:#9aa0a6;stroke:#9aa0a6"></i>
        <strong id="offlineCount">—</strong>&nbsp;Offline Drivers
    </div>
    <div class="map-stat">
        <i data-lucide="clock" style="width:14px;height:14px;stroke:var(--text-muted)"></i>
        <span id="lastUpdated" style="font-size:12px;color:var(--text-muted)">Last updated: —</span>
    </div>
    <button class="btn btn-outline btn-sm" onclick="loadDrivers()">
        <i data-lucide="refresh-cw" style="width:14px;height:14px;vertical-align:middle;margin-right:4px"></i>Refresh
    </button>
</div>

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="/assets/js/map.js"></script>

<?php require_once 'includes/footer.php'; ?>

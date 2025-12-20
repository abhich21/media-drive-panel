<?php
/**
 * MDM Client - GPS Tracking Report
 * View real-time promoter GPS tracking and location history
 */

$pageTitle = 'GPS Tracking Report';
$currentPage = 'attendance';
$clientLogo = 'Client Logo';

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

// requireAuth(['client', 'superadmin']);

// TODO: Fetch from database and GPS tracking API
$gpsData = [
    ['id' => 1, 'name' => 'John Smith', 'location' => 'Phoenix Mall, Whitefield', 'lastUpdate' => '13:45', 'distance' => '42.5 km', 'stops' => 5, 'status' => 'active'],
    ['id' => 2, 'name' => 'Sarah Johnson', 'location' => 'Forum Mall, Koramangala', 'lastUpdate' => '13:42', 'distance' => '38.2 km', 'stops' => 4, 'status' => 'active'],
    ['id' => 3, 'name' => 'Mike Wilson', 'location' => 'Orion Mall, Brigade Gateway', 'lastUpdate' => '13:38', 'distance' => '51.8 km', 'stops' => 6, 'status' => 'active'],
    ['id' => 4, 'name' => 'Emily Brown', 'location' => 'Offline - Last at MG Road', 'lastUpdate' => '11:20', 'distance' => '28.3 km', 'stops' => 3, 'status' => 'inactive'],
    ['id' => 5, 'name' => 'David Lee', 'location' => 'VR Mall, Bengaluru', 'lastUpdate' => '13:50', 'distance' => '45.7 km', 'stops' => 5, 'status' => 'active'],
];

$summary = [
    'activePromters' => count(array_filter($gpsData, fn($g) => $g['status'] === 'active')),
    'totalDistance' => array_sum(array_map(fn($g) => floatval($g['distance']), $gpsData)),
    'totalStops' => array_sum(array_map(fn($g) => $g['stops'], $gpsData)),
    'avgSpeed' => 45, // km/h - would be calculated from actual GPS data
];

include __DIR__ . '/../../components/layout.php';
?>

<!-- Tracking Summary Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="mdm-card text-center">
        <div class="text-3xl font-bold text-mdm-success"><?= $summary['activePromters'] ?></div>
        <div class="text-sm text-mdm-text/60 mt-1">Active Promoters</div>
    </div>
    <div class="mdm-card text-center">
        <div class="text-3xl font-bold text-mdm-accent"><?= number_format($summary['totalDistance'], 1) ?> km</div>
        <div class="text-sm text-mdm-text/60 mt-1">Total Distance</div>
    </div>
    <div class="mdm-card text-center">
        <div class="text-3xl font-bold text-mdm-text"><?= $summary['totalStops'] ?></div>
        <div class="text-sm text-mdm-text/60 mt-1">Total Stops</div>
    </div>
    <div class="mdm-card text-center">
        <div class="text-3xl font-bold text-mdm-warning"><?= $summary['avgSpeed'] ?> km/h</div>
        <div class="text-sm text-mdm-text/60 mt-1">Avg Speed</div>
    </div>
</div>

<!-- Date/Time Filter -->
<div class="mdm-card mb-6">
    <div class="flex flex-wrap items-center gap-4">
        <span class="text-sm font-medium text-mdm-text">Date Range:</span>
        <input type="date" value="<?= date('Y-m-d') ?>" class="mdm-tag border-0 cursor-pointer text-sm px-3 py-1.5"
            id="startDate">
        <span class="text-mdm-text/60">to</span>
        <input type="date" value="<?= date('Y-m-d') ?>" class="mdm-tag border-0 cursor-pointer text-sm px-3 py-1.5"
            id="endDate">
        <button onclick="filterByDateRange()" 
            class="px-4 py-1.5 bg-mdm-accent text-white rounded-lg text-sm font-medium hover:bg-mdm-accent/90 transition-colors">
            Apply Filter
        </button>
        <button onclick="refreshTracking()" 
            class="px-4 py-1.5 bg-mdm-success text-white rounded-lg text-sm font-medium hover:bg-mdm-success/90 transition-colors ml-auto">
            🔄 Refresh Live
        </button>
    </div>
</div>

<!-- GPS Map View -->
<div class="mdm-card mb-6 overflow-hidden">
    <div class="p-4 border-b border-mdm-tag">
        <h2 class="text-lg font-semibold text-mdm-text">Live GPS Map</h2>
        <p class="text-sm text-mdm-text/60 mt-1">Real-time promoter location tracking</p>
    </div>
    <div class="relative bg-mdm-bg/50" style="height: 450px;">
        <!-- Map Container - Replace with actual Google Maps/Mapbox integration -->
        <div id="gpsMap" class="w-full h-full flex items-center justify-center">
            <div class="text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-mdm-text/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
                <p class="text-sm text-mdm-text/60 mb-2">Map integration placeholder</p>
                <p class="text-xs text-mdm-text/40">Connect Google Maps API or Mapbox for live tracking</p>
            </div>
        </div>
    </div>
</div>

<!-- Promoter Tracking Table -->
<div class="mdm-card overflow-hidden">
    <div class="p-4 border-b border-mdm-tag">
        <h2 class="text-lg font-semibold text-mdm-text">Promoter Location Status</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-mdm-tag bg-mdm-bg/30">
                    <th class="text-left py-4 px-4 font-semibold text-mdm-text">Promoter</th>
                    <th class="text-left py-4 px-4 font-semibold text-mdm-text">Current Location</th>
                    <th class="text-left py-4 px-4 font-semibold text-mdm-text">Last Update</th>
                    <th class="text-left py-4 px-4 font-semibold text-mdm-text">Distance</th>
                    <th class="text-left py-4 px-4 font-semibold text-mdm-text">Stops</th>
                    <th class="text-left py-4 px-4 font-semibold text-mdm-text">Status</th>
                    <th class="text-left py-4 px-4 font-semibold text-mdm-text">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($gpsData as $record): ?>
                    <?php
                    $statusClasses = [
                        'active' => 'bg-green-100 text-green-800',
                        'inactive' => 'bg-gray-100 text-gray-800',
                    ];
                    $statusClass = $statusClasses[$record['status']] ?? 'bg-gray-100 text-gray-800';
                    $statusLabel = ucfirst($record['status']);
                    ?>
                    <tr class="border-b border-mdm-tag/50 hover:bg-mdm-bg/50 transition-colors">
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-mdm-tag flex items-center justify-center font-medium text-mdm-text">
                                    <?= strtoupper(substr($record['name'], 0, 1)) ?>
                                </div>
                                <span class="font-medium text-mdm-text"><?= h($record['name']) ?></span>
                            </div>
                        </td>
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-2 text-mdm-text">
                                <svg class="w-4 h-4 text-mdm-accent" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm"><?= h($record['location']) ?></span>
                            </div>
                        </td>
                        <td class="py-4 px-4 text-mdm-text text-sm">
                            <?= h($record['lastUpdate']) ?>
                        </td>
                        <td class="py-4 px-4 text-mdm-text font-medium text-sm">
                            <?= h($record['distance']) ?>
                        </td>
                        <td class="py-4 px-4 text-mdm-text text-sm">
                            <?= $record['stops'] ?>
                        </td>
                        <td class="py-4 px-4">
                            <span class="px-3 py-1 rounded-full text-xs font-medium <?= $statusClass ?>">
                                <?= $statusLabel ?>
                            </span>
                        </td>
                        <td class="py-4 px-4">
                            <button onclick="viewRoute(<?= $record['id'] ?>)" 
                                class="text-mdm-accent hover:text-mdm-accent/80 text-sm font-medium transition-colors">
                                View Route →
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function filterByDateRange() {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        
        const url = new URL(window.location);
        url.searchParams.set('start', startDate);
        url.searchParams.set('end', endDate);
        window.location = url;
    }

    function refreshTracking() {
        // In production, this would fetch fresh GPS data via AJAX
        window.location.reload();
    }

    function viewRoute(promoterId) {
        // In production, this would show detailed route on map
        alert(`Viewing detailed route for promoter ID: ${promoterId}\n\nThis will show the full GPS tracking history on the map.`);
    }

    // Auto-refresh every 60 seconds for live tracking
    // Uncomment in production
    // setInterval(refreshTracking, 60000);
</script>

<?php include __DIR__ . '/../../components/layout-footer.php'; ?>
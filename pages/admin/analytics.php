<?php
/**
 * MDM Admin - Analytics
 * Admin analytics dashboard
 */

$pageTitle = 'Analytics';
$currentPage = 'analytics';
$clientLogo = 'Admin Panel';

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

// requireAuth(['superadmin']);

// TODO: Fetch from database
$stats = [
    'totalEvents' => 12,
    'totalCars' => 150,
    'totalPromoters' => 35,
    'totalDrives' => 1250,
    'totalDistance' => 15680.5,
];

include __DIR__ . '/../../components/layout.php';
?>

<!-- Summary Stats -->
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="mdm-card text-center">
        <div class="text-3xl font-bold text-mdm-text"><?= $stats['totalEvents'] ?></div>
        <div class="text-sm text-mdm-text/60">Total Events</div>
    </div>
    <div class="mdm-card text-center">
        <div class="text-3xl font-bold text-mdm-text"><?= $stats['totalCars'] ?></div>
        <div class="text-sm text-mdm-text/60">Total Cars</div>
    </div>
    <div class="mdm-card text-center">
        <div class="text-3xl font-bold text-mdm-text"><?= $stats['totalPromoters'] ?></div>
        <div class="text-sm text-mdm-text/60">Promoters</div>
    </div>
    <div class="mdm-card text-center">
        <div class="text-3xl font-bold text-mdm-text"><?= number_format($stats['totalDrives']) ?></div>
        <div class="text-sm text-mdm-text/60">Total Drives</div>
    </div>
    <div class="mdm-card text-center">
        <div class="text-3xl font-bold text-mdm-text"><?= number_format($stats['totalDistance'], 0) ?></div>
        <div class="text-sm text-mdm-text/60">KM Covered</div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="mdm-card">
        <h3 class="text-lg font-semibold text-mdm-text mb-4">Events by Month</h3>
        <canvas id="eventsChart" height="200"></canvas>
    </div>
    <div class="mdm-card">
        <h3 class="text-lg font-semibold text-mdm-text mb-4">Drives by Month</h3>
        <canvas id="drivesChart" height="200"></canvas>
    </div>
</div>

<!-- Top Performers -->
<div class="mdm-card">
    <h3 class="text-lg font-semibold text-mdm-text mb-4">Top Performing Cars</h3>
    <div class="space-y-3">
        <?php
        $topCars = [
            ['name' => 'Tata Harrier', 'drives' => 156, 'km' => 2340],
            ['name' => 'Tata Safari', 'drives' => 142, 'km' => 2180],
            ['name' => 'Tata Nexon EV', 'drives' => 138, 'km' => 1950],
        ];
        foreach ($topCars as $index => $car):
            ?>
            <div class="flex items-center justify-between p-3 bg-mdm-bg rounded-xl">
                <div class="flex items-center gap-3">
                    <span
                        class="w-8 h-8 rounded-full bg-mdm-tag flex items-center justify-center font-bold text-mdm-text"><?= $index + 1 ?></span>
                    <span class="font-medium text-mdm-text"><?= h($car['name']) ?></span>
                </div>
                <div class="flex gap-4 text-sm">
                    <span class="text-mdm-text/60"><?= $car['drives'] ?> drives</span>
                    <span class="text-mdm-text/60"><?= number_format($car['km']) ?> km</span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Events Chart
        new Chart(document.getElementById('eventsChart'), {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Events',
                    data: [2, 1, 3, 2, 2, 2],
                    backgroundColor: '#C7C1B4',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });

        // Drives Chart
        new Chart(document.getElementById('drivesChart'), {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Drives',
                    data: [180, 220, 310, 280, 350, 410],
                    borderColor: '#080808',
                    backgroundColor: 'rgba(8, 8, 8, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    });
</script>

<?php include __DIR__ . '/../../components/layout-footer.php'; ?>
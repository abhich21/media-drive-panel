<?php
/**
 * MDM Promoter Dashboard
 * Main dashboard for ground-level operators
 */

$pageTitle = 'My Dashboard';
$currentPage = 'dashboard';
$clientLogo = 'Client Logo';

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../components/stat-card.php';

// requireAuth('promoter');

$user = getCurrentUser() ?? ['name' => 'Promoter', 'id' => 1];

// TODO: Fetch from database
$myStats = [
    'carsAssigned' => 5,
    'drivesToday' => 3,
    'pendingReturns' => 1,
    'feedbackPending' => 2,
];

$myCars = [
    ['id' => 1, 'name' => 'BMW X5 Sport', 'status' => 'on_drive', 'journalist' => 'Mike Ross', 'exitTime' => '10:30'],
    ['id' => 2, 'name' => 'Mercedes GLC', 'status' => 'cleaned', 'journalist' => null, 'exitTime' => null],
    ['id' => 3, 'name' => 'Audi Q7', 'status' => 'standby', 'journalist' => null, 'exitTime' => null],
];

$schedule = [
    ['time' => '09:00', 'car' => 'BMW X5', 'journalist' => 'John Smith', 'outlet' => 'Auto Weekly'],
    ['time' => '11:30', 'car' => 'Mercedes GLC', 'journalist' => 'Sarah Johnson', 'outlet' => 'Car Magazine'],
    ['time' => '14:00', 'car' => 'Audi Q7', 'journalist' => 'Mike Wilson', 'outlet' => 'Drive Today'],
];

include __DIR__ . '/../../components/layout.php';
?>

<!-- Welcome Section -->
<div class="mdm-card mb-6 bg-gradient-to-r from-mdm-sidebar to-gray-700 text-white">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold">Welcome back, <?= h($user['name']) ?>!</h2>
            <p class="text-white/70 mt-1"><?= date('l, F j, Y') ?></p>
        </div>
        <button onclick="markAttendance()"
            class="px-6 py-3 bg-white/10 hover:bg-white/20 rounded-xl text-white font-medium transition-colors">
            ✓ Mark Attendance
        </button>
    </div>
</div>

<!-- Quick Stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="mdm-card text-center">
        <div class="text-3xl font-bold text-mdm-text"><?= $myStats['carsAssigned'] ?></div>
        <div class="text-sm text-mdm-text/60 mt-1">Cars Assigned</div>
    </div>
    <div class="mdm-card text-center">
        <div class="text-3xl font-bold text-mdm-success"><?= $myStats['drivesToday'] ?></div>
        <div class="text-sm text-mdm-text/60 mt-1">Drives Today</div>
    </div>
    <div class="mdm-card text-center">
        <div class="text-3xl font-bold text-mdm-warning"><?= $myStats['pendingReturns'] ?></div>
        <div class="text-sm text-mdm-text/60 mt-1">Pending Returns</div>
    </div>
    <div class="mdm-card text-center">
        <div class="text-3xl font-bold text-mdm-alert"><?= $myStats['feedbackPending'] ?></div>
        <div class="text-sm text-mdm-text/60 mt-1">Feedback Pending</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- My Cars -->
    <div class="mdm-card">
        <h3 class="text-lg font-semibold text-mdm-text mb-4">My Cars Today</h3>
        <div class="space-y-3">
            <?php foreach ($myCars as $car): ?>
                <?php
                $statusBadges = [
                    'on_drive' => ['bg-blue-100 text-blue-800', 'On Drive'],
                    'cleaned' => ['bg-green-100 text-green-800', 'Ready'],
                    'standby' => ['bg-gray-100 text-gray-800', 'Standby'],
                    'cleaning' => ['bg-yellow-100 text-yellow-800', 'Cleaning'],
                ];
                $badge = $statusBadges[$car['status']] ?? ['bg-gray-100', 'Unknown'];
                ?>
                <div class="flex items-center justify-between p-4 bg-mdm-bg rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-mdm-tag rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-mdm-text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M8 17h.01M16 17h.01M3 11l1.5-5A2 2 0 016.4 4.5h11.2a2 2 0 011.9 1.5L21 11M3 11v6a1 1 0 001 1h1m16-7v6a1 1 0 01-1 1h-1M3 11h18" />
                            </svg>
                        </div>
                        <div>
                            <div class="font-medium text-mdm-text"><?= h($car['name']) ?></div>
                            <?php if ($car['journalist']): ?>
                                <div class="text-sm text-mdm-text/60">With: <?= h($car['journalist']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 rounded-full text-xs font-medium <?= $badge[0] ?>">
                            <?= $badge[1] ?>
                        </span>
                        <button onclick="updateCarStatus(<?= $car['id'] ?>)"
                            class="p-2 hover:bg-mdm-tag rounded-lg transition-colors">
                            <svg class="w-5 h-5 text-mdm-text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                            </svg>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <a href="<?= BASE_PATH ?>/pages/promoter/car-status.php"
            class="block mt-4 text-center text-sm text-mdm-accent hover:text-mdm-text transition-colors">
            View All Assigned Cars →
        </a>
    </div>

    <!-- Today's Schedule -->
    <div class="mdm-card">
        <h3 class="text-lg font-semibold text-mdm-text mb-4">Today's Schedule</h3>
        <div class="space-y-3">
            <?php foreach ($schedule as $slot): ?>
                <div class="flex items-center gap-4 p-4 bg-mdm-bg rounded-xl">
                    <div class="text-center">
                        <div class="text-lg font-bold text-mdm-text"><?= $slot['time'] ?></div>
                    </div>
                    <div class="w-px h-12 bg-mdm-tag"></div>
                    <div class="flex-1">
                        <div class="font-medium text-mdm-text"><?= h($slot['journalist']) ?></div>
                        <div class="text-sm text-mdm-text/60"><?= h($slot['outlet']) ?> • <?= h($slot['car']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <a href="<?= BASE_PATH ?>/pages/promoter/exit-log.php"
            class="block mt-4 text-center text-sm text-mdm-accent hover:text-mdm-text transition-colors">
            View Full Schedule →
        </a>
    </div>
</div>

<!-- Quick Actions -->
<div class="mdm-card mt-6">
    <h3 class="text-lg font-semibold text-mdm-text mb-4">Quick Actions</h3>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="<?= BASE_PATH ?>/pages/promoter/car-status.php"
            class="flex flex-col items-center gap-2 p-4 bg-mdm-bg rounded-xl hover:bg-mdm-tag transition-colors">
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </div>
            <span class="text-sm font-medium text-mdm-text">Update Status</span>
        </a>

        <a href="<?= BASE_PATH ?>/pages/promoter/exit-log.php"
            class="flex flex-col items-center gap-2 p-4 bg-mdm-bg rounded-xl hover:bg-mdm-tag transition-colors">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </div>
            <span class="text-sm font-medium text-mdm-text">Log Exit</span>
        </a>

        <a href="<?= BASE_PATH ?>/pages/promoter/feedback.php"
            class="flex flex-col items-center gap-2 p-4 bg-mdm-bg rounded-xl hover:bg-mdm-tag transition-colors">
            <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
            </div>
            <span class="text-sm font-medium text-mdm-text">Log Return</span>
        </a>

        <a href="<?= BASE_PATH ?>/pages/promoter/feedback.php"
            class="flex flex-col items-center gap-2 p-4 bg-mdm-bg rounded-xl hover:bg-mdm-tag transition-colors">
            <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </div>
            <span class="text-sm font-medium text-mdm-text">Submit Feedback</span>
        </a>
    </div>
</div>

<script>
    function markAttendance() {
        if (confirm('Mark your attendance for today?')) {
            // TODO: API call
            alert('Attendance marked successfully!');
        }
    }

    function updateCarStatus(carId) {
        window.location = '<?= BASE_PATH ?>/pages/promoter/car-status.php?id=' + carId;
    }
</script>

<?php include __DIR__ . '/../../components/layout-footer.php'; ?>
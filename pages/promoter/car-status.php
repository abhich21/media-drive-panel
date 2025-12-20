<?php
/**
 * MDM Promoter - Car Status Update
 * Update car status (standby, cleaning, cleaned, on_drive, returned, hotel)
 */

$pageTitle = 'Update Car Status';
$currentPage = 'cars';
$clientLogo = 'Client Logo';

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

// requireAuth('promoter');

$carId = $_GET['id'] ?? null;

// TODO: Fetch from database
$car = [
    'id' => $carId ?? 1,
    'name' => 'BMW X5 Sport',
    'model' => '2024',
    'registration' => 'MH-01-AB-1234',
    'currentStatus' => 'cleaned',
    'lastKm' => 45678.5,
    'lastFuel' => 85,
];

$statuses = [
    'standby' => ['label' => 'Standby', 'icon' => 'clock', 'color' => 'gray'],
    'cleaning' => ['label' => 'Cleaning', 'icon' => 'sparkles', 'color' => 'yellow'],
    'cleaned' => ['label' => 'Cleaned', 'icon' => 'check', 'color' => 'green'],
    'pod_lineup' => ['label' => 'Pod Line Up', 'icon' => 'list', 'color' => 'orange'],
    'on_drive' => ['label' => 'On Drive', 'icon' => 'car', 'color' => 'blue'],
    'returned' => ['label' => 'Returned', 'icon' => 'arrow-left', 'color' => 'purple'],
    'hotel' => ['label' => 'Back to Hotel', 'icon' => 'building', 'color' => 'gray'],
];

include __DIR__ . '/../../components/layout.php';
?>

<!-- Car Info Card -->
<div class="mdm-card mb-6">
    <div class="flex items-center gap-4">
        <div class="w-20 h-20 bg-mdm-tag rounded-xl flex items-center justify-center">
            <svg class="w-10 h-10 text-mdm-text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 17h.01M16 17h.01M3 11l1.5-5A2 2 0 016.4 4.5h11.2a2 2 0 011.9 1.5L21 11M3 11v6a1 1 0 001 1h1m16-7v6a1 1 0 01-1 1h-1M3 11h18"/>
            </svg>
        </div>
        <div>
            <h2 class="text-xl font-semibold text-mdm-text"><?= h($car['name']) ?></h2>
            <p class="text-mdm-text/60"><?= h($car['registration']) ?> • <?= h($car['model']) ?></p>
            <div class="flex items-center gap-4 mt-2 text-sm">
                <span class="text-mdm-text/70">Last KM: <strong><?= number_format($car['lastKm'], 1) ?></strong></span>
                <span class="text-mdm-text/70">Fuel: <strong><?= $car['lastFuel'] ?>%</strong></span>
            </div>
        </div>
    </div>
</div>

<!-- Status Selection -->
<div class="mdm-card">
    <h3 class="text-lg font-semibold text-mdm-text mb-6">Select New Status</h3>
    
    <form action="<?= BASE_PATH ?>/api/cars.php" method="POST" id="statusForm">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="car_id" value="<?= $car['id'] ?>">
        
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <?php foreach ($statuses as $key => $status): ?>
                <?php
                $isActive = $car['currentStatus'] === $key;
                $colorClasses = [
                    'gray' => 'border-gray-300 hover:border-gray-500',
                    'yellow' => 'border-yellow-300 hover:border-yellow-500',
                    'green' => 'border-green-300 hover:border-green-500',
                    'orange' => 'border-orange-300 hover:border-orange-500',
                    'blue' => 'border-blue-300 hover:border-blue-500',
                    'purple' => 'border-purple-300 hover:border-purple-500',
                ];
                $activeClasses = [
                    'gray' => 'border-gray-500 bg-gray-50',
                    'yellow' => 'border-yellow-500 bg-yellow-50',
                    'green' => 'border-green-500 bg-green-50',
                    'orange' => 'border-orange-500 bg-orange-50',
                    'blue' => 'border-blue-500 bg-blue-50',
                    'purple' => 'border-purple-500 bg-purple-50',
                ];
                ?>
                <label class="cursor-pointer">
                    <input type="radio" 
                           name="status" 
                           value="<?= $key ?>" 
                           class="hidden peer"
                           <?= $isActive ? 'checked' : '' ?>>
                    <div class="p-4 rounded-xl border-2 transition-all <?= $isActive ? $activeClasses[$status['color']] : $colorClasses[$status['color']] ?> peer-checked:<?= $activeClasses[$status['color']] ?>">
                        <div class="text-center">
                            <div class="w-12 h-12 mx-auto rounded-full bg-mdm-tag flex items-center justify-center mb-2">
                                <!-- Icon placeholder -->
                                <span class="text-xl">
                                    <?php
                                    $icons = [
                                        'standby' => '⏱️',
                                        'cleaning' => '🧹',
                                        'cleaned' => '✅',
                                        'pod_lineup' => '📋',
                                        'on_drive' => '🚗',
                                        'returned' => '↩️',
                                        'hotel' => '🏨',
                                    ];
                                    echo $icons[$key] ?? '📌';
                                    ?>
                                </span>
                            </div>
                            <span class="font-medium text-mdm-text"><?= $status['label'] ?></span>
                        </div>
                    </div>
                </label>
            <?php endforeach; ?>
        </div>
        
        <!-- Notes -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-mdm-text mb-2">Notes (Optional)</label>
            <textarea name="notes" 
                      rows="3" 
                      class="w-full px-4 py-3 rounded-xl border border-mdm-tag bg-white focus:border-mdm-sidebar focus:outline-none resize-none"
                      placeholder="Add any notes about this status change..."></textarea>
        </div>
        
        <!-- Submit -->
        <div class="flex gap-4">
            <button type="submit" class="flex-1 py-3 bg-mdm-sidebar text-white font-medium rounded-xl hover:bg-black transition-colors">
                Update Status
            </button>
            <a href="<?= BASE_PATH ?>/pages/promoter/dashboard.php" class="px-6 py-3 border border-mdm-tag text-mdm-text font-medium rounded-xl hover:bg-mdm-bg transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
document.getElementById('statusForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const status = formData.get('status');
    
    // If going to on_drive, redirect to exit log form
    if (status === 'on_drive') {
        window.location = '<?= BASE_PATH ?>/pages/promoter/exit-log.php?car_id=<?= $car['id'] ?>';
        return;
    }
    
    // If returned, redirect to return log form
    if (status === 'returned') {
        window.location = '<?= BASE_PATH ?>/pages/promoter/feedback.php?car_id=<?= $car['id'] ?>';
        return;
    }
    
    // TODO: Submit via API
    alert('Status updated successfully!');
    window.location = '<?= BASE_PATH ?>/pages/promoter/dashboard.php';
});
</script>

<?php include __DIR__ . '/../../components/layout-footer.php'; ?>

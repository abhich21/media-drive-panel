<?php
/**
 * MDM Promoter - Car Status Update
 * Matches UI Mockup - Fixed Version
 */

$pageTitle = 'Car Status Update';
$currentPage = 'cars';

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

// For now, hardcode event_id = 1 (should come from session/context in production)
$eventId = 1;

// Get all cars for this event (for the dropdown)
$allCars = dbQuery("
    SELECT id, car_code, name, status 
    FROM cars 
    WHERE event_id = ? AND is_active = 1 
    ORDER BY car_code ASC
", [$eventId]);

$carId = $_GET['car_id'] ?? $_GET['id'] ?? null;

// Fetch car if ID provided
$car = null;
if ($carId) {
    $car = dbQueryOne("SELECT * FROM cars WHERE id = ?", [$carId]);
}

// Status Options matching mockup colors
$statusOptions = [
    'pod_lineup' => [
        'label' => 'POD Line Up',
        'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>',
        'bg_color' => '#8FBC8F',
    ],
    'cleaning' => [
        'label' => 'Cleaning',
        'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>',
        'bg_color' => '#DEB887',
    ],
    'cleaned' => [
        'label' => 'Cleaned',
        'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/></svg>',
        'bg_color' => '#87CEEB',
    ],
    'on_drive' => [
        'label' => 'On Drive',
        'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>',
        'bg_color' => '#CD5C5C',
    ],
    'standby' => [
        'label' => 'Standby',
        'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'bg_color' => '#A8A8A8',
    ]
];

include __DIR__ . '/../../components/layout.php';
?>

<style>
    .car-status-page {
        max-width: 900px;
        margin: 0 auto;
        padding: 20px;
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 24px;
    }

    .car-selector {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 32px;
    }

    .car-selector-label {
        font-size: 1rem;
        font-weight: 600;
        color: #1a1a1a;
    }

    .car-dropdown {
        position: relative;
    }

    .car-dropdown select {
        appearance: none;
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 8px 40px 8px 16px;
        font-size: 1rem;
        font-weight: 600;
        color: #1a1a1a;
        cursor: pointer;
        min-width: 80px;
    }

    .car-dropdown select:focus {
        outline: none;
        border-color: #999;
    }

    .car-dropdown::after {
        content: '';
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 6px solid #666;
        pointer-events: none;
    }

    .car-name {
        color: #888;
        font-size: 0.9rem;
    }

    .status-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        background: #DCCFB8;
        padding: 16px;
        border-radius: 12px;
        max-width: 500px;
        margin: 0 auto 40px auto;
    }

    .status-btn {
        height: 80px;
        border-radius: 10px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: white;
        cursor: pointer;
        transition: all 0.2s;
        border: 3px solid transparent;
    }

    .status-btn:hover:not(.disabled) {
        transform: scale(1.02);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .status-btn.selected {
        border-color: white;
        box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
    }

    .status-btn.disabled {
        opacity: 0.35;
        cursor: not-allowed;
        filter: grayscale(0.5);
    }

    .status-btn .icon {
        opacity: 0.9;
    }

    .status-btn .label {
        font-weight: 600;
        font-size: 0.95rem;
    }

    .submit-row {
        display: flex;
        justify-content: center;
    }

    .submit-btn {
        background: #1a1a1a;
        color: white;
        padding: 12px 32px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 1rem;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
    }

    .submit-btn:hover {
        background: #333;
    }

    .submit-btn:disabled {
        background: #ccc;
        cursor: not-allowed;
    }

    .on-drive-warning {
        text-align: center;
        padding: 16px;
        color: #c00;
        font-weight: 500;
    }

    .empty-state {
        background: #fff;
        border: 2px dashed #ddd;
        border-radius: 16px;
        padding: 60px 20px;
        text-align: center;
        color: #999;
    }

    .empty-state svg {
        width: 64px;
        height: 64px;
        margin-bottom: 16px;
        opacity: 0.5;
    }
</style>

<div class="car-status-page">
    <a href="dashboard.php"
        class="inline-flex items-center gap-2 text-gray-500 hover:text-gray-900 font-medium mb-6 transition-colors">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to Dashboard
    </a>

    <!-- Page Title -->
    <h1 class="page-title">Car Status Update</h1>

    <!-- Car Selector -->
    <div class="car-selector">
        <span class="car-selector-label">Car Code</span>
        <div class="car-dropdown">
            <select id="carSelect" onchange="window.location.href='?car_id='+this.value">
                <?php if (!$carId): ?>
                    <option value="" disabled selected>Select</option>
                <?php endif; ?>
                <?php foreach ($allCars as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($carId == $c['id']) ? 'selected' : '' ?>>
                        <?= h($c['car_code']) ?> (<?= h($c['status']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($car): ?>
            <span class="car-name"><?= h($car['name']) ?></span>
        <?php endif; ?>
    </div>

    <?php if ($car): ?>
        <!-- Status Form -->
        <form id="statusForm" method="POST" action="<?= BASE_PATH ?>/api/cars.php">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="car_id" value="<?= $car['id'] ?>">

            <div class="status-grid">
                <?php foreach ($statusOptions as $key => $option):
                    $isSelected = ($car['status'] === $key);
                    $isDisabled = ($car['status'] === 'on_drive' && $key !== 'on_drive');
                    ?>
                    <div class="status-btn <?= $isSelected ? 'selected' : '' ?> <?= $isDisabled ? 'disabled' : '' ?>"
                        style="background-color: <?= $option['bg_color'] ?>;" data-status="<?= $key ?>"
                        onclick="<?= $isDisabled ? '' : "selectStatus('$key')" ?>">
                        <div class="icon"><?= $option['icon'] ?></div>
                        <div class="label"><?= $option['label'] ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <input type="hidden" name="status" id="selectedStatus" value="<?= $car['status'] ?>">

            <?php if ($car['status'] === 'on_drive'): ?>
                <div class="on-drive-warning">
                    ⚠️ Car is On Drive. <a href="dashboard.php" style="color:#1a1a1a;font-weight:700;">Log Return</a> to change
                    status.
                </div>
            <?php else: ?>
                <div class="submit-row">
                    <button type="submit" class="submit-btn">Update Status</button>
                </div>
            <?php endif; ?>
        </form>
    <?php else: ?>
        <!-- Empty State -->
        <div class="empty-state">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M8 17h.01M16 17h.01M3 11l1.5-5A2 2 0 016.4 4.5h11.2a2 2 0 011.9 1.5L21 11M3 11v6a1 1 0 001 1h1m16-7v6a1 1 0 01-1 1h-1M3 11h18" />
            </svg>
            <p>Select a car from the dropdown above</p>
        </div>
    <?php endif; ?>
</div>

<script>
    // Status selection
    function selectStatus(status) {
        document.querySelectorAll('.status-btn').forEach(btn => {
            btn.classList.remove('selected');
            if (btn.dataset.status === status) {
                btn.classList.add('selected');
            }
        });
        document.getElementById('selectedStatus').value = status;
    }

    // Form submit
    document.getElementById('statusForm')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = this.querySelector('.submit-btn');
        btn.disabled = true;
        btn.textContent = 'Updating...';

        try {
            const apiUrl = '<?= BASE_PATH ?>/api/cars.php';
            const res = await fetch(apiUrl, { method: 'POST', body: new FormData(this) });
            const json = await res.json();
            if (json.success) {
                showToast('Status updated successfully', 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showToast(json.message || 'Error updating status', 'error');
                btn.disabled = false;
                btn.textContent = 'Update Status';
            }
        } catch (e) {
            console.error('Error:', e);
            showToast('Error updating status', 'error');
            btn.disabled = false;
            btn.textContent = 'Update Status';
        }
    });

    // Toast notification
    function showToast(message, type = 'info') {
        document.querySelectorAll('.toast').forEach(t => t.remove());

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 12px 24px;
            border-radius: 12px;
            color: white;
            font-weight: 500;
            z-index: 9999;
            ${type === 'success' ? 'background: #16a34a;' : type === 'error' ? 'background: #dc2626;' : 'background: #1a1a1a;'}
        `;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
</script>

<?php include __DIR__ . '/../../components/status-legend.php'; ?>
<?php include __DIR__ . '/../../components/layout-footer.php'; ?>
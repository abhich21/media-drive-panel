<?php
/**
 * MDM Cleaning Staff Dashboard
 * Simple 3-button interface for car status updates
 */

$pageTitle = 'Cleaning Dashboard';
$currentPage = 'dashboard';

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

// requireAuth('cleaning_staff');

// For now, hardcode event_id = 1
$eventId = 1;

// Fetch cars for cleaning (includes under_cleaning and cleaning statuses)
$cars = dbQuery("
    SELECT c.*, e.name as event_name 
    FROM cars c
    JOIN events e ON e.id = c.event_id
    WHERE c.status IN ('returned', 'under_cleaning', 'cleaning', 'cleaned', 'standby')
    AND c.is_active = 1 AND c.event_id = ?
    ORDER BY 
        CASE 
            WHEN c.status = 'returned' THEN 1
            WHEN c.status = 'under_cleaning' THEN 2
            WHEN c.status = 'cleaning' THEN 3
            WHEN c.status = 'cleaned' THEN 4
            ELSE 5
        END, c.car_code
", [$eventId]);

// Count by status
$counts = [
    'returned' => 0,
    'under_cleaning' => 0,
    'cleaning' => 0,
    'cleaned' => 0,
    'standby' => 0
];
foreach ($cars as $car) {
    if (isset($counts[$car['status']])) {
        $counts[$car['status']]++;
    }
}

include __DIR__ . '/../../components/cleaning-layout.php';
?>

<style>
    .status-btn {
        padding: 12px 20px;
        border-radius: 12px;
        font-size: 0.95rem;
        font-weight: 600;
        border: 2px solid transparent;
        cursor: pointer;
        transition: all 0.2s;
        min-width: 120px;
        text-align: center;
    }

    .status-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .status-btn.cleaning {
        background: #FFF8E1;
        color: #F57C00;
        border-color: #FFE082;
    }

    .status-btn.cleaning.active,
    .status-btn.cleaning:hover:not(:disabled) {
        background: #F57C00;
        color: white;
        transform: scale(1.02);
    }

    .status-btn.cleaned {
        background: #E8F5E9;
        color: #2E7D32;
        border-color: #A5D6A7;
    }

    .status-btn.cleaned.active,
    .status-btn.cleaned:hover:not(:disabled) {
        background: #2E7D32;
        color: white;
        transform: scale(1.02);
    }

    .status-btn.pod_lineup {
        background: #E3F2FD;
        color: #1565C0;
        border-color: #90CAF9;
    }

    .status-btn.pod_lineup.active,
    .status-btn.pod_lineup:hover:not(:disabled) {
        background: #1565C0;
        color: white;
        transform: scale(1.02);
    }

    .car-row {
        background: #FFFFFF;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        transition: all 0.2s;
    }

    .car-row:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .car-row.needs-attention {
        border-left: 5px solid #F57C00;
        background: linear-gradient(90deg, #FFF8E1 0%, #FFFFFF 10%);
    }

    @media (max-width: 768px) {
        .car-row {
            flex-direction: column;
            gap: 16px;
            align-items: flex-start;
        }
        
        .car-row > div:last-child {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .status-btn {
            width: 100%;
        }
    }
</style>

<!-- Stats -->
<div class="grid grid-cols-5 gap-4 mb-6">
    <div class="mdm-card text-center">
        <div class="text-2xl font-bold text-orange-600"><?= $counts['returned'] ?></div>
        <div class="text-sm text-mdm-text/60">Returned</div>
    </div>
    <div class="mdm-card text-center">
        <div class="text-2xl font-bold text-amber-600"><?= $counts['under_cleaning'] ?></div>
        <div class="text-sm text-mdm-text/60">Under Cleaning</div>
    </div>
    <div class="mdm-card text-center">
        <div class="text-2xl font-bold text-yellow-600"><?= $counts['cleaning'] ?></div>
        <div class="text-sm text-mdm-text/60">Cleaning</div>
    </div>
    <div class="mdm-card text-center">
        <div class="text-2xl font-bold text-green-600"><?= $counts['cleaned'] ?></div>
        <div class="text-sm text-mdm-text/60">Cleaned</div>
    </div>
    <div class="mdm-card text-center">
        <div class="text-2xl font-bold text-blue-600"><?= $counts['standby'] ?></div>
        <div class="text-sm text-mdm-text/60">POD Lineup</div>
    </div>
</div>

<!-- Cars List -->
<div class="mdm-card">
    <h2 class="text-lg font-bold text-mdm-text mb-4">Cars</h2>

    <?php if (empty($cars)): ?>
        <div class="text-center py-8 text-mdm-text/60">No cars to display.</div>
    <?php else: ?>
        <div id="carsList">
            <?php foreach ($cars as $car): 
                $needsAttention = in_array($car['status'], ['returned', 'under_cleaning']);
                $statusLabels = [
                    'returned' => '🔙 Returned',
                    'under_cleaning' => '🧽 Under Cleaning',
                    'cleaning' => '🧹 Cleaning',
                    'cleaned' => '✅ Cleaned',
                    'standby' => '📋 POD Lineup'
                ];
            ?>
                <div class="car-row <?= $needsAttention ? 'needs-attention' : '' ?>" id="car-<?= $car['id'] ?>">
                    <div>
                        <div class="font-bold text-lg"><?= h($car['car_code']) ?></div>
                        <div class="text-sm text-mdm-text/60"><?= h($car['name']) ?></div>
                        <div class="text-xs text-amber-600 font-medium mt-1"><?= $statusLabels[$car['status']] ?? $car['status'] ?></div>
                    </div>
                    <div class="flex gap-2">
                        <button class="status-btn cleaning <?= $car['status'] === 'cleaning' ? 'active' : '' ?>"
                            onclick="updateStatus(<?= $car['id'] ?>, 'cleaning')" <?= $car['status'] === 'cleaning' ? 'disabled' : '' ?>>
                            🧹 Cleaning
                        </button>
                        <button class="status-btn cleaned <?= $car['status'] === 'cleaned' ? 'active' : '' ?>"
                            onclick="updateStatus(<?= $car['id'] ?>, 'cleaned')" <?= $car['status'] === 'cleaned' ? 'disabled' : '' ?>>
                            ✅ Cleaned
                        </button>
                        <button class="status-btn pod_lineup <?= $car['status'] === 'standby' ? 'active' : '' ?>"
                            onclick="updateStatus(<?= $car['id'] ?>, 'standby')" <?= $car['status'] === 'standby' ? 'disabled' : '' ?>>
                            📋 POD Lineup
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    async function updateStatus(carId, newStatus) {
        try {
            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('car_id', carId);
            formData.append('status', newStatus);

            const response = await fetch('<?= BASE_PATH ?>/api/cleaning.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                showToast('Status updated successfully', 'success');
                
                // Update UI
                const row = document.getElementById('car-' + carId);
                row.querySelectorAll('.status-btn').forEach(btn => {
                    btn.classList.remove('active');
                    btn.disabled = false;
                });

                const activeBtn = row.querySelector('.status-btn.' + (newStatus === 'standby' ? 'pod_lineup' : newStatus));
                if (activeBtn) {
                    activeBtn.classList.add('active');
                    activeBtn.disabled = true;
                }

                // Update status label
                const statusLabels = {
                    'cleaning': '🧹 Cleaning',
                    'cleaned': '✅ Cleaned',
                    'standby': '📋 POD Lineup'
                };
                const statusEl = row.querySelector('.text-xs.text-amber-600');
                if (statusEl && statusLabels[newStatus]) {
                    statusEl.textContent = statusLabels[newStatus];
                }

                // Remove needs-attention if status changed
                row.classList.remove('needs-attention');
                
                // Reload page to update stats
                setTimeout(() => location.reload(), 1000);

            } else {
                showToast(data.message || 'Error updating status', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Failed to update status', 'error');
        }
    }

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

<?php include __DIR__ . '/../../components/cleaning-layout-footer.php'; ?>
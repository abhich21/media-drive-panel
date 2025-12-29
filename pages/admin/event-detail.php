<?php
/**
 * MDM Admin - Event Detail Page
 * View single event with full details
 */

$currentPage = 'events';
$clientLogo = 'Admin Panel';

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/queries/events.php';

requireAdmin();

$eventId = intval($_GET['id'] ?? 0);

if (!$eventId) {
    header('Location: ' . BASE_PATH . '/pages/admin/events.php');
    exit;
}

$event = getEventById($eventId);

if (!$event) {
    header('Location: ' . BASE_PATH . '/pages/admin/events.php');
    exit;
}

$pageTitle = $event['name'];
$cars = getEventCars($eventId, 50);
$promoters = getEventPromoters($eventId);

$statusColors = [
    'active' => 'bg-green-100 text-green-800',
    'upcoming' => 'bg-blue-100 text-blue-800',
    'completed' => 'bg-gray-100 text-gray-600',
];
$statusColor = $statusColors[$event['status']] ?? 'bg-gray-100 text-gray-600';

include __DIR__ . '/../../components/layout.php';
?>

<!-- Back Button & Header -->
<div class="mb-6">
    <a href="<?= BASE_PATH ?>/pages/admin/events.php" 
       class="inline-flex items-center gap-2 text-mdm-text/60 hover:text-mdm-text transition-colors mb-4">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Back to Events
    </a>
    
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <h1 class="text-2xl font-bold text-mdm-text"><?= h($event['name']) ?></h1>
                <span class="px-3 py-1 rounded-full text-sm <?= $statusColor ?>"><?= ucfirst($event['status']) ?></span>
            </div>
            <p class="text-mdm-text/60"><?= h($event['client_name'] ?? 'No client specified') ?></p>
        </div>
        <button onclick="openEditModal()" class="mdm-header-btn">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit Event
        </button>
    </div>
</div>

<!-- Event Stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="mdm-card text-center">
        <div class="text-3xl font-bold text-mdm-text"><?= $event['car_count'] ?></div>
        <div class="text-sm text-mdm-text/60 mt-1">Cars</div>
    </div>
    <div class="mdm-card text-center">
        <div class="text-3xl font-bold text-mdm-text"><?= $event['promoter_count'] ?></div>
        <div class="text-sm text-mdm-text/60 mt-1">Promoters</div>
    </div>
    <div class="mdm-card text-center">
        <div class="text-3xl font-bold text-blue-500"><?= $event['drive_count'] ?></div>
        <div class="text-sm text-mdm-text/60 mt-1">Total Drives</div>
    </div>
    <div class="mdm-card text-center">
        <div class="text-3xl font-bold text-green-500"><?= $event['car_stats']['on_drive'] ?? 0 ?></div>
        <div class="text-sm text-mdm-text/60 mt-1">On Drive Now</div>
    </div>
</div>

<!-- Event Details -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Info Card -->
    <div class="mdm-card">
        <h3 class="font-semibold text-mdm-text mb-4">Event Information</h3>
        <div class="space-y-3">
            <div class="flex items-center gap-3">
                <span class="text-xl">📍</span>
                <div>
                    <div class="text-xs text-mdm-text/60">Location</div>
                    <div class="text-mdm-text"><?= h($event['location'] ?? 'Not specified') ?></div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xl">📅</span>
                <div>
                    <div class="text-xs text-mdm-text/60">Start Date</div>
                    <div class="text-mdm-text"><?= formatDate($event['start_date']) ?></div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xl">🏁</span>
                <div>
                    <div class="text-xs text-mdm-text/60">End Date</div>
                    <div class="text-mdm-text"><?= formatDate($event['end_date']) ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Car Status Breakdown -->
    <div class="mdm-card lg:col-span-2">
        <h3 class="font-semibold text-mdm-text mb-4">Car Status Breakdown</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <?php 
            $statuses = [
                'standby' => ['label' => 'Standby', 'color' => 'bg-gray-100 text-gray-800'],
                'cleaning' => ['label' => 'Cleaning', 'color' => 'bg-yellow-100 text-yellow-800'],
                'cleaned' => ['label' => 'Cleaned', 'color' => 'bg-green-100 text-green-800'],
                'on_drive' => ['label' => 'On Drive', 'color' => 'bg-blue-100 text-blue-800'],
                'returned' => ['label' => 'Returned', 'color' => 'bg-purple-100 text-purple-800'],
                'pod_lineup' => ['label' => 'Pod Lineup', 'color' => 'bg-orange-100 text-orange-800'],
                'hotel' => ['label' => 'Hotel', 'color' => 'bg-gray-100 text-gray-600'],
            ];
            foreach ($statuses as $key => $info): 
                $count = $event['car_stats'][$key] ?? 0;
            ?>
                <div class="p-3 rounded-xl <?= $info['color'] ?> text-center">
                    <div class="text-xl font-bold"><?= $count ?></div>
                    <div class="text-xs"><?= $info['label'] ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Cars List -->
<div class="mdm-card mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-mdm-text">Cars (<?= count($cars) ?>)</h3>
        <a href="<?= BASE_PATH ?>/pages/admin/cars.php?event_id=<?= $eventId ?>" 
           class="text-sm text-mdm-accent hover:text-mdm-text">View All →</a>
    </div>
    
    <?php if (empty($cars)): ?>
        <p class="text-mdm-text/60 text-center py-8">No cars assigned to this event yet.</p>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach (array_slice($cars, 0, 6) as $car): 
                $statusBadge = getStatusBadge($car['status']);
            ?>
                <div class="p-4 bg-mdm-bg rounded-xl">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-medium text-mdm-text"><?= h($car['name']) ?></span>
                        <span class="px-2 py-1 rounded-full text-xs <?= $statusBadge[0] ?> <?= $statusBadge[1] ?>">
                            <?= $statusBadge[2] ?>
                        </span>
                    </div>
                    <div class="text-sm text-mdm-text/60">
                        <?= h($car['model'] ?? '') ?> • <?= h($car['registration_number'] ?? '') ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Promoters List -->
<div class="mdm-card">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-mdm-text">Promoters (<?= count($promoters) ?>)</h3>
    </div>
    
    <?php if (empty($promoters)): ?>
        <p class="text-mdm-text/60 text-center py-8">No promoters assigned to this event yet.</p>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($promoters as $promoter): ?>
                <div class="flex items-center gap-3 p-4 bg-mdm-bg rounded-xl">
                    <div class="w-10 h-10 rounded-full bg-mdm-sidebar flex items-center justify-center text-white font-medium">
                        <?= strtoupper(substr($promoter['name'], 0, 1)) ?>
                    </div>
                    <div>
                        <div class="font-medium text-mdm-text"><?= h($promoter['name']) ?></div>
                        <div class="text-sm text-mdm-text/60"><?= h($promoter['email']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Edit Event Modal -->
<div id="editModal" class="hidden" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; display: none; align-items: center; justify-content: center; padding: 1rem; overflow-y: auto;">
    <div class="bg-white rounded-2xl w-full max-w-md mx-auto" style="max-height: 90vh; overflow-y: auto;">
        <div class="p-6 border-b border-mdm-tag">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-mdm-text">Edit Event</h2>
                <button onclick="closeEditModal()" class="text-mdm-text/60 hover:text-mdm-text">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        <form id="editEventForm" class="p-6 space-y-4">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= $event['id'] ?>">
            
            <div>
                <label class="block text-sm font-medium text-mdm-text mb-2">Event Name *</label>
                <input type="text" name="name" value="<?= h($event['name']) ?>" required
                       class="w-full px-4 py-3 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-mdm-text mb-2">Client Name</label>
                <input type="text" name="client_name" value="<?= h($event['client_name'] ?? '') ?>"
                       class="w-full px-4 py-3 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-mdm-text mb-2">Location</label>
                <input type="text" name="location" value="<?= h($event['location'] ?? '') ?>"
                       class="w-full px-4 py-3 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none">
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-mdm-text mb-2">Start Date *</label>
                    <input type="date" name="start_date" value="<?= $event['start_date'] ?>" required
                           class="w-full px-4 py-3 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-mdm-text mb-2">End Date *</label>
                    <input type="date" name="end_date" value="<?= $event['end_date'] ?>" required
                           class="w-full px-4 py-3 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-mdm-text mb-2">Logo URL</label>
                <input type="url" name="logo_url" value="<?= h($event['logo_url'] ?? '') ?>" placeholder="https://example.com/logo.png"
                       class="w-full px-4 py-3 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-mdm-text mb-2">Status</label>
                <select name="status" 
                        class="w-full px-4 py-3 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none">
                    <option value="upcoming" <?= $event['status'] === 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
                    <option value="active" <?= $event['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="completed" <?= $event['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>
            
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeEditModal()" 
                        class="flex-1 py-3 border border-mdm-tag rounded-xl text-mdm-text hover:bg-mdm-bg transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        class="flex-1 py-3 bg-mdm-sidebar text-white rounded-xl hover:bg-black transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const basePath = '<?= BASE_PATH ?>';

function openEditModal() {
    const modal = document.getElementById('editModal');
    modal.style.display = 'flex';
    modal.classList.remove('hidden');
}

function closeEditModal() {
    const modal = document.getElementById('editModal');
    modal.style.display = 'none';
    modal.classList.add('hidden');
}

document.getElementById('editEventForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Saving...';
    submitBtn.disabled = true;
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch(`${basePath}/api/events.php`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Failed to update event');
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    } catch (error) {
        alert('An error occurred. Please try again.');
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }
});

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeEditModal();
});

// Close modal on backdrop click
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});
</script>

<?php include __DIR__ . '/../../components/layout-footer.php'; ?>


<?php
/**
 * MDM Admin - Events Management
 * Admin events management page
 */

$pageTitle = 'Events';
$currentPage = 'events';
$clientLogo = 'Admin Panel';

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

// requireAuth(['superadmin']);

// TODO: Fetch from database
$events = [
    ['id' => 1, 'name' => 'Tata Motors Media Drive 2024', 'client' => 'Tata Motors', 'location' => 'Goa', 'startDate' => '2024-12-18', 'endDate' => '2024-12-19', 'status' => 'active', 'cars' => 50],
    ['id' => 2, 'name' => 'Tata Safari Launch', 'client' => 'Tata Motors', 'location' => 'Mumbai', 'startDate' => '2024-12-25', 'endDate' => '2024-12-26', 'status' => 'upcoming', 'cars' => 30],
    ['id' => 3, 'name' => 'EV Roadshow', 'client' => 'Tata Motors', 'location' => 'Delhi', 'startDate' => '2024-11-10', 'endDate' => '2024-11-11', 'status' => 'completed', 'cars' => 25],
];

include __DIR__ . '/../../components/layout.php';
?>

<!-- Actions Bar -->
<div class="mdm-card mb-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="text-sm text-mdm-text/60">Total: <strong><?= count($events) ?> events</strong></span>
        </div>
        <button class="mdm-header-btn" onclick="alert('Create Event form coming soon')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Create Event
        </button>
    </div>
</div>

<!-- Events List -->
<div class="space-y-4">
    <?php foreach ($events as $event):
        $statusColors = [
            'active' => 'bg-green-100 text-green-800',
            'upcoming' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-gray-100 text-gray-600',
        ];
        $statusColor = $statusColors[$event['status']] ?? 'bg-gray-100 text-gray-600';
        ?>
        <div class="mdm-card mdm-card-hover">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <h3 class="font-semibold text-lg text-mdm-text"><?= h($event['name']) ?></h3>
                        <span
                            class="px-2 py-1 rounded-full text-xs <?= $statusColor ?>"><?= ucfirst($event['status']) ?></span>
                    </div>
                    <div class="flex flex-wrap gap-4 text-sm text-mdm-text/60">
                        <span>📍 <?= h($event['location']) ?></span>
                        <span>📅 <?= formatDate($event['startDate']) ?> - <?= formatDate($event['endDate']) ?></span>
                        <span>🚗 <?= $event['cars'] ?> cars</span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button class="mdm-tag text-xs" onclick="alert('View event <?= $event['id'] ?>')">View</button>
                    <button class="mdm-tag text-xs" onclick="alert('Edit event <?= $event['id'] ?>')">Edit</button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/../../components/layout-footer.php'; ?>
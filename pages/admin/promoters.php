<?php
/**
 * MDM Admin - Manage Promoters
 * Admin promoter management page
 */

$pageTitle = 'Manage Promoters';
$currentPage = 'promoters';
$clientLogo = 'Admin Panel';

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

// requireAuth(['superadmin']);

// TODO: Fetch from database
$promoters = [
    ['id' => 1, 'name' => 'John Smith', 'email' => 'john@example.com', 'phone' => '+91 98765 43210', 'status' => 'active', 'assignedCars' => 4],
    ['id' => 2, 'name' => 'Sarah Johnson', 'email' => 'sarah@example.com', 'phone' => '+91 98765 43211', 'status' => 'active', 'assignedCars' => 5],
    ['id' => 3, 'name' => 'Mike Wilson', 'email' => 'mike@example.com', 'phone' => '+91 98765 43212', 'status' => 'active', 'assignedCars' => 3],
    ['id' => 4, 'name' => 'Emily Brown', 'email' => 'emily@example.com', 'phone' => '+91 98765 43213', 'status' => 'inactive', 'assignedCars' => 0],
];

include __DIR__ . '/../../components/layout.php';
?>

<!-- Actions Bar -->
<div class="mdm-card mb-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="text-sm text-mdm-text/60">Total: <strong><?= count($promoters) ?> promoters</strong></span>
        </div>
        <button class="mdm-header-btn" onclick="alert('Add Promoter form coming soon')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Promoter
        </button>
    </div>
</div>

<!-- Promoters Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php foreach ($promoters as $promoter): ?>
        <div class="mdm-card mdm-card-hover">
            <div class="flex items-start justify-between mb-3">
                <div class="w-12 h-12 rounded-full bg-mdm-tag flex items-center justify-center">
                    <span class="text-lg font-bold text-mdm-text"><?= strtoupper(substr($promoter['name'], 0, 1)) ?></span>
                </div>
                <span
                    class="px-2 py-1 rounded-full text-xs <?= $promoter['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' ?>">
                    <?= ucfirst($promoter['status']) ?>
                </span>
            </div>
            <h3 class="font-semibold text-mdm-text"><?= h($promoter['name']) ?></h3>
            <p class="text-sm text-mdm-text/60 mb-2"><?= h($promoter['email']) ?></p>
            <p class="text-sm text-mdm-text/60 mb-3"><?= h($promoter['phone']) ?></p>
            <div class="flex items-center justify-between pt-3 border-t border-mdm-tag/30">
                <span class="text-sm text-mdm-text/60"><?= $promoter['assignedCars'] ?> cars assigned</span>
                <button class="text-mdm-text/60 hover:text-mdm-text"
                    onclick="alert('Edit promoter <?= $promoter['id'] ?>')">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </button>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/../../components/layout-footer.php'; ?>
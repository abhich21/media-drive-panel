<?php
/**
 * MDM Superadmin Dashboard
 * Full control panel for CloudPlay admins
 */

$pageTitle = 'Admin Dashboard';
$currentPage = 'dashboard';
$clientLogo = 'MDM Admin';

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/queries/dashboard.php';

// requireAuth('superadmin');

// Fetch data from database
$overview = getOverviewStats();
$recentEvents = getRecentEvents(5);

include __DIR__ . '/../../components/layout.php';
?>

<!-- Overview Stats -->
<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
    <div class="mdm-card text-center">
        <div class="text-3xl font-bold text-mdm-text"><?= $overview['totalEvents'] ?></div>
        <div class="text-sm text-mdm-text/60 mt-1">Total Events</div>
    </div>
    <div class="mdm-card text-center">
        <div class="text-3xl font-bold text-mdm-success"><?= $overview['activeEvents'] ?></div>
        <div class="text-sm text-mdm-text/60 mt-1">Active Now</div>
    </div>
    <div class="mdm-card text-center">
        <div class="text-3xl font-bold text-mdm-text"><?= $overview['totalCars'] ?></div>
        <div class="text-sm text-mdm-text/60 mt-1">Total Cars</div>
    </div>
    <div class="mdm-card text-center">
        <div class="text-3xl font-bold text-mdm-text"><?= $overview['totalPromoters'] ?></div>
        <div class="text-sm text-mdm-text/60 mt-1">Promoters</div>
    </div>
    <div class="mdm-card text-center">
        <div class="text-3xl font-bold text-mdm-text"><?= number_format($overview['totalDrives']) ?></div>
        <div class="text-sm text-mdm-text/60 mt-1">Total Drives</div>
    </div>
    <div class="mdm-card text-center">
        <div class="text-3xl font-bold text-blue-500"><?= $overview['activeNow'] ?></div>
        <div class="text-sm text-mdm-text/60 mt-1">Cars On Drive</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="mdm-card mb-8">
    <h3 class="text-lg font-semibold text-mdm-text mb-4">Quick Actions</h3>
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <a href="<?= BASE_PATH ?>/pages/admin/events.php?action=new"
            class="flex items-center gap-3 p-4 bg-mdm-bg rounded-xl hover:bg-mdm-tag transition-colors">
            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </div>
            <span class="font-medium text-mdm-text">New Event</span>
        </a>

        <a href="<?= BASE_PATH ?>/pages/admin/cars.php?action=add"
            class="flex items-center gap-3 p-4 bg-mdm-bg rounded-xl hover:bg-mdm-tag transition-colors">
            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </div>
            <span class="font-medium text-mdm-text">Add Car</span>
        </a>

        <a href="<?= BASE_PATH ?>/pages/admin/promoters.php?action=add"
            class="flex items-center gap-3 p-4 bg-mdm-bg rounded-xl hover:bg-mdm-tag transition-colors">
            <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
            </div>
            <span class="font-medium text-mdm-text">Add Promoter</span>
        </a>

        <a href="<?= BASE_PATH ?>/pages/admin/analytics.php"
            class="flex items-center gap-3 p-4 bg-mdm-bg rounded-xl hover:bg-mdm-tag transition-colors">
            <div class="w-10 h-10 rounded-lg bg-yellow-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <span class="font-medium text-mdm-text">Analytics</span>
        </a>

        <a href="<?= BASE_PATH ?>/pages/admin/settings.php"
            class="flex items-center gap-3 p-4 bg-mdm-bg rounded-xl hover:bg-mdm-tag transition-colors">
            <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <span class="font-medium text-mdm-text">Settings</span>
        </a>
    </div>
</div>

<!-- Recent Events -->
<div class="mdm-card">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-mdm-text">Recent Events</h3>
        <a href="<?= BASE_PATH ?>/pages/admin/events.php"
            class="text-sm text-mdm-accent hover:text-mdm-text transition-colors">View All
            →</a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-mdm-tag">
                    <th class="text-left py-3 px-4 font-semibold text-mdm-text">Event</th>
                    <th class="text-left py-3 px-4 font-semibold text-mdm-text">Client</th>
                    <th class="text-left py-3 px-4 font-semibold text-mdm-text">Date</th>
                    <th class="text-left py-3 px-4 font-semibold text-mdm-text">Status</th>
                    <th class="text-right py-3 px-4 font-semibold text-mdm-text">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentEvents as $event): ?>
                    <?php
                    $statusClass = $event['status'] === 'active'
                        ? 'bg-green-100 text-green-800'
                        : 'bg-gray-100 text-gray-800';
                    ?>
                    <tr class="border-b border-mdm-tag/50 hover:bg-mdm-bg/50">
                        <td class="py-4 px-4 font-medium text-mdm-text"><?= h($event['name']) ?></td>
                        <td class="py-4 px-4 text-mdm-text/70"><?= h($event['client']) ?></td>
                        <td class="py-4 px-4 text-mdm-text/70"><?= formatDate($event['date']) ?></td>
                        <td class="py-4 px-4">
                            <span class="px-3 py-1 rounded-full text-xs font-medium <?= $statusClass ?>">
                                <?= ucfirst($event['status']) ?>
                            </span>
                        </td>
                        <td class="py-4 px-4 text-right">
                            <a href="<?= BASE_PATH ?>/pages/admin/events.php?id=<?= $event['id'] ?>"
                                class="text-mdm-accent hover:text-mdm-text">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../components/layout-footer.php'; ?>
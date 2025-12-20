<?php
/**
 * MDM Client - Event Details
 * Shows current event information
 */

$pageTitle = 'Event Details';
$currentPage = 'events';
$clientLogo = 'Tata Motors';

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

// requireAuth(['client', 'superadmin']);

// TODO: Fetch from database
$event = [
    'name' => 'Tata Motors Media Drive 2024',
    'client' => 'Tata Motors',
    'location' => 'Goa, India',
    'venue' => 'Taj Exotica Resort & Spa',
    'startDate' => '2024-12-18',
    'endDate' => '2024-12-19',
    'status' => 'active',
    'totalCars' => 50,
    'totalPromoters' => 12,
    'totalJournalists' => 45,
];

include __DIR__ . '/../../components/layout.php';
?>

<!-- Event Header Card -->
<div class="mdm-card mb-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <span class="mdm-tag text-xs mb-2 inline-block"><?= ucfirst($event['status']) ?></span>
            <h2 class="text-2xl font-bold text-mdm-text"><?= h($event['name']) ?></h2>
            <p class="text-mdm-text/60 mt-1"><?= h($event['client']) ?></p>
        </div>
        <div class="flex gap-3">
            <div class="text-center px-4 py-2 bg-mdm-tag rounded-xl">
                <div class="text-2xl font-bold text-mdm-text"><?= formatDate($event['startDate'], 'd') ?></div>
                <div class="text-xs text-mdm-text/60"><?= formatDate($event['startDate'], 'M Y') ?></div>
            </div>
            <div class="flex items-center text-mdm-text/40">→</div>
            <div class="text-center px-4 py-2 bg-mdm-tag rounded-xl">
                <div class="text-2xl font-bold text-mdm-text"><?= formatDate($event['endDate'], 'd') ?></div>
                <div class="text-xs text-mdm-text/60"><?= formatDate($event['endDate'], 'M Y') ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Event Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <!-- Cars -->
    <div class="mdm-card text-center">
        <div class="mdm-icon-circle mx-auto mb-3">
            <svg class="w-6 h-6 text-mdm-text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M8 17h.01M16 17h.01M3 11l1.5-5A2 2 0 016.4 4.5h11.2a2 2 0 011.9 1.5L21 11M3 11v6a1 1 0 001 1h1m16-7v6a1 1 0 01-1 1h-1M3 11h18" />
            </svg>
        </div>
        <div class="text-3xl font-bold text-mdm-text"><?= $event['totalCars'] ?></div>
        <div class="text-sm text-mdm-text/60">Total Cars</div>
    </div>

    <!-- Promoters -->
    <div class="mdm-card text-center">
        <div class="mdm-icon-circle mx-auto mb-3">
            <svg class="w-6 h-6 text-mdm-text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </div>
        <div class="text-3xl font-bold text-mdm-text"><?= $event['totalPromoters'] ?></div>
        <div class="text-sm text-mdm-text/60">Promoters</div>
    </div>

    <!-- Journalists -->
    <div class="mdm-card text-center">
        <div class="mdm-icon-circle mx-auto mb-3">
            <svg class="w-6 h-6 text-mdm-text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
            </svg>
        </div>
        <div class="text-3xl font-bold text-mdm-text"><?= $event['totalJournalists'] ?></div>
        <div class="text-sm text-mdm-text/60">Journalists</div>
    </div>
</div>

<!-- Location Details -->
<div class="mdm-card">
    <h3 class="text-lg font-semibold text-mdm-text mb-4">Location Details</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <p class="text-sm text-mdm-text/60 mb-1">City / Region</p>
            <p class="text-lg font-medium text-mdm-text"><?= h($event['location']) ?></p>
        </div>
        <div>
            <p class="text-sm text-mdm-text/60 mb-1">Venue</p>
            <p class="text-lg font-medium text-mdm-text"><?= h($event['venue']) ?></p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../components/layout-footer.php'; ?>
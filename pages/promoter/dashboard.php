<?php
/**
 * MDM Promoter Dashboard - Styled to match UI mockup
 * PR Firm based workflow with real-time influencer mapping
 */

$pageTitle = 'Dashboard';
$currentPage = 'dashboard';

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

// requireAuth('promoter');

$user = getCurrentUser() ?? ['name' => 'Promoter', 'id' => 6];
$userId = $user['id'] ?? 6;
$userName = $user['name'] ?? 'Promoter';

// Get active event for this promoter
$eventId = $_SESSION['active_event_id'] ?? 1;

// Get PR firm names for header display
$prFirmNames = dbQuery("SELECT pf.name FROM pr_firms pf JOIN promoter_pr_firms ppf ON ppf.pr_firm_id = pf.id WHERE ppf.promoter_id = ? AND ppf.event_id = ? ORDER BY pf.name", [$userId, $eventId]);
$assignedFirms = array_column($prFirmNames, 'name');
$prFirmDisplay = !empty($assignedFirms) ? implode(', ', $assignedFirms) : 'No PR Firm Assigned';

// Fetch promoter stats
$stats = dbQueryOne("
    SELECT 
        COUNT(DISTINCT c.id) as total_cars,
        SUM(CASE WHEN c.status IN ('standby', 'cleaned', 'pod_lineup') THEN 1 ELSE 0 END) as ready_cars,
        SUM(CASE WHEN c.status = 'on_drive' THEN 1 ELSE 0 END) as on_drive,
        SUM(CASE WHEN c.status IN ('cleaning', 'returned', 'under_inspection') THEN 1 ELSE 0 END) as pending_cars
    FROM cars c
    JOIN pr_firm_cars pfc ON pfc.car_id = c.id
    JOIN promoter_pr_firms ppf ON ppf.pr_firm_id = pfc.pr_firm_id AND ppf.event_id = pfc.event_id
    WHERE ppf.promoter_id = ? AND ppf.event_id = ? AND c.is_active = 1
", [$userId, $eventId]) ?? ['total_cars' => 0, 'ready_cars' => 0, 'on_drive' => 0, 'pending_cars' => 0];

// Fetch PR firms for this promoter
$prFirms = dbQuery("
    SELECT pf.*, ppf.event_id,
        (SELECT COUNT(*) FROM pr_firm_cars pfc 
         JOIN cars c ON c.id = pfc.car_id 
         WHERE pfc.pr_firm_id = pf.id 
         AND pfc.event_id = ppf.event_id 
         AND c.status IN ('standby', 'cleaned', 'pod_lineup')) as ready_cars,
        (SELECT COUNT(*) FROM pr_firm_cars pfc 
         WHERE pfc.pr_firm_id = pf.id AND pfc.event_id = ppf.event_id) as total_cars
    FROM pr_firms pf
    JOIN promoter_pr_firms ppf ON ppf.pr_firm_id = pf.id
    WHERE ppf.promoter_id = ? AND ppf.event_id = ?
    ORDER BY pf.name
", [$userId, $eventId]);

// Fetch cars currently on drive (only most recent exit log per car)
$activeDrives = dbQuery("
    SELECT c.*, cl.journalist_name, cl.exit_time, pf.name as pr_firm_name, cl.id as log_id
    FROM cars c
    JOIN pr_firm_cars pfc ON pfc.car_id = c.id
    JOIN promoter_pr_firms ppf ON ppf.pr_firm_id = pfc.pr_firm_id AND ppf.event_id = pfc.event_id
    JOIN pr_firms pf ON pf.id = pfc.pr_firm_id
    LEFT JOIN car_logs cl ON cl.car_id = c.id AND cl.log_type = 'exit' 
        AND cl.id = (SELECT MAX(cl2.id) FROM car_logs cl2 WHERE cl2.car_id = c.id AND cl2.log_type = 'exit')
    WHERE ppf.promoter_id = ? AND ppf.event_id = ? AND c.status = 'on_drive'
    ORDER BY cl.exit_time DESC
", [$userId, $eventId]);

include __DIR__ . '/../../components/layout.php';
?>

<style>
    /* Header Section */
    .dashboard-header {
        margin-bottom: 24px;
    }

    .dashboard-header .greeting {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 4px;
    }

    .dashboard-header .event-info {
        font-size: 0.9rem;
        color: #666;
    }

    .dashboard-header .event-name {
        font-weight: 600;
        color: #1a1a1a;
    }

    /* Stat Cards - Matching mockup */
    .stat-card {
        background: #FFFFFF;
        border-radius: 16px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    .stat-card .icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .stat-card .icon-box.gray {
        background: #F5F5F5;
    }

    .stat-card .icon-box.green {
        background: #E8F5E9;
    }

    .stat-card .icon-box.red {
        background: #FFEBEE;
    }

    .stat-card .icon-box.yellow {
        background: #FFF8E1;
    }

    .stat-card .stat-content .value {
        font-size: 1.75rem;
        font-weight: 800;
        color: #1a1a1a;
        line-height: 1;
    }

    .stat-card .stat-content .label {
        font-size: 0.8rem;
        color: #888;
        margin-top: 2px;
    }

    /* PR Firm Cards - Matching mockup */
    .section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 16px;
    }

    .pr-firm-card {
        background: #FFFFFF;
        border-radius: 16px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 2px solid transparent;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    .pr-firm-card:hover {
        border-color: #e0e0e0;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .pr-firm-card.active {
        border-color: #1a1a1a;
    }

    .pr-firm-card .firm-name {
        font-size: 1rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 8px;
    }

    .pr-firm-card .firm-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .pr-firm-card .ready-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #E8F5E9;
        color: #2E7D32;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .pr-firm-card .ready-badge .dot {
        width: 6px;
        height: 6px;
        background: #4CAF50;
        border-radius: 50%;
    }

    .pr-firm-card .total-count {
        font-size: 0.8rem;
        color: #999;
    }

    /* Car Cards */
    .car-card {
        background: #FFFFFF;
        border-radius: 16px;
        padding: 16px;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 2px solid transparent;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    .car-card:hover:not(.disabled) {
        border-color: #4CAF50;
        transform: translateY(-2px);
    }

    .car-card.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: #FAFAFA;
    }

    .car-card .car-code {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1a1a1a;
    }

    .car-card .car-name {
        font-size: 0.8rem;
        color: #888;
        margin-top: 4px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .status-badge.ready {
        background: #E8F5E9;
        color: #2E7D32;
    }

    .status-badge.on-drive {
        background: #FFEBEE;
        color: #C62828;
    }

    .status-badge.pending {
        background: #FFF8E1;
        color: #F57C00;
    }

    /* Active Drive Cards */
    .active-drive-card {
        background: #FFFFFF;
        border-radius: 16px;
        padding: 16px 20px;
        border-left: 4px solid #C62828;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    .drive-timer {
        font-family: 'Inter', monospace;
        font-size: 1.25rem;
        font-weight: 700;
        color: #C62828;
    }

    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        align-items: center;
        /* Center on all screens */
        justify-content: center;
        z-index: 1000;
        padding: 16px;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-content {
        background: #FFFFFF;
        border-radius: 24px;
        width: 100%;
        max-width: 500px;
        max-height: 85vh;
        display: flex;
        flex-direction: column;
        animation: modalFadeIn 0.3s ease;
        overflow: hidden;
        /* Added to clip children to rounded corners */
    }

    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: scale(0.95);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    @keyframes slideUp {
        from {
            transform: translateY(100%);
        }

        to {
            transform: translateY(0);
        }
    }

    .modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f0f0f0;
        position: sticky;
        top: 0;
        background: #FFFFFF;
        z-index: 10;
    }

    .modal-body {
        padding: 24px;
        overflow-y: auto;
        /* Scroll only the body part */
        flex: 1;
    }

    @media (min-width: 768px) {
        .modal-content {
            max-height: 80vh;
        }
    }
</style>

<!-- Header Section -->
<div class="dashboard-header">
    <div class="greeting">Hello, <?= h($userName) ?></div>
    <div class="event-info">
        You're assigned to <span class="event-name"><?= h($prFirmDisplay) ?></span>
    </div>
</div>

<!-- Stats Section -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-card">
        <div class="icon-box gray">🚗</div>
        <div class="stat-content">
            <div class="value"><?= intval($stats['total_cars'] ?? 0) ?></div>
            <div class="label">Total Cars</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="icon-box green">✓</div>
        <div class="stat-content">
            <div class="value"><?= intval($stats['ready_cars'] ?? 0) ?></div>
            <div class="label">Ready</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="icon-box red">↗</div>
        <div class="stat-content">
            <div class="value"><?= intval($stats['on_drive'] ?? 0) ?></div>
            <div class="label">On Drive</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="icon-box yellow">⏳</div>
        <div class="stat-content">
            <div class="value"><?= intval($stats['pending_cars'] ?? 0) ?></div>
            <div class="label">Pending</div>
        </div>
    </div>
</div>

<!-- Active Drives Section -->
<?php if (!empty($activeDrives)): ?>
    <div class="mb-6">
        <h2 class="section-title">🚗 Active Drives</h2>
        <div class="space-y-3">
            <?php foreach ($activeDrives as $drive): ?>
                <div class="active-drive-card">
                    <div>
                        <div class="font-bold text-[#1a1a1a]"><?= h($drive['car_code']) ?> - <?= h($drive['name']) ?></div>
                        <div class="text-sm text-gray-500">
                            <?= h($drive['journalist_name']) ?> • <?= h($drive['pr_firm_name']) ?>
                        </div>
                    </div>
                    <div class="text-right flex flex-col items-end gap-2">
                        <div class="drive-timer" data-exit-time="<?= $drive['exit_time'] ?>">--:--</div>
                        <button onclick="openReturnModal(<?= $drive['id'] ?>, '<?= h($drive['car_code']) ?>')"
                            class="px-3 py-1.5 text-sm font-semibold text-red-600 border border-red-200 rounded-lg hover:bg-red-50 hover:border-red-300 transition-colors">
                            Log Return
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- PR Firms Section -->
<div class="mb-6">
    <h2 class="section-title">PR Firms</h2>
    <?php if (empty($prFirms)): ?>
        <div
            class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl p-8 flex flex-col items-center justify-center text-center">
            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                <span class="text-2xl">🏢</span>
            </div>
            <h3 class="text-base font-semibold text-gray-900 mb-1">No PR Firms Assigned</h3>
            <p class="text-sm text-gray-500">You are not currently mapped to any PR firms for this event.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="prFirmsList">
            <?php foreach ($prFirms as $firm): ?>
                <div class="pr-firm-card" onclick="selectPrFirm(<?= $firm['id'] ?>, '<?= h($firm['name']) ?>')"
                    data-firm-id="<?= $firm['id'] ?>">
                    <div class="firm-name"><?= h($firm['name']) ?></div>
                    <div class="firm-meta">
                        <span class="ready-badge">
                            <span class="dot"></span>
                            <?= intval($firm['ready_cars']) ?> cars ready
                        </span>
                        <span class="total-count"><?= intval($firm['total_cars']) ?> total</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Cars for Selected PR Firm (hidden initially) -->
<div id="carsSection" class="mb-6 hidden">
    <div class="flex items-center justify-between mb-4">
        <h2 class="section-title">Cars for <span id="selectedFirmName"></span></h2>
        <button onclick="closeCarsSection()" class="text-sm text-gray-500 hover:text-gray-800">✕ Close</button>
    </div>
    <div id="carsList" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"></div>
</div>

<!-- Exit Form Modal -->
<div class="modal-overlay" id="exitModal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-500" id="modalFirmName"></div>
                    <div class="text-xl font-bold text-[#1a1a1a]">
                        Car <span id="modalCarCode"></span>
                        <span class="status-badge ready ml-2">Ready</span>
                    </div>
                </div>
                <button onclick="closeExitModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        <div class="modal-body">
            <form id="exitForm" onsubmit="submitExit(event)"
                style="height: 100%; display: flex; flex-direction: column;">
                <div class="flex-1 overflow-y-auto px-1">
                    <input type="hidden" id="exitCarId" name="car_id">
                    <input type="hidden" id="exitPrFirmId" name="pr_firm_id">

                    <!-- Influencer Name -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Influencer/Journalist Name *
                        </label>
                        <div class="relative">
                            <input type="text" id="influencerInput" name="journalist_name"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white focus:border-gray-400 focus:outline-none"
                                placeholder="Type name or select..." autocomplete="off" required>
                            <div id="influencerDropdown"
                                class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-xl mt-1 hidden max-h-48 overflow-y-auto z-20 shadow-lg">
                            </div>
                        </div>
                        <input type="hidden" id="influencerId" name="influencer_id">
                    </div>

                    <!-- Phone -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                        <input type="tel" id="influencerPhone" name="journalist_phone"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white focus:border-gray-400 focus:outline-none"
                            placeholder="+91 XXXXX XXXXX">
                    </div>

                    <!-- Starting KM Reading -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Starting Odometer (KM) *</label>
                        <input type="number" name="km_reading" step="0.1" min="0" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white focus:border-gray-400 focus:outline-none"
                            placeholder="Enter current KM reading">
                    </div>

                    <!-- Exit Time -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-xl">
                        <div class="text-sm text-gray-500">Exit Time</div>
                        <div class="text-xl font-bold text-[#1a1a1a]" id="currentTime"></div>
                    </div>
                </div>
        </div>
        <!-- Submit Footer -->
        <div class="mt-auto bg-white sticky bottom-0 z-20 p-6 border-t border-gray-100">
            <button type="submit" style="background-color: #16a34a;"
                class="w-full max-w-sm mx-auto py-3 text-white font-bold text-base hover:bg-green-700 transition-colors flex items-center justify-center gap-2 rounded-xl shadow-md active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
                <span>Confirm & Start Drive</span>
            </button>
        </div>
        </form>
    </div>
</div>
</div>

<!-- Return Log Modal -->
<div class="modal-overlay" id="returnModal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-500">Log Return</div>
                    <div class="text-xl font-bold text-[#1a1a1a]">
                        Car <span id="returnCarCode"></span>
                    </div>
                </div>
                <button onclick="closeReturnModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        <div class="modal-body">
            <form id="returnForm" onsubmit="submitReturn(event)">
                <input type="hidden" id="returnCarId" name="car_id">

                <!-- Return Time (Real-time clock) -->
                <div class="mb-6 p-4 bg-green-50 rounded-xl text-center">
                    <div class="text-sm text-gray-500">Return Time</div>
                    <div class="text-3xl font-bold text-green-700" id="returnCurrentTime">--:--:--</div>
                </div>

                <!-- KM Reading -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Odometer Reading (KM) *</label>
                    <input type="number" name="km_reading" step="0.1" min="0" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white focus:border-gray-400 focus:outline-none"
                        placeholder="Enter current KM">
                </div>

                <!-- Damage Toggle -->
                <div class="mb-4 p-4 bg-gray-50 rounded-xl">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" id="damageToggle" name="has_damage" value="1"
                            class="w-5 h-5 text-red-600 rounded focus:ring-red-500">
                        <div>
                            <div class="font-medium text-gray-900">Report Damage</div>
                            <div class="text-sm text-gray-500">Check if the car has damage</div>
                        </div>
                    </label>
                </div>

                <!-- Damage Description (hidden by default) -->
                <div id="damageSection" class="hidden mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Damage Description *</label>
                    <textarea name="damage_description" rows="3"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white focus:border-gray-400 focus:outline-none"
                        placeholder="Describe the damage..."></textarea>
                </div>

                <button type="submit" style="background-color: #16a34a;"
                    class="w-full py-3 text-white font-bold rounded-xl hover:bg-green-700 transition-colors">
                    ✓ Log Return & Continue to Feedback
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Feedback Modal -->
<div class="modal-overlay" id="feedbackModal">
    <div class="modal-content" style="max-width: 600px; max-height: 90vh;">
        <div class="modal-header">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-500">Quick Feedback for</div>
                    <div class="text-xl font-bold text-[#1a1a1a]" id="feedbackInfluencerName">Loading...</div>
                    <div class="text-sm text-gray-500" id="feedbackContext">Car • PR Firm</div>
                </div>
                <button onclick="skipFeedback()" class="text-sm text-gray-400 hover:text-gray-600">Skip →</button>
            </div>
        </div>
        <div class="modal-body" style="overflow-y: auto;">
            <form id="feedbackForm" onsubmit="submitFeedback(event)">
                <input type="hidden" id="feedbackCarId" name="car_id">
                <input type="hidden" id="feedbackPrFirmId" name="pr_firm_id">
                <input type="hidden" id="feedbackInfluencerId" name="influencer_id">
                <input type="hidden" id="feedbackCarLogId" name="car_log_id">

                <div class="mb-4 text-center text-gray-600">Please rate the drive out of 5</div>

                <!-- Star Ratings -->
                <div class="space-y-4 mb-6">
                    <div class="rating-row">
                        <span class="rating-label">Handling</span>
                        <div class="stars" data-category="handling">
                            <span class="star" data-value="1">★</span>
                            <span class="star" data-value="2">★</span>
                            <span class="star" data-value="3">★</span>
                            <span class="star" data-value="4">★</span>
                            <span class="star" data-value="5">★</span>
                        </div>
                    </div>
                    <div class="rating-row">
                        <span class="rating-label">Comfort</span>
                        <div class="stars" data-category="comfort">
                            <span class="star" data-value="1">★</span>
                            <span class="star" data-value="2">★</span>
                            <span class="star" data-value="3">★</span>
                            <span class="star" data-value="4">★</span>
                            <span class="star" data-value="5">★</span>
                        </div>
                    </div>
                    <div class="rating-row">
                        <span class="rating-label">Performance</span>
                        <div class="stars" data-category="performance">
                            <span class="star" data-value="1">★</span>
                            <span class="star" data-value="2">★</span>
                            <span class="star" data-value="3">★</span>
                            <span class="star" data-value="4">★</span>
                            <span class="star" data-value="5">★</span>
                        </div>
                    </div>
                    <div class="rating-row">
                        <span class="rating-label">NVH</span>
                        <div class="stars" data-category="nvh">
                            <span class="star" data-value="1">★</span>
                            <span class="star" data-value="2">★</span>
                            <span class="star" data-value="3">★</span>
                            <span class="star" data-value="4">★</span>
                            <span class="star" data-value="5">★</span>
                        </div>
                    </div>
                    <div class="rating-row">
                        <span class="rating-label">Features</span>
                        <div class="stars" data-category="features">
                            <span class="star" data-value="1">★</span>
                            <span class="star" data-value="2">★</span>
                            <span class="star" data-value="3">★</span>
                            <span class="star" data-value="4">★</span>
                            <span class="star" data-value="5">★</span>
                        </div>
                    </div>
                    <div class="rating-row">
                        <span class="rating-label">Appearance</span>
                        <div class="stars" data-category="appearance">
                            <span class="star" data-value="1">★</span>
                            <span class="star" data-value="2">★</span>
                            <span class="star" data-value="3">★</span>
                            <span class="star" data-value="4">★</span>
                            <span class="star" data-value="5">★</span>
                        </div>
                    </div>
                    <div class="rating-row" style="background: #f3f4f6; padding: 12px; border-radius: 12px;">
                        <span class="rating-label" style="font-weight: 700;">Overall</span>
                        <div class="stars" data-category="overall">
                            <span class="star" data-value="1">★</span>
                            <span class="star" data-value="2">★</span>
                            <span class="star" data-value="3">★</span>
                            <span class="star" data-value="4">★</span>
                            <span class="star" data-value="5">★</span>
                        </div>
                    </div>
                </div>

                <!-- Comments -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Additional Comments</label>
                    <textarea name="comments" rows="3"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white focus:border-gray-400 focus:outline-none"
                        placeholder="Any additional feedback..."></textarea>
                </div>

                <button type="submit" style="background-color: #16a34a;"
                    class="w-full py-3 text-white font-bold rounded-xl hover:bg-green-700 transition-colors">
                    ✓ Submit Feedback & Continue
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Post-Drive Modal -->
<div class="modal-overlay" id="postDriveModal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-500">Post-Drive Details</div>
                    <div class="text-xl font-bold text-[#1a1a1a]">
                        Car <span id="postDriveCarCode"></span>
                    </div>
                </div>
                <button onclick="closePostDriveModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        <div class="modal-body">
            <form id="postDriveForm" onsubmit="submitPostDrive(event)" enctype="multipart/form-data">
                <input type="hidden" id="postDriveCarId" name="car_id">

                <!-- KM Reading -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Odometer Reading (KM) *</label>
                    <input type="number" name="km_reading" id="postDriveKm" step="0.1" min="0" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white focus:border-gray-400 focus:outline-none"
                        placeholder="Enter current KM">
                </div>

                <!-- Damage Toggle -->
                <div class="mb-4 p-4 bg-gray-50 rounded-xl">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" id="postDriveDamageToggle" name="has_damage" value="1"
                            class="w-5 h-5 text-red-600 rounded focus:ring-red-500">
                        <div>
                            <div class="font-medium text-gray-900">Report Damage</div>
                            <div class="text-sm text-gray-500">Check if the car has damage</div>
                        </div>
                    </label>
                </div>

                <!-- Damage Description -->
                <div id="postDriveDamageSection" class="hidden mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Damage Description *</label>
                    <textarea name="damage_description" rows="2"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white focus:border-gray-400 focus:outline-none"
                        placeholder="Describe the damage..."></textarea>
                </div>

                <!-- Photo Upload -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Car Condition Photos</label>
                    <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center cursor-pointer hover:border-gray-300 transition-colors"
                        onclick="document.getElementById('postDrivePhotos').click()">
                        <svg class="w-10 h-10 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <div class="text-gray-500">Click to upload photos</div>
                        <div class="text-xs text-gray-400 mt-1">Front, rear, sides & odometer</div>
                    </div>
                    <input type="file" id="postDrivePhotos" name="photos[]" multiple accept="image/*" class="hidden"
                        onchange="previewPostDrivePhotos(event)">
                    <div id="postDrivePhotoPreview" class="grid grid-cols-4 gap-2 mt-3"></div>
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes / Comments</label>
                    <textarea name="notes" rows="2"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white focus:border-gray-400 focus:outline-none"
                        placeholder="Any observations about car condition..."></textarea>
                </div>

                <button type="submit" style="background-color: #1a1a1a;"
                    class="w-full py-3 text-white font-bold rounded-xl hover:bg-gray-800 transition-colors">
                    ✓ Complete & Send to Cleaning
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    /* Rating styles for feedback modal */
    .rating-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .rating-row:last-child {
        border-bottom: none;
    }

    .rating-label {
        font-size: 0.95rem;
        color: #1a1a1a;
    }

    .stars {
        display: flex;
        gap: 4px;
    }

    .star {
        font-size: 1.5rem;
        color: #d1d5db;
        cursor: pointer;
        transition: all 0.15s;
    }

    .star:hover,
    .star.filled {
        color: #f59e0b;
    }

    .star:hover {
        transform: scale(1.1);
    }
</style>

<script>
    let selectedPrFirmId = null;
    let selectedPrFirmName = '';
    let influencers = [];
    const eventId = <?= $eventId ?>;

    // Update drive timers
    function updateDriveTimers() {
        document.querySelectorAll('.drive-timer').forEach(timer => {
            const exitTime = new Date(timer.dataset.exitTime);
            const now = new Date();
            const diff = Math.floor((now - exitTime) / 1000);
            const hours = Math.floor(diff / 3600);
            const mins = Math.floor((diff % 3600) / 60);
            const secs = diff % 60;
            timer.textContent = `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        });
    }
    setInterval(updateDriveTimers, 1000);
    updateDriveTimers();

    function updateCurrentTime() {
        document.getElementById('currentTime').textContent = new Date().toLocaleTimeString('en-IN', {
            hour: '2-digit', minute: '2-digit', second: '2-digit'
        });
    }
    setInterval(updateCurrentTime, 1000);

    function selectPrFirm(firmId, firmName) {
        selectedPrFirmId = firmId;
        selectedPrFirmName = firmName;
        document.querySelectorAll('.pr-firm-card').forEach(card => {
            card.classList.toggle('active', card.dataset.firmId == firmId);
        });
        document.getElementById('carsSection').classList.remove('hidden');
        document.getElementById('selectedFirmName').textContent = firmName;
        loadCarsForPrFirm(firmId);
        loadInfluencers(firmId);
    }

    function closeCarsSection() {
        document.getElementById('carsSection').classList.add('hidden');
        document.querySelectorAll('.pr-firm-card').forEach(card => card.classList.remove('active'));
        selectedPrFirmId = null;
    }

    async function loadCarsForPrFirm(firmId) {
        try {
            const response = await fetch(`<?= BASE_PATH ?>/api/cars.php?action=cars_for_pr_firm&pr_firm_id=${firmId}&event_id=${eventId}`);
            const data = await response.json();
            if (data.success) renderCars(data.data);
        } catch (error) { console.error('Error:', error); }
    }

    function renderCars(cars) {
        const container = document.getElementById('carsList');
        container.innerHTML = cars.length === 0
            ? '<div class="col-span-full text-center py-8 text-gray-400">No cars assigned.</div>'
            : '';

        cars.forEach(car => {
            const isReady = car.display_status === 'ready';
            const statusClass = car.display_status === 'ready' ? 'ready' : car.display_status === 'on_drive' ? 'on-drive' : 'pending';
            const statusLabel = car.display_status === 'ready' ? '🟢 Ready' : car.display_status === 'on_drive' ? '🔴 On Drive' : '🟡 Pending';

            const card = document.createElement('div');
            card.className = `car-card ${isReady ? '' : 'disabled'}`;
            card.innerHTML = `
            <div class="flex justify-between items-start mb-2">
                <div class="car-code">${car.car_code}</div>
                <span class="status-badge ${statusClass}">${statusLabel}</span>
            </div>
            <div class="car-name">${car.name}</div>
        `;
            if (isReady) card.onclick = () => openExitModal(car);
            container.appendChild(card);
        });
    }

    async function loadInfluencers(firmId) {
        try {
            const response = await fetch(`<?= BASE_PATH ?>/api/cars.php?action=influencers_for_pr_firm&pr_firm_id=${firmId}&event_id=${eventId}`);
            const data = await response.json();
            if (data.success) { influencers = data.data; renderInfluencerDropdown(); }
        } catch (error) { console.error('Error:', error); }
    }

    function renderInfluencerDropdown() {
        const dropdown = document.getElementById('influencerDropdown');
        dropdown.innerHTML = '';
        influencers.forEach(inf => {
            const option = document.createElement('div');
            option.className = 'px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0';
            option.innerHTML = `<div class="font-medium">${inf.name}</div><div class="text-sm text-gray-500">${inf.outlet || ''}</div>`;
            option.onclick = () => selectInfluencer(inf);
            dropdown.appendChild(option);
        });
    }

    document.getElementById('influencerInput').addEventListener('focus', function () {
        if (influencers.length > 0) document.getElementById('influencerDropdown').classList.remove('hidden');
    });

    document.getElementById('influencerInput').addEventListener('input', function (e) {
        const value = e.target.value.toLowerCase();
        const dropdown = document.getElementById('influencerDropdown');
        const filtered = influencers.filter(inf => inf.name.toLowerCase().includes(value));
        dropdown.innerHTML = '';
        filtered.forEach(inf => {
            const option = document.createElement('div');
            option.className = 'px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0';
            option.innerHTML = `<div class="font-medium">${inf.name}</div><div class="text-sm text-gray-500">${inf.outlet || ''}</div>`;
            option.onclick = () => selectInfluencer(inf);
            dropdown.appendChild(option);
        });
        dropdown.classList.toggle('hidden', filtered.length === 0 && value.length === 0);
        document.getElementById('influencerId').value = '';
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('#influencerInput') && !e.target.closest('#influencerDropdown')) {
            document.getElementById('influencerDropdown').classList.add('hidden');
        }
    });

    function selectInfluencer(inf) {
        document.getElementById('influencerInput').value = inf.name;
        document.getElementById('influencerId').value = inf.id;
        document.getElementById('influencerPhone').value = inf.phone || '';
        document.getElementById('influencerDropdown').classList.add('hidden');
    }

    function openExitModal(car) {
        document.getElementById('exitCarId').value = car.id;
        document.getElementById('exitPrFirmId').value = selectedPrFirmId;
        document.getElementById('modalFirmName').textContent = selectedPrFirmName;
        document.getElementById('modalCarCode').textContent = car.car_code;
        document.getElementById('influencerInput').value = '';
        document.getElementById('influencerId').value = '';
        document.getElementById('influencerPhone').value = '';
        updateCurrentTime();
        document.getElementById('exitModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeExitModal() {
        document.getElementById('exitModal').classList.remove('active');
        document.body.style.overflow = '';
    }

    async function submitExit(e) {
        e.preventDefault();
        const formData = new FormData(document.getElementById('exitForm'));
        formData.append('action', 'log_exit');

        const name = formData.get('journalist_name');
        const carCode = document.getElementById('modalCarCode').textContent;
        if (!confirm(`Confirm: Assign Car ${carCode} to ${name}?`)) return;

        try {
            const response = await fetch('<?= BASE_PATH ?>/api/cars.php', { method: 'POST', body: formData });
            const data = await response.json();
            if (data.success) {
                closeExitModal();
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            }
            else { showToast(data.message, 'error'); }
        } catch (error) { console.error('Error:', error); showToast('Failed to log exit.', 'error'); }
    }

    let currentReturnCarId = null;
    let currentReturnCarCode = '';
    let currentPrFirmId = null;
    let currentInfluencerId = null;
    let currentInfluencerName = '';
    let currentPrFirmName = '';
    let currentReturnLogId = null;

    // Log Return: Instantly capture timestamp and open feedback
    async function openReturnModal(carId, carCode) {
        currentReturnCarId = carId;
        currentReturnCarCode = carCode;

        // Show loading indicator
        showToast('Logging return...', 'info');

        try {
            // Instantly log the return with current timestamp
            const formData = new FormData();
            formData.append('action', 'log_return');
            formData.append('car_id', carId);
            // KM will be captured in post-drive modal

            const response = await fetch('<?= BASE_PATH ?>/api/cars.php', { method: 'POST', body: formData });
            const data = await response.json();

            if (data.success) {
                currentReturnLogId = data.data?.log_id || null;
                // Extract exit log details from response
                currentInfluencerName = data.data?.journalist_name || 'Influencer';
                currentInfluencerId = data.data?.influencer_id || null;
                currentPrFirmName = data.data?.pr_firm_name || '';
                currentPrFirmId = data.data?.pr_firm_id || null;

                showToast('Return logged at ' + new Date().toLocaleTimeString('en-IN'), 'success');
                // Immediately open feedback modal with context
                openFeedbackModal(carId, carCode, currentInfluencerName, currentPrFirmName);
            } else {
                showToast(data.message || 'Error logging return', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Failed to log return', 'error');
        }
    }

    function closeReturnModal() {
        document.getElementById('returnModal')?.classList.remove('active');
        document.body.style.overflow = '';
    }

    // ============================================
    // FEEDBACK MODAL
    // ============================================
    let ratings = { handling: 0, comfort: 0, performance: 0, nvh: 0, features: 0, appearance: 0, overall: 0 };

    function openFeedbackModal(carId, carCode, influencerName = 'Influencer', prFirmName = '') {
        currentReturnCarId = carId;
        currentReturnCarCode = carCode;
        document.getElementById('feedbackCarId').value = carId;
        document.getElementById('feedbackPrFirmId').value = currentPrFirmId || '';
        document.getElementById('feedbackInfluencerId').value = currentInfluencerId || '';
        document.getElementById('feedbackCarLogId').value = currentReturnLogId || '';

        // Update header with context
        document.getElementById('feedbackInfluencerName').textContent = influencerName;
        document.getElementById('feedbackContext').textContent = `Car ${carCode} • ${prFirmName}`;

        resetStars();
        document.getElementById('feedbackForm').reset();
        document.getElementById('feedbackModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeFeedbackModal() {
        document.getElementById('feedbackModal').classList.remove('active');
        document.body.style.overflow = '';
    }

    function skipFeedback() {
        closeFeedbackModal();
        openPostDriveModal(currentReturnCarId, currentReturnCarCode);
    }

    function resetStars() {
        ratings = { handling: 0, comfort: 0, performance: 0, nvh: 0, features: 0, appearance: 0, overall: 0 };
        document.querySelectorAll('#feedbackModal .stars').forEach(container => {
            container.querySelectorAll('.star').forEach(s => s.classList.remove('filled'));
        });
    }

    // Star rating click handlers
    document.querySelectorAll('#feedbackModal .star').forEach(star => {
        star.addEventListener('click', function () {
            const value = parseInt(this.dataset.value);
            const category = this.parentElement.dataset.category;
            ratings[category] = value;

            // Update visual
            this.parentElement.querySelectorAll('.star').forEach((s, i) => {
                s.classList.toggle('filled', i < value);
            });
        });
    });

    async function submitFeedback(e) {
        e.preventDefault();

        // Validate at least overall rating
        if (ratings.overall === 0) {
            showToast('Please provide at least an overall rating', 'error');
            return;
        }

        const formData = new FormData(document.getElementById('feedbackForm'));
        formData.append('action', 'submit_feedback');

        // Add ratings
        Object.entries(ratings).forEach(([key, value]) => {
            formData.append(key, value);
        });

        try {
            const response = await fetch('<?= BASE_PATH ?>/api/feedback.php', { method: 'POST', body: formData });
            const data = await response.json();
            if (data.success) {
                closeFeedbackModal();
                showToast('Feedback submitted!', 'success');
                openPostDriveModal(currentReturnCarId, currentReturnCarCode);
            } else {
                showToast(data.message || 'Error submitting feedback', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Failed to submit feedback', 'error');
        }
    }

    // ============================================
    // POST-DRIVE MODAL
    // ============================================
    let postDrivePhotos = [];

    function openPostDriveModal(carId, carCode) {
        document.getElementById('postDriveCarId').value = carId;
        document.getElementById('postDriveCarCode').textContent = carCode;
        document.getElementById('postDriveForm').reset();
        document.getElementById('postDrivePhotoPreview').innerHTML = '';
        document.getElementById('postDriveDamageSection')?.classList.add('hidden');
        postDrivePhotos = [];
        document.getElementById('postDriveModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    // Post-drive damage toggle
    document.getElementById('postDriveDamageToggle')?.addEventListener('change', function () {
        document.getElementById('postDriveDamageSection').classList.toggle('hidden', !this.checked);
    });

    function closePostDriveModal() {
        document.getElementById('postDriveModal').classList.remove('active');
        document.body.style.overflow = '';
        setTimeout(() => location.reload(), 500);
    }

    function previewPostDrivePhotos(event) {
        const files = Array.from(event.target.files);
        const preview = document.getElementById('postDrivePhotoPreview');

        files.forEach(file => {
            postDrivePhotos.push(file);
            const reader = new FileReader();
            reader.onload = (e) => {
                const div = document.createElement('div');
                div.className = 'relative aspect-square rounded-lg overflow-hidden bg-gray-100';
                div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }

    async function submitPostDrive(e) {
        e.preventDefault();
        const formData = new FormData(document.getElementById('postDriveForm'));
        formData.append('action', 'post_drive_ops');

        // Add photos
        postDrivePhotos.forEach((file, i) => {
            formData.append(`photos[${i}]`, file);
        });

        try {
            const response = await fetch('<?= BASE_PATH ?>/api/cars.php', { method: 'POST', body: formData });
            const data = await response.json();
            if (data.success) {
                closePostDriveModal();
                showToast('Post-drive details saved! Car sent to cleaning.', 'success');
            } else {
                showToast(data.message || 'Error saving details', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Failed to save details', 'error');
        }
    }

    // ============================================
    // TOAST NOTIFICATION
    // ============================================
    function showToast(message, type = 'info') {
        // Remove existing toast
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
            animation: slideUp 0.3s ease;
            ${type === 'success' ? 'background: #16a34a;' : type === 'error' ? 'background: #dc2626;' : 'background: #1a1a1a;'}
        `;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => toast.remove(), 3000);
    }
</script>


<?php include __DIR__ . '/../../components/status-legend.php'; ?>
<?php include __DIR__ . '/../../components/layout-footer.php'; ?>
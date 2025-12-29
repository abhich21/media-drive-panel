<?php
/**
 * MDM Promoter - Drive Logs
 * Chronological log of all completed test drives
 */

$pageTitle = 'Drive Logs';
$currentPage = 'drive-logs';

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

$userId = $_SESSION['user_id'];

// Fetch drive logs (Return logs paired with their Exit logs)
$drives = dbQuery("
    SELECT 
        r.id as log_id,
        r.car_id,
        r.return_time as end_time,
        c.car_code,
        pf.name as pr_firm_name,
        -- Get exit log details
        (
            SELECT exit_time 
            FROM car_logs e 
            WHERE e.car_id = r.car_id 
            AND e.log_type = 'exit' 
            AND e.created_at < r.created_at 
            ORDER BY e.created_at DESC 
            LIMIT 1
        ) as start_time,
        (
            SELECT km_reading 
            FROM car_logs e 
            WHERE e.car_id = r.car_id 
            AND e.log_type = 'exit' 
            AND e.created_at < r.created_at 
            ORDER BY e.created_at DESC 
            LIMIT 1
        ) as start_km,
        (
            SELECT journalist_name 
            FROM car_logs e 
            WHERE e.car_id = r.car_id 
            AND e.log_type = 'exit' 
            AND e.created_at < r.created_at 
            ORDER BY e.created_at DESC 
            LIMIT 1
        ) as journalist_name,
        -- Get end KM from post-drive note OR return log
        COALESCE(
            (SELECT km_reading FROM car_logs n WHERE n.car_id = r.car_id AND n.log_type = 'note' AND n.created_at > r.created_at ORDER BY n.created_at ASC LIMIT 1),
            r.km_reading
        ) as end_km,
        (SELECT COUNT(*) FROM feedback f WHERE f.car_log_id = r.id) as feedback_count
    FROM car_logs r
    JOIN cars c ON c.id = r.car_id
    LEFT JOIN pr_firms pf ON pf.id = r.pr_firm_id
    WHERE r.log_type = 'return'
    AND r.promoter_id = ?
    ORDER BY r.created_at DESC
", [$userId]);

include __DIR__ . '/../../components/layout.php';
?>

<style>
    .logs-page {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .back-nav {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #6b7280;
        font-size: 0.9rem;
        font-weight: 500;
        margin-bottom: 24px;
        text-decoration: none;
        transition: color 0.2s;
    }

    .back-nav:hover {
        color: #1a1a1a;
    }

    /* Table Header - Desktop Only */
    .logs-table-header {
        display: grid;
        grid-template-columns: 80px 140px 1fr 90px 90px 80px 80px 70px;
        padding: 0 20px 12px;
        gap: 12px;
    }

    .header-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Log Card - Desktop Grid */
    .log-card {
        background: white;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        display: grid;
        grid-template-columns: 80px 140px 1fr 90px 90px 80px 80px 70px;
        gap: 12px;
        align-items: center;
    }

    .col-code {
        font-weight: 700;
        font-size: 1rem;
        color: #1a1a1a;
    }

    .col-outlet {
        font-size: 0.9rem;
        color: #4b5563;
    }

    .col-name {
        font-size: 0.9rem;
        color: #1a1a1a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .col-time,
    .col-duration,
    .col-distance {
        font-size: 0.9rem;
        color: #4b5563;
        font-variant-numeric: tabular-nums;
    }

    .col-feedback {
        display: flex;
        justify-content: center;
    }

    .feedback-icon {
        width: 22px;
        height: 22px;
    }

    .icon-completed {
        color: #16a34a;
    }

    .icon-pending {
        color: #f97316;
    }

    .empty-state {
        background: white;
        border-radius: 16px;
        text-align: center;
        padding: 60px 20px;
        color: #6b7280;
    }

    .empty-state svg {
        width: 48px;
        height: 48px;
        margin: 0 auto 16px;
        color: #d1d5db;
    }

    /* Hide mobile elements on desktop */
    .mobile-only {
        display: none;
    }

    /* Mobile Responsive */
    @media (max-width: 900px) {
        .logs-table-header {
            display: none;
        }

        .log-card {
            display: block;
            padding: 16px;
            position: relative;
        }

        .desktop-only {
            display: none !important;
        }

        .mobile-only {
            display: block;
        }

        .mobile-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .mobile-header .col-code {
            font-size: 1.1rem;
        }

        .mobile-header .col-outlet {
            font-size: 0.8rem;
            color: #888;
            margin-top: 2px;
        }

        .mobile-header .col-feedback {
            position: static;
        }

        .col-name {
            font-weight: 500;
            margin-bottom: 12px;
            white-space: normal;
        }

        .mobile-stats {
            display: flex;
            gap: 16px;
            background: #f8f8f8;
            padding: 10px 12px;
            border-radius: 8px;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .stat-label {
            font-size: 0.65rem;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 0.85rem;
            font-weight: 600;
            color: #1a1a1a;
        }
    }
</style>

<div class="logs-page">
    <a href="dashboard.php" class="back-nav">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to Dashboard
    </a>

    <!-- Table Header (Desktop) -->
    <div class="logs-table-header">
        <div class="header-label">Car Code</div>
        <div class="header-label">Media Outlet</div>
        <div class="header-label">Name</div>
        <div class="header-label">Start</div>
        <div class="header-label">End</div>
        <div class="header-label">Duration</div>
        <div class="header-label">Distance</div>
        <div class="header-label" style="text-align: center">Status</div>
    </div>

    <!-- Rows -->
    <div class="logs-list">
        <?php if (empty($drives)): ?>
            <div class="empty-state">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                <p>No drive logs found</p>
            </div>
        <?php else: ?>
            <?php foreach ($drives as $drive):
                // Calculate duration
                $start = strtotime($drive['start_time']);
                $end = strtotime($drive['end_time']);
                $durationMins = $start && $end ? round(($end - $start) / 60) : 0;

                // Calculate distance
                $dist = ($drive['end_km'] > 0 && $drive['start_km'] > 0)
                    ? number_format($drive['end_km'] - $drive['start_km'], 1)
                    : '-';

                // Format times
                $startTimeStr = $drive['start_time'] ? date('H:i', strtotime($drive['start_time'])) : '-';
                $endTimeStr = $drive['end_time'] ? date('H:i', strtotime($drive['end_time'])) : '-';
                $durationStr = $durationMins > 0 ? $durationMins . 'min' : '-';
                $distStr = $dist !== '-' ? $dist . 'Km' : '-';

                // Check feedback
                $hasFeedback = $drive['feedback_count'] > 0;
                ?>
                <div class="log-card">
                    <!-- Desktop Layout -->
                    <div class="col-code desktop-only"><?= h($drive['car_code']) ?></div>
                    <div class="col-outlet desktop-only"><?= h($drive['pr_firm_name'] ?? '-') ?></div>
                    <div class="col-name desktop-only" title="<?= h($drive['journalist_name']) ?>">
                        <?= h($drive['journalist_name'] ?? 'Unknown') ?>
                    </div>
                    <div class="col-time desktop-only"><?= $startTimeStr ?></div>
                    <div class="col-time desktop-only"><?= $endTimeStr ?></div>
                    <div class="col-duration desktop-only"><?= $durationStr ?></div>
                    <div class="col-distance desktop-only"><?= $distStr ?></div>
                    <div class="col-feedback desktop-only">
                        <?php if ($hasFeedback): ?>
                            <svg class="feedback-icon icon-completed" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        <?php else: ?>
                            <svg class="feedback-icon icon-pending" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        <?php endif; ?>
                    </div>

                    <!-- Mobile Layout -->
                    <div class="mobile-only">
                        <div class="mobile-header">
                            <div>
                                <div class="col-code"><?= h($drive['car_code']) ?></div>
                                <div class="col-outlet"><?= h($drive['pr_firm_name'] ?? '-') ?></div>
                            </div>
                            <div class="col-feedback">
                                <?php if ($hasFeedback): ?>
                                    <svg class="feedback-icon icon-completed" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                <?php else: ?>
                                    <svg class="feedback-icon icon-pending" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-name"><?= h($drive['journalist_name'] ?? 'Unknown') ?></div>
                        <div class="mobile-stats">
                            <div class="stat-item">
                                <span class="stat-label">Time</span>
                                <span class="stat-value"><?= $startTimeStr ?> - <?= $endTimeStr ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Duration</span>
                                <span class="stat-value"><?= $durationStr ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Distance</span>
                                <span class="stat-value"><?= $distStr ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../components/status-legend.php'; ?>
<?php include __DIR__ . '/../../components/layout-footer.php'; ?>
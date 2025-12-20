<?php
/**
 * MDM Client Dashboard - Overall Car Stats
 * Pixel-perfect recreation of the mockup
 */

// Page config
$pageTitle = 'Overall Car Stats';
$currentPage = 'dashboard';
$clientLogo = 'Tata Motors';

// Include required components
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

// Sample data matching the mockup
$stats = [
    'totalDistance' => 679.5,
    'carsEngaged' => 25,
    'activeCars' => 45,
    'totalCars' => 50,
    'maintenance' => [
        'cleaning' => 5,
        'cleaned' => 45,
        'pod_lineup' => 40,
        'on_drive' => 5,
    ]
];

// Start layout
include __DIR__ . '/../../components/layout.php';
?>

<style>
    /* Stat Card - Compact for Viewport */
    .stat-card {
        background: #FFFFFF;
        border-radius: 20px;
        position: relative;
        padding: 45px 28px 28px 28px;
        margin-top: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    }

    .floating-pill {
        position: absolute;
        top: -16px;
        left: 28px;
        background: #D9D9D6;
        padding: 10px 20px;
        border-radius: 9999px;
        border: 4px solid #E6E7E2;
        z-index: 10;
    }

    .floating-pill span {
        font-weight: 700;
        font-size: 13px;
        color: #000000;
        letter-spacing: 0.2px;
    }

    .stat-value-row {
        display: flex;
        align-items: baseline;
        gap: 4px;
    }

    .stat-value {
        font-size: 64px;
        font-weight: 800;
        color: #000000;
        line-height: 1;
        letter-spacing: -2px;
        font-family: 'Inter', sans-serif;
    }

    .stat-unit {
        font-size: 24px;
        font-weight: 600;
        color: #000000;
        margin-left: 2px;
        align-self: baseline;
        margin-bottom: 8px;
    }

    .stat-positive-wrapper {
        display: flex;
        flex-direction: column;
        align-self: center;
        margin-left: 12px;
        margin-bottom: 4px;
    }

    .stat-positive {
        font-size: 12px;
        font-weight: 600;
        color: #4CAF50;
        line-height: 1.3;
    }

    .subtitle-pill {
        display: inline-block;
        background: #EAEAE6;
        padding: 8px 14px;
        border-radius: 8px;
        margin-top: 16px;
    }

    .subtitle-pill span {
        font-size: 11px;
        font-weight: 700;
        color: #000000;
        letter-spacing: 0.1px;
    }

    .subtitle-value {
        font-size: 32px;
        font-weight: 800;
        color: #000000;
        margin-top: 8px;
        letter-spacing: -1px;
    }

    .split-card {
        background: #FFFFFF;
        border-radius: 20px;
        position: relative;
        padding: 45px 28px 28px 28px;
        margin-top: 20px;
        min-height: 160px;
        overflow: visible;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .split-value {
        font-size: 64px;
        font-weight: 700;
        color: #000000;
        line-height: 0.9;
        letter-spacing: -2px;
    }

    .split-value-dim {
        font-size: 28px;
        font-weight: 600;
        color: rgba(0, 0, 0, 0.35);
        margin-left: 2px;
    }

    .car-container {
        position: absolute;
        right: -20px;
        bottom: 5px;
        width: 400px;
        z-index: 1;
    }

    .car-container img {
        width: 100%;
        position: relative;
        z-index: 2;
    }

    .maintenance-card {
        background: #FFFFFF;
        border-radius: 20px;
        position: relative;
        padding: 50px 20px 20px 20px;
        margin-top: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .maintenance-card>.floating-pill {
        position: absolute;
        top: -16px;
        left: 20px;
        background: #D9D9D6;
        padding: 10px 20px;
        border-radius: 9999px;
        border: 4px solid #E6E7E2;
        z-index: 10;
    }

    .maintenance-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }

    .status-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 10px 16px 10px 16px;
        background: #ECEAE5;
        border-radius: 16px;
    }

    .status-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #D9D7D2;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 14px;
    }

    .status-icon svg {
        width: 18px;
        height: 18px;
        color: #8B7355;
    }

    .status-label {
        font-size: 13px;
        font-weight: 600;
        color: #000000;
        text-align: center;
        margin-bottom: 8px;
    }

    .status-value {
        font-size: 36px;
        font-weight: 700;
        color: #000000;
    }

    /* Tablet Responsive (max-width: 1024px) */
    @media (max-width: 1024px) {
        .stats-grid {
            grid-template-columns: 1fr !important;
            gap: 20px !important;
        }

        .stat-value {
            font-size: 52px;
        }

        .stat-unit {
            font-size: 20px;
        }

        .split-value {
            font-size: 52px;
        }

        .split-value-dim {
            font-size: 24px;
        }

        .car-container {
            width: 280px;
            right: -15px;
        }

        .maintenance-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .status-value {
            font-size: 32px;
        }
    }

    /* Mobile Responsive (max-width: 768px) */
    @media (max-width: 768px) {

        .stat-card,
        .split-card {
            padding: 40px 20px 20px 20px;
            margin-top: 24px;
        }

        .floating-pill {
            left: 20px;
            padding: 8px 16px;
        }

        .floating-pill span {
            font-size: 11px;
        }

        .stat-value {
            font-size: 42px;
            letter-spacing: -1px;
        }

        .stat-unit {
            font-size: 18px;
            margin-bottom: 6px;
        }

        .stat-positive-wrapper {
            margin-left: 8px;
        }

        .stat-positive {
            font-size: 10px;
        }

        .subtitle-pill {
            padding: 6px 12px;
            margin-top: 12px;
        }

        .subtitle-pill span {
            font-size: 10px;
        }

        .subtitle-value {
            font-size: 28px;
            margin-top: 6px;
        }

        .split-card {
            min-height: 140px;
        }

        .split-value {
            font-size: 42px;
        }

        .split-value-dim {
            font-size: 20px;
        }

        .car-container {
            width: 180px;
            right: -10px;
            bottom: 0;
        }

        .maintenance-card {
            padding: 20px 16px 16px 16px;
        }

        .maintenance-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }

        .status-item {
            padding: 16px 12px 20px 12px;
        }

        .status-icon {
            width: 36px;
            height: 36px;
            margin-bottom: 10px;
        }

        .status-icon svg {
            width: 16px;
            height: 16px;
        }

        .status-label {
            font-size: 11px;
            margin-bottom: 6px;
        }

        .status-value {
            font-size: 28px;
        }
    }

    /* Small Mobile (max-width: 480px) */
    @media (max-width: 480px) {
        .stat-value {
            font-size: 36px;
        }

        .split-value {
            font-size: 36px;
        }

        .car-container {
            width: 140px;
            right: 0;
        }

        .maintenance-grid {
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }

        .status-value {
            font-size: 24px;
        }
    }
</style>

<!-- Stats Grid -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 32px; margin-bottom: 32px;">

    <!-- Left Column: Total Distance -->
    <div class="stat-card">
        <div class="floating-pill">
            <span>Total Distance Covered</span>
        </div>

        <div class="stat-value-row">
            <span class="stat-value"><?= number_format($stats['totalDistance'], 1) ?></span>
            <span class="stat-unit">km</span>
            <div class="stat-positive-wrapper">
                <span class="stat-positive">+ 2% since</span>
                <span class="stat-positive">yesterday</span>
            </div>
        </div>

        <div class="subtitle-pill">
            <span>Total Number of Cars Engaged</span>
        </div>
        <div class="subtitle-value"><?= $stats['carsEngaged'] ?></div>
    </div>

    <!-- Right Column: Active/Inactive Cars -->
    <div class="split-card">
        <div class="floating-pill">
            <span>Total Active/ Inactive Cars</span>
        </div>

        <div style="position: relative; z-index: 10;">
            <span class="split-value"><?= $stats['activeCars'] ?></span><span
                class="split-value-dim">/<?= $stats['totalCars'] ?></span>
        </div>

        <div class="car-container">
            <img src="<?= BASE_PATH ?>/img/Asset 1@5x.png" alt="Car">
        </div>
    </div>
</div>

<!-- Maintenance Status Section -->
<div class="maintenance-card">
    <div class="floating-pill">
        <span>Maintenance Status</span>
    </div>

    <div class="maintenance-grid">
        <!-- Under Cleaning -->
        <div class="status-item">
            <div class="status-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
            </div>
            <span class="status-label">Under Cleaning</span>
            <span class="status-value"><?= $stats['maintenance']['cleaning'] ?></span>
        </div>

        <!-- Cleaned Cars -->
        <div class="status-item">
            <div class="status-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <span class="status-label">Cleaned Cars</span>
            <span class="status-value"><?= $stats['maintenance']['cleaned'] ?></span>
        </div>

        <!-- POD Line Up -->
        <div class="status-item">
            <div class="status-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
            </div>
            <span class="status-label">POD Line Up</span>
            <span class="status-value"><?= $stats['maintenance']['pod_lineup'] ?></span>
        </div>

        <!-- On Drive -->
        <div class="status-item">
            <div class="status-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M18.364 5.636a9 9 0 010 12.728M5.636 5.636a9 9 0 000 12.728M12 8v4l3 3" />
                </svg>
            </div>
            <span class="status-label">On Drive</span>
            <span class="status-value"><?= $stats['maintenance']['on_drive'] ?></span>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../components/layout-footer.php'; ?>
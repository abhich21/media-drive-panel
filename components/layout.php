<?php
/**
 * MDM Base Layout Component
 * Main wrapper with sidebar and header - Pixel Perfect Version
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

// Defaults
$pageTitle = $pageTitle ?? 'Dashboard';
$currentPage = $currentPage ?? 'dashboard';
$clientLogo = $clientLogo ?? 'Client Logo';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?> | MDM</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Styles -->
    <link href="<?= BASE_PATH ?>/assets/css/styles.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* Critical Layout Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Montserrat', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #E6E7E2 !important;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        .mdm-main {
            margin-left: 240px;
            padding-top: 80px;
            min-height: 100vh;
        }

        .mdm-content {
            padding: 15px 40px;
        }

        .mdm-page-heading {
            font-size: 32px;
            font-weight: 800;
            color: #000000;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }

        /* Mobile Responsive Layout */
        @media (max-width: 768px) {
            .mdm-main {
                margin-left: 0;
                padding-top: 70px;
            }

            .mdm-content {
                padding: 20px 16px;
            }

            .mdm-page-heading {
                font-size: 24px;
                margin-bottom: 16px;
            }
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/sidebar.php'; ?>
    <?php include __DIR__ . '/header.php'; ?>

    <!-- Main Content Area -->
    <main class="mdm-main">
        <div class="mdm-content">
            <!-- Page Title -->
            <h1 class="mdm-page-heading"><?= h($pageTitle) ?></h1>

            <!-- Page Content Slot -->
            <?php if (isset($pageContent)): ?>
                <?php echo $pageContent; ?>
            <?php endif; ?>
<?php
/**
 * Cleaning Staff Layout
 * Same styling as main layout but without the sidebar
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

// Defaults
$pageTitle = $pageTitle ?? 'Cleaning Dashboard';
$currentPage = $currentPage ?? 'dashboard';
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Styles -->
    <link href="<?= BASE_PATH ?>/assets/css/styles.css" rel="stylesheet">

    <style>
        /* Critical Layout Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #E6E7E2 !important;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* Cleaning layout - no sidebar margin */
        .mdm-main-cleaning {
            padding-top: 80px;
            min-height: 100vh;
        }

        .mdm-content {
            padding: 15px 40px;
            max-width: 1400px;
            margin: 0 auto;
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
            .mdm-main-cleaning {
                padding-top: 70px;
            }

            .mdm-content {
                padding: 20px 16px;
            }

            .mdm-page-heading {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/header.php'; ?>

    <main class="mdm-main-cleaning">
        <div class="mdm-content">
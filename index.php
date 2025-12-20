<?php
/**
 * MDM Entry Point
 * Redirects to appropriate dashboard or login
 */

session_start();

// Get the base path (folder where this script lives)
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['user_role'] ?? 'client';
    $redirects = [
        'superadmin' => $basePath . '/pages/admin/dashboard.php',
        'client' => $basePath . '/pages/client/dashboard.php',
        'promoter' => $basePath . '/pages/promoter/dashboard.php',
    ];
    header('Location: ' . ($redirects[$role] ?? $basePath . '/login.php'));
} else {
    header('Location: ' . $basePath . '/login.php');
}
exit;


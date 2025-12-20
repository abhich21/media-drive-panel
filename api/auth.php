<?php
/**
 * MDM API - Authentication
 * Login, logout, session management
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        handleLogin();
        break;
    case 'logout':
        handleLogout();
        break;
    case 'check':
        checkSession();
        break;
    default:
        errorResponse('Invalid action', 400);
}

/**
 * Handle user login
 */
function handleLogin()
{
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        errorResponse('Email and password are required');
    }

    $user = loginUser($email, $password);

    if ($user) {
        successResponse([
            'user' => $user,
            'redirect' => getDashboardUrl($user['role'])
        ], 'Login successful');
    } else {
        errorResponse('Invalid email or password', 401);
    }
}

/**
 * Handle user logout
 */
function handleLogout()
{
    logoutUser();
    successResponse(null, 'Logged out successfully');
}

/**
 * Check current session
 */
function checkSession()
{
    if (isLoggedIn()) {
        $user = getCurrentUser();
        successResponse(['user' => $user], 'Session active');
    } else {
        errorResponse('Not authenticated', 401);
    }
}

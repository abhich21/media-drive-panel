<?php
/**
 * MDM Authentication Helpers
 * Media Drive Management System
 */

session_start();

require_once __DIR__ . '/../config/database.php';

// Get base URL for redirects (calculate from includes folder, not script location)
// This ensures correct path whether called from /api/, /pages/, or root
$scriptPath = $_SERVER['SCRIPT_NAME'];
$projectRoot = '/mdm-new/media-drive-panel'; // Updated project folder path
define('BASE_PATH', $projectRoot);

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current logged in user
 * @return array|null
 */
function getCurrentUser()
{
    if (!isLoggedIn()) {
        return null;
    }

    return dbQueryOne(
        "SELECT id, name, email, role, phone, avatar FROM users WHERE id = ? AND is_active = 1",
        [$_SESSION['user_id']]
    );
}

/**
 * Check if current user has specific role
 * @param string|array $roles
 * @return bool
 */
function hasRole($roles)
{
    $user = getCurrentUser();
    if (!$user)
        return false;

    if (is_string($roles)) {
        $roles = [$roles];
    }

    return in_array($user['role'], $roles);
}

/**
 * Require authentication - redirect if not logged in
 * @param string|array|null $roles Optional role(s) required
 */
function requireAuth($roles = null)
{
    if (!isLoggedIn()) {
        header('Location: ' . BASE_PATH . '/login.php');
        exit;
    }

    if ($roles !== null && !hasRole($roles)) {
        header('Location: ' . BASE_PATH . '/unauthorized.php');
        exit;
    }
}

/**
 * Login user
 * @param string $email
 * @param string $password
 * @return array|false
 */
function loginUser($email, $password)
{
    // Debug: Log the login attempt
    error_log("LOGIN ATTEMPT: email=$email");

    $user = dbQueryOne(
        "SELECT * FROM users WHERE email = ? AND is_active = 1",
        [$email]
    );

    // Debug: Log what we got from the database
    error_log("USER FOUND: " . ($user ? "YES - ID: " . $user['id'] . ", Role: " . $user['role'] : "NO"));

    if ($user) {
        // TESTING MODE: Plain text password comparison (remove in production!)
        $passwordMatch = ($password === $user['password']);
        error_log("PASSWORD CHECK (plain text): " . ($passwordMatch ? "MATCH" : "NO MATCH"));
        error_log("STORED PASSWORD: " . $user['password']);

        if ($passwordMatch) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['name'];

            return [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ];
        }
    }

    return false;
}

/**
 * Logout user
 */
function logoutUser()
{
    session_unset();
    session_destroy();
}

/**
 * Get dashboard URL based on role
 * @param string $role
 * @return string
 */
function getDashboardUrl($role)
{
    $base = defined('BASE_PATH') ? BASE_PATH : '';
    switch ($role) {
        case 'superadmin':
            return $base . '/pages/admin/dashboard.php';
        case 'client':
            return $base . '/pages/client/dashboard.php';
        case 'promoter':
            return $base . '/pages/promoter/dashboard.php';
        case 'cleaning_staff':
            return $base . '/pages/cleaning/dashboard.php';
        default:
            return $base . '/login.php';
    }
}

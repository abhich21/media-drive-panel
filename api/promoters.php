<?php
/**
 * MDM API - Promoters
 * Promoter management and attendance
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        listPromoters();
        break;
    case 'get':
        getPromoter();
        break;
    case 'create':
        createPromoter();
        break;
    case 'update':
        updatePromoter();
        break;
    case 'delete':
        deletePromoter();
        break;
    case 'attendance':
        getAttendance();
        break;
    case 'mark_attendance':
        markAttendance();
        break;
    default:
        errorResponse('Invalid action', 400);
}

/**
 * List all promoters
 */
function listPromoters()
{
    $eventId = $_GET['event_id'] ?? null;

    if ($eventId) {
        $sql = "SELECT u.* FROM users u 
                JOIN event_promoters ep ON ep.promoter_id = u.id 
                WHERE ep.event_id = ? AND u.role = 'promoter' AND u.is_active = 1
                ORDER BY u.name";
        $promoters = dbQuery($sql, [$eventId]);
    } else {
        $promoters = dbQuery(
            "SELECT * FROM users WHERE role = 'promoter' AND is_active = 1 ORDER BY name"
        );
    }

    successResponse($promoters);
}

/**
 * Get single promoter
 */
function getPromoter()
{
    $id = $_GET['id'] ?? null;
    if (!$id)
        errorResponse('Promoter ID required');

    $promoter = dbQueryOne(
        "SELECT id, name, email, phone, avatar FROM users WHERE id = ? AND role = 'promoter'",
        [$id]
    );

    if (!$promoter)
        errorResponse('Promoter not found', 404);

    successResponse($promoter);
}

/**
 * Create new promoter
 */
function createPromoter()
{
    // requireAuth('superadmin');

    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'password' => $_POST['password'] ?? '',
    ];

    if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
        errorResponse('Name, email, and password are required');
    }

    // Check if email exists
    $existing = dbQueryOne("SELECT id FROM users WHERE email = ?", [$data['email']]);
    if ($existing) {
        errorResponse('Email already exists');
    }

    // TESTING MODE: Store plain text password (remove in production!)
    $plainPassword = $data['password'];

    $sql = "INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'promoter')";
    dbExecute($sql, [$data['name'], $data['email'], $data['phone'], $plainPassword]);

    successResponse(['id' => dbLastId()], 'Promoter created successfully');
}

/**
 * Update promoter
 */
function updatePromoter()
{
    // requireAuth('superadmin');

    $id = $_POST['id'] ?? null;
    if (!$id)
        errorResponse('Promoter ID required');

    $updates = [];
    $params = [];

    foreach (['name', 'email', 'phone'] as $field) {
        if (isset($_POST[$field])) {
            $updates[] = "$field = ?";
            $params[] = $_POST[$field];
        }
    }

    if (isset($_POST['password']) && !empty($_POST['password'])) {
        $updates[] = "password = ?";
        // TESTING MODE: Store plain text password (remove in production!)
        $params[] = $_POST['password'];
    }

    if (empty($updates))
        errorResponse('No fields to update');

    $params[] = $id;
    dbExecute("UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?", $params);

    successResponse(null, 'Promoter updated successfully');
}

/**
 * Delete (deactivate) promoter
 */
function deletePromoter()
{
    // requireAuth('superadmin');

    $id = $_POST['id'] ?? null;
    if (!$id)
        errorResponse('Promoter ID required');

    dbExecute("UPDATE users SET is_active = 0 WHERE id = ?", [$id]);
    successResponse(null, 'Promoter deleted successfully');
}

/**
 * Get attendance records
 */
function getAttendance()
{
    $eventId = $_GET['event_id'] ?? null;
    $date = $_GET['date'] ?? date('Y-m-d');
    $promoterId = $_GET['promoter_id'] ?? null;

    $sql = "SELECT pa.*, u.name as promoter_name 
            FROM promoter_attendance pa
            JOIN users u ON u.id = pa.promoter_id
            WHERE 1=1";
    $params = [];

    if ($eventId) {
        $sql .= " AND pa.event_id = ?";
        $params[] = $eventId;
    }

    if ($date) {
        $sql .= " AND pa.date = ?";
        $params[] = $date;
    }

    if ($promoterId) {
        $sql .= " AND pa.promoter_id = ?";
        $params[] = $promoterId;
    }

    $sql .= " ORDER BY u.name";

    $attendance = dbQuery($sql, $params);
    successResponse($attendance);
}

/**
 * Mark attendance
 */
function markAttendance()
{
    $data = [
        'event_id' => $_POST['event_id'] ?? null,
        'promoter_id' => $_POST['promoter_id'] ?? $_SESSION['user_id'] ?? null,
        'date' => $_POST['date'] ?? date('Y-m-d'),
        'check_in_time' => $_POST['check_in_time'] ?? date('H:i:s'),
        'status' => $_POST['status'] ?? 'present',
    ];

    if (!$data['event_id'] || !$data['promoter_id']) {
        errorResponse('Event ID and promoter ID required');
    }

    // Check if already marked
    $existing = dbQueryOne(
        "SELECT id FROM promoter_attendance WHERE event_id = ? AND promoter_id = ? AND date = ?",
        [$data['event_id'], $data['promoter_id'], $data['date']]
    );

    if ($existing) {
        // Update existing
        dbExecute(
            "UPDATE promoter_attendance SET check_in_time = ?, status = ? WHERE id = ?",
            [$data['check_in_time'], $data['status'], $existing['id']]
        );
    } else {
        // Create new
        dbExecute(
            "INSERT INTO promoter_attendance (event_id, promoter_id, date, check_in_time, status) VALUES (?, ?, ?, ?, ?)",
            [$data['event_id'], $data['promoter_id'], $data['date'], $data['check_in_time'], $data['status']]
        );
    }

    successResponse(null, 'Attendance marked successfully');
}

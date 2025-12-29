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
    case 'get_events':
        getEventsList();
        break;
    case 'restore':
        restorePromoter();
        break;
    default:
        errorResponse('Invalid action', 400);
}

/**
 * List all promoters with pagination
 */
function listPromoters()
{
    require_once __DIR__ . '/../includes/queries/promoters.php';
    
    $eventId = $_GET['event_id'] ?? null;
    $filter = $_GET['filter'] ?? 'all';
    $page = max(1, intval($_GET['page'] ?? 1));
    $perPage = max(1, min(50, intval($_GET['per_page'] ?? 12)));
    
    $promoters = getPromotersPaginated($eventId, $filter, $page, $perPage);
    $total = getTotalPromotersCount($eventId, $filter);
    $totalPages = ceil($total / $perPage);
    
    successResponse([
        'promoters' => $promoters,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages
        ]
    ]);
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
        "SELECT id, name, email, phone, password, avatar FROM users WHERE id = ? AND role = 'promoter'",
        [$id]
    );

    if (!$promoter)
        errorResponse('Promoter not found', 404);

    // Get event assignment
    $eventAssignment = dbQueryOne(
        "SELECT id as event_promoter_id, event_id, assigned_cars FROM event_promoters WHERE promoter_id = ? AND is_deleted = 0 ORDER BY id DESC LIMIT 1",
        [$id]
    );

    if ($eventAssignment) {
        $promoter['event_id'] = $eventAssignment['event_id'];
        $promoter['event_promoter_id'] = $eventAssignment['event_promoter_id'];
        $promoter['assigned_cars'] = $eventAssignment['assigned_cars'];
    }

    successResponse($promoter);
}

/**
 * Create new promoter
 */
function createPromoter()
{
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'password' => $_POST['password'] ?? '',
    ];

    $eventId = $_POST['event_id'] ?? null;
    $assignedCars = isset($_POST['assigned_cars']) ? implode(',', $_POST['assigned_cars']) : null;

    if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
        errorResponse('Name, email, and password are required');
    }

    if (empty($eventId)) {
        errorResponse('Event is required');
    }

    // Check if email exists
    $existing = dbQueryOne("SELECT id FROM users WHERE email = ?", [$data['email']]);
    if ($existing) {
        errorResponse('Email already exists');
    }

    // Create promoter user
    $sql = "INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'promoter')";
    dbExecute($sql, [$data['name'], $data['email'], $data['phone'], $data['password']]);
    $promoterId = dbLastId();

    // Create event assignment
    dbExecute(
        "INSERT INTO event_promoters (event_id, promoter_id, assigned_cars, is_deleted) VALUES (?, ?, ?, 0)",
        [$eventId, $promoterId, $assignedCars]
    );

    successResponse(['id' => $promoterId], 'Promoter created successfully');
}

/**
 * Update promoter
 */
function updatePromoter()
{
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
        $params[] = $_POST['password'];
    }

    if (!empty($updates)) {
        $params[] = $id;
        dbExecute("UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?", $params);
    }

    // Handle event assignment
    $eventId = $_POST['event_id'] ?? null;
    $eventPromoterId = $_POST['event_promoter_id'] ?? null;
    $assignedCars = isset($_POST['assigned_cars']) ? implode(',', $_POST['assigned_cars']) : null;

    if ($eventId) {
        if ($eventPromoterId) {
            // Update existing assignment
            dbExecute(
                "UPDATE event_promoters SET event_id = ?, assigned_cars = ? WHERE id = ?",
                [$eventId, $assignedCars, $eventPromoterId]
            );
        } else {
            // Check if assignment exists
            $existing = dbQueryOne(
                "SELECT id FROM event_promoters WHERE promoter_id = ? AND is_deleted = 0",
                [$id]
            );
            
            if ($existing) {
                // Update existing
                dbExecute(
                    "UPDATE event_promoters SET event_id = ?, assigned_cars = ? WHERE id = ?",
                    [$eventId, $assignedCars, $existing['id']]
                );
            } else {
                // Create new assignment
                dbExecute(
                    "INSERT INTO event_promoters (event_id, promoter_id, assigned_cars, is_deleted) VALUES (?, ?, ?, 0)",
                    [$eventId, $id, $assignedCars]
                );
            }
        }
    }

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

/**
 * Get events list for dropdown filter
 */
function getEventsList()
{
    $events = dbQuery(
        "SELECT id, name FROM events WHERE (is_deleted = 0 OR is_deleted IS NULL) ORDER BY name ASC"
    );
    successResponse($events);
}

/**
 * Restore soft-deleted promoter
 */
function restorePromoter()
{
    $id = $_POST['id'] ?? null;
    if (!$id)
        errorResponse('Promoter ID required');

    dbExecute("UPDATE users SET is_active = 1 WHERE id = ?", [$id]);
    successResponse(null, 'Promoter restored successfully');
}

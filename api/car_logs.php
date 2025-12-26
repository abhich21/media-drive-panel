<?php
/**
 * MDM API - Car Logs
 * Car activity logs management
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        listCarLogs();
        break;
    case 'get_events':
        getEventsList();
        break;
    default:
        errorResponse('Invalid action', 400);
}

/**
 * List car logs with pagination
 */
function listCarLogs()
{
    require_once __DIR__ . '/../includes/queries/car_logs.php';
    
    $eventId = $_GET['event_id'] ?? null;
    $page = max(1, intval($_GET['page'] ?? 1));
    $perPage = max(1, min(100, intval($_GET['per_page'] ?? 20)));
    
    $logs = getCarLogsPaginated($eventId, $page, $perPage);
    $total = getTotalCarLogsCount($eventId);
    $totalPages = ceil($total / $perPage);
    
    successResponse([
        'logs' => $logs,
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
 * Get events list for dropdown filter
 */
function getEventsList()
{
    $events = dbQuery(
        "SELECT id, name FROM events WHERE (is_deleted = 0 OR is_deleted IS NULL) ORDER BY name ASC"
    );
    successResponse($events);
}

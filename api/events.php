<?php
/**
 * MDM API - Events
 * CRUD operations for events
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/queries/events.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        listEvents();
        break;
    case 'get':
        getEvent($_GET['id'] ?? 0);
        break;
    case 'create':
        createEvent();
        break;
    case 'update':
        updateEvent();
        break;
    case 'delete':
        deleteEvent($_POST['id'] ?? 0);
        break;
    case 'soft_delete':
        softDeleteEvent($_POST['id'] ?? 0);
        break;
    case 'restore':
        restoreEvent($_POST['id'] ?? 0);
        break;
    case 'permanent_delete':
        permanentDeleteEvent($_POST['id'] ?? 0);
        break;
    default:
        errorResponse('Invalid action', 400);
}

/**
 * List all events with pagination and filtering
 */
function listEvents()
{
    $status = $_GET['status'] ?? 'all';
    $page = max(1, intval($_GET['page'] ?? 1));
    $perPage = max(1, min(50, intval($_GET['per_page'] ?? 12)));
    
    $events = getEventsPaginated($status, $page, $perPage);
    $total = getTotalEventsCount($status);
    $totalPages = ceil($total / $perPage);
    
    successResponse([
        'events' => $events,
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
 * Get single event
 */
function getEvent($id)
{
    if (!$id) {
        errorResponse('Event ID required');
    }

    $event = dbQueryOne("SELECT * FROM events WHERE id = ?", [$id]);

    if ($event) {
        // Get related stats
        $event['carCount'] = dbQueryOne("SELECT COUNT(*) as count FROM cars WHERE event_id = ?", [$id])['count'] ?? 0;
        $event['promoterCount'] = dbQueryOne("SELECT COUNT(*) as count FROM event_promoters WHERE event_id = ?", [$id])['count'] ?? 0;
        successResponse($event);
    } else {
        errorResponse('Event not found', 404);
    }
}

/**
 * Create new event
 */
function createEvent()
{
    $name = trim($_POST['name'] ?? '');
    $clientName = trim($_POST['client_name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $logoUrl = trim($_POST['logo_url'] ?? '');

    if (empty($name) || empty($startDate) || empty($endDate)) {
        errorResponse('Name, start date, and end date are required');
    }

    // Auto-determine status based on dates
    $today = date('Y-m-d');
    if ($today >= $startDate && $today <= $endDate) {
        $status = 'active';
    } elseif ($today < $startDate) {
        $status = 'upcoming';
    } else {
        $status = 'completed';
    }

    dbExecute(
        "INSERT INTO events (name, client_name, location, start_date, end_date, logo_url, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
        [$name, $clientName, $location, $startDate, $endDate, $logoUrl, $status, $_SESSION['user_id'] ?? null]
    );

    successResponse(['id' => dbLastId()], 'Event created successfully');
}

/**
 * Update event
 */
function updateEvent()
{
    $id = $_POST['id'] ?? 0;
    $name = trim($_POST['name'] ?? '');
    $clientName = trim($_POST['client_name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $status = $_POST['status'] ?? 'upcoming';
    $logoUrl = trim($_POST['logo_url'] ?? '');

    if (!$id) {
        errorResponse('Event ID required');
    }

    dbExecute(
        "UPDATE events SET name = ?, client_name = ?, location = ?, start_date = ?, end_date = ?, status = ?, logo_url = ? WHERE id = ?",
        [$name, $clientName, $location, $startDate, $endDate, $status, $logoUrl, $id]
    );

    successResponse(null, 'Event updated successfully');
}

/**
 * Delete event (legacy - now calls soft delete)
 */
function deleteEvent($id)
{
    softDeleteEvent($id);
}

/**
 * Soft delete event - set is_deleted flag
 */
function softDeleteEvent($id)
{
    if (!$id) {
        errorResponse('Event ID required');
    }

    dbExecute("UPDATE events SET is_deleted = 1 WHERE id = ?", [$id]);
    successResponse(null, 'Event moved to trash');
}

/**
 * Restore soft-deleted event
 */
function restoreEvent($id)
{
    if (!$id) {
        errorResponse('Event ID required');
    }

    dbExecute("UPDATE events SET is_deleted = 0 WHERE id = ?", [$id]);
    successResponse(null, 'Event restored successfully');
}

/**
 * Permanently delete event from database
 */
function permanentDeleteEvent($id)
{
    if (!$id) {
        errorResponse('Event ID required');
    }

    dbExecute("DELETE FROM events WHERE id = ?", [$id]);
    successResponse(null, 'Event permanently deleted');
}

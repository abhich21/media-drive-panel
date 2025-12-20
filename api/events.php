<?php
/**
 * MDM API - Events
 * CRUD operations for events
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

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
    default:
        errorResponse('Invalid action', 400);
}

/**
 * List all events
 */
function listEvents()
{
    $events = dbQuery("SELECT * FROM events ORDER BY start_date DESC");
    successResponse($events);
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

    if (empty($name) || empty($startDate) || empty($endDate)) {
        errorResponse('Name, start date, and end date are required');
    }

    dbExecute(
        "INSERT INTO events (name, client_name, location, start_date, end_date, created_by) VALUES (?, ?, ?, ?, ?, ?)",
        [$name, $clientName, $location, $startDate, $endDate, $_SESSION['user_id'] ?? null]
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

    if (!$id) {
        errorResponse('Event ID required');
    }

    dbExecute(
        "UPDATE events SET name = ?, client_name = ?, location = ?, start_date = ?, end_date = ?, status = ? WHERE id = ?",
        [$name, $clientName, $location, $startDate, $endDate, $status, $id]
    );

    successResponse(null, 'Event updated successfully');
}

/**
 * Delete event
 */
function deleteEvent($id)
{
    if (!$id) {
        errorResponse('Event ID required');
    }

    dbExecute("DELETE FROM events WHERE id = ?", [$id]);
    successResponse(null, 'Event deleted successfully');
}

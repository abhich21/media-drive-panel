<?php
/**
 * MDM API - Dashboard
 * Statistics and overview data endpoints
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/queries/dashboard.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'stats':
        getStats();
        break;
    case 'recent_events':
        getRecentEventsApi();
        break;
    case 'car_status':
        getCarStatusApi();
        break;
    default:
        errorResponse('Invalid action', 400);
}

/**
 * Get overview statistics
 */
function getStats()
{
    $stats = getOverviewStats();
    successResponse($stats);
}

/**
 * Get recent events
 */
function getRecentEventsApi()
{
    $limit = intval($_GET['limit'] ?? 5);
    $events = getRecentEvents($limit);
    successResponse($events);
}

/**
 * Get car status counts
 */
function getCarStatusApi()
{
    $eventId = $_GET['event_id'] ?? null;
    $counts = getCarCountsByStatus($eventId);
    successResponse($counts);
}

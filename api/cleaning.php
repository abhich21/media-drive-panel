<?php
/**
 * MDM API - Cleaning Staff
 * Simple 3-button status updates for cleaning team
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'list_cars':
        listCarsForCleaning();
        break;
    case 'update_status':
        updateCleaningStatus();
        break;
    default:
        errorResponse('Invalid action', 400);
}

/**
 * Get cars that need cleaning attention (returned, cleaning, cleaned)
 */
function listCarsForCleaning()
{
    $eventId = $_GET['event_id'] ?? null;

    $sql = "SELECT c.*, e.name as event_name 
            FROM cars c
            JOIN events e ON e.id = c.event_id
            WHERE c.status IN ('returned', 'cleaning', 'cleaned', 'standby')
            AND c.is_active = 1";
    $params = [];

    if ($eventId) {
        $sql .= " AND c.event_id = ?";
        $params[] = $eventId;
    }

    // Order: returned first (needs attention), then cleaning, then others
    $sql .= " ORDER BY 
              CASE 
                  WHEN c.status = 'returned' THEN 1
                  WHEN c.status = 'cleaning' THEN 2
                  WHEN c.status = 'cleaned' THEN 3
                  ELSE 4
              END, c.car_code";

    $cars = dbQuery($sql, $params);
    successResponse($cars);
}

/**
 * Update car status (3 options: cleaning, cleaned, pod_lineup)
 */
function updateCleaningStatus()
{
    $carId = $_POST['car_id'] ?? null;
    $newStatus = $_POST['status'] ?? null;

    if (!$carId || !$newStatus) {
        errorResponse('Car ID and status required');
    }

    // Only allow cleaning-related status changes
    $allowedStatuses = ['cleaning', 'cleaned', 'standby'];
    if (!in_array($newStatus, $allowedStatuses)) {
        errorResponse('Invalid status. Allowed: ' . implode(', ', $allowedStatuses));
    }

    // Get current car info
    $car = dbQueryOne("SELECT status, event_id FROM cars WHERE id = ?", [$carId]);
    if (!$car) {
        errorResponse('Car not found', 404);
    }

    $userId = $_SESSION['user_id'] ?? null;

    // Update status
    dbExecute("UPDATE cars SET status = ? WHERE id = ?", [$newStatus, $carId]);

    // Log the change
    dbExecute(
        "INSERT INTO car_logs (car_id, event_id, promoter_id, log_type, previous_status, new_status, notes) 
         VALUES (?, ?, ?, 'status_change', ?, ?, 'Status updated by cleaning staff')",
        [$carId, $car['event_id'], $userId, $car['status'], $newStatus]
    );

    successResponse([
        'car_id' => $carId,
        'previous_status' => $car['status'],
        'new_status' => $newStatus
    ], 'Status updated successfully');
}

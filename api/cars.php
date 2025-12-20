<?php
/**
 * MDM API - Cars
 * CRUD operations, status updates, logging
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        listCars();
        break;
    case 'get':
        getCar();
        break;
    case 'create':
        createCar();
        break;
    case 'update':
        updateCar();
        break;
    case 'delete':
        deleteCar();
        break;
    case 'update_status':
        updateCarStatus();
        break;
    case 'log_exit':
        logCarExit();
        break;
    case 'log_return':
        logCarReturn();
        break;
    case 'stats':
        getCarStats();
        break;
    default:
        errorResponse('Invalid action', 400);
}

/**
 * List all cars (optionally filtered by event)
 */
function listCars()
{
    $eventId = $_GET['event_id'] ?? null;
    $status = $_GET['status'] ?? null;

    $sql = "SELECT * FROM cars WHERE 1=1";
    $params = [];

    if ($eventId) {
        $sql .= " AND event_id = ?";
        $params[] = $eventId;
    }

    if ($status && $status !== 'all') {
        $sql .= " AND status = ?";
        $params[] = $status;
    }

    $sql .= " ORDER BY name ASC";

    $cars = dbQuery($sql, $params);
    successResponse($cars);
}

/**
 * Get single car details
 */
function getCar()
{
    $id = $_GET['id'] ?? null;
    if (!$id)
        errorResponse('Car ID required');

    $car = dbQueryOne("SELECT * FROM cars WHERE id = ?", [$id]);
    if (!$car)
        errorResponse('Car not found', 404);

    // Get recent logs
    $logs = dbQuery(
        "SELECT * FROM car_logs WHERE car_id = ? ORDER BY created_at DESC LIMIT 10",
        [$id]
    );

    $car['logs'] = $logs;
    successResponse($car);
}

/**
 * Create new car
 */
function createCar()
{
    // requireAuth('superadmin');

    $data = [
        'event_id' => $_POST['event_id'] ?? null,
        'name' => trim($_POST['name'] ?? ''),
        'model' => trim($_POST['model'] ?? ''),
        'registration_number' => trim($_POST['registration_number'] ?? ''),
        'color' => trim($_POST['color'] ?? ''),
        'initial_km' => floatval($_POST['initial_km'] ?? 0),
        'initial_fuel' => intval($_POST['initial_fuel'] ?? 100),
    ];

    if (empty($data['event_id']) || empty($data['name'])) {
        errorResponse('Event ID and name are required');
    }

    $sql = "INSERT INTO cars (event_id, name, model, registration_number, color, initial_km, initial_fuel) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    dbExecute($sql, array_values($data));
    $id = dbLastId();

    successResponse(['id' => $id], 'Car created successfully');
}

/**
 * Update car details
 */
function updateCar()
{
    // requireAuth('superadmin');

    $id = $_POST['id'] ?? null;
    if (!$id)
        errorResponse('Car ID required');

    $updates = [];
    $params = [];

    $fields = ['name', 'model', 'registration_number', 'color', 'image_url'];
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $updates[] = "$field = ?";
            $params[] = $_POST[$field];
        }
    }

    if (empty($updates))
        errorResponse('No fields to update');

    $params[] = $id;
    $sql = "UPDATE cars SET " . implode(', ', $updates) . " WHERE id = ?";

    dbExecute($sql, $params);
    successResponse(null, 'Car updated successfully');
}

/**
 * Delete car
 */
function deleteCar()
{
    // requireAuth('superadmin');

    $id = $_POST['id'] ?? null;
    if (!$id)
        errorResponse('Car ID required');

    dbExecute("UPDATE cars SET is_active = 0 WHERE id = ?", [$id]);
    successResponse(null, 'Car deleted successfully');
}

/**
 * Update car status
 */
function updateCarStatus()
{
    $carId = $_POST['car_id'] ?? null;
    $newStatus = $_POST['status'] ?? null;
    $notes = $_POST['notes'] ?? '';

    if (!$carId || !$newStatus) {
        errorResponse('Car ID and status required');
    }

    // Get current status
    $car = dbQueryOne("SELECT status FROM cars WHERE id = ?", [$carId]);
    if (!$car)
        errorResponse('Car not found', 404);

    // Update status
    dbExecute("UPDATE cars SET status = ? WHERE id = ?", [$newStatus, $carId]);

    // Log the change
    $userId = $_SESSION['user_id'] ?? null;
    dbExecute(
        "INSERT INTO car_logs (car_id, event_id, promoter_id, log_type, previous_status, new_status, notes) 
         SELECT ?, event_id, ?, 'status_change', ?, ?, ? FROM cars WHERE id = ?",
        [$carId, $userId, $car['status'], $newStatus, $notes, $carId]
    );

    successResponse(null, 'Status updated successfully');
}

/**
 * Log car exit (on_drive)
 */
function logCarExit()
{
    $data = [
        'car_id' => $_POST['car_id'] ?? null,
        'journalist_name' => trim($_POST['journalist_name'] ?? ''),
        'journalist_outlet' => trim($_POST['journalist_outlet'] ?? ''),
        'journalist_phone' => trim($_POST['journalist_phone'] ?? ''),
        'km_reading' => floatval($_POST['km_reading'] ?? 0),
        'fuel_level' => intval($_POST['fuel_level'] ?? 0),
        'exit_time' => $_POST['exit_time'] ?? date('Y-m-d H:i:s'),
        'notes' => trim($_POST['notes'] ?? ''),
    ];

    if (!$data['car_id'] || !$data['journalist_name']) {
        errorResponse('Car ID and journalist name required');
    }

    $userId = $_SESSION['user_id'] ?? null;

    // Update car status
    dbExecute("UPDATE cars SET status = 'on_drive' WHERE id = ?", [$data['car_id']]);

    // Create log entry
    $sql = "INSERT INTO car_logs (car_id, event_id, promoter_id, log_type, journalist_name, journalist_outlet, journalist_phone, km_reading, fuel_level, exit_time, notes)
            SELECT ?, event_id, ?, 'exit', ?, ?, ?, ?, ?, ?, ? FROM cars WHERE id = ?";

    dbExecute($sql, [
        $data['car_id'],
        $userId,
        $data['journalist_name'],
        $data['journalist_outlet'],
        $data['journalist_phone'],
        $data['km_reading'],
        $data['fuel_level'],
        $data['exit_time'],
        $data['notes'],
        $data['car_id']
    ]);

    successResponse(['log_id' => dbLastId()], 'Exit logged successfully');
}

/**
 * Log car return
 */
function logCarReturn()
{
    $data = [
        'car_id' => $_POST['car_id'] ?? null,
        'km_reading' => floatval($_POST['km_reading'] ?? 0),
        'fuel_level' => intval($_POST['fuel_level'] ?? 0),
        'return_time' => $_POST['return_time'] ?? date('Y-m-d H:i:s'),
        'notes' => trim($_POST['notes'] ?? ''),
    ];

    if (!$data['car_id']) {
        errorResponse('Car ID required');
    }

    $userId = $_SESSION['user_id'] ?? null;

    // Update car status
    dbExecute("UPDATE cars SET status = 'returned' WHERE id = ?", [$data['car_id']]);

    // Create log entry
    $sql = "INSERT INTO car_logs (car_id, event_id, promoter_id, log_type, km_reading, fuel_level, return_time, notes)
            SELECT ?, event_id, ?, 'return', ?, ?, ?, ? FROM cars WHERE id = ?";

    dbExecute($sql, [
        $data['car_id'],
        $userId,
        $data['km_reading'],
        $data['fuel_level'],
        $data['return_time'],
        $data['notes'],
        $data['car_id']
    ]);

    successResponse(['log_id' => dbLastId()], 'Return logged successfully');
}

/**
 * Get car statistics for dashboard
 */
function getCarStats()
{
    $eventId = $_GET['event_id'] ?? null;

    $where = $eventId ? "WHERE event_id = ?" : "";
    $params = $eventId ? [$eventId] : [];

    // Total stats
    $stats = dbQueryOne("
        SELECT 
            COUNT(*) as total_cars,
            SUM(CASE WHEN status != 'standby' AND status != 'hotel' THEN 1 ELSE 0 END) as active_cars,
            SUM(CASE WHEN status = 'cleaning' THEN 1 ELSE 0 END) as cleaning,
            SUM(CASE WHEN status = 'cleaned' THEN 1 ELSE 0 END) as cleaned,
            SUM(CASE WHEN status = 'pod_lineup' THEN 1 ELSE 0 END) as pod_lineup,
            SUM(CASE WHEN status = 'standby' THEN 1 ELSE 0 END) as standby,
            SUM(CASE WHEN status = 'on_drive' THEN 1 ELSE 0 END) as on_drive,
            SUM(CASE WHEN status = 'hotel' THEN 1 ELSE 0 END) as hotel
        FROM cars $where
    ", $params);

    // Total distance (from logs)
    $distance = dbQueryOne("
        SELECT COALESCE(SUM(
            CASE WHEN log_type = 'return' THEN km_reading ELSE 0 END -
            CASE WHEN log_type = 'exit' THEN km_reading ELSE 0 END
        ), 0) as total_km
        FROM car_logs cl
        JOIN cars c ON c.id = cl.car_id
        $where
    ", $params);

    $stats['total_km'] = abs($distance['total_km'] ?? 0);

    successResponse($stats);
}

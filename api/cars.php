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
    case 'emergency_return':
        emergencyReturn();
        break;
    case 'stats':
        getCarStats();
        break;
    case 'get_events':
        getEventsList();
        break;
    case 'restore':
        restoreCar();
        break;
    // NEW: Promoter workflow endpoints
    case 'pr_firms_for_promoter':
        getPrFirmsForPromoter();
        break;
    case 'cars_for_pr_firm':
        getCarsForPrFirm();
        break;
    case 'promoter_stats':
        getPromoterStats();
        break;
    case 'influencers_for_pr_firm':
        getInfluencersForPrFirm();
        break;
    case 'cars_for_promoter':
        getCarsForPromoter();
        break;
    case 'post_drive_ops':
        postDriveOps();
        break;
    case 'drive_summary':
        getDriveSummary();
        break;
    default:
        errorResponse('Invalid action', 400);
}

/**
 * List all cars with pagination (optionally filtered by event)
 */
function listCars()
{
    require_once __DIR__ . '/../includes/queries/cars.php';
    
    $eventId = $_GET['event_id'] ?? null;
    $status = $_GET['status'] ?? 'all';
    $page = max(1, intval($_GET['page'] ?? 1));
    $perPage = max(1, min(50, intval($_GET['per_page'] ?? 12)));
    
    $cars = getCarsPaginated($eventId, $status, $page, $perPage);
    $total = getTotalCarsCount($eventId, $status);
    $totalPages = ceil($total / $perPage);
    
    successResponse([
        'cars' => $cars,
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
    $data = [
        'event_id' => $_POST['event_id'] ?? null,
        'car_code' => trim($_POST['car_code'] ?? ''),
        'engine_number' => trim($_POST['engine_number'] ?? ''),
        'name' => trim($_POST['name'] ?? ''),
        'model' => trim($_POST['model'] ?? ''),
        'engine_number' => trim($_POST['engine_number'] ?? ''),
        'color' => trim($_POST['color'] ?? ''),
        'initial_km' => floatval($_POST['initial_km'] ?? 0),
        'initial_fuel' => intval($_POST['initial_fuel'] ?? 100),
    ];

    if (empty($data['event_id']) || empty($data['name'])) {
        errorResponse('Event ID and name are required');
    }

    $sql = "INSERT INTO cars (event_id, car_code, engine_number, name, model, color, initial_km, initial_fuel) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    dbExecute($sql, array_values($data));
    $id = dbLastId();

    successResponse(['id' => $id], 'Car created successfully');
}

/**
 * Update car details
 */
function updateCar()
{
    $id = $_POST['id'] ?? null;
    if (!$id)
        errorResponse('Car ID required');

    $updates = [];
    $params = [];

    $fields = ['event_id', 'car_code', 'engine_number', 'name', 'model', 'color', 'image_url'];
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
 * Log car exit (on_drive) - Updated for new promoter workflow
 */
function logCarExit()
{
    $data = [
        'car_id' => $_POST['car_id'] ?? null,
        'influencer_id' => $_POST['influencer_id'] ?? null,
        'pr_firm_id' => $_POST['pr_firm_id'] ?? null,
        'journalist_name' => trim($_POST['journalist_name'] ?? ''),
        'journalist_outlet' => trim($_POST['journalist_outlet'] ?? ''),
        'journalist_phone' => trim($_POST['journalist_phone'] ?? ''),
        'exit_time' => date('Y-m-d H:i:s'), // Auto-capture current time
        'notes' => trim($_POST['notes'] ?? ''),
    ];

    if (!$data['car_id'] || !$data['journalist_name']) {
        errorResponse('Car ID and journalist/influencer name required');
    }

    $userId = $_SESSION['user_id'] ?? null;

    // Race condition handling - check if car is still available
    $pdo = getDB();
    try {
        $pdo->beginTransaction();

        // Lock the car row and check status
        $car = $pdo->prepare("SELECT id, status, event_id FROM cars WHERE id = ? FOR UPDATE");
        $car->execute([$data['car_id']]);
        $carData = $car->fetch();

        if (!$carData) {
            $pdo->rollBack();
            errorResponse('Car not found', 404);
        }

        // Check if car is in a ready state
        if (!in_array($carData['status'], ['standby', 'cleaned', 'pod_lineup'])) {
            $pdo->rollBack();
            errorResponse('Car is not available for drive (current status: ' . $carData['status'] . ')', 409);
        }

        // Update car status to on_drive
        $pdo->prepare("UPDATE cars SET status = 'on_drive' WHERE id = ?")->execute([$data['car_id']]);

        // Create log entry with full mapping
        $stmt = $pdo->prepare(
            "INSERT INTO car_logs (car_id, event_id, promoter_id, influencer_id, pr_firm_id, log_type, 
             journalist_name, journalist_outlet, journalist_phone, exit_time, notes) 
             VALUES (?, ?, ?, ?, ?, 'exit', ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['car_id'],
            $carData['event_id'],
            $userId,
            $data['influencer_id'],
            $data['pr_firm_id'],
            $data['journalist_name'],
            $data['journalist_outlet'],
            $data['journalist_phone'],
            $data['exit_time'],
            $data['notes']
        ]);

        $logId = $pdo->lastInsertId();
        $pdo->commit();

        successResponse([
            'log_id' => $logId,
            'exit_time' => $data['exit_time']
        ], 'Exit logged successfully');

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("logCarExit error: " . $e->getMessage());
        errorResponse('Failed to log exit: ' . $e->getMessage(), 500);
    }
}

/**
 * Log car return - Updated with damage handling
 */
function logCarReturn()
{
    $data = [
        'car_id' => $_POST['car_id'] ?? null,
        'km_reading' => floatval($_POST['km_reading'] ?? 0),
        'fuel_level' => intval($_POST['fuel_level'] ?? 0),
        'return_time' => date('Y-m-d H:i:s'), // Auto-capture
        'has_damage' => isset($_POST['has_damage']) && $_POST['has_damage'] == '1' ? 1 : 0,
        'damage_description' => trim($_POST['damage_description'] ?? ''),
        'photo_urls' => trim($_POST['photo_urls'] ?? ''),
        'notes' => trim($_POST['notes'] ?? ''),
    ];

    if (!$data['car_id']) {
        errorResponse('Car ID required');
    }

    // If damage is reported, damage description is required
    if ($data['has_damage'] && empty($data['damage_description'])) {
        errorResponse('Damage description is required when reporting damage');
    }

    $userId = $_SESSION['user_id'] ?? null;

    // Get car info
    $car = dbQueryOne("SELECT event_id, status FROM cars WHERE id = ?", [$data['car_id']]);
    if (!$car) {
        errorResponse('Car not found', 404);
    }

    // Check if car is actually on drive
    if ($car['status'] !== 'on_drive') {
        errorResponse('Car is not currently on drive (status: ' . $car['status'] . ')', 409);
    }

    // Get the exit log to retrieve influencer info
    $exitLog = dbQueryOne("
        SELECT cl.*, pf.name as pr_firm_name 
        FROM car_logs cl 
        LEFT JOIN pr_firms pf ON pf.id = cl.pr_firm_id
        WHERE cl.car_id = ? AND cl.log_type = 'exit' 
        ORDER BY cl.id DESC LIMIT 1
    ", [$data['car_id']]);

    // Determine new status based on damage
    $newStatus = $data['has_damage'] ? 'under_inspection' : 'returned';

    // Update car status
    dbExecute("UPDATE cars SET status = ? WHERE id = ?", [$newStatus, $data['car_id']]);

    // Create log entry
    $sql = "INSERT INTO car_logs (car_id, event_id, promoter_id, log_type, km_reading, fuel_level, 
            return_time, has_damage, damage_description, photo_urls, notes)
            VALUES (?, ?, ?, 'return', ?, ?, ?, ?, ?, ?, ?)";

    dbExecute($sql, [
        $data['car_id'],
        $car['event_id'],
        $userId,
        $data['km_reading'],
        $data['fuel_level'],
        $data['return_time'],
        $data['has_damage'],
        $data['damage_description'],
        $data['photo_urls'],
        $data['notes']
    ]);

    $message = $data['has_damage']
        ? 'Return logged - car marked for inspection due to damage'
        : 'Return logged successfully';

    successResponse([
        'log_id' => dbLastId(),
        'return_time' => $data['return_time'],
        'new_status' => $newStatus,
        'journalist_name' => $exitLog['journalist_name'] ?? 'Unknown',
        'influencer_id' => $exitLog['influencer_id'] ?? null,
        'pr_firm_name' => $exitLog['pr_firm_name'] ?? '',
        'pr_firm_id' => $exitLog['pr_firm_id'] ?? null
    ], $message);
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

/**
 * Get events list for dropdown
 */
function getEventsList()
{
    require_once __DIR__ . '/../includes/queries/cars.php';
    $events = getAllActiveEvents();
    successResponse($events);
}
function restoreCar()
{
    $id = $_POST['id'] ?? null;
    if (!$id)
        errorResponse('Car ID required');

    dbExecute("UPDATE cars SET is_active = 1 WHERE id = ?", [$id]);
    successResponse(null, 'Car restored successfully');
}

// ============================================================
// NEW PROMOTER WORKFLOW FUNCTIONS
// ============================================================

/**
 * Get PR Firms assigned to a specific promoter
 */
function getPrFirmsForPromoter()
{
    $promoterId = $_GET['promoter_id'] ?? $_SESSION['user_id'] ?? null;
    $eventId = $_GET['event_id'] ?? null;

    if (!$promoterId) {
        errorResponse('Promoter ID required');
    }

    $sql = "SELECT pf.*, ppf.event_id,
            (SELECT COUNT(*) FROM pr_firm_cars pfc 
             JOIN cars c ON c.id = pfc.car_id 
             WHERE pfc.pr_firm_id = pf.id 
             AND pfc.event_id = ppf.event_id 
             AND c.status IN ('standby', 'cleaned', 'pod_lineup')) as ready_cars
            FROM pr_firms pf
            JOIN promoter_pr_firms ppf ON ppf.pr_firm_id = pf.id
            WHERE ppf.promoter_id = ?";
    $params = [$promoterId];

    if ($eventId) {
        $sql .= " AND ppf.event_id = ?";
        $params[] = $eventId;
    }

    $sql .= " ORDER BY pf.name";

    $prFirms = dbQuery($sql, $params);
    successResponse($prFirms);
}

/**
 * Get Cars assigned to a specific PR Firm (with status badges)
 */
function getCarsForPrFirm()
{
    $prFirmId = $_GET['pr_firm_id'] ?? null;
    $eventId = $_GET['event_id'] ?? null;

    if (!$prFirmId) {
        errorResponse('PR Firm ID required');
    }

    $sql = "SELECT c.*, pfc.pr_firm_id,
            CASE 
                WHEN c.status IN ('standby', 'cleaned', 'pod_lineup') THEN 'ready'
                WHEN c.status = 'on_drive' THEN 'on_drive'
                ELSE 'pending'
            END as display_status,
            (SELECT cl.exit_time FROM car_logs cl 
             WHERE cl.car_id = c.id AND cl.log_type = 'exit' 
             ORDER BY cl.created_at DESC LIMIT 1) as last_exit_time
            FROM cars c
            JOIN pr_firm_cars pfc ON pfc.car_id = c.id
            WHERE pfc.pr_firm_id = ? AND c.is_active = 1";
    $params = [$prFirmId];

    if ($eventId) {
        $sql .= " AND pfc.event_id = ?";
        $params[] = $eventId;
    }

    // Order: ready cars first, then on_drive, then pending
    $sql .= " ORDER BY 
              CASE 
                  WHEN c.status IN ('standby', 'cleaned', 'pod_lineup') THEN 1
                  WHEN c.status = 'on_drive' THEN 2
                  ELSE 3
              END, c.car_code";

    $cars = dbQuery($sql, $params);
    successResponse($cars);
}

/**
 * Get stats for promoter dashboard (their assigned cars only)
 */
function getPromoterStats()
{
    $promoterId = $_GET['promoter_id'] ?? $_SESSION['user_id'] ?? null;
    $eventId = $_GET['event_id'] ?? null;

    if (!$promoterId) {
        errorResponse('Promoter ID required');
    }

    // Get cars assigned to this promoter via PR firms
    $sql = "SELECT 
            COUNT(DISTINCT c.id) as total_cars,
            SUM(CASE WHEN c.status IN ('standby', 'cleaned', 'pod_lineup') THEN 1 ELSE 0 END) as ready_cars,
            SUM(CASE WHEN c.status = 'on_drive' THEN 1 ELSE 0 END) as on_drive,
            SUM(CASE WHEN c.status IN ('cleaning', 'returned', 'under_inspection') THEN 1 ELSE 0 END) as pending_cars
            FROM cars c
            JOIN pr_firm_cars pfc ON pfc.car_id = c.id
            JOIN promoter_pr_firms ppf ON ppf.pr_firm_id = pfc.pr_firm_id AND ppf.event_id = pfc.event_id
            WHERE ppf.promoter_id = ? AND c.is_active = 1";
    $params = [$promoterId];

    if ($eventId) {
        $sql .= " AND ppf.event_id = ?";
        $params[] = $eventId;
    }

    $stats = dbQueryOne($sql, $params);
    successResponse($stats);
}

/**
 * Get influencers for a specific PR Firm (for dropdown in form)
 */
function getInfluencersForPrFirm()
{
    $prFirmId = $_GET['pr_firm_id'] ?? null;
    $eventId = $_GET['event_id'] ?? null;

    if (!$prFirmId) {
        errorResponse('PR Firm ID required');
    }

    $sql = "SELECT id, name, outlet, phone FROM influencers WHERE pr_firm_id = ?";
    $params = [$prFirmId];

    if ($eventId) {
        $sql .= " AND event_id = ?";
        $params[] = $eventId;
    }

    $sql .= " ORDER BY name";

    $influencers = dbQuery($sql, $params);
    successResponse($influencers);
}

/**
 * Emergency return - car breakdown/issue during drive
 */
function emergencyReturn()
{
    $data = [
        'car_id' => $_POST['car_id'] ?? null,
        'notes' => trim($_POST['notes'] ?? ''),
    ];

    if (!$data['car_id']) {
        errorResponse('Car ID required');
    }

    if (empty($data['notes'])) {
        errorResponse('Notes are required for emergency return');
    }

    $userId = $_SESSION['user_id'] ?? null;

    // Get current car info
    $car = dbQueryOne("SELECT status, event_id FROM cars WHERE id = ?", [$data['car_id']]);
    if (!$car) {
        errorResponse('Car not found', 404);
    }

    // Update car status to out_of_service
    dbExecute("UPDATE cars SET status = 'out_of_service' WHERE id = ?", [$data['car_id']]);

    // Create emergency log entry
    dbExecute(
        "INSERT INTO car_logs (car_id, event_id, promoter_id, log_type, previous_status, new_status, return_time, notes) 
         VALUES (?, ?, ?, 'emergency', ?, 'out_of_service', NOW(), ?)",
        [$data['car_id'], $car['event_id'], $userId, $car['status'], $data['notes']]
    );

    successResponse(['log_id' => dbLastId()], 'Emergency return logged - car marked out of service');
}

/**
 * Get Drive Summary - All drive logs with joined data
 * Returns: car_code, influencer name, PR firm, promoter, exit/return times, duration, distance, feedback
 */
function getDriveSummary()
{
    $eventId = $_GET['event_id'] ?? null;
    $carId = $_GET['car_id'] ?? null;
    $promoterId = $_GET['promoter_id'] ?? null;
    $prFirmId = $_GET['pr_firm_id'] ?? null;
    $dateFrom = $_GET['date_from'] ?? null;
    $dateTo = $_GET['date_to'] ?? null;
    $limit = intval($_GET['limit'] ?? 100);
    $offset = intval($_GET['offset'] ?? 0);

    // Build query with all joins
    $sql = "
        SELECT 
            cl.id as log_id,
            cl.log_type,
            cl.exit_time,
            cl.return_time,
            TIMESTAMPDIFF(MINUTE, cl.exit_time, cl.return_time) as duration_minutes,
            cl.km_reading,
            cl.fuel_level,
            cl.has_damage,
            cl.damage_description,
            cl.notes,
            cl.created_at,
            
            -- Car info
            c.id as car_id,
            c.car_code,
            c.name as car_name,
            c.model as car_model,
            c.color as car_color,
            
            -- Influencer info (from linked record or direct text)
            COALESCE(i.name, cl.journalist_name) as influencer_name,
            COALESCE(i.outlet, cl.journalist_outlet) as influencer_outlet,
            COALESCE(i.phone, cl.journalist_phone) as influencer_phone,
            i.id as influencer_id,
            
            -- PR Firm info
            pf.id as pr_firm_id,
            pf.name as pr_firm_name,
            
            -- Promoter info
            u.id as promoter_id,
            u.name as promoter_name,
            
            -- Event info
            e.id as event_id,
            e.name as event_name,
            
            -- Feedback (if exists)
            f.id as feedback_id,
            f.rating as feedback_rating,
            f.experience as feedback_experience
            
        FROM car_logs cl
        LEFT JOIN cars c ON c.id = cl.car_id
        LEFT JOIN influencers i ON i.id = cl.influencer_id
        LEFT JOIN pr_firms pf ON pf.id = cl.pr_firm_id
        LEFT JOIN users u ON u.id = cl.promoter_id
        LEFT JOIN events e ON e.id = cl.event_id
        LEFT JOIN feedback f ON f.car_log_id = cl.id
        
        WHERE cl.log_type IN ('exit', 'return', 'emergency')
    ";

    $params = [];

    // Apply filters
    if ($eventId) {
        $sql .= " AND cl.event_id = ?";
        $params[] = $eventId;
    }

    if ($carId) {
        $sql .= " AND cl.car_id = ?";
        $params[] = $carId;
    }

    if ($promoterId) {
        $sql .= " AND cl.promoter_id = ?";
        $params[] = $promoterId;
    }

    if ($prFirmId) {
        $sql .= " AND cl.pr_firm_id = ?";
        $params[] = $prFirmId;
    }

    if ($dateFrom) {
        $sql .= " AND DATE(cl.exit_time) >= ?";
        $params[] = $dateFrom;
    }

    if ($dateTo) {
        $sql .= " AND DATE(cl.exit_time) <= ?";
        $params[] = $dateTo;
    }

    $sql .= " ORDER BY cl.exit_time DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    $drives = dbQuery($sql, $params);

    // Get paired exit-return data for distance calculation
    $result = [];
    foreach ($drives as $drive) {
        // If this is a return log, try to find matching exit for distance
        if ($drive['log_type'] === 'return' && $drive['car_id']) {
            $exitLog = dbQueryOne("
                SELECT km_reading FROM car_logs 
                WHERE car_id = ? AND log_type = 'exit' AND exit_time < ?
                ORDER BY exit_time DESC LIMIT 1
            ", [$drive['car_id'], $drive['return_time']]);

            if ($exitLog && $drive['km_reading'] && $exitLog['km_reading']) {
                $drive['distance_km'] = round($drive['km_reading'] - $exitLog['km_reading'], 1);
            }
        }

        // Format duration
        if ($drive['duration_minutes']) {
            $hours = floor($drive['duration_minutes'] / 60);
            $mins = $drive['duration_minutes'] % 60;
            $drive['duration_formatted'] = sprintf('%dh %dm', $hours, $mins);
        }

        $result[] = $drive;
    }

    // Get total count for pagination
    $countSql = "SELECT COUNT(*) as total FROM car_logs cl WHERE cl.log_type IN ('exit', 'return', 'emergency')";
    $countParams = [];

    if ($eventId) {
        $countSql .= " AND cl.event_id = ?";
        $countParams[] = $eventId;
    }

    $total = dbQueryOne($countSql, $countParams);

    successResponse([
        'drives' => $result,
        'total' => intval($total['total'] ?? 0),
        'limit' => $limit,
        'offset' => $offset
    ]);
}

/**
 * Get all cars assigned to a promoter (via PR Firms) for dropdown
 */
function getCarsForPromoter()
{
    // $userId = $_SESSION['user_id'] ?? null;
    // Allow car_id override for testing without login
    $userId = $_SESSION['user_id'] ?? null;

    // For testing if not logged in, get first promoter
    if (!$userId) {
        $promoter = dbQueryOne("SELECT id FROM users WHERE role = 'promoter' LIMIT 1");
        $userId = $promoter['id'] ?? null;
    }

    if (!$userId) {
        errorResponse('User not authenticated');
    }

    $eventId = $_GET['event_id'] ?? null;

    // Get assigned PR Firms (filtered by event if provided)
    $sql = "SELECT pr_firm_id FROM promoter_pr_firms WHERE promoter_id = ?";
    $params = [$userId];

    if ($eventId) {
        $sql .= " AND event_id = ?";
        $params[] = $eventId;
    }

    $rows = dbQuery($sql, $params);
    $prFirmIds = array_column($rows, 'pr_firm_id');

    if (empty($prFirmIds)) {
        successResponse([]);
    }

    $placeholders = implode(',', array_fill(0, count($prFirmIds), '?'));

    // Also filter cars by event_id to be double sure
    $sql = "
        SELECT c.id, c.car_code, c.name, c.status, pf.name as pr_firm_name
        FROM cars c
        JOIN pr_firm_cars pfc ON c.id = pfc.car_id
        JOIN pr_firms pf ON pfc.pr_firm_id = pf.id
        WHERE pfc.pr_firm_id IN ($placeholders)
        AND c.is_active = 1
    ";

    $queryParams = $prFirmIds;

    if ($eventId) {
        $sql .= " AND c.event_id = ?";
        $queryParams[] = $eventId;
    }

    $sql .= " ORDER BY c.car_code ASC";

    $cars = dbQuery($sql, $queryParams);
    successResponse($cars);
}

/**
 * Post Drive Operations - Promoter fills after influencer leaves
 * Sets car status to 'under_cleaning'
 */
function postDriveOps()
{
    $carId = $_POST['car_id'] ?? null;
    $kmReading = $_POST['km_reading'] ?? null;
    $notes = trim($_POST['notes'] ?? '');

    if (!$carId) {
        errorResponse('Car ID is required');
    }

    // Verify car is in 'returned' status
    $car = dbQueryOne("SELECT * FROM cars WHERE id = ?", [$carId]);
    if (!$car) {
        errorResponse('Car not found', 404);
    }
    if ($car['status'] !== 'returned') {
        errorResponse('Car must be in "Returned" status for post-drive operations');
    }

    $userId = $_SESSION['user_id'] ?? null;
    $eventId = $car['event_id'];

    // Handle photo uploads
    $photoUrls = [];
    if (!empty($_FILES['photos'])) {
        $uploadDir = __DIR__ . '/../uploads/post-drive/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Handle both single and multiple file uploads
        $files = [];
        if (is_array($_FILES['photos']['name'])) {
            for ($i = 0; $i < count($_FILES['photos']['name']); $i++) {
                if ($_FILES['photos']['error'][$i] === UPLOAD_ERR_OK) {
                    $files[] = [
                        'name' => $_FILES['photos']['name'][$i],
                        'tmp_name' => $_FILES['photos']['tmp_name'][$i],
                        'size' => $_FILES['photos']['size'][$i]
                    ];
                }
            }
        }

        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $filename = 'postdrive_' . $carId . '_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    $photoUrls[] = 'uploads/post-drive/' . $filename;
                }
            }
        }
    }

    // Create car log entry for post-drive ops
    dbExecute("
        INSERT INTO car_logs (car_id, event_id, promoter_id, log_type, km_reading, notes, photo_urls, created_at)
        VALUES (?, ?, ?, 'note', ?, ?, ?, NOW())
    ", [
        $carId,
        $eventId,
        $userId,
        $kmReading,
        'Post-drive operations: ' . $notes,
        !empty($photoUrls) ? json_encode($photoUrls) : null
    ]);

    // Update car status to under_cleaning
    dbExecute("UPDATE cars SET status = 'under_cleaning', updated_at = NOW() WHERE id = ?", [$carId]);

    successResponse(['car_id' => $carId], 'Post-drive operations saved. Car sent for cleaning.');
}


<?php
/**
 * MDM API - Feedback
 * Handle feedback submission for test drives
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'submit_feedback':
        submitFeedback();
        break;
    case 'list':
        listFeedback();
        break;
    case 'get':
        getFeedback();
        break;
    default:
        errorResponse('Invalid action', 400);
}

/**
 * Submit feedback for a test drive
 */
function submitFeedback()
{
    $carId = $_POST['car_id'] ?? null;
    $prFirmId = $_POST['pr_firm_id'] ?? null;
    $influencerId = $_POST['influencer_id'] ?? null;

    // Rating categories
    $handling = intval($_POST['handling'] ?? 0);
    $comfort = intval($_POST['comfort'] ?? 0);
    $performance = intval($_POST['performance'] ?? 0);
    $nvh = intval($_POST['nvh'] ?? 0);
    $features = intval($_POST['features'] ?? 0);
    $appearance = intval($_POST['appearance'] ?? 0);
    $overall = intval($_POST['overall'] ?? 0);
    $comments = trim($_POST['comments'] ?? '');

    if (!$carId || !$prFirmId || !$influencerId) {
        errorResponse('Car, PR Firm, and Influencer are required');
    }

    // Validate ratings (1-5)
    $ratings = [$handling, $comfort, $performance, $nvh, $features, $appearance, $overall];
    foreach ($ratings as $r) {
        if ($r < 1 || $r > 5) {
            errorResponse('All ratings must be between 1 and 5');
        }
    }

    $userId = $_SESSION['user_id'] ?? null;

    // Get car's event_id
    $car = dbQueryOne("SELECT event_id FROM cars WHERE id = ?", [$carId]);
    if (!$car) {
        errorResponse('Car not found', 404);
    }

    // Get influencer name
    $influencer = dbQueryOne("SELECT name FROM influencers WHERE id = ?", [$influencerId]);
    $journalistName = $influencer['name'] ?? 'Unknown';

    // Find the most recent car log for this car (to link feedback to drive)
    $carLog = dbQueryOne("
        SELECT id FROM car_logs 
        WHERE car_id = ? AND log_type IN ('exit', 'return')
        ORDER BY created_at DESC LIMIT 1
    ", [$carId]);

    // Store all ratings as JSON in 'experience' column
    $ratingsJson = json_encode([
        'handling' => $handling,
        'comfort' => $comfort,
        'performance' => $performance,
        'nvh' => $nvh,
        'features' => $features,
        'appearance' => $appearance,
        'overall' => $overall
    ]);

    // Insert feedback
    $sql = "INSERT INTO feedback (car_log_id, event_id, promoter_id, journalist_name, rating, experience, concerns) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    dbExecute($sql, [
        $carLog['id'] ?? null,
        $car['event_id'],
        $userId,
        $journalistName,
        $overall, // Use overall as the primary rating
        $ratingsJson, // Store individual ratings as JSON
        $comments
    ]);

    successResponse(['id' => dbLastId()], 'Feedback submitted successfully');
}

/**
 * List all feedback for an event
 */
function listFeedback()
{
    $eventId = $_GET['event_id'] ?? null;

    $sql = "SELECT f.*, c.car_code, c.name as car_name
            FROM feedback f
            LEFT JOIN car_logs cl ON cl.id = f.car_log_id
            LEFT JOIN cars c ON c.id = cl.car_id
            WHERE 1=1";
    $params = [];

    if ($eventId) {
        $sql .= " AND f.event_id = ?";
        $params[] = $eventId;
    }

    $sql .= " ORDER BY f.created_at DESC";

    $feedback = dbQuery($sql, $params);

    // Parse ratings JSON
    foreach ($feedback as &$f) {
        if ($f['experience']) {
            $ratings = json_decode($f['experience'], true);
            if (is_array($ratings)) {
                $f['ratings'] = $ratings;
            }
        }
    }

    successResponse($feedback);
}

/**
 * Get single feedback
 */
function getFeedback()
{
    $id = $_GET['id'] ?? null;
    if (!$id) {
        errorResponse('Feedback ID required');
    }

    $feedback = dbQueryOne("SELECT * FROM feedback WHERE id = ?", [$id]);
    if (!$feedback) {
        errorResponse('Feedback not found', 404);
    }

    // Parse ratings
    if ($feedback['experience']) {
        $ratings = json_decode($feedback['experience'], true);
        if (is_array($ratings)) {
            $feedback['ratings'] = $ratings;
        }
    }

    successResponse($feedback);
}

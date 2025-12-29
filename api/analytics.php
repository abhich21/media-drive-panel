<?php
/**
 * MDM API - Analytics
 * Analytics data and export
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'stats':
        getStats();
        break;
    case 'events_by_month':
        getEventsByMonth();
        break;
    case 'top_cars':
        getTopCarsData();
        break;
    case 'get_events':
        getEventsList();
        break;
    case 'export':
        exportEventData();
        break;
    default:
        errorResponse('Invalid action', 400);
}

/**
 * Get analytics stats
 */
function getStats()
{
    require_once __DIR__ . '/../includes/queries/analytics.php';
    
    $eventId = $_GET['event_id'] ?? null;
    $year = $_GET['year'] ?? null;
    $stats = getAnalyticsStats($eventId, $year);
    
    successResponse($stats);
}

/**
 * Get events count by month for specified year
 */
function getEventsByMonth()
{
    $year = $_GET['year'] ?? date('Y');
    
    $sql = "SELECT MONTH(start_date) as month, COUNT(*) as count 
            FROM events 
            WHERE YEAR(start_date) = ?
            GROUP BY MONTH(start_date)
            ORDER BY month";
    
    $results = dbQuery($sql, [$year]);
    
    // Initialize all months with 0
    $monthlyData = array_fill(1, 12, 0);
    
    // Fill in actual counts
    foreach ($results as $row) {
        $monthlyData[(int)$row['month']] = (int)$row['count'];
    }
    
    // Convert to array format for chart
    $data = [];
    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    
    foreach ($monthlyData as $monthNum => $count) {
        $data[] = [
            'month' => $months[$monthNum - 1],
            'count' => $count
        ];
    }
    
    successResponse($data);
}

/**
 * Get top performing cars
 */
function getTopCarsData()
{
    require_once __DIR__ . '/../includes/queries/analytics.php';
    
    $eventId = $_GET['event_id'] ?? null;
    $limit = min(10, max(1, intval($_GET['limit'] ?? 5)));
    
    $cars = getTopCars($eventId, $limit);
    
    successResponse($cars);
}

/**
 * Get events list for dropdown
 */
function getEventsList()
{
    $events = dbQuery(
        "SELECT id, name FROM events WHERE (is_deleted = 0 OR is_deleted IS NULL) ORDER BY name ASC"
    );
    successResponse($events);
}

/**
 * Export event data as CSV
 */
function exportEventData()
{
    require_once __DIR__ . '/../includes/queries/analytics.php';
    
    $eventId = $_GET['event_id'] ?? null;
    
    if (!$eventId || $eventId === 'all') {
        errorResponse('Please select a specific event to export');
    }
    
    $data = getEventExportData($eventId);
    
    if (!$data) {
        errorResponse('Event not found', 404);
    }
    
    // Generate CSV content
    $csv = [];
    
    // Event section
    $csv[] = "=== EVENT DETAILS ===";
    $csv[] = "Name,Client,Start Date,End Date,Location";
    $csv[] = '"' . str_replace('"', '""', $data['event']['name']) . '",' .
             '"' . str_replace('"', '""', $data['event']['client_name'] ?? '') . '",' .
             ($data['event']['start_date'] ?? '') . ',' .
             ($data['event']['end_date'] ?? '') . ',' .
             '"' . str_replace('"', '""', $data['event']['location'] ?? '') . '"';
    $csv[] = "";
    
    // Promoters section
    $csv[] = "=== PROMOTERS ===";
    $csv[] = "Name,Email,Phone,Assigned Cars";
    foreach ($data['promoters'] as $p) {
        $csv[] = '"' . str_replace('"', '""', $p['name']) . '",' .
                 ($p['email'] ?? '') . ',' .
                 ($p['phone'] ?? '') . ',' .
                 '"' . ($p['assigned_cars'] ?? '') . '"';
    }
    $csv[] = "";
    
    // Cars section
    $csv[] = "=== CARS ===";
    $csv[] = "Name,Car Code,Model,Registration,Color,Initial KM,Initial Fuel,Total Drives,Total KM";
    foreach ($data['cars'] as $c) {
        $csv[] = '"' . str_replace('"', '""', $c['name']) . '",' .
                 ($c['car_code'] ?? '') . ',' .
                 '"' . str_replace('"', '""', $c['model'] ?? '') . '",' .
                 ($c['registration_number'] ?? '') . ',' .
                 ($c['color'] ?? '') . ',' .
                 ($c['initial_km'] ?? 0) . ',' .
                 ($c['initial_fuel'] ?? 0) . ',' .
                 ($c['total_drives'] ?? 0) . ',' .
                 ($c['total_km'] ?? 0);
    }
    
    // Return CSV data for download
    successResponse([
        'filename' => 'event_' . $eventId . '_export_' . date('Y-m-d') . '.csv',
        'content' => implode("\n", $csv)
    ]);
}

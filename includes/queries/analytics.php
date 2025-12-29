<?php
/**
 * MDM Analytics Query Functions
 * Reusable database queries for analytics
 */

require_once __DIR__ . '/../../config/database.php';

/**
 * Get analytics stats with optional event and year filter
 * @param int|null $eventId Filter by event ID
 * @param int|null $year Filter by year
 * @return array Stats data
 */
function getAnalyticsStats($eventId = null, $year = null)
{
    $stats = [];
    $currentYear = $year ?? date('Y');
    
    // Total Events (filtered by year)
    if ($eventId && $eventId !== 'all') {
        $stats['totalEvents'] = 1;
    } else {
        $result = dbQueryOne(
            "SELECT COUNT(*) as count FROM events WHERE YEAR(start_date) = ?",
            [$currentYear]
        );
        $stats['totalEvents'] = (int) ($result['count'] ?? 0);
    }
    
    // Total Cars
    if ($eventId && $eventId !== 'all') {
        $result = dbQueryOne("SELECT COUNT(*) as count FROM cars WHERE event_id = ? AND is_active = 1", [$eventId]);
    } else {
        $result = dbQueryOne(
            "SELECT COUNT(*) as count FROM cars c 
             JOIN events e ON c.event_id = e.id 
             WHERE c.is_active = 1 AND YEAR(e.start_date) = ?",
            [$currentYear]
        );
    }
    $stats['totalCars'] = (int) ($result['count'] ?? 0);
    
    // Total Promoters
    if ($eventId && $eventId !== 'all') {
        $result = dbQueryOne(
            "SELECT COUNT(DISTINCT promoter_id) as count FROM event_promoters WHERE event_id = ? AND is_deleted = 0",
            [$eventId]
        );
    } else {
        $result = dbQueryOne(
            "SELECT COUNT(DISTINCT ep.promoter_id) as count FROM event_promoters ep
             JOIN events e ON ep.event_id = e.id
             WHERE ep.is_deleted = 0 AND YEAR(e.start_date) = ?",
            [$currentYear]
        );
    }
    $stats['totalPromoters'] = (int) ($result['count'] ?? 0);
    
    // Total Drives (from car_logs with exit type)
    if ($eventId && $eventId !== 'all') {
        $result = dbQueryOne(
            "SELECT COUNT(*) as count FROM car_logs WHERE event_id = ? AND log_type = 'exit'",
            [$eventId]
        );
    } else {
        $result = dbQueryOne(
            "SELECT COUNT(*) as count FROM car_logs cl
             JOIN events e ON cl.event_id = e.id
             WHERE cl.log_type = 'exit' AND YEAR(e.start_date) = ?",
            [$currentYear]
        );
    }
    $stats['totalDrives'] = (int) ($result['count'] ?? 0);
    
    // Total Kilometers
    if ($eventId && $eventId !== 'all') {
        $result = dbQueryOne(
            "SELECT SUM(km_reading) as total FROM car_logs WHERE event_id = ? AND km_reading IS NOT NULL",
            [$eventId]
        );
    } else {
        $result = dbQueryOne(
            "SELECT SUM(cl.km_reading) as total FROM car_logs cl
             JOIN events e ON cl.event_id = e.id
             WHERE cl.km_reading IS NOT NULL AND YEAR(e.start_date) = ?",
            [$currentYear]
        );
    }
    $stats['totalKm'] = (float) ($result['total'] ?? 0);
    
    return $stats;
}

/**
 * Get top performing cars
 * @param int|null $eventId Filter by event ID
 * @param int $limit Number of cars to return
 * @return array Top cars list
 */
function getTopCars($eventId = null, $limit = 5)
{
    $params = [];
    $where = "";
    
    if ($eventId && $eventId !== 'all') {
        $where = "WHERE cl.event_id = ?";
        $params[] = $eventId;
    }
    
    $params[] = $limit;
    
    $sql = "SELECT c.name, c.car_code,
                COUNT(CASE WHEN cl.log_type = 'exit' THEN 1 END) as drives,
                COALESCE(SUM(cl.km_reading), 0) as total_km
            FROM cars c
            LEFT JOIN car_logs cl ON cl.car_id = c.id
            $where
            GROUP BY c.id, c.name, c.car_code
            HAVING drives > 0 OR total_km > 0
            ORDER BY drives DESC, total_km DESC
            LIMIT ?";
    
    return dbQuery($sql, $params);
}

/**
 * Get event data for export
 * @param int $eventId Event ID
 * @return array Event data with promoters and cars
 */
function getEventExportData($eventId)
{
    // Event details
    $event = dbQueryOne(
        "SELECT id, name, client_name, start_date, end_date, location FROM events WHERE id = ?",
        [$eventId]
    );
    
    if (!$event) return null;
    
    // Promoters for this event
    $promoters = dbQuery(
        "SELECT u.name, u.email, u.phone, ep.assigned_cars
         FROM event_promoters ep
         JOIN users u ON ep.promoter_id = u.id
         WHERE ep.event_id = ? AND ep.is_deleted = 0
         ORDER BY u.name",
        [$eventId]
    );
    
    // Cars for this event
    $cars = dbQuery(
        "SELECT c.name, c.car_code, c.model, c.registration_number, c.color,
                c.initial_km, c.initial_fuel,
                (SELECT COUNT(*) FROM car_logs cl WHERE cl.car_id = c.id AND cl.log_type = 'exit') as total_drives,
                (SELECT COALESCE(SUM(km_reading), 0) FROM car_logs cl WHERE cl.car_id = c.id) as total_km
         FROM cars c
         WHERE c.event_id = ? AND c.is_active = 1
         ORDER BY c.name",
        [$eventId]
    );
    
    return [
        'event' => $event,
        'promoters' => $promoters,
        'cars' => $cars
    ];
}

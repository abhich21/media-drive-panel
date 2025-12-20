<?php
/**
 * MDM Dashboard Query Functions
 * Reusable database queries for dashboard statistics
 */

require_once __DIR__ . '/../../config/database.php';

/**
 * Get overview statistics for admin dashboard
 * @return array
 */
function getOverviewStats()
{
    // Total events count
    $totalEvents = dbQueryOne("SELECT COUNT(*) as count FROM events")['count'] ?? 0;
    
    // Active events count
    $activeEvents = dbQueryOne("SELECT COUNT(*) as count FROM events WHERE status = 'active'")['count'] ?? 0;
    
    // Total cars count
    $totalCars = dbQueryOne("SELECT COUNT(*) as count FROM cars WHERE is_active = 1")['count'] ?? 0;
    
    // Total promoters count
    $totalPromoters = dbQueryOne("SELECT COUNT(*) as count FROM users WHERE role = 'promoter' AND is_active = 1")['count'] ?? 0;
    
    // Total drives (exit logs)
    $totalDrives = dbQueryOne("SELECT COUNT(*) as count FROM car_logs WHERE log_type = 'exit'")['count'] ?? 0;
    
    // Cars currently on drive
    $activeNow = dbQueryOne("SELECT COUNT(*) as count FROM cars WHERE status = 'on_drive' AND is_active = 1")['count'] ?? 0;

    return [
        'totalEvents' => (int) $totalEvents,
        'activeEvents' => (int) $activeEvents,
        'totalCars' => (int) $totalCars,
        'totalPromoters' => (int) $totalPromoters,
        'totalDrives' => (int) $totalDrives,
        'activeNow' => (int) $activeNow,
    ];
}

/**
 * Get recent events for admin dashboard
 * @param int $limit Number of events to fetch
 * @return array
 */
function getRecentEvents($limit = 5)
{
    $sql = "SELECT id, name, client_name as client, status, start_date as date, location 
            FROM events 
            ORDER BY created_at DESC 
            LIMIT ?";
    
    return dbQuery($sql, [$limit]);
}

/**
 * Get event counts by status
 * @return array
 */
function getEventCountsByStatus()
{
    $sql = "SELECT 
                status,
                COUNT(*) as count 
            FROM events 
            GROUP BY status";
    
    $results = dbQuery($sql);
    $counts = [
        'upcoming' => 0,
        'active' => 0,
        'completed' => 0
    ];
    
    foreach ($results as $row) {
        $counts[$row['status']] = (int) $row['count'];
    }
    
    return $counts;
}

/**
 * Get car counts by status
 * @param int|null $eventId Optional event filter
 * @return array
 */
function getCarCountsByStatus($eventId = null)
{
    $where = $eventId ? "WHERE event_id = ? AND is_active = 1" : "WHERE is_active = 1";
    $params = $eventId ? [$eventId] : [];
    
    $sql = "SELECT 
                status,
                COUNT(*) as count 
            FROM cars 
            $where
            GROUP BY status";
    
    $results = dbQuery($sql, $params);
    $counts = [
        'standby' => 0,
        'cleaning' => 0,
        'cleaned' => 0,
        'pod_lineup' => 0,
        'on_drive' => 0,
        'returned' => 0,
        'hotel' => 0
    ];
    
    foreach ($results as $row) {
        $counts[$row['status']] = (int) $row['count'];
    }
    
    return $counts;
}

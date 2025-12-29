<?php
/**
 * MDM Events Query Functions
 * Reusable database queries for events
 */

require_once __DIR__ . '/../../config/database.php';

/**
 * Get paginated events with optional status filter
 * @param string $status Filter by status (all, active, upcoming, completed)
 * @param int $page Current page number
 * @param int $perPage Items per page
 * @return array Events with car counts
 */
function getEventsPaginated($status = 'all', $page = 1, $perPage = 12)
{
    $offset = ($page - 1) * $perPage;
    
    $where = "WHERE (e.is_deleted = 0 OR e.is_deleted IS NULL)";
    $params = [];
    
    // Handle deleted filter separately
    if ($status === 'deleted') {
        $where = "WHERE e.is_deleted = 1";
    } elseif ($status !== 'all' && in_array($status, ['active', 'upcoming', 'completed'])) {
        $where .= " AND e.status = ?";
        $params[] = $status;
    }
    
    $sql = "SELECT 
                e.*,
                (SELECT COUNT(*) FROM cars WHERE event_id = e.id AND is_active = 1) as car_count,
                (SELECT COUNT(*) FROM event_promoters WHERE event_id = e.id) as promoter_count
            FROM events e
            $where
            ORDER BY e.start_date DESC
            LIMIT ? OFFSET ?";
    
    $params[] = $perPage;
    $params[] = $offset;
    
    return dbQuery($sql, $params);
}

/**
 * Get total events count with optional status filter
 * @param string $status Filter by status
 * @return int Total count
 */
function getTotalEventsCount($status = 'all')
{
    $where = "WHERE (is_deleted = 0 OR is_deleted IS NULL)";
    $params = [];
    
    // Handle deleted filter separately
    if ($status === 'deleted') {
        $where = "WHERE is_deleted = 1";
    } elseif ($status !== 'all' && in_array($status, ['active', 'upcoming', 'completed'])) {
        $where .= " AND status = ?";
        $params[] = $status;
    }
    
    $result = dbQueryOne("SELECT COUNT(*) as count FROM events $where", $params);
    return (int) ($result['count'] ?? 0);
}

/**
 * Get single event by ID with full details
 * @param int $id Event ID
 * @return array|null Event data with stats
 */
function getEventById($id)
{
    $event = dbQueryOne("SELECT * FROM events WHERE id = ? AND (is_deleted = 0 OR is_deleted IS NULL)", [$id]);
    
    if (!$event) {
        return null;
    }
    
    // Get car count
    $event['car_count'] = dbQueryOne(
        "SELECT COUNT(*) as count FROM cars WHERE event_id = ? AND is_active = 1",
        [$id]
    )['count'] ?? 0;
    
    // Get promoter count
    $event['promoter_count'] = dbQueryOne(
        "SELECT COUNT(*) as count FROM event_promoters WHERE event_id = ?",
        [$id]
    )['count'] ?? 0;
    
    // Get car status breakdown
    $event['car_stats'] = getEventCarStats($id);
    
    // Get drive count
    $event['drive_count'] = dbQueryOne(
        "SELECT COUNT(*) as count FROM car_logs cl 
         JOIN cars c ON c.id = cl.car_id 
         WHERE c.event_id = ? AND cl.log_type = 'exit'",
        [$id]
    )['count'] ?? 0;
    
    return $event;
}

/**
 * Get car status breakdown for an event
 * @param int $eventId Event ID
 * @return array Status counts
 */
function getEventCarStats($eventId)
{
    $sql = "SELECT status, COUNT(*) as count 
            FROM cars 
            WHERE event_id = ? AND is_active = 1 
            GROUP BY status";
    
    $results = dbQuery($sql, [$eventId]);
    
    $stats = [
        'standby' => 0,
        'cleaning' => 0,
        'cleaned' => 0,
        'pod_lineup' => 0,
        'on_drive' => 0,
        'returned' => 0,
        'hotel' => 0
    ];
    
    foreach ($results as $row) {
        $stats[$row['status']] = (int) $row['count'];
    }
    
    return $stats;
}

/**
 * Get recent cars for an event
 * @param int $eventId Event ID
 * @param int $limit Number of cars to fetch
 * @return array Cars list
 */
function getEventCars($eventId, $limit = 10)
{
    return dbQuery(
        "SELECT * FROM cars WHERE event_id = ? AND is_active = 1 ORDER BY name ASC LIMIT ?",
        [$eventId, $limit]
    );
}

/**
 * Get promoters assigned to an event
 * @param int $eventId Event ID
 * @return array Promoters list
 */
function getEventPromoters($eventId)
{
    return dbQuery(
        "SELECT u.id, u.name, u.email, u.phone, u.avatar 
         FROM users u 
         JOIN event_promoters ep ON ep.promoter_id = u.id 
         WHERE ep.event_id = ? AND u.is_active = 1",
        [$eventId]
    );
}

<?php
/**
 * MDM Car Logs Query Functions
 * Reusable database queries for car logs
 */

require_once __DIR__ . '/../../config/database.php';

/**
 * Get paginated car logs with optional event filter
 * @param int|null $eventId Filter by event ID
 * @param int $page Current page number
 * @param int $perPage Items per page
 * @return array Logs list with related data
 */
function getCarLogsPaginated($eventId = null, $page = 1, $perPage = 20)
{
    $offset = ($page - 1) * $perPage;
    $params = [];
    
    $where = "WHERE 1=1";
    
    if ($eventId && $eventId !== 'all') {
        $where .= " AND cl.event_id = ?";
        $params[] = $eventId;
    }
    
    $sql = "SELECT cl.*, 
                c.name as car_name, c.car_code,
                e.name as event_name,
                u.name as promoter_name
            FROM car_logs cl
            LEFT JOIN cars c ON cl.car_id = c.id
            LEFT JOIN events e ON cl.event_id = e.id
            LEFT JOIN users u ON cl.promoter_id = u.id
            $where
            ORDER BY cl.created_at DESC
            LIMIT ? OFFSET ?";
    
    $params[] = $perPage;
    $params[] = $offset;
    
    return dbQuery($sql, $params);
}

/**
 * Get total car logs count with optional event filter
 * @param int|null $eventId Filter by event ID
 * @return int Total count
 */
function getTotalCarLogsCount($eventId = null)
{
    $params = [];
    $where = "WHERE 1=1";
    
    if ($eventId && $eventId !== 'all') {
        $where .= " AND event_id = ?";
        $params[] = $eventId;
    }
    
    $sql = "SELECT COUNT(*) as count FROM car_logs $where";
    $result = dbQueryOne($sql, $params);
    
    return (int) ($result['count'] ?? 0);
}

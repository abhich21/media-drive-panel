<?php
/**
 * MDM Promoters Query Functions
 * Reusable database queries for promoters
 */

require_once __DIR__ . '/../../config/database.php';

/**
 * Get paginated promoters with optional event filter
 * @param int|null $eventId Filter by event ID
 * @param string $filter Filter type: 'all', 'deleted'
 * @param int $page Current page number
 * @param int $perPage Items per page
 * @return array Promoters list
 */
function getPromotersPaginated($eventId = null, $filter = 'all', $page = 1, $perPage = 12)
{
    $offset = ($page - 1) * $perPage;
    
    // Handle deleted filter
    $activeCondition = $filter === 'deleted' ? "u.is_active = 0" : "u.is_active = 1";
    
    $params = [];
    
    if ($eventId && $eventId !== 'all') {
        $sql = "SELECT DISTINCT u.id, u.name, u.email, u.phone, u.is_active,
                    (SELECT SUM(LENGTH(ep2.assigned_cars) - LENGTH(REPLACE(ep2.assigned_cars, ',', '')) + 1) 
                     FROM event_promoters ep2 
                     WHERE ep2.promoter_id = u.id AND ep2.is_deleted = 0 AND ep2.assigned_cars IS NOT NULL AND ep2.assigned_cars != '') as assigned_cars_count
                FROM users u 
                JOIN event_promoters ep ON ep.promoter_id = u.id 
                WHERE ep.event_id = ? AND u.role = 'promoter' AND $activeCondition AND ep.is_deleted = 0
                ORDER BY u.name ASC
                LIMIT ? OFFSET ?";
        $params = [$eventId, $perPage, $offset];
    } else {
        $sql = "SELECT u.id, u.name, u.email, u.phone, u.is_active,
                    (SELECT SUM(LENGTH(ep.assigned_cars) - LENGTH(REPLACE(ep.assigned_cars, ',', '')) + 1) 
                     FROM event_promoters ep 
                     WHERE ep.promoter_id = u.id AND ep.is_deleted = 0 AND ep.assigned_cars IS NOT NULL AND ep.assigned_cars != '') as assigned_cars_count
                FROM users u 
                WHERE u.role = 'promoter' AND $activeCondition
                ORDER BY u.name ASC
                LIMIT ? OFFSET ?";
        $params = [$perPage, $offset];
    }
    
    return dbQuery($sql, $params);
}

/**
 * Get total promoters count with optional filters
 * @param int|null $eventId Filter by event ID
 * @param string $filter Filter type: 'all', 'deleted'
 * @return int Total count
 */
function getTotalPromotersCount($eventId = null, $filter = 'all')
{
    $activeCondition = $filter === 'deleted' ? "u.is_active = 0" : "u.is_active = 1";
    
    if ($eventId && $eventId !== 'all') {
        $sql = "SELECT COUNT(DISTINCT u.id) as count 
                FROM users u 
                JOIN event_promoters ep ON ep.promoter_id = u.id 
                WHERE ep.event_id = ? AND u.role = 'promoter' AND $activeCondition AND ep.is_deleted = 0";
        $result = dbQueryOne($sql, [$eventId]);
    } else {
        $sql = "SELECT COUNT(*) as count FROM users u WHERE u.role = 'promoter' AND $activeCondition";
        $result = dbQueryOne($sql);
    }
    
    return (int) ($result['count'] ?? 0);
}

/**
 * Get single promoter by ID
 * @param int $id Promoter ID
 * @return array|null Promoter data
 */
function getPromoterById($id)
{
    return dbQueryOne(
        "SELECT id, name, email, phone, is_active FROM users WHERE id = ? AND role = 'promoter'",
        [$id]
    );
}

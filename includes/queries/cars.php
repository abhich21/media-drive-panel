<?php
/**
 * MDM Cars Query Functions
 * Reusable database queries for cars
 */

require_once __DIR__ . '/../../config/database.php';

/**
 * Get paginated cars with optional event and status filter
 * @param int|null $eventId Filter by event ID
 * @param string $status Filter by status (all = no filter, deleted = soft-deleted)
 * @param int $page Current page number
 * @param int $perPage Items per page
 * @return array Cars with event names
 */
function getCarsPaginated($eventId = null, $status = 'all', $page = 1, $perPage = 12)
{
    $offset = ($page - 1) * $perPage;
    
    // Handle deleted filter separately
    if ($status === 'deleted') {
        $where = "WHERE c.is_active = 0";
    } else {
        $where = "WHERE c.is_active = 1";
    }
    $params = [];
    
    if ($eventId && $eventId !== 'all') {
        $where .= " AND c.event_id = ?";
        $params[] = $eventId;
    }
    
    if ($status && $status !== 'all' && $status !== 'deleted') {
        $where .= " AND c.status = ?";
        $params[] = $status;
    }
    
    $sql = "SELECT 
                c.*,
                e.name as event_name
            FROM cars c
            LEFT JOIN events e ON e.id = c.event_id
            $where
            ORDER BY c.name ASC
            LIMIT ? OFFSET ?";
    
    $params[] = $perPage;
    $params[] = $offset;
    
    return dbQuery($sql, $params);
}

/**
 * Get total cars count with optional filters
 * @param int|null $eventId Filter by event ID
 * @param string $status Filter by status
 * @return int Total count
 */
function getTotalCarsCount($eventId = null, $status = 'all')
{
    // Handle deleted filter separately
    if ($status === 'deleted') {
        $where = "WHERE is_active = 0";
    } else {
        $where = "WHERE is_active = 1";
    }
    $params = [];
    
    if ($eventId && $eventId !== 'all') {
        $where .= " AND event_id = ?";
        $params[] = $eventId;
    }
    
    if ($status && $status !== 'all' && $status !== 'deleted') {
        $where .= " AND status = ?";
        $params[] = $status;
    }
    
    $result = dbQueryOne("SELECT COUNT(*) as count FROM cars $where", $params);
    return (int) ($result['count'] ?? 0);
}

/**
 * Get all active events for dropdown filter
 * @return array Events list
 */
function getAllActiveEvents()
{
    return dbQuery(
        "SELECT id, name FROM events WHERE (is_deleted = 0 OR is_deleted IS NULL) ORDER BY name ASC"
    );
}

/**
 * Get single car by ID
 * @param int $id Car ID
 * @return array|null Car data
 */
function getCarById($id)
{
    return dbQueryOne(
        "SELECT c.*, e.name as event_name 
         FROM cars c 
         LEFT JOIN events e ON e.id = c.event_id 
         WHERE c.id = ? AND c.is_active = 1",
        [$id]
    );
}

/**
 * Get all car statuses
 * @return array Status list
 */
function getCarStatuses()
{
    return [
        'standby' => 'Standby',
        'cleaning' => 'Cleaning',
        'cleaned' => 'Cleaned',
        'pod_lineup' => 'Pod Lineup',
        'on_drive' => 'On Drive',
        'returned' => 'Returned',
        'hotel' => 'Hotel',
        'out_of_service' => 'Out of Service',
        'under_inspection' => 'Under Inspection'
    ];
}

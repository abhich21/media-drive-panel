<?php
/**
 * MDM Helper Functions
 * Media Drive Management System
 */

/**
 * Sanitize output for HTML
 * @param string $str
 * @return string
 */
function h($str)
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Format number with units
 * @param float $num
 * @param string $unit
 * @param int $decimals
 * @return string
 */
function formatStat($num, $unit = '', $decimals = 1)
{
    $formatted = number_format($num, $decimals);
    return $unit ? "{$formatted} <span class='text-xl font-medium'>{$unit}</span>" : $formatted;
}

/**
 * Format date for display
 * @param string $date
 * @param string $format
 * @return string
 */
function formatDate($date, $format = 'd M Y')
{
    return date($format, strtotime($date));
}

/**
 * Format time for display
 * @param string $time
 * @param string $format
 * @return string
 */
function formatTime($time, $format = 'h:i A')
{
    return date($format, strtotime($time));
}

/**
 * Get status badge classes
 * @param string $status
 * @return array [bgClass, textClass, label]
 */
function getStatusBadge($status)
{
    $badges = [
        'standby' => ['bg-mdm-tag', 'text-mdm-text', 'Standby'],
        'cleaning' => ['bg-yellow-100', 'text-yellow-800', 'Cleaning'],
        'cleaned' => ['bg-green-100', 'text-green-800', 'Cleaned'],
        'on_drive' => ['bg-blue-100', 'text-blue-800', 'On Drive'],
        'returned' => ['bg-purple-100', 'text-purple-800', 'Returned'],
        'hotel' => ['bg-gray-100', 'text-gray-800', 'Back to Hotel'],
        'pod_lineup' => ['bg-orange-100', 'text-orange-800', 'Pod Line Up'],
    ];

    return $badges[$status] ?? ['bg-gray-100', 'text-gray-600', ucfirst($status)];
}

/**
 * Get car status icon SVG
 * @param string $status
 * @return string
 */
function getStatusIcon($status)
{
    $icons = [
        'cleaning' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>',
        'cleaned' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
        'pod_lineup' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>',
        'standby' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
        'hotel' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m0 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>',
    ];

    return $icons[$status] ?? '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
}

/**
 * Get JSON response
 * @param mixed $data
 * @param int $status
 */
function jsonResponse($data, $status = 200)
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Get error response
 * @param string $message
 * @param int $status
 */
function errorResponse($message, $status = 400)
{
    jsonResponse(['error' => true, 'message' => $message], $status);
}

/**
 * Success response
 * @param mixed $data
 * @param string $message
 */
function successResponse($data = null, $message = 'Success')
{
    jsonResponse(['success' => true, 'message' => $message, 'data' => $data]);
}

/**
 * Calculate percentage
 * @param int $part
 * @param int $total
 * @return float
 */
function percentage($part, $total)
{
    if ($total == 0)
        return 0;
    return round(($part / $total) * 100, 1);
}

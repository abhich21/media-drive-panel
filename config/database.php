<?php
/**
 * MDM Database Configuration
 * Media Drive Management System
 */

// Database credentials - Update these for your environment
define('DB_HOST', 'localhost');
define('DB_NAME', 'mdm');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get PDO Database Connection
 * @return PDO
 */
function getDB()
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log error and show friendly message
            error_log("Database Connection Error: " . $e->getMessage());
            die("Database connection failed. Please check your configuration.");
        }
    }

    return $pdo;
}

/**
 * Execute a query and return results
 * @param string $sql
 * @param array $params
 * @return array
 */
function dbQuery($sql, $params = [])
{
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Execute a query and return single row
 * @param string $sql
 * @param array $params
 * @return array|null
 */
function dbQueryOne($sql, $params = [])
{
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch() ?: null;
}

/**
 * Execute insert/update/delete and return affected rows
 * @param string $sql
 * @param array $params
 * @return int
 */
function dbExecute($sql, $params = [])
{
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

/**
 * Get last inserted ID
 * @return string
 */
function dbLastId()
{
    return getDB()->lastInsertId();
}

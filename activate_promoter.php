<?php
// Quick script to activate promoter@demo.com account
require_once __DIR__ . '/config/database.php';

try {
    $result = dbExecute(
        "UPDATE users SET is_active = 1 WHERE email = 'promoter@demo.com'",
        []
    );
    
    echo "✅ SUCCESS: promoter@demo.com account activated!\n";
    echo "You can now login with: promoter@demo.com / promoter123\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>

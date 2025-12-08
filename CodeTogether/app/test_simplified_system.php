<?php
// Simple test script for simplified daily submission system
require_once __DIR__ . '/dao/UserDAO.php';

echo "Testing Simplified Daily Submission System...\n\n";

// Test 1: Database Connection
try {
    $conn = Database::getConnection();
    echo "✅ Database connection successful\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Check if user table has new columns
try {
    $result = $conn->query("SHOW COLUMNS FROM user LIKE 'last_daily_submission_date'");
    if ($result->num_rows > 0) {
        echo "✅ user table has last_daily_submission_date column\n";
    } else {
        echo "❌ user table missing last_daily_submission_date column\n";
    }

    $result = $conn->query("SHOW COLUMNS FROM user LIKE 'last_daily_problem_title'");
    if ($result->num_rows > 0) {
        echo "✅ user table has last_daily_problem_title column\n";
    } else {
        echo "❌ user table missing last_daily_problem_title column\n";
    }
} catch (Exception $e) {
    echo "❌ Error checking user table columns: " . $e->getMessage() . "\n";
}

// Test 3: Test DAO instantiation
try {
    $userDAO = new UserDAO();
    echo "✅ UserDAO instantiated successfully\n";
} catch (Exception $e) {
    echo "❌ UserDAO instantiation failed: " . $e->getMessage() . "\n";
}

// Test 4: Test new methods exist
try {
    $methods = ['hasSubmittedToday', 'recordDailySubmission', 'getDailySubmissionStatus'];
    foreach ($methods as $method) {
        if (method_exists($userDAO, $method)) {
            echo "✅ UserDAO has method: $method\n";
        } else {
            echo "❌ UserDAO missing method: $method\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error testing methods: " . $e->getMessage() . "\n";
}

echo "\nSimplified system test completed.\n";
echo "Core functionality: Daily submission limits without streak tracking\n";
?>
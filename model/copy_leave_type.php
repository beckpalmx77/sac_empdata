<?php

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

// Ensure your 'connect_db.php' file is included.
require_once '../config/connect_db.php';

try {
    // Step 1: Find the latest effect_year
    $stmt = $conn->query("SELECT MAX(effect_year) AS latest_year FROM mleave_type");
    $latest_year_row = $stmt->fetch();
    $latest_year = $latest_year_row['latest_year'];

    if (!$latest_year) {
        echo json_encode(['status' => 'error', 'message' => 'No data found in mleave_type.']);
        exit;
    }

    $next_year = (int)$latest_year + 1;

    // Step 2: Check if data for the next year already exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM mleave_type WHERE effect_year = ?");
    $stmt->execute([$next_year]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo json_encode([
            'status' => 'info',
            'message' => 'Data for year ' . $next_year . ' already exists. No action taken.'
        ]);
        exit;
    }

    // Step 3: Begin the transaction
    $conn->beginTransaction();

    // Step 4: Update the effect_year of the latest year's records to the next year
    $update_stmt = $conn->prepare("UPDATE `mleave_type` SET `effect_year` = ? WHERE `effect_year` = ?");
    $update_stmt->execute([$next_year, $latest_year]);

    // Check how many rows were affected to ensure the update was successful
    $rows_updated = $update_stmt->rowCount();

    if ($rows_updated === 0) {
        $conn->rollBack();
        echo json_encode([
            'status' => 'error',
            'message' => 'No records found to update for year ' . $latest_year . '.'
        ]);
        exit;
    }

    // Step 5: Commit the transaction if everything succeeded
    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Successfully updated data from year ' . $latest_year . ' to ' . $next_year . '.',
        'next_year' => $next_year
    ]);

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
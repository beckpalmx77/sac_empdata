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

    // Step 3: Begin the transaction to ensure all operations succeed or fail together
    $conn->beginTransaction();

    // Select all records from the latest year to copy
    $stmt = $conn->prepare("SELECT `leave_type_id`, `leave_type_detail`, `day_max`, `day_max_ext`, `leave_before`
    , `work_age_allow`, `day_flag`, `remark`, `status`, `leave_strict`, `color`, `leave_for`, `retro_flag`, `line_alert` 
    FROM `mleave_type` WHERE `effect_year` = ?");
    $stmt->execute([$latest_year]);
    $records_to_copy = $stmt->fetchAll();

    if (empty($records_to_copy)) {
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'No records found to copy for year ' . $latest_year . '.']);
        exit;
    }

    // Prepare the insert statement
    $insert_sql = "INSERT INTO `mleave_type` (`leave_type_id`, `leave_type_detail`, `day_max`, `day_max_ext`, `leave_before`, `work_age_allow`, `day_flag`, `remark`, `status`, `leave_strict`, `color`, `leave_for`, `retro_flag`, `line_alert`, `effect_year`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);

    // Loop through records and insert new ones with the next year
    foreach ($records_to_copy as $record) {
        $insert_stmt->execute([
            $record['leave_type_id'],
            $record['leave_type_detail'],
            $record['day_max'],
            $record['day_max_ext'],
            $record['leave_before'],
            $record['work_age_allow'],
            $record['day_flag'],
            $record['remark'],
            $record['status'],
            $record['leave_strict'],
            $record['color'],
            $record['leave_for'],
            $record['retro_flag'],
            $record['line_alert'],
            $next_year // Use the new year for the new records
        ]);
    }

    // Step 4: Delete the old data
    $delete_stmt = $conn->prepare("DELETE FROM `mleave_type` WHERE `effect_year` = ?");
    $delete_stmt->execute([$latest_year]);

    // Step 5: Commit the transaction if everything succeeded
    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Successfully copied data from year ' . $latest_year . ' to ' . $next_year . ' and old data has been deleted.',
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
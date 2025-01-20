<?php
include('../config/connect_db.php');

// ดึงข้อมูลจาก view v_ims_time_attendance
$sql_main = "SELECT * FROM v_ims_time_attendance 
             ORDER BY work_date DESC, start_time DESC 
             LIMIT 300";

$statement = $conn->query($sql_main);
$results = $statement->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $result) {
    // เตรียม SQL เพื่อค้นหา record ซ้ำ
    $sql_find = "
        SELECT COUNT(*) 
        FROM ims_time_attendance_work_date 
        WHERE emp_id = :emp_id 
          AND work_date = :work_date 
          AND start_time = :start_time          
    ";

    // ใช้ prepared statement เพื่อป้องกัน SQL Injection
    $stmt_find = $conn->prepare($sql_find);
    $stmt_find->bindParam(':emp_id', $result['emp_id']);
    $stmt_find->bindParam(':work_date', $result['work_date']);
    $stmt_find->bindParam(':start_time', $result['start_time']);
    $stmt_find->execute();

    // ตรวจสอบว่ามีข้อมูลซ้ำหรือไม่
    $nRows = $stmt_find->fetchColumn();
    if ($nRows > 0) {
        // หากพบข้อมูลซ้ำ ให้ทำการ UPDATE
        $sql_update = "
            UPDATE ims_time_attendance_work_date
            SET device = :device,
                end_time = :end_time
            WHERE emp_id = :emp_id 
              AND work_date = :work_date 
              AND start_time = :start_time
        ";

        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bindParam(':device', $result['device']);
        $stmt_update->bindParam(':end_time', $result['end_time']);
        $stmt_update->bindParam(':emp_id', $result['emp_id']);
        $stmt_update->bindParam(':work_date', $result['work_date']);
        $stmt_update->bindParam(':start_time', $result['start_time']);

        if ($stmt_update->execute()) {
            echo "Update = emp_id: " . $result['emp_id'] . ", work_date: " . $result['work_date'] . "\n\r";
        } else {
            echo "Failed to update record for emp_id: " . $result['emp_id'] . ", work_date: " . $result['work_date'] . "\n\r";
        }
    } else {
        // หากไม่พบข้อมูลซ้ำ ให้ทำการ INSERT
        $sql_insert = "
            INSERT INTO ims_time_attendance_work_date 
            (emp_id, f_name, l_name, work_date, start_time, end_time, department_id, dept_id_approve, device) 
            VALUES (:emp_id, :f_name, :l_name, :work_date, :start_time, :end_time, :department_id, :dept_id_approve, :device)
        ";

        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bindParam(':emp_id', $result['emp_id']);
        $stmt_insert->bindParam(':f_name', $result['f_name']);
        $stmt_insert->bindParam(':l_name', $result['l_name']);
        $stmt_insert->bindParam(':work_date', $result['work_date']);
        $stmt_insert->bindParam(':start_time', $result['start_time']);
        $stmt_insert->bindParam(':end_time', $result['end_time']);
        $stmt_insert->bindParam(':department_id', $result['department_id']);
        $stmt_insert->bindParam(':dept_id_approve', $result['dept_id_approve']);
        $stmt_insert->bindParam(':device', $result['device']);

        if ($stmt_insert->execute()) {
            echo "Insert = emp_id: " . $result['emp_id'] . ", work_date: " . $result['work_date'] . "\n\r";
        } else {
            echo "Failed to insert record for emp_id: " . $result['emp_id'] . ", work_date: " . $result['work_date'] . "\n\r";
        }
    }
}
<?php
// ข้อมูลการเชื่อมต่อฐานข้อมูล db1 และ db2
$db1_host = '192.168.88.7';
$db1_port = '3307'; // พอร์ตที่ใช้เชื่อมต่อฐานข้อมูล db1
$db1_user = 'myadmin';
$db1_pass = 'myadmin';
$db1_name = 'sac_emp';

$db2_host = 'localhost';
$db2_port = '3307'; // พอร์ตที่ใช้เชื่อมต่อฐานข้อมูล db2
$db2_user = 'myadmin';
$db2_pass = 'myadmin';
$db2_name = 'sac_emp_dbs';

try {
    // เชื่อมต่อฐานข้อมูล db1
    $dsn_db1 = "mysql:host=$db1_host;port=$db1_port;dbname=$db1_name;charset=utf8mb4";
    $pdo_db1 = new PDO($dsn_db1, $db1_user, $db1_pass);
    $pdo_db1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // เชื่อมต่อฐานข้อมูล db2
    $dsn_db2 = "mysql:host=$db2_host;port=$db2_port;dbname=$db2_name;charset=utf8mb4";
    $pdo_db2 = new PDO($dsn_db2, $db2_user, $db2_pass);
    $pdo_db2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // สืบค้นข้อมูลทั้งหมดจาก db1
    $sql_select_db1 = "SELECT * FROM dholiday_event";
    $stmt_select_db1 = $pdo_db1->prepare($sql_select_db1);
    $stmt_select_db1->execute();

    // วนลูปผ่านข้อมูลจาก db1
    while ($data_db1 = $stmt_select_db1->fetch(PDO::FETCH_ASSOC)) {
        $doc_id = $data_db1['doc_id'];

        // เช็คว่า doc_id มีใน db2 หรือไม่
        $sql_check = "SELECT COUNT(*) FROM dholiday_event WHERE doc_id = :doc_id";
        $stmt_check = $pdo_db2->prepare($sql_check);
        $stmt_check->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);
        $stmt_check->execute();
        $row_count = $stmt_check->fetchColumn();

        if ($row_count > 0) {
            // ถ้า doc_id มีใน db2 อยู่แล้ว
            echo "Document ID $doc_id already exists in db2.\n";
        } else {
            // ถ้าไม่มี doc_id ใน db2 ให้ทำการ INSERT ข้อมูลจาก db1 ลง db2
            $sql_insert = "INSERT INTO dholiday_event 
                (doc_year, doc_id, doc_date, leave_type_id, emp_id, dept_id, 
                 date_leave_start, date_leave_to, time_leave_start, time_leave_to, 
                 approve_1_id, approve_1_status, approve_2_id, approve_2_status, 
                 remark, status, leave_day, leave_hour, picture) 
                VALUES 
                (:doc_year, :doc_id, :doc_date, :leave_type_id, :emp_id, :dept_id, 
                 :date_leave_start, :date_leave_to, :time_leave_start, :time_leave_to, 
                 :approve_1_id, :approve_1_status, :approve_2_id, :approve_2_status, 
                 :remark, :status, :leave_day, :leave_hour, :picture)";

            $stmt_insert = $pdo_db2->prepare($sql_insert);
            $stmt_insert->bindParam(':doc_year', $data_db1['doc_year'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':doc_id', $data_db1['doc_id'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':doc_date', $data_db1['doc_date'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':leave_type_id', $data_db1['leave_type_id'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':emp_id', $data_db1['emp_id'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':dept_id', $data_db1['dept_id'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':date_leave_start', $data_db1['date_leave_start'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':date_leave_to', $data_db1['date_leave_to'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':time_leave_start', $data_db1['time_leave_start'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':time_leave_to', $data_db1['time_leave_to'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':approve_1_id', $data_db1['approve_1_id'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':approve_1_status', $data_db1['approve_1_status'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':approve_2_id', $data_db1['approve_2_id'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':approve_2_status', $data_db1['approve_2_status'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':remark', $data_db1['remark'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':status', $data_db1['status'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':leave_day', $data_db1['leave_day'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':leave_hour', $data_db1['leave_hour'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':picture', $data_db1['picture'], PDO::PARAM_STR);

            if ($stmt_insert->execute()) {
                echo "Document ID $doc_id inserted successfully into db2.\n";
            } else {
                // ถ้ามี doc_id อยู่แล้ว ให้ทำการ update field status
                $sql4 = "UPDATE dholiday_event SET status = :status WHERE doc_id = :doc_id";
                $stmt4 = $pdo2->prepare($sql4);
                $stmt4->execute([
                    'status' => $data_db1['status'],
                    'doc_id' => $doc_id
                ]);
                echo "Updated status for doc_id: " . $doc_id . "\n\r";
            }
        }
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
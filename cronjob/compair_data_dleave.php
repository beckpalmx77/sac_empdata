<?php
// ข้อมูลการเชื่อมต่อฐานข้อมูล db1 และ db2
$db1_host = '192.168.88.7';
$db1_port = '3307'; // กำหนดพอร์ตเป็น 3307
$db1_user = 'myadmin';
$db1_pass = 'myadmin';
$db1_name = 'sac_emp';

$db2_host = 'localhost';
$db2_port = '3307'; // กำหนดพอร์ตเป็น 3307
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
    $sql_select_db1 = "SELECT * FROM dleave_event";
    $stmt_select_db1 = $pdo_db1->prepare($sql_select_db1);
    $stmt_select_db1->execute();

    // วนลูปผ่านข้อมูลจาก db1
    while ($data_db1 = $stmt_select_db1->fetch(PDO::FETCH_ASSOC)) {
        $doc_id = $data_db1['doc_id'];

        // เช็คว่าเลขที่เอกสารมีใน db2 หรือไม่
        $sql_check = "SELECT COUNT(*) FROM dleave_event WHERE doc_id = :doc_id";
        $stmt_check = $pdo_db2->prepare($sql_check);
        $stmt_check->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);
        $stmt_check->execute();
        $row_count = $stmt_check->fetchColumn();

        if ($row_count > 0) {
            // ถ้ามีข้อมูลใน db2 แล้ว
            echo "Document ID $doc_id already exists in db2.\n";
        } else {
            // ถ้าไม่มีข้อมูลใน db2 ให้ insert ข้อมูลจาก db1 ไปยัง db2
            $sql_insert = "INSERT INTO dleave_event 
                (doc_year, doc_id, doc_date, doc_month, emp_id, leave_type_id, dept_id, 
                 date_leave_start, date_leave_to, time_leave_start, time_leave_to, leave_day, 
                 leave_hour, leave_time_type, approve_1_id, approve_1_status, approve_2_id, 
                 approve_2_status, remark, status, total_time, picture, create_date, update_date) 
                VALUES 
                (:doc_year, :doc_id, :doc_date, :doc_month, :emp_id, :leave_type_id, :dept_id, 
                 :date_leave_start, :date_leave_to, :time_leave_start, :time_leave_to, :leave_day, 
                 :leave_hour, :leave_time_type, :approve_1_id, :approve_1_status, :approve_2_id, 
                 :approve_2_status, :remark, :status, :total_time, :picture, :create_date, :update_date)";

            $stmt_insert = $pdo_db2->prepare($sql_insert);
            $stmt_insert->bindParam(':doc_year', $data_db1['doc_year'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':doc_id', $data_db1['doc_id'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':doc_date', $data_db1['doc_date'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':doc_month', $data_db1['doc_month'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':emp_id', $data_db1['emp_id'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':leave_type_id', $data_db1['leave_type_id'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':dept_id', $data_db1['dept_id'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':date_leave_start', $data_db1['date_leave_start'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':date_leave_to', $data_db1['date_leave_to'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':time_leave_start', $data_db1['time_leave_start'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':time_leave_to', $data_db1['time_leave_to'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':leave_day', $data_db1['leave_day'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':leave_hour', $data_db1['leave_hour'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':leave_time_type', $data_db1['leave_time_type'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':approve_1_id', $data_db1['approve_1_id'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':approve_1_status', $data_db1['approve_1_status'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':approve_2_id', $data_db1['approve_2_id'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':approve_2_status', $data_db1['approve_2_status'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':remark', $data_db1['remark'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':status', $data_db1['status'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':total_time', $data_db1['total_time'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':picture', $data_db1['picture'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':create_date', $data_db1['create_date'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':update_date', $data_db1['update_date'], PDO::PARAM_STR);

            if ($stmt_insert->execute()) {
                echo "Document ID $doc_id inserted successfully into db2.\n";
            } else {
                echo "Error inserting Document ID $doc_id into db2.\n";
            }
        }
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

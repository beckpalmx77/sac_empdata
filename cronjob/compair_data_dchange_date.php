<?php
// กำหนดค่าการเชื่อมต่อฐานข้อมูลต้นทาง (db1)
$dsn1 = 'mysql:host=192.168.88.7;port=3307;dbname=sac_emp';
$username1 = 'myadmin';  // ใส่ชื่อผู้ใช้ฐานข้อมูลต้นทาง
$password1 = 'myadmin';      // ใส่รหัสผ่านฐานข้อมูลต้นทาง

// กำหนดค่าการเชื่อมต่อฐานข้อมูลปลายทาง (db2)
$dsn2 = 'mysql:host=localhost;port=3307;dbname=sac_emp_dbs';
$username2 = 'myadmin';  // ใส่ชื่อผู้ใช้ฐานข้อมูลปลายทาง
$password2 = 'myadmin';      // ใส่รหัสผ่านฐานข้อมูลปลายทาง

try {
    // เชื่อมต่อกับฐานข้อมูลต้นทาง (db1)
    $pdo1 = new PDO($dsn1, $username1, $password1);
    $pdo1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // เชื่อมต่อกับฐานข้อมูลปลายทาง (db2)
    $pdo2 = new PDO($dsn2, $username2, $password2);
    $pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ดึงข้อมูลจากฐานข้อมูลต้นทาง (dchange_event)
    $sql1 = "SELECT * FROM dchange_event";
    $stmt1 = $pdo1->query($sql1);

    // วนลูปเพื่อตรวจสอบข้อมูล
    while ($row1 = $stmt1->fetch(PDO::FETCH_ASSOC)) {
        // ตรวจสอบว่า doc_id มีอยู่ในฐานข้อมูลปลายทางหรือไม่
        $doc_id = $row1['doc_id'];
        $sql2 = "SELECT COUNT(*) FROM dchange_event WHERE doc_id = :doc_id";
        $stmt2 = $pdo2->prepare($sql2);
        $stmt2->execute(['doc_id' => $doc_id]);
        $count = $stmt2->fetchColumn();

        // ถ้าไม่มี doc_id ในฐานข้อมูลปลายทาง ให้ทำการ insert ข้อมูลลงไป
        if ($count == 0) {
            $sql3 = "INSERT INTO dchange_event (doc_year, doc_id, doc_date, doc_month, emp_id, leave_type_id, dept_id, 
                    date_leave_start, date_leave_to, time_leave_start, time_leave_to, approve_1_id, approve_1_status, 
                    approve_2_id, approve_2_status, remark, status, total_time)
                    VALUES (:doc_year, :doc_id, :doc_date, :doc_month, :emp_id, :leave_type_id, :dept_id, 
                    :date_leave_start, :date_leave_to, :time_leave_start, :time_leave_to, :approve_1_id, :approve_1_status, 
                    :approve_2_id, :approve_2_status, :remark, :status, :total_time)";

            $stmt3 = $pdo2->prepare($sql3);
            $stmt3->execute([
                'doc_year' => $row1['doc_year'],
                'doc_id' => $row1['doc_id'],
                'doc_date' => $row1['doc_date'],
                'doc_month' => $row1['doc_month'],
                'emp_id' => $row1['emp_id'],
                'leave_type_id' => $row1['leave_type_id'],
                'dept_id' => $row1['dept_id'],
                'date_leave_start' => $row1['date_leave_start'],
                'date_leave_to' => $row1['date_leave_to'],
                'time_leave_start' => $row1['time_leave_start'],
                'time_leave_to' => $row1['time_leave_to'],
                'approve_1_id' => $row1['approve_1_id'],
                'approve_1_status' => $row1['approve_1_status'],
                'approve_2_id' => $row1['approve_2_id'],
                'approve_2_status' => $row1['approve_2_status'],
                'remark' => $row1['remark'],
                'status' => $row1['status'],
                'total_time' => $row1['total_time']
            ]);
            echo "Inserted doc_id: " . $row1['doc_id'] . "\n\r";
        } else {
            // ถ้ามี doc_id อยู่แล้ว ให้ทำการ update field status
            $sql4 = "UPDATE dchange_event SET status = :status WHERE doc_id = :doc_id";
            $stmt4 = $pdo2->prepare($sql4);
            $stmt4->execute([
                'status' => $row1['status'],
                'doc_id' => $doc_id
            ]);
            echo "Updated status for doc_id: " . $doc_id . "\n\r";
        }
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

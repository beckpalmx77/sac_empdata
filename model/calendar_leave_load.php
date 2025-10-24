<?php
include('../config/connect_db.php');

$data = array();

$query = "
    SELECT 
        doc_date, 
        SUM(cnt_record) AS total_cnt_record
    FROM 
        (
            SELECT doc_date, COUNT(id) AS cnt_record FROM v_dleave_event GROUP BY doc_date
            UNION ALL
            SELECT doc_date, COUNT(id) AS cnt_record FROM vdholiday_event GROUP BY doc_date
        ) AS CombinedCounts
    GROUP BY 
        doc_date
    ORDER BY 
        doc_date
";

$statement = $conn->prepare($query);
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);

foreach ($result as $row) {
    // ✅ แปลงวันที่ให้เป็น Y-m-d (FullCalendar รับรูปแบบนี้เท่านั้น)
    $date = date("Y-m-d", strtotime($row["doc_date"]));

    $data[] = array(
        'id'    => $date,
        'title' => "จำนวนเอกสาร " . $row["total_cnt_record"] . " รายการ",
        'start' => $date,
        'end'   => $date,
        'count' => $row["total_cnt_record"]
    );
}

// ✅ แสดง JSON ให้ FullCalendar อ่าน + ให้ภาษาไทยแสดงไม่เพี้ยน
header('Content-Type: application/json; charset=utf-8');
echo json_encode($data, JSON_UNESCAPED_UNICODE);
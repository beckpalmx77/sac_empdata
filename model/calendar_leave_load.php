<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
$clickIcon = "👆";
$data = array();

// ✅ เงื่อนไขค้นหาตามสิทธิ์
if ($_SESSION['role'] === "SUPERVISOR") {
    $searchQuery = " AND dept_id_approve = '" . $_SESSION['dept_id_approve'] . "' ";
} else if ($_SESSION['role'] === "HR" || $_SESSION['role'] === "ADMIN") {
    $searchQuery = " ";
} else {
    $searchQuery = " AND emp_id = '" . $_SESSION['emp_id'] . "' ";
}

// ✅ Query นับจำนวนต่อวัน + เพิ่มเงื่อนไขค้นหา
$query = "
    SELECT 
        doc_date, 
        SUM(cnt_record) AS total_cnt_record
    FROM 
        (
            SELECT doc_date, COUNT(id) AS cnt_record 
            FROM v_dleave_event 
            WHERE 1=1 $searchQuery        -- ✅ เพิ่มตรงนี้
            GROUP BY doc_date

            UNION ALL

            SELECT doc_date, COUNT(id) AS cnt_record 
            FROM vdholiday_event 
            WHERE 1=1 $searchQuery        -- ✅ เพิ่มตรงนี้ (เฉพาะกรณีมีฟิลด์ dep_id หรือ emp_id)
            GROUP BY doc_date
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
    $date = date("Y-m-d", strtotime($row["doc_date"]));
    $data[] = array(
        'id' => $date,
        'title' => "จำนวนเอกสาร " . $row["total_cnt_record"] . " รายการ " . $clickIcon . " Click",
        'start' => $date,
        'end' => $date,
        'count' => $row["total_cnt_record"]
    );
}

// ✅ ส่งค่า JSON ออกไป
header('Content-Type: application/json; charset=utf-8');
echo json_encode($data, JSON_UNESCAPED_UNICODE);
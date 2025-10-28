<?php
include '../../config/connect_db.php';

// กำหนด Header ให้ Browser รู้ว่ากำลังส่งข้อมูล JSON
header('Content-Type: application/json');

// 📜 SQL Query ส่วนนี้ยังเหมือนเดิม
$sql = "SELECT doc_date as id, leave_type_id AS title, 
               DATE_FORMAT(create_date, '%Y-%m-%dT%H:%i:%s') AS start,
               DATE_FORMAT(create_date, '%Y-%m-%dT%H:%i:%s') AS end 
        FROM v_leave_holiday_calendar";

$stmt = $conn->query($sql);
$events = $stmt->fetchAll();

// 📤 ส่งข้อมูล JSON
echo json_encode($events);
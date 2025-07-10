<?php
include('../config/connect_db.php');
date_default_timezone_set('Asia/Bangkok');

// Set CSV filename
$filename = "leave_and_holiday_data-" . date('Ymd-His') . ".csv";

// Set Headers for CSV download - IMPORTANT: Use UTF-8 for better compatibility
@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

// Open the output stream for writing CSV data
$output = fopen('php://output', 'w');

// Add UTF-8 BOM (Byte Order Mark) for Excel compatibility with UTF-8
// This helps Excel recognize the file as UTF-8 when opened
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Get values from the form
$doc_date_start = $_POST["doc_date_start"];
$doc_date_to = $_POST["doc_date_to"];
$employeeSelect = $_POST["employeeSelect"];

// SQL search query construction
$search_Query = " WHERE STR_TO_DATE(date_leave_start, '%d-%m-%Y') BETWEEN STR_TO_DATE('$doc_date_start', '%d-%m-%Y') AND STR_TO_DATE('$doc_date_to', '%d-%m-%Y')
OR STR_TO_DATE(date_leave_to, '%d-%m-%Y') BETWEEN STR_TO_DATE('$doc_date_start', '%d-%m-%Y') AND STR_TO_DATE('$doc_date_to', '%d-%m-%Y')
OR STR_TO_DATE('$doc_date_start', '%d-%m-%Y') BETWEEN STR_TO_DATE(date_leave_start, '%d-%m-%Y') AND STR_TO_DATE(date_leave_to, '%d-%m-%Y') ";

if (!empty($employeeSelect) && $employeeSelect !== '-') {
    $search_Query .= " AND emp_id = '$employeeSelect'";
}

// SQL query for leave data
$select_leave_query = "SELECT * FROM v_dleave_event" . $search_Query . " ORDER BY STR_TO_DATE(doc_date, '%d-%m-%Y') ASC";

// SQL query for holiday usage data
$select_holiday_query = "SELECT * FROM vdholiday_event" . $search_Query . " ORDER BY STR_TO_DATE(doc_date, '%d-%m-%Y') ASC";

// --- Write Leave Data ---
// Write section header
fputcsv($output, ["ข้อมูลการลา พนักงาน"]);
// Write column headers
fputcsv($output, ["#", "วันที่เอกสาร", "รหัสพนักงาน", "ชื่อพนักงาน", "หน่วยงาน", "ประเภทการลา", "วันที่ลาเริ่มต้น", "วันที่ลาสิ้นสุด", "จำนวนวัน", "จำนวนชั่วโมง", "สถานะ", "หมายเหตุ"]);

// Fetch and write leave data
$query_leave = $conn->prepare($select_leave_query);
$query_leave->execute();
$results_leave = $query_leave->fetchAll(PDO::FETCH_OBJ);

$i = 1;
if ($query_leave->rowCount() >= 1) {
    foreach ($results_leave as $result) {
        $status_desc = "";
        switch ($result->status) {
            case 'A':
                $status_desc = "อนุมัติ";
                break;
            case 'R':
                $status_desc = "ไม่อนุมัติ";
                break;
            case 'N':
                $status_desc = "รอพิจารณา";
                break; // Added break for 'N'
            default:
                $status_desc = "ไม่ทราบสถานะ"; // Default case for unexpected status
        }

        fputcsv($output, [
            $i++,
            $result->doc_date,
            $result->emp_id,
            $result->f_name . " " . $result->l_name,
            $result->department_id,
            $result->leave_type_detail,
            $result->date_leave_start,
            $result->date_leave_to,
            $result->leave_day,
            $result->leave_hour,
            $status_desc,
            $result->remark
        ]);
    }
}

// Add an empty row for separation
fputcsv($output, []);

// --- Write Holiday Usage Data ---
// Write section header
fputcsv($output, ["แสดงข้อมูลใช้วันหยุด พนักงาน"]);
// Write column headers
fputcsv($output, ["#", "วันที่เอกสาร", "รหัสพนักงาน", "ชื่อพนักงาน", "หน่วยงาน", "ประเภทการลา", "วันที่ลาเริ่มต้น", "วันที่ลาสิ้นสุด", "จำนวนวัน", "จำนวนชั่วโมง", "สถานะ", "หมายเหตุ"]);

// Fetch and write holiday usage data
$query_holiday = $conn->prepare($select_holiday_query);
$query_holiday->execute();
$results_holiday = $query_holiday->fetchAll(PDO::FETCH_OBJ); // Renamed variable for clarity

$i = 1;
if ($query_holiday->rowCount() >= 1) {
    foreach ($results_holiday as $result) {
        $status_desc = "";
        switch ($result->status) {
            case 'A':
                $status_desc = "อนุมัติ";
                break;
            case 'R':
                $status_desc = "ไม่อนุมัติ";
                break;
            case 'N':
                $status_desc = "รอพิจารณา";
                break; // Added break for 'N'
            default:
                $status_desc = "ไม่ทราบสถานะ"; // Default case for unexpected status
        }

        fputcsv($output, [
            $i++,
            $result->doc_date,
            $result->emp_id,
            $result->f_name . " " . $result->l_name,
            $result->department_id,
            $result->leave_type_detail,
            $result->date_leave_start,
            $result->date_leave_to,
            $result->leave_day,
            $result->leave_hour,
            $status_desc,
            $result->remark
        ]);
    }
}

// Close the output stream
fclose($output);
exit();
?>
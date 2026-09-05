<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(0);

include(__DIR__ . '/../config/connect_db.php');
include(__DIR__ . '/../config/lang.php');

date_default_timezone_set('Asia/Bangkok');

// Filename
$filename = "attendance_and_leave_report-" . date('Ymd-His') . ".csv";

// CSV headers
@header('Content-Type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

$output = fopen('php://output', 'w');

// UTF-8 BOM for Excel
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Read parameters
$raw_start_date = $_POST['doc_date_start'] ?? ($_POST['start_date'] ?? '');
$raw_end_date = $_POST['doc_date_to'] ?? ($_POST['end_date'] ?? '');

if (!empty($raw_start_date)) {
    if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $raw_start_date)) {
        $p = explode('-', $raw_start_date);
        $start_date = $p[2] . '-' . $p[1] . '-' . $p[0];
    } else {
        $start_date = $raw_start_date;
    }
} else {
    $start_date = date('Y-m-01');
}

$today = date('Y-m-d');
if (!empty($raw_end_date)) {
    if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $raw_end_date)) {
        $p = explode('-', $raw_end_date);
        $end_date = $p[2] . '-' . $p[1] . '-' . $p[0];
    } else {
        $end_date = $raw_end_date;
    }
} else {
    $end_date = $today;
}

// จำกัดให้แสดงถึงวันที่ปัจจุบันและย้อนหลังเท่านั้น (ไม่แสดงข้อมูลวันที่ในอนาคต)
if ($end_date > $today) {
    $end_date = $today;
}
if ($start_date > $today) {
    $start_date = $today;
}

$dept_filter = trim($_POST['department_id'] ?? '');
$emp_filter = trim($_POST['employeeSelect'] ?? ($_POST['emp_id'] ?? ''));
$filter_status = trim($_POST['filter_status'] ?? 'ALL');
$searchValue = trim($_POST['search_keyword'] ?? '');

$whereClauses = array("1=1", "c.work_date <= CURDATE()");
$queryParams = array(
    'start_date' => $start_date,
    'end_date' => $end_date
);

// Role-based security
$is_supervisor = (isset($_SESSION['role']) && strtoupper($_SESSION['role']) === 'SUPERVISOR') ||
                 (isset($_SESSION['account_type']) && strtolower($_SESSION['account_type']) === 'supervisor');
if ($is_supervisor) {
    $whereClauses[] = "(e.dept_id_approve = :session_dept_approve OR c.emp_id = :session_emp_id)";
    $queryParams['session_dept_approve'] = $_SESSION['dept_id_approve'];
    $queryParams['session_emp_id'] = $_SESSION['emp_id'];
} else if ($_SESSION['role'] !== "HR" && $_SESSION['role'] !== "ADMIN") {
    $whereClauses[] = "c.emp_id = :session_emp_id";
    $queryParams['session_emp_id'] = $_SESSION['emp_id'];
}

// Dropdown Filters
if (!empty($dept_filter) && $dept_filter !== '-') {
    $whereClauses[] = "(e.department_id = :filter_dept OR e.dept_id_approve = :filter_dept OR e.dept_id = :filter_dept)";
    $queryParams['filter_dept'] = $dept_filter;
}

if (!empty($emp_filter) && $emp_filter !== '-') {
    $whereClauses[] = "c.emp_id = :filter_emp_id";
    $queryParams['filter_emp_id'] = $emp_filter;
}

// Status Filters
if ($filter_status === 'PRESENT') {
    $whereClauses[] = "(c.att_start_time IS NOT NULL OR c.att_end_time IS NOT NULL) AND (c.leave_type_detail IS NULL OR c.leave_type_detail = '')";
} else if ($filter_status === 'LEAVE') {
    $whereClauses[] = "(c.leave_type_detail IS NOT NULL AND c.leave_type_detail != '')";
} else if ($filter_status === 'NO_ATT_WITH_LEAVE') {
    $whereClauses[] = "(c.att_start_time IS NULL AND c.att_end_time IS NULL) AND (c.leave_type_detail IS NOT NULL AND c.leave_type_detail != '')";
} else if ($filter_status === 'NO_ATT_NO_LEAVE') {
    $whereClauses[] = "(c.att_start_time IS NULL AND c.att_end_time IS NULL) AND (c.leave_type_detail IS NULL OR c.leave_type_detail = '')";
} else if ($filter_status === 'BOTH') {
    $whereClauses[] = "(c.att_start_time IS NOT NULL OR c.att_end_time IS NOT NULL) AND (c.leave_type_detail IS NOT NULL AND c.leave_type_detail != '')";
}

if (!empty($searchValue)) {
    $whereClauses[] = "(c.emp_id LIKE :search 
                       OR e.f_name LIKE :search 
                       OR e.l_name LIKE :search 
                       OR e.department_id LIKE :search 
                       OR c.leave_type_detail LIKE :search 
                       OR c.doc_id LIKE :search 
                       OR c.remark LIKE :search 
                       OR DATE_FORMAT(c.work_date, '%d-%m-%Y') LIKE :search)";
    $queryParams['search'] = "%$searchValue%";
}

session_write_close();

$whereSql = implode(" AND ", $whereClauses);

$cte_sql = "
WITH RECURSIVE numbers AS (
    SELECT 0 AS n
    UNION ALL
    SELECT n + 1 FROM numbers WHERE n < 30
),
all_leaves AS (
    SELECT 
        l.emp_id,
        DATE_ADD(
            LEAST(
                STR_TO_DATE(l.date_leave_start, '%d-%m-%Y'), 
                STR_TO_DATE(COALESCE(NULLIF(l.date_leave_to, ''), l.date_leave_start), '%d-%m-%Y')
            ), 
            INTERVAL n.n DAY
        ) AS event_date,
        l.leave_type_detail,
        l.leave_type_id,
        l.leave_day,
        l.leave_hour,
        l.remark,
        l.status,
        l.color,
        'LEAVE' AS event_source,
        l.doc_id,
        COALESCE(NULLIF(l.time_leave_start, ''), '08:30') AS time_leave_start,
        COALESCE(NULLIF(l.time_leave_to, ''), '17:30') AS time_leave_to,
        l.date_leave_start,
        l.date_leave_to
    FROM v_dleave_event l
    JOIN numbers n ON n.n <= DATEDIFF(
        GREATEST(
            STR_TO_DATE(l.date_leave_start, '%d-%m-%Y'), 
            STR_TO_DATE(COALESCE(NULLIF(l.date_leave_to, ''), l.date_leave_start), '%d-%m-%Y')
        ),
        LEAST(
            STR_TO_DATE(l.date_leave_start, '%d-%m-%Y'), 
            STR_TO_DATE(COALESCE(NULLIF(l.date_leave_to, ''), l.date_leave_start), '%d-%m-%Y')
        )
    )
    WHERE l.status != 'R' 
      AND l.date_leave_start IS NOT NULL AND l.date_leave_start != ''

    UNION ALL

    SELECT 
        h.emp_id,
        DATE_ADD(
            LEAST(
                STR_TO_DATE(h.date_leave_start, '%d-%m-%Y'), 
                STR_TO_DATE(COALESCE(NULLIF(h.date_leave_to, ''), h.date_leave_start), '%d-%m-%Y')
            ), 
            INTERVAL n.n DAY
        ) AS event_date,
        h.leave_type_detail,
        h.leave_type_id,
        h.leave_day,
        h.leave_hour,
        h.remark,
        h.status,
        h.color,
        'HOLIDAY' AS event_source,
        h.doc_id,
        COALESCE(NULLIF(h.time_leave_start, ''), '08:30') AS time_leave_start,
        COALESCE(NULLIF(h.time_leave_to, ''), '17:30') AS time_leave_to,
        h.date_leave_start,
        h.date_leave_to
    FROM vdholiday_event h
    JOIN numbers n ON n.n <= DATEDIFF(
        GREATEST(
            STR_TO_DATE(h.date_leave_start, '%d-%m-%Y'), 
            STR_TO_DATE(COALESCE(NULLIF(h.date_leave_to, ''), h.date_leave_start), '%d-%m-%Y')
        ),
        LEAST(
            STR_TO_DATE(h.date_leave_start, '%d-%m-%Y'), 
            STR_TO_DATE(COALESCE(NULLIF(h.date_leave_to, ''), h.date_leave_start), '%d-%m-%Y')
        )
    )
    WHERE h.status != 'R' 
      AND h.date_leave_start IS NOT NULL AND h.date_leave_start != ''

    UNION ALL

    -- v_dchange_event: date_leave_to (วันที่ต้องการหยุด)
    SELECT 
        c.emp_id,
        STR_TO_DATE(c.date_leave_to, '%d-%m-%Y') AS event_date,
        c.leave_type_detail,
        c.leave_type_id,
        1 AS leave_day,
        0 AS leave_hour,
        c.remark,
        c.status,
        '#f39c12' AS color,
        'CHANGE' AS event_source,
        c.doc_id,
        COALESCE(NULLIF(c.time_leave_start, ''), '08:30') AS time_leave_start,
        COALESCE(NULLIF(c.time_leave_to, ''), '17:30') AS time_leave_to,
        c.date_leave_start,
        c.date_leave_to
    FROM v_dchange_event c
    WHERE c.status != 'R' 
      AND c.date_leave_to IS NOT NULL AND c.date_leave_to != ''

    UNION ALL

    -- v_dchange_event: date_leave_start (วันที่หยุดปกติ ที่สลับมาทำงาน)
    SELECT 
        c.emp_id,
        STR_TO_DATE(c.date_leave_start, '%d-%m-%Y') AS event_date,
        c.leave_type_detail,
        c.leave_type_id,
        1 AS leave_day,
        0 AS leave_hour,
        c.remark,
        c.status,
        '#f39c12' AS color,
        'CHANGE' AS event_source,
        c.doc_id,
        COALESCE(NULLIF(c.time_leave_start, ''), '08:30') AS time_leave_start,
        COALESCE(NULLIF(c.time_leave_to, ''), '17:30') AS time_leave_to,
        c.date_leave_start,
        c.date_leave_to
    FROM v_dchange_event c
    WHERE c.status != 'R' 
      AND c.date_leave_start IS NOT NULL AND c.date_leave_start != ''
      AND STR_TO_DATE(c.date_leave_start, '%d-%m-%Y') != STR_TO_DATE(COALESCE(NULLIF(c.date_leave_to, ''), '01-01-1970'), '%d-%m-%Y')
),
grouped_leaves AS (
    SELECT 
        emp_id,
        event_date,
        GROUP_CONCAT(DISTINCT leave_type_detail SEPARATOR ', ') AS leave_type_detail,
        GROUP_CONCAT(DISTINCT doc_id SEPARATOR ', ') AS doc_id,
        GROUP_CONCAT(DISTINCT remark SEPARATOR ', ') AS remark,
        MAX(color) AS color,
        MAX(status) AS leave_status,
        MAX(event_source) AS event_source,
        SUM(leave_day) AS total_leave_day,
        SUM(leave_hour) AS total_leave_hour,
        MIN(time_leave_start) AS time_leave_start,
        MAX(time_leave_to) AS time_leave_to,
        GROUP_CONCAT(DISTINCT date_leave_start SEPARATOR ', ') AS date_leave_start,
        GROUP_CONCAT(DISTINCT date_leave_to SEPARATOR ', ') AS date_leave_to
    FROM all_leaves
    WHERE event_date BETWEEN :start_date AND :end_date
      AND event_date <= CURDATE()
    GROUP BY emp_id, event_date
),
att_days AS (
    SELECT 
        a.emp_id,
        a.date AS work_date,
        MIN(CASE WHEN a.time < '12:00:00' THEN a.time END) AS start_time,
        MAX(CASE WHEN a.time >= '12:00:00' THEN a.time END) AS end_time,
        MAX(a.device) AS device
    FROM ims_time_attendance a
    WHERE a.date BETWEEN :start_date AND :end_date
      AND a.date <= CURDATE()
    GROUP BY a.date, a.emp_id
),
combined_all AS (
    SELECT 
        COALESCE(a.emp_id, l.emp_id) AS emp_id,
        COALESCE(a.work_date, l.event_date) AS work_date,
        COALESCE(a.start_time, l.time_leave_start) AS start_time,
        COALESCE(a.end_time, l.time_leave_to) AS end_time,
        a.start_time AS att_start_time,
        a.end_time AS att_end_time,
        a.device,
        l.leave_type_detail,
        l.doc_id,
        l.remark,
        l.color,
        l.leave_status,
        l.total_leave_day,
        l.total_leave_hour,
        l.event_source,
        l.time_leave_start,
        l.time_leave_to,
        l.date_leave_start,
        l.date_leave_to
    FROM att_days a
    LEFT JOIN grouped_leaves l ON l.emp_id = a.emp_id AND l.event_date = a.work_date

    UNION

    SELECT 
        l.emp_id,
        l.event_date AS work_date,
        l.time_leave_start AS start_time,
        l.time_leave_to AS end_time,
        NULL AS att_start_time,
        NULL AS att_end_time,
        NULL AS device,
        l.leave_type_detail,
        l.doc_id,
        l.remark,
        l.color,
        l.leave_status,
        l.total_leave_day,
        l.total_leave_hour,
        l.event_source,
        l.time_leave_start,
        l.time_leave_to,
        l.date_leave_start,
        l.date_leave_to
    FROM grouped_leaves l
    LEFT JOIN att_days a ON a.emp_id = l.emp_id AND a.work_date = l.event_date
    WHERE a.emp_id IS NULL
)
";

$sql_export = $cte_sql . "
SELECT 
    c.emp_id,
    e.f_name,
    e.l_name,
    e.department_id,
    e.dept_id_approve,
    c.work_date,
    c.start_time,
    c.end_time,
    c.att_start_time,
    c.att_end_time,
    c.device,
    c.leave_type_detail,
    c.doc_id,
    c.remark,
    c.total_leave_day,
    c.total_leave_hour,
    c.date_leave_start,
    c.date_leave_to,
    c.time_leave_start,
    c.time_leave_to
FROM combined_all c
LEFT JOIN memployee e ON e.emp_id = c.emp_id
WHERE $whereSql
ORDER BY c.work_date DESC, c.start_time DESC, c.emp_id DESC
";

// Title row
fputcsv($output, ["รายงานเวลา เข้า - ออก และการลาประจำวัน"]);
fputcsv($output, ["ช่วงวันที่: " . date('d-m-Y', strtotime($start_date)) . " ถึง " . date('d-m-Y', strtotime($end_date))]);
fputcsv($output, []); // blank line

// Column Headers
fputcsv($output, [
    "ลำดับ",
    "รหัสพนักงาน",
    "ชื่อ",
    "นามสกุล",
    "แผนก",
    "วันที่",
    "เวลาเข้า",
    "เวลาออก",
    "สถานะ / การลา",
    "เลขที่เอกสารใบลา",
    "วันที่เริ่มในเอกสาร",
    "วันที่สิ้นสุดในเอกสาร",
    "จำนวนวันลา",
    "จำนวนชั่วโมงลา",
    "หมายเหตุ / อุปกรณ์"
]);

$stmt = $conn->prepare($sql_export);
foreach ($queryParams as $k => $v) {
    $stmt->bindValue(':' . $k, $v);
}
$stmt->execute();

$index = 1;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $formatted_date = date('d-m-Y', strtotime($row['work_date']));
    $start_time = !empty($row['start_time']) ? substr(str_replace('.', ':', $row['start_time']), 0, 5) : '-';
    $end_time = !empty($row['end_time']) ? substr(str_replace('.', ':', $row['end_time']), 0, 5) : '-';

    $has_att = (!empty($row['att_start_time']) || !empty($row['att_end_time']));
    $leave_desc = trim($row['leave_type_detail'] ?? '');
    
    $status_desc = '';
    if (!empty($leave_desc)) {
        $status_desc = $leave_desc;
        if ($has_att) {
            $status_desc .= " (มีเวลาสแกนเข้า-ออก)";
        }
    } else if ($has_att) {
        $status_desc = "ปฏิบัติงานปกติ";
    } else {
        $status_desc = "ไม่มีข้อมูลเวลา/ไม่มีใบลา";
    }

    $remark_col = $row['remark'] ?? '';
    if (!empty($row['device'])) {
        if (!empty($remark_col)) $remark_col .= " | ";
        $remark_col .= "เครื่อง: " . $row['device'];
    }

    fputcsv($output, [
        $index++,
        $row['emp_id'],
        $row['f_name'],
        $row['l_name'],
        $row['department_id'],
        $formatted_date,
        $start_time,
        $end_time,
        $status_desc,
        $row['doc_id'] ?? '-',
        $row['date_leave_start'] ?? '-',
        $row['date_leave_to'] ?? '-',
        $row['total_leave_day'] > 0 ? $row['total_leave_day'] : '-',
        $row['total_leave_hour'] > 0 ? $row['total_leave_hour'] : '-',
        $remark_col ?: '-'
    ]);
}

fclose($output);
exit;

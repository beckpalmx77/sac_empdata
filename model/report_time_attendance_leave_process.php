<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(0);

include(__DIR__ . '/../config/connect_db.php');
include(__DIR__ . '/../config/lang.php');
include(__DIR__ . '/../util/record_util.php');

// 1. GET DETAIL DATA FOR MODAL
if ($_POST["action"] === 'GET_DATA') {
    $id = $_POST["id"];
    $return_arr = array();

    if (strpos($id, '@') !== false) {
        list($emp_id, $work_date) = explode('@', $id);
        
        // Convert to Y-m-d if in d-m-Y
        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $work_date)) {
            $parts = explode('-', $work_date);
            $work_date = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
        }

        // Fetch employee
        $stmtEmp = $conn->prepare("SELECT emp_id, f_name, l_name, department_id, dept_id_approve FROM memployee WHERE emp_id = :emp_id");
        $stmtEmp->execute(['emp_id' => $emp_id]);
        $emp = $stmtEmp->fetch(PDO::FETCH_ASSOC);

        // Fetch attendance punches for that day
        $stmtAtt = $conn->prepare("
            SELECT MIN(CASE WHEN time < '12:00:00' THEN time END) AS start_time,
                   MAX(CASE WHEN time >= '12:00:00' THEN time END) AS end_time,
                   MAX(device) AS device,
                   GROUP_CONCAT(time ORDER BY time SEPARATOR ', ') AS all_scans
            FROM ims_time_attendance 
            WHERE emp_id = :emp_id AND date = :work_date
        ");
        $stmtAtt->execute(['emp_id' => $emp_id, 'work_date' => $work_date]);
        $att = $stmtAtt->fetch(PDO::FETCH_ASSOC);

        // Fetch leave events on that day
        $leaves = array();

        // v_dleave_event
        $stmtL = $conn->prepare("
            SELECT 'LEAVE' AS source_type, doc_id, leave_type_detail, date_leave_start, date_leave_to,
                   COALESCE(NULLIF(time_leave_start, ''), '08:30') AS time_leave_start,
                   COALESCE(NULLIF(time_leave_to, ''), '17:30') AS time_leave_to,
                   leave_day, leave_hour, remark, status, color
            FROM v_dleave_event
            WHERE emp_id = :emp_id 
              AND STR_TO_DATE(:wdate, '%Y-%m-%d') BETWEEN LEAST(
                    STR_TO_DATE(date_leave_start, '%d-%m-%Y'), 
                    STR_TO_DATE(COALESCE(NULLIF(date_leave_to, ''), date_leave_start), '%d-%m-%Y')
                  )
                  AND GREATEST(
                    STR_TO_DATE(date_leave_start, '%d-%m-%Y'), 
                    STR_TO_DATE(COALESCE(NULLIF(date_leave_to, ''), date_leave_start), '%d-%m-%Y')
                  )
              AND status != 'R'
        ");
        $stmtL->execute(['emp_id' => $emp_id, 'wdate' => $work_date]);
        while ($r = $stmtL->fetch(PDO::FETCH_ASSOC)) {
            $leaves[] = $r;
        }

        // vdholiday_event
        $stmtH = $conn->prepare("
            SELECT 'HOLIDAY' AS source_type, doc_id, leave_type_detail, date_leave_start, date_leave_to,
                   COALESCE(NULLIF(time_leave_start, ''), '08:30') AS time_leave_start,
                   COALESCE(NULLIF(time_leave_to, ''), '17:30') AS time_leave_to,
                   leave_day, leave_hour, remark, status, color
            FROM vdholiday_event
            WHERE emp_id = :emp_id 
              AND STR_TO_DATE(:wdate, '%Y-%m-%d') BETWEEN LEAST(
                    STR_TO_DATE(date_leave_start, '%d-%m-%Y'), 
                    STR_TO_DATE(COALESCE(NULLIF(date_leave_to, ''), date_leave_start), '%d-%m-%Y')
                  )
                  AND GREATEST(
                    STR_TO_DATE(date_leave_start, '%d-%m-%Y'), 
                    STR_TO_DATE(COALESCE(NULLIF(date_leave_to, ''), date_leave_start), '%d-%m-%Y')
                  )
              AND status != 'R'
        ");
        $stmtH->execute(['emp_id' => $emp_id, 'wdate' => $work_date]);
        while ($r = $stmtH->fetch(PDO::FETCH_ASSOC)) {
            $leaves[] = $r;
        }

        // v_dchange_event: matches either date_leave_to (วันที่ต้องการหยุด) OR date_leave_start (วันที่หยุดปกติ)
        $stmtC = $conn->prepare("
            SELECT 'CHANGE' AS source_type, doc_id, leave_type_detail, date_leave_start, date_leave_to,
                   COALESCE(NULLIF(time_leave_start, ''), '08:30') AS time_leave_start,
                   COALESCE(NULLIF(time_leave_to, ''), '17:30') AS time_leave_to,
                   1 AS leave_day, 0 AS leave_hour, remark, status, '#f39c12' AS color
            FROM v_dchange_event
            WHERE emp_id = :emp_id 
              AND (STR_TO_DATE(date_leave_to, '%d-%m-%Y') = STR_TO_DATE(:wdate, '%Y-%m-%d')
                   OR STR_TO_DATE(date_leave_start, '%d-%m-%Y') = STR_TO_DATE(:wdate, '%Y-%m-%d'))
              AND status != 'R'
        ");
        $stmtC->execute(['emp_id' => $emp_id, 'wdate' => $work_date]);
        while ($r = $stmtC->fetch(PDO::FETCH_ASSOC)) {
            $leaves[] = $r;
        }

        $formatted_work_date = date('d-m-Y', strtotime($work_date));

        $start_time = !empty($att['start_time']) ? $att['start_time'] : (!empty($leaves[0]['time_leave_start']) ? $leaves[0]['time_leave_start'] : '');
        $end_time = !empty($att['end_time']) ? $att['end_time'] : (!empty($leaves[0]['time_leave_to']) ? $leaves[0]['time_leave_to'] : '');

        $return_arr[] = array(
            "id" => $emp_id . '@' . $work_date,
            "emp_id" => $emp_id,
            "f_name" => $emp['f_name'] ?? '',
            "l_name" => $emp['l_name'] ?? '',
            "department_id" => $emp['department_id'] ?? '',
            "dept_id_approve" => $emp['dept_id_approve'] ?? '',
            "work_date" => $formatted_work_date,
            "start_time" => $start_time,
            "end_time" => $end_time,
            "att_start_time" => $att['start_time'] ?? '',
            "att_end_time" => $att['end_time'] ?? '',
            "device" => $att['device'] ?? '',
            "all_scans" => $att['all_scans'] ?? '',
            "leaves" => $leaves
        );
    }

    echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
    exit;
}

// 2. GET DEPARTMENTS FOR DROPDOWN
if ($_POST["action"] === 'GET_DEPT') {
    $depts = array();
    try {
        $q = $conn->query("SELECT DISTINCT department_id, department_desc FROM mdepartment WHERE status = 'Y' ORDER BY department_id");
        while ($r = $q->fetch(PDO::FETCH_ASSOC)) {
            $depts[] = [
                'department_id' => $r['department_id'],
                'department_desc' => $r['department_desc']
            ];
        }
    } catch (Exception $e) {}
    echo json_encode($depts, JSON_UNESCAPED_UNICODE);
    exit;
}

// 3. GET EMPLOYEES FOR DROPDOWN
if ($_POST["action"] === 'GET_EMPLOYEE') {
    $employees = array();
    try {
        $whereRole = "1=1";
        $params = [];
        if ($_SESSION['role'] === "SUPERVISOR") {
            $whereRole = "dept_id_approve = :dept_id_approve";
            $params['dept_id_approve'] = $_SESSION['dept_id_approve'];
        } else if ($_SESSION['role'] !== "HR" && $_SESSION['role'] !== "ADMIN") {
            $whereRole = "emp_id = :session_emp_id";
            $params['session_emp_id'] = $_SESSION['emp_id'];
        }
        $stmt = $conn->prepare("SELECT emp_id, f_name, l_name, department_id FROM memployee WHERE $whereRole ORDER BY emp_id ASC");
        $stmt->execute($params);
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $employees[] = [
                'emp_id' => $r['emp_id'],
                'f_name' => $r['f_name'],
                'l_name' => $r['l_name'],
                'department_id' => $r['department_id']
            ];
        }
    } catch (Exception $e) {}
    echo json_encode($employees, JSON_UNESCAPED_UNICODE);
    exit;
}

// 4. GET REPORT ATTENDANCE & LEAVES (MAIN DATATABLES)
if ($_POST["action"] === 'GET_ATTENDANCE_LEAVE') {
    $draw = (int)($_POST['draw'] ?? 1);
    $row = (int)($_POST['start'] ?? 0);
    $rowperpage = (int)($_POST['length'] ?? 10);
    $searchValue = trim($_POST['search']['value'] ?? '');

    // Date range
    $raw_start_date = $_POST['start_date'] ?? '';
    $raw_end_date = $_POST['end_date'] ?? '';

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
    $emp_filter = trim($_POST['emp_id'] ?? '');
    $filter_status = trim($_POST['filter_status'] ?? 'ALL');

    $whereClauses = array("1=1", "c.work_date <= CURDATE()");
    $queryParams = array(
        'start_date' => $start_date,
        'end_date' => $end_date
    );

    // Role-based filtering
    if ($_SESSION['role'] === "SUPERVISOR") {
        $whereClauses[] = "e.dept_id_approve = :session_dept_approve";
        $queryParams['session_dept_approve'] = $_SESSION['dept_id_approve'];
    } else if ($_SESSION['role'] !== "HR" && $_SESSION['role'] !== "ADMIN") {
        $whereClauses[] = "c.emp_id = :session_emp_id";
        $queryParams['session_emp_id'] = $_SESSION['emp_id'];
    }

    // UI Dropdown Filters
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

    // Search filter
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

    session_write_close(); // Unlock session to prevent blocking

    $whereSql = implode(" AND ", $whereClauses);

    // Sorting
    $columns = array(
        0 => 'c.emp_id',
        1 => 'e.f_name',
        2 => 'e.l_name',
        3 => 'e.department_id',
        4 => 'c.work_date',
        5 => 'c.start_time',
        6 => 'c.end_time',
        7 => 'c.leave_type_detail',
        8 => 'c.doc_id'
    );
    $orderColIndex = (int)($_POST['order'][0]['column'] ?? 4);
    $orderDir = (strtolower($_POST['order'][0]['dir'] ?? 'desc') === 'asc') ? 'ASC' : 'DESC';
    $orderCol = $columns[$orderColIndex] ?? 'c.work_date';

    // Base CTE definition
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

    // 1. Total records with filters applied
    $sql_count = $cte_sql . " SELECT COUNT(*) FROM combined_all c LEFT JOIN memployee e ON e.emp_id = c.emp_id WHERE $whereSql";
    $stmtCount = $conn->prepare($sql_count);
    foreach ($queryParams as $k => $v) {
        $stmtCount->bindValue(':' . $k, $v);
    }
    $stmtCount->execute();
    $totalFiltered = (int)$stmtCount->fetchColumn();

    // 2. Fetch records
    $sql_data = $cte_sql . "
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
        c.color,
        c.leave_status,
        c.total_leave_day,
        c.total_leave_hour,
        c.event_source,
        c.date_leave_start,
        c.date_leave_to,
        c.time_leave_start,
        c.time_leave_to
    FROM combined_all c
    LEFT JOIN memployee e ON e.emp_id = c.emp_id
    WHERE $whereSql
    ORDER BY $orderCol $orderDir, c.emp_id DESC
    LIMIT :limit_start, :limit_length
    ";

    $stmtData = $conn->prepare($sql_data);
    foreach ($queryParams as $k => $v) {
        $stmtData->bindValue(':' . $k, $v);
    }
    $stmtData->bindValue(':limit_start', $row, PDO::PARAM_INT);
    $stmtData->bindValue(':limit_length', $rowperpage, PDO::PARAM_INT);
    $stmtData->execute();
    $results = $stmtData->fetchAll(PDO::FETCH_ASSOC);

    $data = array();
    foreach ($results as $rowItem) {
        $formatted_date = date('d-m-Y', strtotime($rowItem['work_date']));
        $start_time = !empty($rowItem['start_time']) ? substr(str_replace('.', ':', $rowItem['start_time']), 0, 5) : '-';
        $end_time = !empty($rowItem['end_time']) ? substr(str_replace('.', ':', $rowItem['end_time']), 0, 5) : '-';

        $leave_type = trim($rowItem['leave_type_detail'] ?? '');
        $has_attendance = (!empty($rowItem['att_start_time']) || !empty($rowItem['att_end_time']));
        $has_leave = (!empty($leave_type));

        // Format Status & Badge
        $status_badge = '';
        if ($has_leave) {
            $color = !empty($rowItem['color']) ? $rowItem['color'] : '#17a2b8';
            $status_badge = "<span class='badge text-white px-2 py-1' style='background-color: " . htmlspecialchars($color) . "; font-size: 12px;'><i class='fa fa-calendar-check-o'></i> " . htmlspecialchars($leave_type) . "</span>";
            if ($has_attendance) {
                $status_badge .= " <span class='badge badge-success px-1 py-1' title='มีบันทึกเวลาสแกนเข้า/ออก'><i class='fa fa-clock-o'></i> มีเวลาสแกน</span>";
            }
        } else if ($has_attendance) {
            $status_badge = "<span class='badge badge-success px-2 py-1' style='font-size: 12px;'><i class='fa fa-check-circle'></i> มาปฏิบัติงาน</span>";
        } else {
            $status_badge = "<span class='badge badge-secondary px-2 py-1' style='font-size: 12px;'><i class='fa fa-times-circle'></i> ไม่มีข้อมูล</span>";
        }

        // Format Start / End Time styling
        if ($start_time !== '-') {
            if ($has_attendance && !empty($rowItem['att_start_time'])) {
                $start_display = "<span class='text-success font-weight-bold' title='เวลาเข้าจากการสแกน'><i class='fa fa-clock-o'></i> " . $start_time . "</span>";
            } else {
                $start_display = "<span class='text-info font-weight-bold' title='เวลาตามใบลา/เอกสาร'><i class='fa fa-calendar-check-o'></i> " . $start_time . "</span>";
            }
        } else {
            $start_display = "<span class='text-muted'>-</span>";
        }

        if ($end_time !== '-') {
            if ($has_attendance && !empty($rowItem['att_end_time'])) {
                $end_display = "<span class='text-primary font-weight-bold' title='เวลาออกจากกาารสแกน'><i class='fa fa-clock-o'></i> " . $end_time . "</span>";
            } else {
                $end_display = "<span class='text-info font-weight-bold' title='เวลาตามใบลา/เอกสาร'><i class='fa fa-calendar-check-o'></i> " . $end_time . "</span>";
            }
        } else {
            $end_display = "<span class='text-muted'>-</span>";
        }

        // Remark / Document info
        $remark_display = '';
        if (!empty($rowItem['doc_id'])) {
            $remark_display .= "<small class='font-weight-bold text-dark'><i class='fa fa-file-text-o'></i> " . htmlspecialchars($rowItem['doc_id']) . "</small>";
        }
        if (!empty($rowItem['remark'])) {
            if (!empty($remark_display)) $remark_display .= "<br>";
            $remark_display .= "<small class='text-muted'>" . htmlspecialchars($rowItem['remark']) . "</small>";
        }
        if (empty($remark_display) && !empty($rowItem['device'])) {
            $remark_display = "<small class='text-muted'>เครื่อง: " . htmlspecialchars($rowItem['device']) . "</small>";
        }

        $detail_btn = "<button type='button' id='" . $rowItem['emp_id'] . "@" . $rowItem['work_date'] . "' class='btn btn-info btn-xs detail' title='ดูรายละเอียด'><i class='fa fa-eye'></i> Detail</button>";

        $data[] = array(
            "emp_id" => $rowItem['emp_id'],
            "f_name" => $rowItem['f_name'] ?? '',
            "l_name" => $rowItem['l_name'] ?? '',
            "full_name" => trim(($rowItem['f_name'] ?? '') . ' ' . ($rowItem['l_name'] ?? '')),
            "department_id" => $rowItem['department_id'] ?? '',
            "work_date" => $formatted_date,
            "start_time" => $start_display,
            "end_time" => $end_display,
            "leave_type_detail" => $status_badge,
            "remark" => !empty($remark_display) ? $remark_display : '-',
            "action" => $detail_btn
        );
    }

    echo json_encode(array(
        "draw" => $draw,
        "recordsTotal" => $totalFiltered,
        "recordsFiltered" => $totalFiltered,
        "data" => $data
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

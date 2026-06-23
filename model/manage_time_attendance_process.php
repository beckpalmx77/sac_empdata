<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    if (strpos($id, '@') !== false) {
        list($emp_id, $work_date) = explode('@', $id);
        $sql_get = "SELECT 
                        SHA2(CONCAT(a.emp_id, a.date, COALESCE(MIN(CASE WHEN a.time < '12:00:00' THEN a.time END), '')), 256) AS id,
                        a.emp_id,
                        e.f_name,
                        e.l_name,
                        e.department_id,
                        e.dept_id_approve,
                        a.date AS work_date,
                        MIN(CASE WHEN a.time < '12:00:00' THEN a.time END) AS start_time,
                        MAX(CASE WHEN a.time >= '12:00:00' THEN a.time END) AS end_time,
                        MAX(a.device) AS device
                    FROM ims_time_attendance a
                    LEFT JOIN memployee e ON e.emp_id = a.emp_id
                    WHERE a.emp_id = :emp_id AND a.date = :work_date
                    GROUP BY a.date, a.emp_id";
        $statement = $conn->prepare($sql_get);
        $statement->execute(['emp_id' => $emp_id, 'work_date' => $work_date]);
    } else {
        $sql_get = "SELECT * FROM v_ims_time_attendance WHERE id = :id";
        $statement = $conn->prepare($sql_get);
        $statement->execute(['id' => $id]);
    }
    
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "emp_id" => $result['emp_id'],
            "f_name" => $result['f_name'],
            "l_name" => $result['l_name'],
            "department_id" => $result['department_id'],
            "dept_id_approve" => $result['dept_id_approve'],
            "work_date" => $result['work_date'],
            "start_time" => $result['start_time'],
            "end_time" => $result['end_time'],
            "device" => $result['device']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'GET_TIME_ATTENDANCE') {

    ## Read value
    $draw = $_POST['draw'];
    $row = (int)$_POST['start'];
    $rowperpage = (int)$_POST['length'];
    $searchValue = $_POST['search']['value'];

    $searchArray = array();
    $whereClauses = array("1=1"); // พื้นฐานสำหรับ WHERE

    ## 1. Role-based Security Filtering
    if ($_SESSION['role'] === "SUPERVISOR") {
        $whereClauses[] = "e.dept_id_approve = :dept_id_approve";
        $searchArray['dept_id_approve'] = $_SESSION['dept_id_approve'];
    } else if ($_SESSION['role'] !== "HR" && $_SESSION['role'] !== "ADMIN") {
        $whereClauses[] = "a.emp_id = :session_emp_id";
        $searchArray['session_emp_id'] = $_SESSION['emp_id'];
    }

    ## 2. Search Filter
    if (!empty($searchValue)) {
        $whereClauses[] = "(a.emp_id LIKE :search OR e.f_name LIKE :search OR e.l_name LIKE :search OR e.department_id LIKE :search OR a.date LIKE :search)";
        $searchArray['search'] = "%$searchValue%";
    }

    $whereSql = implode(" AND ", $whereClauses);

    ## 3. Total number of records without filtering (ดึงจาก Base Table โดยตรงเพื่อความเร็ว)
    $countSecurityClauses = array("1=1");
    $countSecurityArray = array();
    
    if ($_SESSION['role'] === "SUPERVISOR") {
        $countSecurityClauses[] = "e.dept_id_approve = :dept_id_approve";
        $countSecurityArray['dept_id_approve'] = $_SESSION['dept_id_approve'];
        
        $sql_total = "SELECT COUNT(DISTINCT a.emp_id, a.date) 
                      FROM ims_time_attendance a 
                      JOIN memployee e ON a.emp_id = e.emp_id 
                      WHERE " . implode(" AND ", $countSecurityClauses);
        
        $stmtTotal = $conn->prepare($sql_total);
        $stmtTotal->execute($countSecurityArray);
        $totalRecords = $stmtTotal->fetchColumn();
    } else if ($_SESSION['role'] !== "HR" && $_SESSION['role'] !== "ADMIN") {
        $countSecurityClauses[] = "a.emp_id = :session_emp_id";
        $countSecurityArray['session_emp_id'] = $_SESSION['emp_id'];
        
        $sql_total = "SELECT COUNT(DISTINCT a.date) 
                      FROM ims_time_attendance a 
                      WHERE " . implode(" AND ", $countSecurityClauses);
                      
        $stmtTotal = $conn->prepare($sql_total);
        $stmtTotal->execute($countSecurityArray);
        $totalRecords = $stmtTotal->fetchColumn();
    } else {
        $stmtTotal = $conn->query("SELECT COUNT(DISTINCT emp_id, date) FROM ims_time_attendance");
        $totalRecords = $stmtTotal->fetchColumn();
    }

    ## 4. Total number of records with filtering
    if (empty($searchValue)) {
        $totalRecordwithFilter = $totalRecords;
    } else {
        $sql_filtered = "SELECT COUNT(DISTINCT a.emp_id, a.date) 
                         FROM ims_time_attendance a 
                         LEFT JOIN memployee e ON e.emp_id = a.emp_id 
                         WHERE $whereSql";
        $stmtFiltered = $conn->prepare($sql_filtered);
        $stmtFiltered->execute($searchArray);
        $totalRecordwithFilter = $stmtFiltered->fetchColumn();
    }

    ## 5. Fetch records (ดึงตรงจาก Base Table + Query Optimization)
    $sql_record = "SELECT 
        SHA2(CONCAT(a.emp_id, a.date, COALESCE(MIN(CASE WHEN a.time < '12:00:00' THEN a.time END), '')), 256) AS id,
        a.emp_id,
        e.f_name,
        e.l_name,
        e.department_id,
        e.dept_id_approve,
        a.date AS work_date,
        MIN(CASE WHEN a.time < '12:00:00' THEN a.time END) AS start_time,
        MAX(CASE WHEN a.time >= '12:00:00' THEN a.time END) AS end_time,
        MAX(a.device) AS device
    FROM ims_time_attendance a
    LEFT JOIN memployee e ON e.emp_id = a.emp_id
    WHERE $whereSql 
    GROUP BY a.date, a.emp_id
    ORDER BY a.date DESC, a.emp_id DESC 
    LIMIT :limit, :offset";

    $stmt = $conn->prepare($sql_record);

    // Bind values ทั้งหมดในครั้งเดียว
    foreach ($searchArray as $key => $val) {
        $stmt->bindValue(':' . $key, $val);
    }
    $stmt->bindValue(':limit', $row, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $rowperpage, PDO::PARAM_INT);
    $stmt->execute();

    $empRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $data = array();

    foreach ($empRecords as $row) {
        if ($_POST['sub_action'] === "GET_MASTER") {
            // ใช้ date() และ strtotime() แทน DateTime Object เพื่อความเร็ว (High Performance)
            $formatted_date = date('d-m-Y', strtotime($row['work_date']));

            $data[] = array(
                "id" => $row['id'],
                "emp_id" => $row['emp_id'],
                "f_name" => $row['f_name'],
                "l_name" => $row['l_name'],
                "full_name" => $row['f_name'] . " " . $row['l_name'],
                "department_id" => $row['department_id'],
                "dept_id_approve" => $row['dept_id_approve'],
                "work_date" => $formatted_date,
                "start_time" => $row['start_time'],
                "end_time" => $row['end_time'],
                "device" => $row['device'],
                "detail" => "<button type='button' id='" . $row['emp_id'] . "@" . $row['work_date'] . "' class='btn btn-info btn-xs detail' title='Detail'>Detail</button>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "f_name" => $row['f_name'],
                "l_name" => $row['l_name'],
                "select" => "<button type='button' id='" . $row['f_name'] . "@" . $row['l_name'] . "' class='btn btn-outline-success btn-xs select'>select <i class='fa fa-check'></i></button>",
            );
        }
    }

    ## Response Return Value
    echo json_encode(array(
        "draw" => (int)$draw,
        "recordsTotal" => (int)$totalRecords,
        "recordsFiltered" => (int)$totalRecordwithFilter,
        "aaData" => $data
    ));
}

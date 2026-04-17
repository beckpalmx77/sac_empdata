<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM v_ims_time_attendance WHERE id = " . $id;
    $statement = $conn->query($sql_get);
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
        $whereClauses[] = "dept_id_approve = :dept_id_approve";
        $searchArray['dept_id_approve'] = $_SESSION['dept_id_approve'];
    } else if ($_SESSION['role'] !== "HR" && $_SESSION['role'] !== "ADMIN") {
        $whereClauses[] = "emp_id = :session_emp_id";
        $searchArray['session_emp_id'] = $_SESSION['emp_id'];
    }

    ## 2. Search Filter
    if (!empty($searchValue)) {
        $whereClauses[] = "(emp_id LIKE :search OR f_name LIKE :search OR l_name LIKE :search OR department_id LIKE :search OR work_date LIKE :search)";
        $searchArray['search'] = "%$searchValue%";
    }

    $whereSql = implode(" AND ", $whereClauses);

    ## Total number of records without filtering (ดึงจาก View โดยตรง)
    $stmtTotal = $conn->query("SELECT COUNT(id) FROM v_ims_time_attendance");
    $totalRecords = $stmtTotal->fetchColumn();

    ## Total number of records with filtering
    $stmtFiltered = $conn->prepare("SELECT COUNT(id) FROM v_ims_time_attendance WHERE $whereSql");
    $stmtFiltered->execute($searchArray);
    $totalRecordwithFilter = $stmtFiltered->fetchColumn();

    ## 3. Fetch records
    // เลือกเฉพาะ Column ที่จำเป็น ลดภาระ Memory
    $sql_record = "SELECT id, emp_id, f_name, l_name, department_id, dept_id_approve, work_date, start_time, end_time, device 
                   FROM v_ims_time_attendance 
                   WHERE $whereSql 
                   ORDER BY work_date DESC, start_time DESC 
                   LIMIT :limit, :offset";

/*
    $txt = $sql_record ;
    $my_file = fopen("sql_record.txt", "w") or die("Unable to open file!");
    fwrite($my_file, $txt. " - " . $row . " , " . $rowperpage );
    fclose($my_file);
*/

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
                "detail" => "<button type='button' id='" . $row['id'] . "' class='btn btn-info btn-xs detail' title='Detail'>Detail</button>"
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

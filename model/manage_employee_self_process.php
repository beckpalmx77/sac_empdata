<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');
include('../util/reorder_record.php');

if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT em.*,mt.work_time_detail FROM memployee em            
            left join mwork_time mt on mt.work_time_id = em.work_time_id  
            WHERE em.id = " . $id;

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "emp_id" => $result['emp_id'],
            "f_name" => $result['f_name'],
            "l_name" => $result['l_name'],
            "sex" => $result['sex'],
            "start_work_date" => $result['start_work_date'],
            "dept_id" => $result['dept_id'],
            "department_id" => $result['department_id'],
            "work_time_id" => $result['work_time_id'],
            "work_time_detail" => $result['work_time_detail'],
            "prefix" => $result['prefix'],
            "nick_name" => $result['nick_name'],
            "dept_id_approve" => $result['dept_id_approve'],
            "remark" => $result['remark'],
            "position" => $result['position'],
            "week_holiday" => $result['week_holiday'],
            "status" => $result['status']);
    }
    echo json_encode($return_arr);
}


if ($_POST["action"] === 'GET_SELECT_EMP_DATA') {
    $branch = isset($_POST['branch']) ? $_POST['branch'] : '';

    $query = "SELECT emp_id,CONCAT(f_name, '-', l_name) AS fullname  
             FROM memployee ";
    $query .= " WHERE branch = :branch";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':branch', $branch);
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($employees as $row) {
        echo '<option value="' . $row['fullname'] . '">' . $row['fullname'] . '</option>';
    }
}

if ($_POST["action"] === 'GET_SELECT_EMP_BY_DEPT') {

    $document_dept_cond = isset($_POST['document_dept_cond']) ? $_POST['document_dept_cond'] : '';
    $dept_id_approve = isset($_POST['dept_id_approve']) ? $_POST['dept_id_approve'] : '';
    $emp_id = isset($_POST['emp_id']) ? $_POST['emp_id'] : '';

    $query = "SELECT emp_id, CONCAT(f_name, '   ', l_name) AS fullname FROM memployee WHERE status = 'Y' ";

    if ($document_dept_cond === 'A') {
        $con_query = $query . " AND dept_id_approve = :dept_id_approve";
        $stmt = $conn->prepare($con_query);
        $stmt->bindParam(':dept_id_approve', $dept_id_approve);
    } else {
        $con_query = $query . " AND emp_id = :emp_id";
        $stmt = $conn->prepare($con_query);
        $stmt->bindParam(':emp_id', $_SESSION['emp_id']);
    }

    if ($_SESSION['role'] === 'ADMIN' || $_SESSION['role'] === 'HR') {
        $con_query = $query . " AND (branch <> 'XXX' AND branch NOT LIKE 'CP%') " ;
        $stmt = $conn->prepare($con_query);
    }
/*
    $txt = $document_dept_cond . " | " . $dept_id_approve . " | " . $emp_id . " | " . $con_query;
    $my_file = fopen("leave_1.txt", "w") or die("Unable to open file!");
    fwrite($my_file, $txt);
    fclose($my_file);
*/
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($employees as $row) {
        echo '<option value="' . $row['emp_id'] . '">' . $row['fullname'] . '</option>';
    }
}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["l_name"] !== '') {

        $emp_id = $_POST["emp_id"];
        $sql_find = "SELECT * FROM memployee WHERE emp_id = '" . $emp_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}


if ($_POST["action"] === 'GET_EMPLOYEE') {

    ## Read value
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    //$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    $columnSortOrder = 'desc'; // asc or desc
    $searchValue = $_POST['search']['value']; // Search value

    $searchArray = array();

## Search
    $searchQuery = " ";
    //if ($_POST["page_manage"]!=="ADMIN") {
    //$searchQuery = " AND emp_id = '" . $_SESSION['emp_id'] . "'";
    //}

    if ($searchValue != '') {
        $searchQuery = " AND (emp_id LIKE :emp_id or l_name LIKE :l_name or
        f_name LIKE :f_name or nick_name LIKE :nick_name or dept_id_approve LIKE :dept_id_approve) ";
        $searchArray = array(
            'emp_id' => "%$searchValue%",
            'l_name' => "%$searchValue%",
            'f_name' => "%$searchValue%",
            'nick_name' => "%$searchValue%",
            'dept_id_approve' => "%$searchValue%"
        );
    }

    $searchQuery .= " AND emp_id = '" . $_SESSION['emp_id'] . "'";


## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM memployee ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM memployee WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $sql_getdata = "SELECT em.*,mt.work_time_detail,dp.department_desc FROM memployee em            
            left join mwork_time mt on mt.work_time_id = em.work_time_id 
            left join mdepartment dp on dp.department_id = em.dept_id 	
            WHERE 1 " . $searchQuery
        . " ORDER BY status DESC, emp_id DESC , " . $columnName . " " . $columnSortOrder . " LIMIT :limit,:offset";

    $stmt = $conn->prepare($sql_getdata);

// Bind values
    foreach ($searchArray as $key => $search) {
        $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
    }

    $stmt->bindValue(':limit', (int)$row, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $empRecords = $stmt->fetchAll();
    $data = array();

    foreach ($empRecords as $row) {

        if ($_POST['sub_action'] === "GET_MASTER") {

            $data[] = array(
                "id" => $row['id'],
                "emp_id" => $row['emp_id'],
                "f_name" => $row['f_name'],
                "l_name" => $row['l_name'],
                "nick_name" => $row['nick_name'],
                "prefix" => $row['prefix'],
                "sex" => $row['sex'],
                "full_name" => $row['f_name'] . " " . $row['l_name'],
                "dept_id" => $row['dept_id'],
                "department_id" => $row['department_id'],
                "department_desc" => $row['department_desc'],
                "work_time_id" => $row['work_time_id'],
                "work_time_detail" => $row['work_time_detail'],
                "start_work_date" => $row['start_work_date'],
                "week_holiday" => $row['week_holiday'],
                "detail1" => "<button type='button' name='detail1' emp_id='" . $row['emp_id'] . "' class='btn btn-info btn-xs detail1' data-toggle='tooltip' title='Detail1'>Detail1</button>",
                "detail" => "<button type='button' name='detail' id='" . $row['id'] . "' class='btn btn-info btn-xs detail' data-toggle='tooltip' title='Detail'>Detail</button>",
                "approve" => "<button type='button' name='approve' id='" . $row['id'] . "' class='btn btn-success btn-xs approve' data-toggle='tooltip' title='Approve'>Approve</button>",
                "status" => $row['status'] === 'A' ? "<div class='text-success'>" . $row['status'] . "</div>" : "<div class='text-muted'> " . $row['status'] . "</div>",
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "dept_id" => $row['dept_id'],
                "department_id" => $row['department_id'],
                "select" => "<button type='button' name='select' id='" . $row['department_id'] . "@" . $row['dept_id'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
</button>",
            );
        }

    }

## Response Return Value
    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    );

    echo json_encode($response);

}

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
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    $searchValue = $_POST['search']['value']; // Search value

    $searchArray = array();

    $searchQuery = " ";

    if ($_SESSION['role'] === "SUPERVISOR") {
        $searchQuery = " AND dept_id_approve = '" . $_SESSION['dept_id_approve'] . "' ";
    } else if ($_SESSION['role'] === "HR" || $_SESSION['role'] === "ADMIN") {
        $searchQuery = " ";
    } else {
        $searchQuery = " AND emp_id = '" . $_SESSION['emp_id'] . "' ";
    }

## Search

    if ($searchValue != '') {
        $searchQuery = " AND (emp_id LIKE :emp_id or f_name LIKE :f_name or
        l_name LIKE :l_name or department_id LIKE :department_id or work_date LIKE :work_date) ";
        $searchArray = array(
            'emp_id' => "%$searchValue%",
            'f_name' => "%$searchValue%",
            'l_name' => "%$searchValue%",
            'department_id' => "%$searchValue%",
            'work_date' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_ims_time_attendance ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_ims_time_attendance WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records

    $sql_record = "SELECT * FROM v_ims_time_attendance WHERE 1 " . $searchQuery;

    $sql_record .= " ORDER BY work_date DESC , start_time DESC LIMIT :limit,:offset";

    $stmt = $conn->prepare($sql_record);

// Bind values
    foreach ($searchArray as $key => $search) {
        $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
    }


        $txt = $sql_record . " | " . (int)$row . " | " . (int)$rowperpage;
        $my_file = fopen("msg.txt", "w") or die("Unable to open file!");
        fwrite($my_file, $txt);
        fclose($my_file);


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
                "department_id" => $row['department_id'],
                "dept_id_approve" => $row['dept_id_approve'],
                "work_date" => $row['work_date'],
                "start_time" => $row['start_time'],
                "end_time" => $row['end_time'],
                "device" => $row['device'],
                "detail" => "<button type='button' name='detail' id='" . $row['id'] . "' class='btn btn-info btn-xs detail' data-toggle='tooltip' title='Detail'>Detail</button>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "f_name" => $row['f_name'],
                "l_name" => $row['l_name'],
                "select" => "<button type='button' name='select' id='" . $row['f_name'] . "@" . $row['l_name'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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

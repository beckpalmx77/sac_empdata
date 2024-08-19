<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

if ($_POST["action"] === 'GET_LEAVE_DATA') {
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

    if ($_POST["action_for"] === "LEAVE") {
        $searchQuery = " AND (day_flag ='L') ";
    }

    if ($searchValue != '') {
        $searchQuery = " AND (leave_type_id LIKE :leave_type_id or
        leave_type_detail LIKE :leave_type_detail ) ";
        $searchArray = array(
            'leave_type_id' => "%$searchValue%",
            'leave_type_detail' => "%$searchValue%",
        );
    }

## Total number of records without filtering

    $sql_cond = "SELECT COUNT(*) AS allcount FROM v_dleave_event WHERE day_flag = 'L' ";

    $stmt = $conn->prepare($sql_cond);
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_dleave_event WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $stmt = $conn->prepare("SELECT * FROM v_dleave_event WHERE 1 " . $searchQuery
        . " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :limit,:offset");

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
                "leave_type_id" => $row['leave_type_id'],
                "leave_type_detail" => $row['leave_type_detail'],
                "day_max" => $row['day_max'],
                "leave_before" => $row['leave_before'],
                "work_age_allow" => $row['work_age_allow'],
                "remark" => $row['remark'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "status" => $row['status'] === 'Y' ? "<div class='text-success'>" . $row['status'] . "</div>" : "<div class='text-muted'> " . $row['status'] . "</div>"
            );
        } else {

            $data[] = array(
                "id" => $row['id'],
                "leave_type_id" => $row['leave_type_id'],
                "leave_type_detail" => $row['leave_type_detail'],
                "select" => "<button type='button' name='select' id='" . $row['leave_type_id'] . "@" . $row['leave_type_detail'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
</button>",
            );
/*
            $txt = $txt. ' ' . $row['leave_type_id'] . " | " .$row['leave_type_detail'] ;
            $my_file = fopen("leave_select.txt", "w") or die("Unable to open file!");
            fwrite($my_file, $txt);
            fclose($my_file);
*/

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

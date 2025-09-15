<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');


if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM mleave_type "
        . " WHERE mleave_type.id = " . $id;

    //$myfile = fopen("myqeury_1.txt", "w") or die("Unable to open file!");
    //fwrite($myfile, $sql_get);
    //fclose($myfile);

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "leave_type_id" => $result['leave_type_id'],
            "leave_type_detail" => $result['leave_type_detail'],
            "effect_year" => $result['effect_year'],
            "leave_before" => $result['leave_before'],
            "color" => $result['color'],
            "day_max" => $result['day_max'],
            "day_max_ext" => $result['day_max_ext'],
            "work_age_allow" => $result['work_age_allow'],
            "day_flag" => $result['day_flag'],
            "remark" => $result['remark'],
            "line_alert" => $result['line_alert'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH_DATA') {

    $leave_type_id = $_POST["leave_type_id"];

    $return_arr = array();

    $sql_get = "SELECT * FROM mleave_type "
        . " WHERE mleave_type.leave_type_id = '" . $leave_type_id . "'";
/*
    $myfile = fopen("myqeury_2.txt", "w") or die("Unable to open file!");
    fwrite($myfile, $sql_get);
    fclose($myfile);
*/

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "leave_type_id" => $result['leave_type_id'],
            "leave_type_detail" => $result['leave_type_detail'],
            "leave_before" => $result['leave_before'],
            "day_max" => $result['day_max'],
            "day_max_ext" => $result['day_max_ext'],
            "color" => $result['color'],
            "work_age_allow" => $result['work_age_allow'],
            "day_flag" => $result['day_flag'],
            "remark" => $result['remark'],
            "line_alert" => $result['line_alert'],
            "status" => $result['status']);
    }

    echo json_encode($return_arr);

}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["leave_type_id"] !== '') {

        $leave_type_id = $_POST["leave_type_id"];
        $sql_find = "SELECT * FROM mleave_type WHERE leave_type_id = '" . $leave_type_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'CHECK_DAYS') {
// ตรวจสอบว่ามีการส่ง leave_type_id มา
    if (isset($_POST['leave_type_id'])) {
        $leave_type_id = $_POST['leave_type_id'];

        // Query ข้อมูล Leave Type จากฐานข้อมูล
        $stmt = $conn->prepare("SELECT leave_before as advance_days FROM leave_types WHERE leave_type_id = :leave_type_id");
        $stmt->bindParam(':leave_type_id', $leave_type_id);
        $stmt->execute();

        // ดึงผลลัพธ์
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            echo json_encode(['advance_days' => $result['advance_days']]);
        } else {
            echo json_encode(['advance_days' => 0]); // หากไม่มีข้อมูล, ส่งค่า 0
        }
    }
}

if ($_POST["action"] === 'ADD') {
    if ($_POST["leave_type_detail"] !== '') {
        //$leave_type_id = "D" . sprintf('%03s', LAST_ID($conn, "mleave_type", 'id'));
        $leave_type_id = $_POST["leave_type_id"];
        $leave_type_detail = $_POST["leave_type_detail"];
        $effect_year = $_POST["effect_year"];
        $day_max = $_POST["day_max"];
        $day_max_ext = $_POST["day_max_ext"];
        $day_flag = $_POST["day_flag"];
        $leave_before = $_POST["leave_before"];
        $work_age_allow = $_POST["work_age_allow"];
        $remark = $_POST["remark"];
        $line_alert = $_POST["line_alert"];
        $status = $_POST["status"];
        $sql_find = "SELECT * FROM mleave_type WHERE leave_type_id = '" . $leave_type_id . "'";

        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo $dup;
        } else {
            $sql = "INSERT INTO mleave_type(leave_type_id,leave_type_detail,effect_year,day_max,day_max_ext,leave_before,work_age_allow,remark,day_flag,line_alert,status) 
                    VALUES (:leave_type_id,:leave_type_detail,:effect_year,:day_max,:day_max_ext,:leave_before,:work_age_allow,:remark,:day_flag,:line_alert,:status)";
            $query = $conn->prepare($sql);
            $query->bindParam(':leave_type_id', $leave_type_id, PDO::PARAM_STR);
            $query->bindParam(':leave_type_detail', $leave_type_detail, PDO::PARAM_STR);
            $query->bindParam(':effect_year', $effect_year, PDO::PARAM_STR);
            $query->bindParam(':day_max', $day_max, PDO::PARAM_STR);
            $query->bindParam(':day_max_ext', $day_max_ext, PDO::PARAM_STR);
            $query->bindParam(':leave_before', $leave_before, PDO::PARAM_STR);
            $query->bindParam(':work_age_allow', $work_age_allow, PDO::PARAM_STR);
            $query->bindParam(':remark', $remark, PDO::PARAM_STR);
            $query->bindParam(':day_flag', $day_flag, PDO::PARAM_STR);
            $query->bindParam(':line_alert', $line_alert, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->execute();
            $lastInsertId = $conn->lastInsertId();

            if ($lastInsertId) {
                echo $save_success;
            } else {
                echo $error;
            }
        }
    }
}

if ($_POST["action"] === 'UPDATE') {
    if ($_POST["leave_type_detail"] != '') {
        $id = $_POST["id"];
        $leave_type_id = $_POST["leave_type_id"];
        $leave_type_detail = $_POST["leave_type_detail"];
        $effect_year = $_POST["effect_year"];
        $day_max = $_POST["day_max"];
        $day_max_ext = $_POST["day_max_ext"];
        $day_flag = $_POST["day_flag"];
        $leave_before = $_POST["leave_before"];
        $remark = $_POST["remark"];
        $line_alert = $_POST["line_alert"];
        $status = $_POST["status"];
        $sql_find = "SELECT * FROM mleave_type WHERE id = '" . $id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            $sql_update = "UPDATE mleave_type SET leave_type_id=:leave_type_id,leave_type_detail=:leave_type_detail,effect_year=:effect_year
            ,day_max=:day_max,day_max_ext=:day_max_ext,leave_before=:leave_before,work_age_allow=:work_age_allow,remark=:remark,line_alert=:line_alert,day_flag=:day_flag,status=:status            
            WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':leave_type_id', $leave_type_id, PDO::PARAM_STR);
            $query->bindParam(':leave_type_detail', $leave_type_detail, PDO::PARAM_STR);
            $query->bindParam(':effect_year', $effect_year, PDO::PARAM_STR);
            $query->bindParam(':day_max', $day_max, PDO::PARAM_STR);
            $query->bindParam(':day_max_ext', $day_max_ext, PDO::PARAM_STR);
            $query->bindParam(':leave_before', $leave_before, PDO::PARAM_STR);
            $query->bindParam(':work_age_allow', $work_age_allow, PDO::PARAM_STR);
            $query->bindParam(':remark', $remark, PDO::PARAM_STR);
            $query->bindParam(':line_alert', $line_alert, PDO::PARAM_STR);
            $query->bindParam(':day_flag', $day_flag, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_STR);
            $query->execute();

            $sql_update_year = "UPDATE mleave_type SET effect_year=:effect_year ";
            $query_year = $conn->prepare($sql_update_year);
            $query_year->bindParam(':effect_year', $effect_year, PDO::PARAM_STR);
            $query_year->execute();

            echo $save_success;
        }
    }
}

if ($_POST["action"] === 'DELETE') {
    $id = $_POST["id"];
    $sql_find = "SELECT * FROM mleave_type WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM mleave_type WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_LEAVE_TYPE') {
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

    $sql_cond = "SELECT COUNT(*) AS allcount FROM mleave_type WHERE day_flag = 'L' ";

    $stmt = $conn->prepare($sql_cond);
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM mleave_type WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $stmt = $conn->prepare("SELECT * FROM mleave_type WHERE 1 " . $searchQuery
        . " ORDER BY effect_year desc,day_flag,leave_type_id " . " LIMIT :limit,:offset");

/*
        $txt = $_POST["action"] . " | "  . $_POST["sub_action"] . " | " . $_POST["action_for"] . " | " . $columnName . " | " . $columnSortOrder ;
        $my_file = fopen("leave_a.txt", "w") or die("Unable to open file!");
        fwrite($my_file, $txt);
        fclose($my_file);
*/


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
            $leave_type_detail = '<span style="color: '. $row['color'] . ';">' . $row['leave_type_detail'] . '</span>';
            $data[] = array(
                "id" => $row['id'],
                "leave_type_id" => $row['leave_type_id'],
                "leave_type_detail" => $leave_type_detail,
                "effect_year" => $row['effect_year'],
                "day_max" => $row['day_max'],
                "day_max_ext" => $row['day_max_ext'],
                "day_flag" => $row['day_flag'],
                "leave_before" => $row['leave_before'],
                "work_age_allow" => $row['work_age_allow'],
                "color" => $result['color'],
                "remark" => $row['remark'],
                "line_alert" => $row['line_alert'],
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='Delete'>Delete</button>",
                "status" => $row['status'] === 'Y' ? "<div class='text-success'>" . $row['status'] . "</div>" : "<div class='text-muted'> " . $row['status'] . "</div>"
            );
        } else {

            $leave_type_detail = '<span style="color: '. $row['color'] . ';">' . $row['leave_type_detail'] . '</span>';

            $data[] = array(
                "id" => $row['id'],
                "leave_type_id" => $row['leave_type_id'],
                "leave_type_detail" => $leave_type_detail,
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

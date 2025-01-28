<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');


// กรณีที่ต้องการดึงข้อมูลสำหรับ Select2
if ($_POST["action"] === 'GET_LEAVE_TYPES') {

    $return_arr = array();

    $sql_get = "SELECT leave_type_id, leave_type_detail FROM mleave_type WHERE day_flag = 'L' ORDER BY leave_type_detail ASC";

/*
    $txt = $sql_get ;
    $my_file = fopen("leave_select.txt", "w") or die("Unable to open file!");
    fwrite($my_file, $txt);
    fclose($my_file);
*/

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array(
            "id" => $result['leave_type_id'],
            "text" => $result['leave_type_detail']
        );
    }

    echo json_encode($return_arr);
}


<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');


if ($_POST["action"] === 'GET_DATA') {

    $emp_id = $_POST["emp_id"];
    $leave_type_id = $_POST["leave_type_id"];
    $table = $_POST["table"];

    $return_arr = array();

    $sql_get = "SELECT COUNT(*) AS leave_use_before FROM  " . $table . " WHERE emp_id = '" . $emp_id . "' AND leave_type_id = '" . $leave_type_id ."'";

/*
    $myfile = fopen("leave-param.txt", "w") or die("Unable to open file!");
    fwrite($myfile,  $sql_get);
    fclose($myfile);
*/

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("leave_use_before" => $result['leave_use_before']);
    }

    echo json_encode($return_arr);

}


<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');


if ($_POST["action"] === 'GET_BEFORE_DAY') {

    $leave_type_id = $_POST["leave_type_id"];
    $table = $_POST["table"];

    $return_arr = array();

    $sql_get = "SELECT leave_before FROM  " . $table . " WHERE leave_type_id = '" . $leave_type_id ."'";

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("leave_before" => $result['leave_before']);
    }

    echo json_encode($return_arr);

}


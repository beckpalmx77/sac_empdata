<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

$table_name = $_POST["table_name"];

if ($_POST["action"] === 'GET_COUNT_RECORDS') {
    $return_arr = array();
    $sql_get = "SELECT count(*) as record_counts  FROM " . $table_name;
    /*
        $sql_sqlsvr1 = $sql_get . "\n\r";
        $myfile = fopen($table_name . "-1.txt", "w") or die("Unable to open file!");
        fwrite($myfile, "Action: GET_COUNT_RECORDS\n\nSQL Query: " . $sql_sqlsvr1 . "\n\n");
    */

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    $record = 0; // Default value to prevent undefined variable
    foreach ($results as $result) {
        $record = $result['record_counts'];
    }
    /*
        fwrite($myfile, "Record count: " . $record . "\n");
        fclose($myfile);
    */
    echo $record;
}

if ($_POST["action"] === 'GET_COUNT_RECORDS_COND') {
    $cond = trim($_POST["cond"]);
    $return_arr = array();
    $sql_get = "SELECT count(*) as record_counts  FROM " . $table_name . " " . $cond;
    /*
        $sql_sqlsvr2 = $sql_get . "\n\r";
        $myfile = fopen($table_name . "-2.txt", "w") or die("Unable to open file!");
        fwrite($myfile, "Action: GET_COUNT_RECORDS_COND\n\nSQL Query: " . $sql_sqlsvr2 . "\n\n");
    */
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    $record = 0; // Default value to prevent undefined variable
    foreach ($results as $result) {
        $record = $result['record_counts'];
    }
    /*
        fwrite($myfile, "Record count with condition: " . $record . "\n");
        fclose($myfile);
    */
    echo $record;
}

if ($_POST["action"] === 'GET_SUM_RESULT_COND') {
    $field = $_POST["field"];
    $cond = trim($_POST["cond"]);
    $return_arr = array();

    // Check if condition starts with 'WHERE'
    if ($cond !== '') {
        if (stripos($cond, 'where') !== 0) {
            $cond = ' WHERE ' . $cond;
        }
    }

    // SQL query with COALESCE to avoid NULL for sum
    $sql_get = "SELECT COALESCE(SUM($field), 0) as sum_result FROM " . $table_name . " " . $cond;

    /*
        $sql_sqlsvr2 = $sql_get . "\n\r";
        $myfile = fopen($table_name . "-leave-sum.txt", "w") or die("Unable to open file!");
        fwrite($myfile, "Action: GET_SUM_RESULT_COND\n\nSQL Query: " . $sql_sqlsvr2 . "\n\n");
    */
    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    $sum_result = 0; // Default value to ensure no null value
    foreach ($results as $result) {
        $sum_result = $result['sum_result'];
    }
    /*
        fwrite($myfile, "Sum of " . $field . " with condition: " . $sum_result . "\n");
        fclose($myfile);
    */
    echo $sum_result;
}
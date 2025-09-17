<?php

$searchValue = 'somchai'; // <--- สมมติค่าที่ใช้ค้นหา
$searchArray = array();

if ($searchValue != '') {
    $searchQuery = " AND (emp_id LIKE :emp_id or l_name LIKE :l_name or
    f_name LIKE :f_name) ";

    $searchArray = array(
        'emp_id' => "%$searchValue%",
        'l_name' => "%$searchValue%",
        'f_name' => "%$searchValue%"
    );
}

// ใช้ print_r เพื่อแสดงค่าใน Array
print_r($searchArray);

<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');
include('../util/reorder_record.php');

if ($_POST["action"] === 'GET_LEAVE_DETAIL') {

    ## Read value
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    $columnSortOrder = $_POST['order'][0]['dir']; // ASc or desc
    //$columnSortOrder = 'desc'; // ASc or desc
    $searchValue = $_POST['search']['value']; // Search value

    $searchArray = array();

    $doc_date_format = $_POST['doc_date']; // 2025-10-24
    //$doc_date_format = '2025-10-23'; // 2025-10-23

    list($year, $month, $day) = explode('-', $doc_date_format);
    $doc_date = $day . "-" . $month . "-" . $year;  // 24-10-2025

    /*
        $txt = $doc_date ;
        $my_file = fopen("a_doc_leave_select.txt", "w") or die("Unable to open file!");
        fwrite($my_file, $searchValue . " | " .  $txt);
        fclose($my_file);
    */

## Search
    /*
        if ($_SESSION['document_dept_cond']!=="A") {
            $searchQuery = " AND dept_id = '" . $_SESSION['department_id'] . "' ";
        }
    */


    if ($_SESSION['role'] === "SUPERVISOR") {
        $searchQuery = " AND dept_id_approve = '" . $_SESSION['dept_id_approve'] . "' ";
    } else if ($_SESSION['role'] === "HR" || $_SESSION['role'] === "ADMIN") {
        $searchQuery = " ";
    } else {
        $searchQuery = " AND emp_id = '" . $_SESSION['emp_id'] . "' ";
    }


    if ($searchValue != '') {
        $searchQuery = " AND (f_name LIKE :f_name 
        or l_name LIKE :l_name 
        or department_id LIKE :department_id 
        or leave_type_detail LIKE :leave_type_detail        
        or doc_date LIKE :doc_date ) ";
        $searchArray = array(
            'f_name' => "%$searchValue%",
            'l_name' => "%$searchValue%",
            'department_id' => "%$searchValue%",
            'leave_type_detail' => "%$searchValue%",
            'doc_date' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_leave_holiday_calendar WHERE doc_date = '" . $doc_date . "'");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $sql_count_record = "SELECT COUNT(*) AS allcount FROM v_leave_holiday_calendar WHERE doc_date = '" . $doc_date . "' " . $searchQuery;
    $stmt = $conn->prepare($sql_count_record);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

    $sql_get_leave = "SELECT v_leave_holiday_calendar.*,ms.status_doc_desc
            FROM v_leave_holiday_calendar 
            LEFT JOIN mstatus ms on ms.status_doctype = 'LEAVE' AND ms.status_doc_id = v_leave_holiday_calendar.status               
            WHERE v_leave_holiday_calendar.doc_date = '" . $doc_date . "' "
        . $searchQuery . " ORDER BY sort_order " . " LIMIT :limit,:offset";

    /*
                    $txt = $sql_get_leave ;
                    $my_file = fopen("a_leave_select.txt", "w") or die("Unable to open file!");
                    fwrite($my_file, $searchValue. " | " .  $txt);
                    fclose($my_file);
    */

    $stmt = $conn->prepare($sql_get_leave);


    // Bind values
    foreach ($searchArray as $key => $search) {
        $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
    }

    $stmt->bindValue(':limit', (int)$row, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $empRecords = $stmt->fetchAll();
    $data = array();

    $colors = ['DarkRed', 'DarkGreen', 'DarkBlue', 'DarkOrange', 'Indigo', 'DarkSlateGray', 'BlueViolet', 'DarkCyan', 'Chocolate', 'DarkMagenta']; // รายการสีที่ใช้สุ่ม
    $nameColorMap = []; // แมปชื่อกับสี

    foreach ($empRecords as $row) {

        if ($_POST['sub_action'] === "GET_MASTER") {

            // ตรวจสอบว่าชื่อ f_name มีสีที่แมปไว้หรือยัง
            if (!isset($nameColorMap[$row['f_name']])) {
                // สุ่มสีใหม่หากยังไม่มีการแมป
                $nameColorMap[$row['f_name']] = $colors[array_rand($colors)];
            }

            // ใช้สีที่แมปไว้กับชื่อ f_name
            $color_full_name = $nameColorMap[$row['f_name']];

            $leave_type_detail = '<span style="color: ' . $row['color'] . ';">' . $row['leave_type_detail'] . '</span>';

            $data[] = array(
                "id" => $row['id'],
                "doc_id" => $row['doc_id'],
                "doc_date" => $row['doc_date'],
                "doc_year" => $row['doc_year'],
                "emp_id" => $row['emp_id'],
                //"f_name" => $row['f_name'],
                //"l_name" => $row['l_name'],
                "f_name" => '<span style="color: ' . $color_full_name . ';">' . $row['f_name'] . '</span>',
                "l_name" => '<span style="color: ' . $color_full_name . ';">' . $row['l_name'] . '</span>',
                "leave_type_id" => $row['leave_type_id'],
                "leave_type_detail" => $leave_type_detail,
                "date_leave_start" => $row['date_leave_start'],
                "date_leave_to" => $row['date_leave_to'],
                "time_leave_start" => $row['time_leave_start'],
                "time_leave_to" => $row['time_leave_to'],
                "dt_leave_start" => $row['date_leave_start'] . " " . $row['time_leave_start'],
                "dt_leave_to" => $row['date_leave_to'] . " " . $row['time_leave_to'],
                "department_id" => $row['department_id'],
                "department_desc" => $row['department_desc'],
                "remark" => $row['remark'],
                "leave_day" => $row['leave_day'],
                "leave_hour" => $row['leave_hour'],
                "full_name" => '<span style="color: ' . $color_full_name . ';">' . $row['f_name'] . ' ' . $row['l_name'] . '</span>',
                //"full_name" => $row['f_name'] . " " . $row['l_name'],
                "image" => "<button type='button' name='image' id='" . $row['id'] . "' Class='btn btn-secondary btn-xs image' data-picture='" . $row['picture'] . "' data-toggle='tooltip' title='image'>Image</button>",
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' Class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='" . $row['id'] . "' Class='btn btn-danger btn-xs delete' data-toggle='tooltip' title='delete'>Delete</button>",
                "approve" => "<button type='button' name='approve' id='" . $row['id'] . "' Class='btn btn-success btn-xs approve' data-toggle='tooltip' title='Approve'>Approve</button>",
                "status" => $row['status'] === 'A' ? "<div Class='text-success'>" . $row['status_doc_desc'] . "</div>" : "<div Class='text-muted'> " . $row['status_doc_desc'] . "</div>",
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "leave_type_id" => $row['leave_type_id'],
                "leave_type_detail" => $row['leave_type_detail'],
                "SELECT" => "<button type='button' name='SELECT' id='" . $row['leave_type_id'] . "@" . $row['leave_type_detail'] . "' Class='btn btn-outline-success btn-xs SELECT' data-toggle='tooltip' title='SELECT'>SELECT <i Class='fa fa-check' aria-hidden='true'></i>
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




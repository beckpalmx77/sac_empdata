<?php
session_start();
error_reporting(0);

include('../config/config_rabbit.inc');
include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');
include('../util/GetData.php');
include('../util/send_message.php');

$img_path = '/img_doc/';
$valid_extensions = array('jpeg', 'jpg', 'png', 'gif', 'bmp', 'pdf', 'doc', 'ppt'); // valid extensions

if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT dl.*,lt.leave_type_detail,lt.leave_before,ms.status_doc_desc,em.f_name,em.l_name 
            FROM dleave_event dl
            LEFT JOIN mleave_type lt ON lt.leave_type_id = dl.leave_type_id
            LEFT JOIN mstatus ms ON ms.status_doctype = 'LEAVE' AND ms.status_doc_id = dl.status
            LEFT JOIN memployee em ON em.emp_id = dl.emp_id  
            WHERE dl.id = " . $id;

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "doc_id" => $result['doc_id'],
            "doc_date" => $result['doc_date'],
            "doc_year" => $result['doc_year'],
            "leave_type_id" => $result['leave_type_id'],
            "leave_type_detail" => $result['leave_type_detail'],
            "emp_id" => $result['emp_id'],
            "date_leave_start" => $result['date_leave_start'],
            "date_leave_to" => $result['date_leave_to'],
            "time_leave_start" => $result['time_leave_start'],
            "time_leave_to" => $result['time_leave_to'],
            "f_name" => $result['f_name'],
            "l_name" => $result['l_name'],
            "full_name" => $result['f_name'] . " " . $result['l_name'],
            "approve_1_id" => $result['approve_1_id'],
            "approve_1_status" => $result['approve_1_status'],
            "approve_2_id" => $result['approve_2_id'],
            "approve_2_status" => $result['approve_2_status'],
            "leave_before" => $result['leave_before'],
            "leave_day" => $result['leave_day'],
            "leave_hour" => $result['leave_hour'],
            "picture" => $result['picture'],
            "remark" => $result['remark'],
            "status" => $result['status']);
    }
    echo json_encode($return_arr);
}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["leave_type_id"] !== '') {

        $doc_id = $_POST["doc_id"];
        $sql_find = "SELECT * FROM dleave_event WHERE doc_id = '" . $doc_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {

    if ($_POST["doc_date"] !== '' && $_POST["emp_id"] !== '' && $_POST["leave_type_id"] !== '') {

        $table = "dleave_event";
        $dept_id = $_POST["department"];
        $doc_date = $_POST["doc_date"];
        $doc_year = substr($_POST["date_leave_start"], 6);
        $doc_month = substr($_POST["date_leave_start"], 3, 2);
        $filed = "id";
        $currentDate = date('d-m-Y');

        $sql_get_work_age = "SELECT DATEDIFF(NOW(), STR_TO_DATE(em.start_work_date, '%d-%m-%Y')) AS data FROM memployee em WHERE em.emp_id = '" . $_POST["emp_id"] . "'";
        $work_age = GET_VALUE($conn, $sql_get_work_age);

        /*
                $myfile = fopen("emp-param_work.txt", "w") or die("Unable to open file!");
                fwrite($myfile, $work_age . " | " . $sql_get_work_age);
                fclose($myfile);
        */

        $sql_get_dept = "SELECT mp.dept_ids AS data FROM memployee em LEFT JOIN mdepartment mp ON mp.department_id = em.dept_id WHERE em.emp_id = '" . $_POST["emp_id"] . "'";

        $dept_id_save = GET_VALUE($conn, $sql_get_dept);

        $sql_get_dept_desc = "SELECT mp.department_desc AS data FROM memployee em LEFT JOIN mdepartment mp ON mp.department_id = em.dept_id WHERE em.emp_id = '" . $_POST["emp_id"] . "'";

        $dept_desc = GET_VALUE($conn, $sql_get_dept_desc);

        $emp_full_name = $_POST["full_name"];

        $leave_type_desc = $_POST["leave_type_detail"];

        $condition = " WHERE doc_year = '" . $doc_year . "' AND doc_month = '" . $doc_month . "' AND dept_id = '" . $_SESSION['department_id'] . "'";

        $last_number = LAST_DOCUMENT_NUMBER($conn, $filed, $table, $condition);

        $doc_id = "L-" . $_SESSION['dept_id_approve'] . "-" . substr($doc_date, 3) . "-" . sprintf('%04s', $last_number);

        /*
        $myfile = fopen("emp-param.txt", "w") or die("Unable to open file!");
        fwrite($myfile,  $currentDate . " | " . $sql_get_work_date . " | " . $start_work_date);
        fclose($myfile);
        */

        $leave_type_id = $_POST["leave_type_id"];
        $emp_id = $_POST["emp_id"];
        $date_leave_start = $_POST["date_leave_start"];
        $time_leave_start = $_POST["time_leave_start"];
        $date_leave_to = $_POST["date_leave_to"];
        $time_leave_to = $_POST["time_leave_to"];

        $leave_day = !empty($_POST["leave_day"]) ? $_POST["leave_day"] : 0;
        $leave_hour = !empty($_POST["leave_hour"]) ? $_POST["leave_hour"] : 0;

        $remark = $_POST["remark"];

        $create_by = $_SESSION['alogin'];

        $sql_get_max = "SELECT day_max AS data FROM mleave_type WHERE leave_type_id ='" . $leave_type_id . "'";

        $day_max = GET_VALUE($conn, $sql_get_max);

        $sql_leave_type = "SELECT leave_type_detail AS data FROM mleave_type WHERE leave_type_id ='" . $leave_type_id . "'";

        $leave_type_desc = GET_VALUE($conn, $sql_leave_type);

        $table = "v_dleave_event";

        $cnt_day = "";
        $sql_cnt = "SELECT SUM(leave_day) AS days , SUM(leave_hour) AS hours FROM " . $table
            . " WHERE status <> 'R' AND doc_year = '" . $doc_year . "' AND leave_type_id = '" . $leave_type_id . "' AND emp_id = '" . $emp_id . "'";
        foreach ($conn->query($sql_cnt) as $row) {
            $cnt_day = $row['days'];
            $cnt_hour = $row['hours'];
        }

        $cnt_day = $cnt_day + (float)$leave_day;
        $cnt_hour = $cnt_hour + (float)$leave_hour;

        $leave_save = "Y";

        $day_hour_max = ($day_max * 8);
        $cnt_total_day_hour = ($cnt_day * 8) + $cnt_hour;

        /*
                $txt = "Leave Type = " . $leave_type_id . " Max = " . $day_max . " | Count = " . $cnt_day . " | " . $sql_cnt . " | "
                    . $leave_save . " | " . $work_age . " | hour = " . $day_max * 8 . " | cnt_hour = " . $cnt_hour . " | cnt_total_day_hour = " . $cnt_total_day_hour;
                $myfile = fopen("a-emp-param.txt", "w") or die("Unable to open file!");
                fwrite($myfile, $txt);
                fclose($myfile);
        */

        if ($leave_type_id === 'L3' && ($cnt_total_day_hour > $day_hour_max || $work_age < 365)) {
            $leave_save = "N";
            echo $Error_Over1;
        } else if ($leave_type_id !== 'L3' && $cnt_total_day_hour > $day_hour_max) {
            $leave_save = "N";
            echo $Error_Over2;
        }

        $filename = "";
        $picture = "";

        if (!empty($_FILES['image_upload']['name'])) {
            $uploadDir = "../img_doc/";
            $filename = time() . "_" . basename($_FILES['image_upload']['name']);
            $uploadFile = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['image_upload']['tmp_name'], $uploadFile)) {
                $picture = $filename;
                echo "บันทึกสำเร็จ: " . $filename;
            } else {
                echo "อัปโหลดไฟล์ล้มเหลว";
            }
        } else {
            echo "ไม่มีไฟล์ที่ถูกอัปโหลด";
        }

        if ($leave_save === 'Y') {
            //$sql_find = "SELECT * FROM dleave_event dl WHERE dl.date_leave_start = :date_leave_start AND dl.emp_id = :emp_id";
            $sql_find = "SELECT * FROM dleave_event dl WHERE dl.date_leave_start = :date_leave_start AND dl.status not in ('R') AND dl.emp_id = :emp_id";
            $query_find = $conn->prepare($sql_find);
            $query_find->bindParam(':date_leave_start', $date_leave_start, PDO::PARAM_STR);
            $query_find->bindParam(':emp_id', $emp_id, PDO::PARAM_STR);
            $query_find->execute();
            $nRows = $query_find->fetchColumn();

            if ($nRows > 0) {
                echo $dup;
            } else {
                $sql = "INSERT INTO dleave_event (doc_id, doc_year, doc_month, dept_id, doc_date, leave_type_id, emp_id, date_leave_start, time_leave_start, date_leave_to, time_leave_to, remark, picture, leave_day,leave_hour,create_by) 
                VALUES (:doc_id, :doc_year, :doc_month, :dept_id, :doc_date, :leave_type_id, :emp_id, :date_leave_start, :time_leave_start, :date_leave_to, :time_leave_to, :remark, :picture, :leave_day,:leave_hour,:create_by)";
                $query = $conn->prepare($sql);
                $query->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);
                $query->bindParam(':doc_year', $doc_year, PDO::PARAM_STR);
                $query->bindParam(':doc_month', $doc_month, PDO::PARAM_STR);
                $query->bindParam(':dept_id', $_SESSION['department_id'], PDO::PARAM_STR);
                $query->bindParam(':doc_date', $doc_date, PDO::PARAM_STR);
                $query->bindParam(':leave_type_id', $leave_type_id, PDO::PARAM_STR);
                $query->bindParam(':emp_id', $emp_id, PDO::PARAM_STR);
                $query->bindParam(':date_leave_start', $date_leave_start, PDO::PARAM_STR);
                $query->bindParam(':time_leave_start', $time_leave_start, PDO::PARAM_STR);
                $query->bindParam(':date_leave_to', $date_leave_to, PDO::PARAM_STR);
                $query->bindParam(':time_leave_to', $time_leave_to, PDO::PARAM_STR);
                $query->bindParam(':remark', $remark, PDO::PARAM_STR);
                $query->bindParam(':picture', $picture, PDO::PARAM_STR);
                $query->bindParam(':leave_day', $leave_day, PDO::PARAM_STR);
                $query->bindParam(':leave_hour', $leave_hour, PDO::PARAM_STR);
                $query->bindParam(':create_by', $create_by, PDO::PARAM_STR);
                $query->execute();
                $lastInsertId = $conn->lastInsertId();

                if ($lastInsertId) {
                    $sToken = "";
                    $sToken = "gf0Sx2unVFgz7u81vqrU6wcUA2XLLVoPOo2d0Dlvdlr";
                    /*
                                        $sMessage = "มีเอกสารการลา " . $leave_type_desc
                                            . "\n\r" . "เลขที่เอกสาร = " . $doc_id . " วันที่เอกสาร = " . $doc_date
                                            . "\n\r" . "วันที่ขอลา : " . $date_leave_start . " - " . $time_leave_start . " ถึง : " . $date_leave_to . " - " . $time_leave_to
                                            . "\n\r" . "ผู้ขอ : " . $emp_full_name . " " . $dept_desc;
                    */

                    $sMessage = "🌟 เอกสารการลา: " . $leave_type_desc . "\n\n";
                    $sMessage .= "🔖 เลขที่เอกสาร: " . $doc_id . "\n";
                    $sMessage .= "📅 วันที่เอกสาร: " . $doc_date . "\n\n";
                    $sMessage .= "📅 วันที่ขอลา: " . $date_leave_start . " - " . $time_leave_start . " ถึง " . $date_leave_to . " - " . $time_leave_to . "\n\n";
                    $sMessage .= "👤 ผู้ขอ: " . $emp_full_name . "\n";
                    $sMessage .= "🏢 แผนก: " . $dept_desc . "\n";

                    echo $sMessage;

                    // ดึง line_api_token สำหรับ doc_type = 'HR'
                    $stmt = $conn->prepare("SELECT line_api_token FROM aline_api WHERE doc_type = 'HR' ");
                    $stmt->execute();
                    $line_api_tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // ดึง user_id จาก ims_line_hr_users
                    $stmt = $conn->prepare("SELECT user_id FROM ims_line_hr_users WHERE status = 'Y' ");
                    $stmt->execute();
                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (!empty($users) && !empty($line_api_tokens)) {
                        foreach ($line_api_tokens as $line_api_token) {
                            $channelAccessToken = $line_api_token['line_api_token'];
                            foreach ($users as $user) {
                                $userId = $user['user_id'];
                                sendLineMessage($conn, $channelAccessToken, $userId, $sMessage);
                            }
                        }
                    } else {
                        error_log("No users or no line_api_tokens found.");
                    }

                    echo $save_success;
                } else {
                    echo $error;
                }
            }
        }

    } else {
        echo $error;
    }
}


if ($_POST["action"] === 'UPDATE') {

    if ($_POST["doc_id"] != '') {
        $id = $_POST["id"];
        $doc_id = $_POST["doc_id"];
        $doc_date = $_POST["doc_date"];
        $doc_year = substr($_POST["date_leave_start"], 6);
        $dept_id = $_POST["department"];
        $leave_type_id = $_POST["leave_type_id"];
        $emp_id = $_POST["emp_id"];
        $date_leave_start = $_POST["date_leave_start"];
        $time_leave_start = $_POST["time_leave_start"];
        $date_leave_to = $_POST["date_leave_to"];
        $time_leave_to = $_POST["time_leave_to"];
        $leave_day = !empty($_POST["leave_day"]) ? $_POST["leave_day"] : 0;
        $leave_hour = !empty($_POST["leave_hour"]) ? $_POST["leave_hour"] : 0;
        $remark = $_POST["remark"];
        $status = $_POST["status"];

        $datetime_leave_start_cal = substr($date_leave_start, 6) . "-" . substr($date_leave_start, 3, 2) . "-" . substr($date_leave_start, 0, 2) . " " . $time_leave_start;
        $datetime_leave_to_cal = substr($date_leave_to, 6) . "-" . substr($date_leave_to, 3, 2) . "-" . substr($date_leave_to, 0, 2) . " " . $time_leave_to;

        $sql_time = "SELECT TIMEDIFF('" . $datetime_leave_to_cal . "','" . $datetime_leave_start_cal . "') AS total_time ";
        foreach ($conn->query($sql_time) as $row) {
            $total_time = $row['total_time'];
        }

        /*
        $myfile = fopen("time1-param.txt", "w") or die("Unable to open file!");
        fwrite($myfile,  $datetime_leave_start_cal . " | " . $datetime_leave_to_cal);
        fclose($myfile); */

        //$total_time = Calculate_Time($datetime_leave_start_cal,$datetime_leave_to_cal);

        //$myfile = fopen("time2-param.txt", "w") or die("Unable to open file!");
        //fwrite($myfile,  $datetime_leave_start_cal . " | " . $datetime_leave_to_cal . " | " . $total_time);
        //fclose($myfile);

        $sql_find = "SELECT * FROM dleave_event WHERE doc_id = '" . $doc_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {

            if ($_SESSION['approve_permission'] === "Y") {
                $sql_update = "UPDATE dleave_event SET status=:status,leave_type_id=:leave_type_id
                ,date_leave_start=:date_leave_start,date_leave_to=:date_leave_to
                ,time_leave_start=:time_leave_start,time_leave_to=:time_leave_to,remark=:remark,doc_year=:doc_year,total_time=:total_time     
                ,emp_id=:emp_id,leave_day=:leave_day,leave_hour=:leave_hour
                WHERE id = :id";

                //$myfile = fopen("update_sql1.txt", "w") or die("Unable to open file!");
                //fwrite($myfile,$sql_update);
                //fclose($myfile);

                $query = $conn->prepare($sql_update);
                $query->bindParam(':status', $status, PDO::PARAM_STR);
                $query->bindParam(':leave_type_id', $leave_type_id, PDO::PARAM_STR);
                $query->bindParam(':date_leave_start', $date_leave_start, PDO::PARAM_STR);
                $query->bindParam(':date_leave_to', $date_leave_to, PDO::PARAM_STR);
                $query->bindParam(':time_leave_start', $time_leave_start, PDO::PARAM_STR);
                $query->bindParam(':time_leave_to', $time_leave_to, PDO::PARAM_STR);
                $query->bindParam(':remark', $remark, PDO::PARAM_STR);
                $query->bindParam(':doc_year', $doc_year, PDO::PARAM_STR);
                $query->bindParam(':total_time', $total_time, PDO::PARAM_STR);
                $query->bindParam(':emp_id', $emp_id, PDO::PARAM_STR);
                $query->bindParam(':leave_day', $leave_day, PDO::PARAM_STR);
                $query->bindParam(':leave_hour', $leave_hour, PDO::PARAM_STR);
                $query->bindParam(':id', $id, PDO::PARAM_STR);
                $query->execute();
                echo $save_success;
            } else {
                $sql_update = "UPDATE dleave_event SET remark=:remark WHERE id = :id";
                $query = $conn->prepare($sql_update);
                $query->bindParam(':remark', $remark, PDO::PARAM_STR);
                $query->bindParam(':id', $id, PDO::PARAM_STR);
                $query->execute();
                echo $save_success;
            }
        }

    } else {
        echo $Approve_Success;
    }

}

if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM dleave_event WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM dleave_event WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_LEAVE_DOCUMENT') {

    ## Read value
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    //$columnSortOrder = $_POST['order'][0]['dir']; // ASc or desc
    $columnSortOrder = 'desc'; // ASc or desc
    $searchValue = $_POST['search']['value']; // Search value

    $searchArray = array();

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
        $searchQuery = " AND (dl.f_name LIKE :f_name 
        or dl.l_name LIKE :l_name 
        or dl.department_id LIKE :department_id 
        or dl.leave_type_detail LIKE :leave_type_detail        
        or dl.doc_date LIKE :doc_date ) ";
        $searchArray = array(
            'f_name' => "%$searchValue%",
            'l_name' => "%$searchValue%",
            'department_id' => "%$searchValue%",
            'leave_type_detail' => "%$searchValue%",
            'doc_date' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_dleave_event dl WHERE dl.leave_type_id <> 'L2' ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $sql_count_record = "SELECT COUNT(*) AS allcount FROM v_dleave_event dl WHERE dl.leave_type_id <> 'L2' " . $searchQuery;
    $stmt = $conn->prepare($sql_count_record);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

    /*
            $txt = $sql_count_record ;
            $my_file = fopen("leave_select_count.txt", "w") or die("Unable to open file!");
            fwrite($my_file, $searchValue . " | " . $txt);
            fclose($my_file);
    */


## Fetch records
    // ,em.f_name,em.l_name,em.department_id

    $sql_get_leave = "SELECT dl.*,lt.leave_type_detail,ms.status_doc_desc,lt.color  
            FROM v_dleave_event dl
            LEFT JOIN mleave_type lt on lt.leave_type_id = dl.leave_type_id
            LEFT JOIN mstatus ms on ms.status_doctype = 'LEAVE' AND ms.status_doc_id = dl.status              
            WHERE dl.leave_type_id <> 'L2' " . $searchQuery . " ORDER BY id desc , " . $columnName . " " . $columnSortOrder . " LIMIT :limit,:offset";

    /*
                $txt = $sql_get_leave ;
                $my_file = fopen("leave_select.txt", "w") or die("Unable to open file!");
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

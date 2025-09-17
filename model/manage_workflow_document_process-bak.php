<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');
include('../util/reorder_record.php');

if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];

    $return_arr = array();

    $sql_get = "SELECT em.*,mt.work_time_detail FROM memployee em            
            left join mwork_time mt on mt.work_time_id = em.work_time_id  
            WHERE em.id = " . $id;

    $statement = $conn->query($sql_get);
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = array("id" => $result['id'],
            "emp_id" => $result['emp_id'],
            "f_name" => $result['f_name'],
            "l_name" => $result['l_name'],
            "sex" => $result['sex'],
            "start_work_date" => $result['start_work_date'],
            "dept_id" => $result['dept_id'],
            "department_id" => $result['department_id'],
            "work_time_id" => $result['work_time_id'],
            "work_time_detail" => $result['work_time_detail'],
            "prefix" => $result['prefix'],
            "nick_name" => $result['nick_name'],
            "dept_id_approve" => $result['dept_id_approve'],
            "remark" => $result['remark'],
            "position" => $result['position'],
            "week_holiday" => $result['week_holiday'],
            "status" => $result['status']);
    }
    echo json_encode($return_arr);
}


if ($_POST["action"] === 'GET_SELECT_EMP_DATA') {
    $branch = isset($_POST['branch']) ? $_POST['branch'] : '';

    $query = "SELECT emp_id,CONCAT(f_name, '-', l_name) AS fullname  
             FROM memployee WHERE memployee.status = 'Y' ";
    $query .= " AND branch = :branch";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':branch', $branch);
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($employees as $row) {
        echo '<option value="' . $row['fullname'] . '">' . $row['fullname'] . '</option>';
    }
}

if ($_POST["action"] === 'GET_SELECT_EMP_BY_DEPT') {

    $document_dept_cond = isset($_POST['document_dept_cond']) ? $_POST['document_dept_cond'] : '';
    $dept_id_approve = isset($_POST['dept_id_approve']) ? $_POST['dept_id_approve'] : '';
    $emp_id = isset($_POST['emp_id']) ? $_POST['emp_id'] : '';

    $query = "SELECT emp_id, CONCAT(f_name, '   ', l_name) AS fullname FROM memployee WHERE status = 'Y' ";

    if ($document_dept_cond === 'A') {
        $con_query = $query . " AND dept_id_approve = :dept_id_approve";
        $stmt = $conn->prepare($con_query);
        $stmt->bindParam(':dept_id_approve', $dept_id_approve);
    } else {
        $con_query = $query . " AND emp_id = :emp_id";
        $stmt = $conn->prepare($con_query);
        $stmt->bindParam(':emp_id', $_SESSION['emp_id']);
    }

    if ($_SESSION['role'] === 'ADMIN' || $_SESSION['role'] === 'HR') {
        $con_query = $query . " AND (branch <> 'XXX' AND branch NOT LIKE 'CP%') ";
        $stmt = $conn->prepare($con_query);
    }
    /*
        $txt = $document_dept_cond . " | " . $dept_id_approve . " | " . $emp_id . " | " . $con_query;
        $my_file = fopen("leave_1.txt", "w") or die("Unable to open file!");
        fwrite($my_file, $txt);
        fclose($my_file);
    */
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($employees as $row) {
        echo '<option value="' . $row['emp_id'] . '">' . $row['fullname'] . '</option>';
    }
}

if ($_POST["action"] === 'SEARCH') {

    if ($_POST["l_name"] !== '') {

        $emp_id = $_POST["emp_id"];
        $sql_find = "SELECT * FROM memployee WHERE emp_id = '" . $emp_id . "'";
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo 2;
        } else {
            echo 1;
        }
    }
}

if ($_POST["action"] === 'ADD') {
    if ($_POST["f_name"] !== '' && $_POST["emp_id"] !== '') {
        //$emp_id = $dept_id . "-" . substr($f_name, 6) . "-" . sprintf('%04s', LAST_ID($conn, "memployee", 'id'));
        $emp_id = $_POST["emp_id"];
        $f_name = $_POST["f_name"];
        $l_name = $_POST["l_name"];
        $dept_id = $_POST["dept_id"];
        $work_time_id = $_POST["work_time_id"];
        $remark = $_POST["remark"];
        $sex = $_POST["sex"];
        $prefix = $_POST["prefix"];
        $nick_name = $_POST["nick_name"];
        $position = $_POST["position"];
        $start_work_date = $_POST["start_work_date"];

        switch ($dept_id) {
            case "181":
                $branch = "CP1";
                break;
            case "182":
                $branch = "CP3";
                break;
            case "183":
                $branch = "CP2";
                break;
            case "184":
                $branch = "CP4";
                break;
        }

        $sql_find = "SELECT * FROM memployee WHERE emp_id = '" . $emp_id . "'";

        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo $dup;
        } else {
            $sql = "INSERT INTO memployee (emp_id,f_name,l_name,work_time_id,dept_id,remark,email_address,sex,prefix,nick_name,position,start_work_date,branch) 
                    VALUES (:emp_id,:f_name,:l_name,:work_time_id,:dept_id,:remark,:email_address,:sex,:prefix,:nick_name,:position,:start_work_date,branch)";

            $query = $conn->prepare($sql);
            $query->bindParam(':emp_id', $emp_id, PDO::PARAM_STR);
            $query->bindParam(':f_name', $f_name, PDO::PARAM_STR);
            $query->bindParam(':l_name', $l_name, PDO::PARAM_STR);
            $query->bindParam(':work_time_id', $work_time_id, PDO::PARAM_STR);
            $query->bindParam(':dept_id', $dept_id, PDO::PARAM_STR);
            $query->bindParam(':remark', $remark, PDO::PARAM_STR);
            $query->bindParam(':email_address', $email, PDO::PARAM_STR);
            $query->bindParam(':sex', $sex, PDO::PARAM_STR);
            $query->bindParam(':prefix', $prefix, PDO::PARAM_STR);
            $query->bindParam(':nick_name', $nick_name, PDO::PARAM_STR);
            $query->bindParam(':position', $position, PDO::PARAM_STR);
            $query->bindParam(':start_work_date', $start_work_date, PDO::PARAM_STR);
            $query->execute();
            $lastInsertId = $conn->lastInsertId();
            if ($lastInsertId) {
                $sql_user = "INSERT INTO ims_user (emp_id,user_id,first_name,last_name,password,department_id,account_type,picture,company,email) 
                    VALUES (:emp_id,:user_id,:first_name,:last_name,:password,:dept_id,:account_type,:user_picture,:company,:email)";
                $query_user = $conn->prepare($sql_user);
                $query_user->bindParam(':emp_id', $emp_id, PDO::PARAM_STR);
                $query_user->bindParam(':user_id', $emp_id, PDO::PARAM_STR);
                $query_user->bindParam(':first_name', $f_name, PDO::PARAM_STR);
                $query_user->bindParam(':last_name', $l_name, PDO::PARAM_STR);
                $query_user->bindParam(':password', $user_password, PDO::PARAM_STR);
                $query_user->bindParam(':dept_id', $dept_id, PDO::PARAM_STR);
                $query_user->bindParam(':account_type', $account_type_default, PDO::PARAM_STR);
                $query_user->bindParam(':user_picture', $user_picture, PDO::PARAM_STR);
                $query_user->bindParam(':company', $company, PDO::PARAM_STR);
                $query_user->bindParam(':email', $email, PDO::PARAM_STR);
                $query_user->execute();
                $lastInsertUser = $conn->lastInsertId();
                if ($lastInsertUser) {
                    Reorder_Record($conn, "ims_user");
                    echo $save_success;
                }
            } else {
                echo $error;
            }
        }
    }
}


if ($_POST["action"] === 'UPDATE') {

    if (!empty($_POST["emp_id"])) {

        try {
            // เริ่ม Transaction
            $conn->beginTransaction();

            $id = $_POST["id"];
            $emp_id = $_POST["emp_id"];
            $f_name = $_POST["f_name"];
            $l_name = $_POST["l_name"];
            $dept_id = $_POST["dept_id"];
            $work_time_id = $_POST["work_time_id"];
            $remark = $_POST["remark"];
            $sex = $_POST["sex"];
            $prefix = $_POST["prefix"];
            $nick_name = $_POST["nick_name"];
            $position = $_POST["position"];
            $week_holiday = $_POST["week_holiday"];
            $dept_id_approve = $_POST["dept_id_approve"];

            // 1. ตรวจสอบข้อมูลพนักงาน (ใช้ Prepared Statement เพื่อความปลอดภัย)
            $sql_find = "SELECT COUNT(*) FROM memployee WHERE emp_id = :emp_id";
            $stmt_find = $conn->prepare($sql_find);
            $stmt_find->bindParam(':emp_id', $emp_id, PDO::PARAM_STR);
            $stmt_find->execute();
            $nRows = $stmt_find->fetchColumn();

            if ($nRows > 0) {
                // 2. อัปเดตตาราง memployee
                $sql_update_memployee = "UPDATE memployee SET week_holiday=:week_holiday, dept_id_approve=:dept_id_approve WHERE id = :id";
                $query_memployee = $conn->prepare($sql_update_memployee);
                $query_memployee->bindParam(':week_holiday', $week_holiday, PDO::PARAM_STR);
                $query_memployee->bindParam(':dept_id_approve', $dept_id_approve, PDO::PARAM_STR);
                $query_memployee->bindParam(':id', $id, PDO::PARAM_INT); // ID ควรเป็น INT
                $query_memployee->execute();

                // 3. อัปเดตตาราง ims_user
                $sql_update_user = "UPDATE ims_user SET first_name=:f_name, last_name=:l_name, department_id=:dept_id, dept_id_approve=:dept_id_approve WHERE emp_id = :emp_id";
                $query_user = $conn->prepare($sql_update_user);
                $query_user->bindParam(':f_name', $f_name, PDO::PARAM_STR);
                $query_user->bindParam(':l_name', $l_name, PDO::PARAM_STR);
                $query_user->bindParam(':dept_id', $dept_id, PDO::PARAM_STR);
                $query_user->bindParam(':dept_id_approve', $dept_id_approve, PDO::PARAM_STR);
                $query_user->bindParam(':emp_id', $emp_id, PDO::PARAM_STR);
                $query_user->execute();

                // ถ้าทุกอย่างสำเร็จ ให้ Commit Transaction
                $conn->commit();

                // ส่งข้อความกลับไป
                echo $save_success;

            } else {
                // ถ้าไม่พบ emp_id
                echo "ไม่พบรหัสพนักงานนี้ในระบบ";
            }

        } catch (PDOException $e) {
            // ถ้ามีข้อผิดพลาดเกิดขึ้น ให้ Rollback Transaction
            $conn->rollBack();
            // ส่งข้อความ error กลับไป (เพื่อการ debug)
            // สำหรับใช้งานจริง อาจจะเปลี่ยนเป็นข้อความที่เป็นมิตรกับผู้ใช้
            echo "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    } else {
        echo "ไม่พบรหัสพนักงาน (emp_id)";
    }
}

if ($_POST["action"] === 'DELETE') {

    $id = $_POST["id"];

    $sql_find = "SELECT * FROM memployee WHERE id = " . $id;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM memployee WHERE id = " . $id;
            $query = $conn->prepare($sql);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }
}

if ($_POST["action"] === 'GET_APPROVE_EMP') {

    ## Read value
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    //$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    $columnSortOrder = 'desc'; // asc or desc
    $searchValue = $_POST['search']['value']; // Search value

    $searchArray = array();

## Search
    $searchQuery = " ";
    //if ($_POST["page_manage"]!=="ADMIN") {
    //$searchQuery = " AND emp_id = '" . $_SESSION['emp_id'] . "'";
    //}


// กำหนดค่าเริ่มต้น
    $searchQuery = "";
    $searchArray = array();

// ตรวจสอบว่ามีค่าที่ใช้ค้นหาหรือไม่
    if (!empty($searchValue)) {
        // สร้างส่วนของ SQL query โดยใช้ named placeholders
        $searchQuery = " AND (em.emp_id LIKE '%" . $searchValue . "%' OR em.f_name LIKE '%" . $searchValue . "%') ";
    }

    /*
        if ($searchValue != '') {
            $searchQuery = " AND (em.emp_id LIKE :emp_id or
            em.f_name LIKE :f_name ) ";
            $searchArray = array(
                'emp_id' => "%$searchValue%",
                'f_name' => "%$searchValue%",
            );
        }
    */

    /*
    $myfile = fopen("a-param.txt", "w") or die("Unable to open file!");
    fwrite($myfile, $searchQuery . " | " . $searchValue);
    fclose($myfile);
    */

## Total number of records without filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM memployee where status =  'Y' ");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM memployee where status =  'Y' ");
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    /*
        $myfile = fopen("b-param.txt", "w") or die("Unable to open file!");
        fwrite($myfile, $searchQuery);
        fclose($myfile);
    */
    $sql_getdata = "SELECT em.*,mt.work_time_detail,dp.department_desc,us.first_name,us.last_name
    FROM memployee em
    left join mwork_time mt on mt.work_time_id = em.work_time_id
    left join mdepartment dp on dp.department_id = em.dept_id
    left join v_ims_user_supervisor us on em.dept_id_approve = us.dept_id_approve and us.role = 'SUPERVISOR' and us.status = 'Active'
    WHERE 1 and em.status =  'Y' " . $searchQuery
        . " ORDER BY em.emp_id DESC " . " LIMIT :limit,:offset";

    $stmt = $conn->prepare($sql_getdata);

    /*
        $myfile = fopen("b-param.txt", "w") or die("Unable to open file!");
        fwrite($myfile, $sql_getdata . " | " . $searchQuery);
        fclose($myfile);
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

            $approve_name = $row['first_name'] . " " . $row['last_name'];

            $data[] = array(
                "id" => $row['id'],
                "emp_id" => $row['emp_id'],
                "f_name" => $row['f_name'],
                "l_name" => $row['l_name'],
                "approve_name" => $approve_name,
                "nick_name" => $row['nick_name'],
                "prefix" => $row['prefix'],
                "sex" => $row['sex'],
                "full_name" => $row['f_name'] . " " . $row['l_name'],
                "dept_id" => $row['dept_id'],
                "department_id" => $row['department_id'],
                "department_desc" => $row['department_desc'],
                "work_time_id" => $row['work_time_id'],
                "work_time_detail" => $row['work_time_detail'],
                "start_work_date" => $row['start_work_date'],
                "week_holiday" => $row['week_holiday'],
                "dept_id_approve" => $row['dept_id_approve'],
                "detail" => "<button type='button' name='detail' emp_id='" . $row['emp_id'] . "' class='btn btn-info btn-xs detail' data-toggle='tooltip' title='Detail'>Detail</button>",
                "update" => "<button type='button' name='update' id='" . $row['id'] . "' class='btn btn-info btn-xs update' data-toggle='tooltip' title='Update'>Update</button>",
                "approve" => "<button type='button' name='approve' id='" . $row['id'] . "' class='btn btn-success btn-xs approve' data-toggle='tooltip' title='Approve'>Approve</button>",
                "status" => $row['status'] === 'A' ? "<div class='text-success'>" . $row['status'] . "</div>" : "<div class='text-muted'> " . $row['status'] . "</div>",
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "dept_id" => $row['dept_id'],
                "department_id" => $row['department_id'],
                "select" => "<button type='button' name='select' id='" . $row['department_id'] . "@" . $row['dept_id'] . "' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='select'>select <i class='fa fa-check' aria-hidden='true'></i>
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

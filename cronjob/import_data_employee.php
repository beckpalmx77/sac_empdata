<?php

ini_set('display_errors', 1);
error_reporting(~0);

include("../config/connect_sqlserver.php");
include("../config/connect_db.php");
include("../util/record_util.php");

$previous_year = date("Y") - 2;

$previous_year = "2000";

$sql_sqlsvr = "SELECT EMP_KEY,EMP_INTL,EMPFILE.EMP_NAME,EMPFILE.EMP_SURNME,EMPFILE.EMP_GENDER,EMPFILE.EMP_EMAIL
,PERSONALINFO.PRS_SC_D,PAYROLLINFO.PRI_SALARY ,PERSONALINFO.PRS_DEPT
,PERSONALINFO.PRS_JBT,DEPTTAB.DEPT_THAIDESC,JOBTITLE.JBT_THAIDESC
,PAYROLLINFO.PRI_STATUS,EMPFILE.EMP_BIRTH,PERSONALINFO.PRS_NO
FROM EMPFILE 
LEFT JOIN PAYROLLINFO ON PAYROLLINFO.PRI_EMP = EMPFILE.EMP_KEY
LEFT JOIN PERSONALINFO ON PERSONALINFO.PRS_EMP = EMPFILE.EMP_KEY
LEFT JOIN DEPTTAB ON DEPTTAB.DEPT_KEY = PERSONALINFO.PRS_DEPT
LEFT JOIN JOBTITLE ON JOBTITLE.JBT_KEY = PERSONALINFO.PRS_JBT
WHERE YEAR(PERSONALINFO.PRS_SC_D) >= " . $previous_year
    . " ORDER BY PERSONALINFO.PRS_DEPT DESC  ";

//$myfile = fopen("qry_file1.txt", "w") or die("Unable to open file!");
//fwrite($myfile, $sql_sqlsvr);
//fclose($myfile);


$company = "SAC";
$email_address = "@sac.com";
$work_time_id = "S001";
$password = '$2y$10$F75vk7nW95vHpCYo86RUQOOhnEiVZ693ZPps5S1c96xh5SxWgPXea';
$picture = 'img/icon/admin-001.png';
$status_u = '';
$approve_level = "-";
$approve_permission = "N";
$lang = "th";
$account_type = "user";
$permission_price = "-";
$document_dept_cond = "-";

$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute();

$return_arr = array();

while ($result_sqlsvr = $stmt_sqlsvr->fetch(PDO::FETCH_ASSOC)) {

    $sex = $result_sqlsvr["EMP_GENDER"] == "1" ? "M" : "F";
    $status = $result_sqlsvr["PRI_STATUS"] == "1" ? "Y" : "N";
    $status_u = $result_sqlsvr["PRI_STATUS"] == "1" ? "Active" : "Inactive";

/*
    switch ($result_sqlsvr["PRS_DEPT"]) {
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
        default:
            $branch = "-";
            break;
    }
*/

    switch ($result_sqlsvr["PRS_DEPT"]) {

        case "132":
            $dept_id_approve = "WHL";
            $branch = "-";
            break;
        case "139":
            $dept_id_approve = "OFF";
            $branch = "-";
            break;
        case "156":
            $dept_id_approve = "WHL";
            $branch = "-";
            break;
        case "160":
            $dept_id_approve = "GPH";
            $branch = "-";
            break;
        case "162":
            $dept_id_approve = "ACC";
            $branch = "-";
            break;
        case "163":
            $dept_id_approve = "SL1";
            $branch = "-";
            break;
        case "164":
            $dept_id_approve = "SL2";
            $branch = "-";
            break;
        case "172":
            $dept_id_approve = "RDQ";
            $branch = "-";
            break;
        case "180":
            $dept_id_approve = "BTC";
            $branch = "-";
            break;
        case "181":
            $dept_id_approve = "CP1";
            $branch = "CP1";
            break;
        case "182":
            $dept_id_approve = "CP3";
            $branch = "CP3";
            break;
        case "183":
            $dept_id_approve = "CP2";
            $branch = "CP2";
            break;
        case "184":
            $dept_id_approve = "CP4";
            $branch = "CP4";
            break;
        default:
            $dept_id_approve = "-";
            $branch = "-";
            break;

    }

    if ($result_sqlsvr["PRS_NO"]==='81352') {
        $dept_id_approve = 'IT';
    }

    echo "Check UPDATE Employee : " . $result_sqlsvr["PRS_NO"] . "|" . $dept_id_approve . "\n\r";


    $birth_str = $result_sqlsvr["EMP_BIRTH"] == "" ? "0000-00-00" : $result_sqlsvr["EMP_BIRTH"];
    $birth = substr($birth_str, 8, 2) . "-" . substr($birth_str, 5, 2) . "-" . substr($birth_str, 0, 4);

    $start_work_date_str = $result_sqlsvr["PRS_SC_D"] == "" ? "0000-00-00" : $result_sqlsvr["PRS_SC_D"];
    $start_work_date = substr($start_work_date_str, 8, 2) . "-" . substr($start_work_date_str, 5, 2) . "-" . substr($start_work_date_str, 0, 4);

    $sql_find = "SELECT * FROM memployee WHERE emp_id = '" . $result_sqlsvr["PRS_NO"] . "'";
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {

        if ($result_sqlsvr["PRS_NO"]==='81055') {
            echo "UPDATE Employee : " . $result_sqlsvr["PRS_NO"] . "|" . $dept_id_approve . "\n\r";
        }
        $sql = "UPDATE memployee SET position_id=:position_id,position=:position,dept_id=:dept_id,department_id=:department_id,
        status=:status,work_time_id=:work_time_id,birthday=:birthday,start_work_date=:start_work_date,branch=:branch,dept_id_approve=:dept_id_approve
        WHERE emp_id = :emp_id ";

        $query = $conn->prepare($sql);
        $query->bindParam(':position_id', $result_sqlsvr["PRS_JBT"], PDO::PARAM_STR);
        $query->bindParam(':position', $result_sqlsvr["JBT_THAIDESC"], PDO::PARAM_STR);
        $query->bindParam(':dept_id', $result_sqlsvr["PRS_DEPT"], PDO::PARAM_STR);
        $query->bindParam(':department_id', $result_sqlsvr["DEPT_THAIDESC"], PDO::PARAM_STR);
        $query->bindParam(':status', $status, PDO::PARAM_STR);
        $query->bindParam(':work_time_id', $work_time_id, PDO::PARAM_STR);
        $query->bindParam(':birthday', $birth, PDO::PARAM_STR);
        $query->bindParam(':start_work_date', $start_work_date, PDO::PARAM_STR);
        $query->bindParam(':branch', $branch, PDO::PARAM_STR);
        $query->bindParam(':dept_id_approve', $dept_id_approve, PDO::PARAM_STR);
        $query->bindParam(':emp_id', $result_sqlsvr["PRS_NO"], PDO::PARAM_STR);
        $query->execute();

    } else {

        //echo "INSERT Employee : " . $result_sqlsvr["PRS_NO"] . "|" . $birth . " | " . $result_sqlsvr["EMP_NAME"] . " | " . $result_sqlsvr["EMP_SURNME"] . $result_sqlsvr["DEPT_THAIDESC"] . "\n\r";

        $sql = "INSERT INTO memployee(emp_id,sex,prefix,f_name,l_name,nick_name,email_address,birthday,position_id,position,dept_id
        ,department_id,start_work_date,work_time_id,status,branch,dept_id_approve)
        VALUES (:emp_id,:sex,:prefix,:f_name,:l_name,:nick_name,:email_address,:birthday,:position_id,:position,:dept_id
        ,:department_id,:start_work_date,:work_time_id,:status,:branch,:dept_id_approve)";
        $query = $conn->prepare($sql);
        $query->bindParam(':emp_id', $result_sqlsvr["PRS_NO"], PDO::PARAM_STR);
        $query->bindParam(':sex', $sex, PDO::PARAM_STR);
        $query->bindParam(':prefix', $result_sqlsvr["EMP_INTL"], PDO::PARAM_STR);
        $query->bindParam(':f_name', $result_sqlsvr["EMP_NAME"], PDO::PARAM_STR);
        $query->bindParam(':l_name', $result_sqlsvr["EMP_SURNME"], PDO::PARAM_STR);
        $query->bindParam(':nick_name', $result_sqlsvr["EMP_EMAIL"], PDO::PARAM_STR);
        $query->bindParam(':email_address', $email_address, PDO::PARAM_STR);
        $query->bindParam(':birthday', $birth, PDO::PARAM_STR);
        $query->bindParam(':position_id', $result_sqlsvr["PRS_JBT"], PDO::PARAM_STR);
        $query->bindParam(':position', $result_sqlsvr["JBT_THAIDESC"], PDO::PARAM_STR);
        $query->bindParam(':dept_id', $result_sqlsvr["PRS_DEPT"], PDO::PARAM_STR);
        $query->bindParam(':department_id', $result_sqlsvr["DEPT_THAIDESC"], PDO::PARAM_STR);
        $query->bindParam(':start_work_date', $start_work_date, PDO::PARAM_STR);
        $query->bindParam(':work_time_id', $work_time_id, PDO::PARAM_STR);
        $query->bindParam(':status', $status, PDO::PARAM_STR);
        $query->bindParam(':branch', $branch, PDO::PARAM_STR);
        $query->bindParam(':dept_id_approve', $dept_id_approve, PDO::PARAM_STR);
        $query->execute();

        $lastInsertId = $conn->lastInsertId();

        if ($lastInsertId) {
            echo "Save OK";
        } else {
            echo "Error";
        }
    }

    $sql_find = "SELECT * FROM ims_user WHERE user_id = '" . $result_sqlsvr["PRS_NO"] . "'";
    //echo $result_sqlsvr["PRS_NO"] . " | " . "Update ims_user = " . $sql_find . " | " . $dept_id_approve .  "\n\r";

    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {

        if ($result_sqlsvr["PRS_NO"]==='81055') {
            echo "Update ims_user Row = " . $result_sqlsvr["PRS_NO"] . " | " . $status_u . " > " . $dept_id_approve.  "\n\r";
        }

        $sql_update = "UPDATE ims_user SET status=:status, dept_id_approve=:dept_id_approve WHERE user_id=:user_id";
        $query_update = $conn->prepare($sql_update);
        $query_update->bindParam(':status', $status_u, PDO::PARAM_STR);
        $query_update->bindParam(':dept_id_approve', $dept_id_approve, PDO::PARAM_STR);
        $query_update->bindParam(':user_id', $result_sqlsvr["PRS_NO"], PDO::PARAM_STR);
        $query_update->execute();

    } else {

        $last_row = LAST_ID($conn, "ims_user", "line_no");

        $sql = "INSERT INTO ims_user(line_no,emp_id,email,user_id,first_name,last_name,department_id,account_type,picture,lang,permission_price,company,approve_permission,approve_level,status,password,document_dept_cond,dept_id_approve)
        VALUES (:line_no,:emp_id,:email,:user_id,:first_name,:last_name,:department_id,:account_type,:picture,:lang,:permission_price,:company,:approve_permission,:approve_level,:status,:password,:document_dept_cond,:dept_id_approve)";

        echo "Insert ims_user Row = " . $last_row . " | " . $result_sqlsvr["PRS_NO"] . " > " . $dept_id_approve.  "\n\r";

        $query = $conn->prepare($sql);
        $query->bindParam(':line_no', $last_row, PDO::PARAM_STR);
        $query->bindParam(':emp_id', $result_sqlsvr["PRS_NO"], PDO::PARAM_STR);
        $query->bindParam(':email', $email_address, PDO::PARAM_STR);
        $query->bindParam(':user_id', $result_sqlsvr["PRS_NO"], PDO::PARAM_STR);
        $query->bindParam(':first_name', $result_sqlsvr["EMP_NAME"], PDO::PARAM_STR);
        $query->bindParam(':last_name', $result_sqlsvr["EMP_SURNME"], PDO::PARAM_STR);
        $query->bindParam(':department_id', $result_sqlsvr["PRS_DEPT"], PDO::PARAM_STR);
        $query->bindParam(':account_type', $account_type, PDO::PARAM_STR);
        $query->bindParam(':picture', $picture, PDO::PARAM_STR);
        $query->bindParam(':lang', $lang, PDO::PARAM_STR);
        $query->bindParam(':permission_price', $permission_price, PDO::PARAM_STR);
        $query->bindParam(':company', $company, PDO::PARAM_STR);
        $query->bindParam(':approve_permission', $approve_permission, PDO::PARAM_STR);
        $query->bindParam(':approve_level', $approve_level, PDO::PARAM_STR);
        $query->bindParam(':status', $status_u, PDO::PARAM_STR);
        $query->bindParam(':password', $password, PDO::PARAM_STR);
        $query->bindParam(':document_dept_cond', $document_dept_cond, PDO::PARAM_STR);
        $query->bindParam(':dept_id_approve', $dept_id_approve, PDO::PARAM_STR);
        $query->execute();

    }

}




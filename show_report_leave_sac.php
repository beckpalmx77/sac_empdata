<?php
session_start();
error_reporting(0);
include("config/connect_db.php");

// รับ input จากผู้ใช้
$doc_date_start = $_POST["doc_date_start"];
$doc_date_to = $_POST["doc_date_to"];
$employeeSelect = $_POST["employeeSelect"];
$where_emp = "";

/*
$txt = "Date ";
$my_file = fopen("leave_b.txt", "w") or die("Unable to open file!");
fwrite($my_file, $txt);
fclose($my_file);
*/

// แปลงวันที่จาก dd-mm-yyyy เป็น yyyy-mm-dd
//$start_date = DateTime::createFromFormat('d-m-Y', $doc_date_start)->format('Y-m-d');
//$end_date = DateTime::createFromFormat('d-m-Y', $doc_date_to)->format('Y-m-d');

$start_date = $doc_date_start;
$end_date = $doc_date_to;

if ($employeeSelect !== '-') {
    $where_emp = " emp_id = '" . $employeeSelect . "'";
} else {
    $where_emp = " 1 ";
}

$leave_data = fetchLeaveData($conn, 'v_dleave_event', $start_date, $end_date, $where_emp);
$holiday_data = fetchLeaveData($conn, 'vdholiday_event', $start_date, $end_date, $where_emp);

function fetchLeaveData($conn, $table, $start_date, $end_date, $where_emp)
{
    $sql = "
    SELECT * FROM $table 
    WHERE " . $where_emp . " 
    AND (
    STR_TO_DATE(date_leave_start, '%d-%m-%Y') BETWEEN STR_TO_DATE('$start_date', '%d-%m-%Y') AND STR_TO_DATE('$end_date', '%d-%m-%Y')
    OR STR_TO_DATE(date_leave_to, '%d-%m-%Y') BETWEEN STR_TO_DATE('$start_date', '%d-%m-%Y') AND STR_TO_DATE('$end_date', '%d-%m-%Y')
    OR STR_TO_DATE('$start_date', '%d-%m-%Y') BETWEEN STR_TO_DATE(date_leave_start, '%d-%m-%Y') AND STR_TO_DATE(date_leave_to, '%d-%m-%Y')
    )
    ORDER BY STR_TO_DATE(date_leave_start, '%d-%m-%Y');
    ";

/*
    $txt = $sql . " | " .$start_date . " | " .  $end_date;
    $my_file = fopen("leave_a.txt", "w") or die("Unable to open file!");
    fwrite($my_file, $txt);
    fclose($my_file);
*/

    $query = $conn->prepare($sql);
    $query->execute();
    return $query->fetchAll(PDO::FETCH_OBJ);
}

function fetchLeaveData_BAK($conn, $table, $start_date, $end_date, $where_emp)
{
    $sql = "SELECT * FROM $table WHERE " . $where_emp . " AND STR_TO_DATE(doc_date, '%d-%m-%Y') BETWEEN '$start_date' AND '$end_date' ORDER BY STR_TO_DATE(doc_date, '%d-%m-%Y')";
    $query = $conn->prepare($sql);
    $query->execute();
    return $query->fetchAll(PDO::FETCH_OBJ);
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <script src="js/jquery-3.6.0.js"></script>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="fontawesome/css/font-awesome.css">
    <link href='vendor/calendar/main.css' rel='stylesheet'/>
    <script src='vendor/calendar/main.js'></script>
    <script src='vendor/calendar/locales/th.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.0.0/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" rel="stylesheet">
    <script src='js/util.js'></script>
    <title>สงวนออโต้คาร์</title>
    <style>
        table {
            width: 100%;
        }
        .status.approved {
            color: green;
            font-weight: bold;
        }
        .status.rejected {
            color: red;
            font-weight: bold;
        }
        .status.pending {
            color: black;
            font-style: italic;
        }
    </style>
</head>
<body>

<div class="card">
    <div class="card-header bg-primary text-white">
        <i class="fa fa-signal" aria-hidden="true"></i> แสดงข้อมูลการลา-เปลี่ยนวันหยุด-วันหยุดนักขัตฤกษ์ พนักงาน
        <?php echo " วันที่ " . htmlentities($doc_date_start) . " ถึง " . htmlentities($doc_date_to); ?>
    </div>
    <div class="card-body">
        <button class="btn btn-danger" onclick="window.close()">ปิด (Close)</button>

        <!-- Leave Data -->
        <h4><span class="badge bg-success">แสดงข้อมูลการลา พนักงาน</span></h4>
        <table id="leaveTable" class="display table table-striped table-bordered" cellspacing="0">
            <thead>
            <tr>
                <th>#</th>
                <th>วันที่เอกสาร</th>
                <th>รหัสพนักงาน</th>
                <th>ชื่อพนักงาน</th>
                <th>หน่วยงาน</th>
                <th>ประเภทการลา</th>
                <th>วันที่ลาเริ่มต้น</th>
                <th>วันที่ลาสิ้นสุด</th>
                <th>จำนวนวัน</th>
                <th>จำนวนชั่วโมง</th>
                <th>สถานะ</th>
                <th>เอกสารแนบ</th>
                <th>หมายเหตุ</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($leave_data as $index => $row_leave): ?>
                <?php
                switch ($row_leave->status) {
                    case 'A':
                        $status_desc = "<span style='color: green;'>อนุมัติ</span>";
                        break;
                    case 'R':
                        $status_desc = "<span style='color: red;'>ไม่อนุมัติ</span>";
                        break;
                    case 'N':
                        $status_desc = "<span style='color: black;'>รอพิจารณา</span>";
                }
/*
                if ($row_leave->picture===null) {
                    $picture = "";
                } else {
                    $picture = $row_leave->picture;
                }
*/
                ?>
                <tr>
                    <td><?php echo htmlentities($index + 1); ?></td>
                    <td><?php echo htmlentities($row_leave->doc_date); ?></td>
                    <td><?php echo htmlentities($row_leave->emp_id); ?></td>
                    <td><?php echo htmlentities($row_leave->f_name . " " . $row_leave->l_name); ?></td>
                    <td><?php echo htmlentities($row_leave->department_id); ?></td>
                    <td>
                        <span style="color: <?php echo htmlentities($row_leave->color); ?>">
                            <?php echo htmlentities($row_leave->leave_type_detail); ?>
                        </span>
                    </td>
                    <td><?php echo htmlentities($row_leave->date_leave_start); ?></td>
                    <td><?php echo htmlentities($row_leave->date_leave_to); ?></td>
                    <td><?php echo htmlentities($row_leave->leave_day); ?></td>
                    <td><?php echo htmlentities($row_leave->leave_hour); ?></td>
                    <td><?php echo $status_desc; ?></td>
                    <td>
                        <?php if (!empty($row_leave->picture)): ?>
                            <img src="<?php echo htmlentities("img_doc/" . $row_leave->picture); ?>"
                                 alt="Attached Document"
                                 style="width: 50px; height: 50px; cursor: pointer;"
                                 onclick="openImage('<?php echo htmlentities("img_doc/" . $row_leave->picture); ?>')">
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlentities($row_leave->remark); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Holiday Data -->
        <h4><span class="badge bg-info">แสดงข้อมูลใช้วันหยุด พนักงาน</span></h4>
        <table id="holidayTable" class="display table table-striped table-bordered" cellspacing="0">
            <thead>
            <tr>
                <th>#</th>
                <th>วันที่เอกสาร</th>
                <th>รหัสพนักงาน</th>
                <th>ชื่อพนักงาน</th>
                <th>หน่วยงาน</th>
                <th>ประเภทการลา</th>
                <th>วันที่ลาเริ่มต้น</th>
                <th>วันที่ลาสิ้นสุด</th>
                <th>จำนวนวัน</th>
                <th>จำนวนชั่วโมง</th>
                <th>สถานะ</th>
                <th>หมายเหตุ</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($holiday_data as $index => $row_holiday): ?>
                <?php
                switch ($row_holiday->status) {
                    case 'A':
                        $status_desc = "<span style='color: green;'>อนุมัติ</span>";
                        break;
                    case 'R':
                        $status_desc = "<span style='color: red;'>ไม่อนุมัติ</span>";
                        break;
                    case 'N':
                        $status_desc = "<span style='color: black;'>รอพิจารณา</span>";
                }
                ?>
                <tr>
                    <td><?php echo htmlentities($index + 1); ?></td>
                    <td><?php echo htmlentities($row_holiday->doc_date); ?></td>
                    <td><?php echo htmlentities($row_holiday->emp_id); ?></td>
                    <td><?php echo htmlentities($row_holiday->f_name . " " . $row_holiday->l_name); ?></td>
                    <td><?php echo htmlentities($row_holiday->department_id); ?></td>
                    <td>
                        <span style="color: <?php echo htmlentities($row_holiday->color); ?>">
                            <?php echo htmlentities($row_holiday->leave_type_detail); ?>
                        </span>
                    </td>
                    <td><?php echo htmlentities($row_holiday->date_leave_start); ?></td>
                    <td><?php echo htmlentities($row_holiday->date_leave_to); ?></td>
                    <td><?php echo htmlentities($row_holiday->leave_day); ?></td>
                    <td><?php echo htmlentities($row_holiday->leave_hour); ?></td>
                    <td><?php echo $status_desc; ?></td>
                    <td><?php echo htmlentities($row_holiday->remark); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


<script>
    function openImage(imageUrl) {
        if (imageUrl) {
            const imgWindow = window.open("", "_blank");
            imgWindow.document.write('<img src="' + imageUrl + '" style="width:100%; height:auto;">');
        }
    }
</script>

</body>
</html>

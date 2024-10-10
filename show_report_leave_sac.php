<?php
session_start();
error_reporting(0);
include("config/connect_db.php");

// รับ input จากผู้ใช้
$doc_date_start = $_POST["doc_date_start"];
$doc_date_to = $_POST["doc_date_to"];

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
    </style>
</head>
<body>

<div class="card">
    <div class="card-header bg-primary text-white">
        <i class="fa fa-signal" aria-hidden="true"></i> แสดงข้อมูลการลา-เปลี่ยนวันหยุด-วันหยุดนักขัตฤกษ์ พนักงาน
        <?php echo " วันที่ " . htmlentities($doc_date_start) . " ถึง " . htmlentities($doc_date_to); ?>
    </div>

    <div class="card-body">
        <div class="container-fluid" id="container-wrapper">
            <button class="btn btn-danger" onclick="window.close()">ปิด (Close)</button>
        </div>
    </div>

    <div class="container-fluid" id="container-wrapper">
        <div class="card-body">
            <h4><span class="badge bg-success">แสดงข้อมูลการลา พนักงาน</span></h4>
            <table id="example" class="display table table-striped table-bordered" cellspacing="0">
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
                    <th>หมายเหตุ</th>
                </tr>
                </thead>
                <tbody>
                <?php
                // แปลงวันที่จาก dd-mm-yyyy เป็น yyyy-mm-dd
                $start_date = DateTime::createFromFormat('d-m-Y', $doc_date_start)->format('Y-m-d');
                $end_date = DateTime::createFromFormat('d-m-Y', $doc_date_to)->format('Y-m-d');

                // เขียนคำสั่ง SQL โดยเทียบตามช่วงวันที่
                $sql_leave = "SELECT v_dleave_event.* 
                              FROM v_dleave_event "
                              . " WHERE STR_TO_DATE(v_dleave_event.doc_date, '%d-%m-%Y') BETWEEN '" . $start_date . "' AND '". $end_date ."'"
                              . " ORDER BY STR_TO_DATE(v_dleave_event.doc_date, '%d-%m-%Y')";
/*
                $txt = $sql_leave ;
                $my_file = fopen("wh_param.txt", "w") or die("Unable to open file!");
                fwrite($my_file, $txt);
                fclose($my_file);
*/


                // เตรียมและรันคำสั่ง SQL
                $query = $conn->prepare($sql_leave);
                $query->execute();
                $results_leave = $query->fetchAll(PDO::FETCH_OBJ);

                // เริ่มแสดงผลในรูปแบบตาราง
                $line_no = 0;
                foreach ($results_leave as $row_leave) {
                    $line_no++;
                    ?>
                    <tr>
                        <td><?php echo htmlentities($line_no); ?></td>
                        <td><?php echo htmlentities($row_leave->doc_date); ?></td>
                        <td><?php echo htmlentities($row_leave->emp_id); ?></td>
                        <td><?php echo htmlentities($row_leave->f_name . " " . $row_leave->l_name); ?></td>
                        <td><?php echo htmlentities($row_leave->department_id); ?></td>
                        <td>
                            <span style="color: <?php echo htmlentities($row_leave->color); ?>"><?php echo htmlentities($row_leave->leave_type_detail); ?></span>
                        </td>
                        <td><?php echo htmlentities($row_leave->date_leave_start); ?></td>
                        <td><?php echo htmlentities($row_leave->date_leave_to); ?></td>
                        <td><?php echo htmlentities($row_leave->leave_day); ?></td>
                        <td><?php echo htmlentities($row_leave->remark); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="container-fluid" id="container-wrapper">
        <div class="card-body">
            <h4><span class="badge bg-info">แสดงข้อมูลใช้วันหยุด พนักงาน</span></h4>
            <table id="example" class="display table table-striped table-bordered" cellspacing="0">
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
                    <th>หมายเหตุ</th>
                </tr>
                </thead>
                <tbody>
                <?php
                // แปลงวันที่จาก dd-mm-yyyy เป็น yyyy-mm-dd
                $start_date = DateTime::createFromFormat('d-m-Y', $doc_date_start)->format('Y-m-d');
                $end_date = DateTime::createFromFormat('d-m-Y', $doc_date_to)->format('Y-m-d');

                // เขียนคำสั่ง SQL โดยเทียบตามช่วงวันที่
                $sql_leave = "SELECT vdholiday_event.* 
                              FROM vdholiday_event "
                    . " WHERE STR_TO_DATE(vdholiday_event.doc_date, '%d-%m-%Y') BETWEEN '" . $start_date . "' AND '". $end_date ."'"
                    . " ORDER BY STR_TO_DATE(vdholiday_event.doc_date, '%d-%m-%Y')";
                /*
                                $txt = $sql_leave ;
                                $my_file = fopen("wh_param.txt", "w") or die("Unable to open file!");
                                fwrite($my_file, $txt);
                                fclose($my_file);
                */


                // เตรียมและรันคำสั่ง SQL
                $query = $conn->prepare($sql_leave);
                $query->execute();
                $results_leave = $query->fetchAll(PDO::FETCH_OBJ);

                // เริ่มแสดงผลในรูปแบบตาราง
                $line_no = 0;
                foreach ($results_leave as $row_leave) {
                    $line_no++;
                    ?>
                    <tr>
                        <td><?php echo htmlentities($line_no); ?></td>
                        <td><?php echo htmlentities($row_leave->doc_date); ?></td>
                        <td><?php echo htmlentities($row_leave->emp_id); ?></td>
                        <td><?php echo htmlentities($row_leave->f_name . " " . $row_leave->l_name); ?></td>
                        <td><?php echo htmlentities($row_leave->department_id); ?></td>
                        <td>
                            <span style="color: <?php echo htmlentities($row_leave->color); ?>"><?php echo htmlentities($row_leave->leave_type_detail); ?></span>
                        </td>
                        <td><?php echo htmlentities($row_leave->date_leave_start); ?></td>
                        <td><?php echo htmlentities($row_leave->date_leave_to); ?></td>
                        <td><?php echo htmlentities($row_leave->leave_day); ?></td>
                        <td><?php echo htmlentities($row_leave->remark); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>


</div>

</body>
</html>

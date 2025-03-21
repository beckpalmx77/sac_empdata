<?php

include("config/connect_db.php");

$month_start = $_POST["month_start"];
$month_to = $_POST["month_to"];
$year = $_POST["year"];
$branch = $_POST["branch"];

$form_type = $_POST["form_type"];

$sql_leave_addition1 = "";
$sql_leave_addition2 = "";

$emp_name = $_POST["employee"];

$f_name = "";
$l_name = "";

if (!empty($emp_name)) {
    $emp_name = explode("-", $emp_name);
    $f_name = $emp_name[0];
    $l_name = $emp_name[1];
}

$month_name_start = "";
$month_name_to = "";

$sql_start_month = "SELECT * FROM ims_month WHERE month = :month_start";
$stmt_start_month = $conn->prepare($sql_start_month);
$stmt_start_month->bindParam(':month_start', $month_start);
$stmt_start_month->execute();
$MonthStart = $stmt_start_month->fetchAll();
foreach ($MonthStart as $row_start) {
    $month_id_start = $row_start["month_id"];
    $month_name_start = $row_start["month_name"];
}

$sql_to_month = "SELECT * FROM ims_month WHERE month = :month_to";
$stmt_to_month = $conn->prepare($sql_to_month);
$stmt_to_month->bindParam(':month_to', $month_to);
$stmt_to_month->execute();
$MonthTo = $stmt_to_month->fetchAll();
foreach ($MonthTo as $row_to) {
    $month_id_to = $row_to["month_id"];
    $month_name_to = $row_to["month_name"];
}

$date = date("d/m/Y");
$total = 0;
$total_payment = 0;
$sql_leave_addition = "";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta date="viewport" content="width=device-width, initial-scale=1.0">
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
    <!--link href="../vendor/datatables/v11/fontawesome53.all.min.css" rel="stylesheet"-->

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" rel="stylesheet">
    <script src='js/util.js'></script>
    <title>สงวนออโต้คาร์</title>
    <style>
        table {
            width: 50%;
        }
    </style>

</head>
<p class="card">
<div class="card-header bg-primary text-white">
    <i class="fa fa-signal" aria-hidden="true"></i> แสดงข้อมูลการลา-เปลี่ยนวันหยุด-วันหยุดนักขัตฤกษ์ พนักงาน
    <?php echo " เดือน " . $month_name_start . " ถึงเดือน " . $month_name_to . " ปี " . $year; ?>
</div>

<div class="card-body">
    <!--a id="myLink" href="#" onclick="PrintPage();"><i class="fa fa-print"></i> พิมพ์ </a-->
    <!--a id="myLink" href="#" onclick="window.close();"><i class="fa fa-window-close"></i> ปิด (Close) </a-->
    <div class="container-fluid" id="container-wrapper">
        <button class="btn btn-danger" onclick="window.close()">ปิด (Close)</button>
    </div>
</div>

<input type="hidden" class="form-control" id="f_name" name="f_name" value="">
<input type="hidden" class="form-control" id="form_type" name="form_type" value="<?php echo $form_type ?>">

<?php
if ($form_type === 'employee') {
    echo '<div class="container-fluid" id="container-wrapper">';
    echo '<th><b>' . 'ชื่อพนักงาน : ' . $f_name . ' ' . $l_name . '</b></th>';
    echo '<table id="leaveTable" class="display table table-striped table-bordered" cellspacing="0" width="100%">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>ประเภทการลา</th>';
    echo '<th>จำนวนวันที่ใช้การลา (วัน)</th>';
    echo '<th>จำนวนวันที่ลาได้สูงสุด (วัน)</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    for ($loop = 1; $loop <= 5; $loop++) {
        $leave_type_id = "L" . $loop;
        $sql_leave_count = "SELECT v_dleave_event.leave_type_detail, SUM(v_dleave_event.leave_day) AS leave_day, mleave_type.day_max, mleave_type.color
                            FROM v_dleave_event 
                            LEFT JOIN mleave_type ON mleave_type.leave_type_id = v_dleave_event.leave_type_id
                            WHERE v_dleave_event.doc_year = :year 
                            AND v_dleave_event.f_name = :f_name 
                            AND v_dleave_event.l_name = :l_name 
                            AND v_dleave_event.leave_type_id = :leave_type_id 
                            AND v_dleave_event.doc_month BETWEEN :month_id_start AND :month_id_to 
                            GROUP BY v_dleave_event.leave_type_detail, mleave_type.day_max , mleave_type.color";

        $statement_leave = $conn->prepare($sql_leave_count);
        $statement_leave->bindParam(':year', $year);
        $statement_leave->bindParam(':f_name', $f_name);
        $statement_leave->bindParam(':l_name', $l_name);
        $statement_leave->bindParam(':leave_type_id', $leave_type_id);
        $statement_leave->bindParam(':month_id_start', $month_id_start);
        $statement_leave->bindParam(':month_id_to', $month_id_to);
        $statement_leave->execute();
        $results_leave = $statement_leave->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results_leave as $row_leave) {
            $leave_type_detail = '<td><span style="color: ' . $row_leave['color'] . ';">' . $row_leave['leave_type_detail'] . '</span></td>';
            echo '<tr>';
            echo $leave_type_detail;
            //echo '<td>' . $row_leave['leave_type_detail'] . '</td>';
            echo '<td>' . $row_leave['leave_day'] . '</td>';
            echo '<td>' . $row_leave['day_max'] . '</td>';
            echo '</tr>';
        }
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}
?>


<div class="container-fluid" id="container-wrapper">
    <div class="card-body">
        <h4><span class="badge bg-success">แสดงข้อมูลการลา พนักงาน</span></h4>
        <table id="example" class="display table table-striped table-bordered" cellspacing="0" width="100%">
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
                <th>สถานะ</th>
                <th>หมายเหตุ</th>
            </tr>
            </thead>
            <tfoot></tfoot>
            <tbody>
            <?php

            $sql_leave = " SELECT v_dleave_event.* , em.status AS emp_status FROM v_dleave_event 
                       LEFT JOIN memployee em on em.emp_id = v_dleave_event.emp_id
                       WHERE v_dleave_event.doc_year = :year
                       AND v_dleave_event.doc_month BETWEEN :month_id_start AND :month_id_to
                       AND v_dleave_event.dept_id = :branch   
                       ";

            if (!empty($f_name)) {
                $sql_leave_addition1 = " AND v_dleave_event.f_name = :f_name";
            }

            if (!empty($l_name)) {
                $sql_leave_addition2 = " AND v_dleave_event.l_name = :l_name";
            }

            $sql_oder = " ORDER BY v_dleave_event.f_name,v_dleave_event.doc_date ";
            $sql_leave = $sql_leave . $sql_leave_addition1 . $sql_leave_addition2 . $sql_oder;

            $statement_leave = $conn->prepare($sql_leave);
            $statement_leave->bindParam(':year', $year);
            $statement_leave->bindParam(':month_id_start', $month_id_start);
            $statement_leave->bindParam(':month_id_to', $month_id_to);
            $statement_leave->bindParam(':branch', $branch);

            if (!empty($f_name)) {
                $statement_leave->bindParam(':f_name', $f_name);
            }

            if (!empty($l_name)) {
                $statement_leave->bindParam(':l_name', $l_name);
            }

            $statement_leave->execute();
            $results_leave = $statement_leave->fetchAll(PDO::FETCH_ASSOC);
            $line_no = 0;
            foreach ($results_leave as $row_leave) {
                $line_no++;
                $status = $row_leave["status"];
                switch ($status) {
                    case 'A':
                        $status_desc = "<span style='color: green;'>อนุมัติ</span>";
                        break;
                    case 'R':
                        $status_desc = "<span style='color: red;'>ไม่อนุมัติ</span>";
                        break;
                    default:
                        $status_desc = "<span style='color: black;'>รอพิจารณา</span>";
                        break;
                }
                ?>
                <tr>
                    <td><?php echo htmlentities($line_no); ?></td>
                    <td><?php echo htmlentities($row_leave['doc_date']); ?></td>
                    <td><?php echo htmlentities($row_leave['emp_id']); ?></td>
                    <td><?php echo htmlentities($row_leave['f_name'] . " " . $row_leave['l_name']); ?></td>
                    <td><?php echo htmlentities($row_leave['department_id']); ?></td>
                    <td>
                        <span style="color: <?php echo $row_leave['color']; ?>"><?php echo htmlentities($row_leave['leave_type_detail']); ?></span>
                    </td>
                    <td><?php echo htmlentities($row_leave['date_leave_start']); ?></td>
                    <td><?php echo htmlentities($row_leave['date_leave_to']); ?></td>
                    <td><?php echo htmlentities($row_leave['leave_day']); ?></td>
                    <td><?php echo $status_desc; ?></td>
                    <td><?php echo htmlentities($row_leave['remark']); ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <?php
    if ($form_type === 'employee') {
        echo '<div class="container-fluid" id="container-wrapper">';
        //echo '<th><b>' . 'ชื่อพนักงาน : ' . $f_name . ' ' . $l_name . '</b></th>';
        echo '<table id="leaveTable" class="display table table-striped table-bordered" cellspacing="0" width="100%">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ประเภทการลา</th>';
        echo '<th>จำนวนวันที่ใช้การลา (วัน)</th>';
        echo '<th>จำนวนวันที่ลาได้สูงสุด (วัน)</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        for ($loop = 2; $loop <= 2; $loop++) {
            $leave_type_id = "H" . $loop;
            $sql_leave_count = "SELECT vdholiday_event.leave_type_detail, SUM(vdholiday_event.leave_day) AS leave_day, mleave_type.day_max, mleave_type.color
                            FROM vdholiday_event 
                            LEFT JOIN mleave_type ON mleave_type.leave_type_id = vdholiday_event.leave_type_id
                            WHERE vdholiday_event.doc_year = :year 
                            AND vdholiday_event.f_name = :f_name 
                            AND vdholiday_event.l_name = :l_name 
                            AND vdholiday_event.leave_type_id = :leave_type_id 
                            AND vdholiday_event.month BETWEEN :month_id_start AND :month_id_to 
                            GROUP BY vdholiday_event.leave_type_detail, mleave_type.day_max , mleave_type.color";

            $statement_leave = $conn->prepare($sql_leave_count);
            $statement_leave->bindParam(':year', $year);
            $statement_leave->bindParam(':f_name', $f_name);
            $statement_leave->bindParam(':l_name', $l_name);
            $statement_leave->bindParam(':leave_type_id', $leave_type_id);
            $statement_leave->bindParam(':month_id_start', $month_id_start);
            $statement_leave->bindParam(':month_id_to', $month_id_to);
            $statement_leave->execute();
            $results_leave = $statement_leave->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results_leave as $row_leave) {
                $leave_type_detail = '<td><span style="color: ' . $row_leave['color'] . ';">' . $row_leave['leave_type_detail'] . '</span></td>';
                echo '<tr>';
                echo $leave_type_detail;
                //echo '<td>' . $row_leave['leave_type_detail'] . '</td>';
                echo '<td>' . $row_leave['leave_day'] . '</td>';
                echo '<td>' . $row_leave['day_max'] . '</td>';
                echo '</tr>';
            }
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }
    ?>

    <div class="card-body">
        <h4><span class="badge bg-info">แสดงข้อมูลการใช้วันหยุด (นักขัตฤกษ์-ประจำปี) พนักงาน</span></h4>
        <table id="example" class="display table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th>#</th>
                <th>วันที่เอกสาร</th>
                <th>รหัสพนักงาน</th>
                <th>ชื่อพนักงาน</th>
                <th>หน่วยงาน</th>
                <th>ประเภท</th>
                <th>วันที่เริ่มต้น</th>
                <th>วันที่สิ้นสุด</th>
                <th>สถานะ</th>
                <th>หมายเหตุ</th>
            </tr>
            </thead>
            <tfoot></tfoot>
            <tbody>
            <?php
            $sql_leave = " SELECT vdholiday_event.* , em.status  AS emp_status FROM vdholiday_event 
               LEFT JOIN memployee em on em.emp_id = vdholiday_event.emp_id
               WHERE vdholiday_event.doc_year = :year
               AND vdholiday_event.month BETWEEN :month_id_start AND :month_id_to
               AND vdholiday_event.dept_id = :branch   
               ";

            if (!empty($f_name)) {
                $sql_leave_addition1 = " AND vdholiday_event.f_name = :f_name";
            }

            if (!empty($l_name)) {
                $sql_leave_addition2 = " AND vdholiday_event.l_name = :l_name";
            }

            $sql_oder = " ORDER BY vdholiday_event.f_name,vdholiday_event.doc_date ";
            $sql_leave = $sql_leave . $sql_leave_addition1 . $sql_leave_addition2 . $sql_oder;
            $statement_leave = $conn->prepare($sql_leave);
            $statement_leave->bindParam(':year', $year);
            $statement_leave->bindParam(':month_id_start', $month_id_start);
            $statement_leave->bindParam(':month_id_to', $month_id_to);
            $statement_leave->bindParam(':branch', $branch);

            if (!empty($f_name)) {
                $statement_leave->bindParam(':f_name', $f_name);
            }

            if (!empty($l_name)) {
                $statement_leave->bindParam(':l_name', $l_name);
            }

            $statement_leave->execute();
            $results_leave = $statement_leave->fetchAll(PDO::FETCH_ASSOC);
            $line_no = 0;
            foreach ($results_leave as $row_leave) {
                $line_no++;
                $status = $row_leave["status"];
                switch ($status) {
                    case 'A':
                        $status_desc = "<span style='color: green;'>อนุมัติ</span>";
                        break;
                    case 'R':
                        $status_desc = "<span style='color: red;'>ไม่อนุมัติ</span>";
                        break;
                    default:
                        $status_desc = "<span style='color: black;'>รอพิจารณา</span>";
                        break;
                }
                ?>
                <tr>
                    <td><?php echo htmlentities($line_no); ?></td>
                    <td><?php echo htmlentities($row_leave['doc_date']); ?></td>
                    <td><?php echo htmlentities($row_leave['emp_id']); ?></td>
                    <td><?php echo htmlentities($row_leave['f_name'] . " " . $row_leave['l_name']); ?></td>
                    <td><?php echo htmlentities($row_leave['department_id']); ?></td>
                    <td><?php echo htmlentities($row_leave['leave_type_detail']); ?></td>
                    <td><?php echo htmlentities($row_leave['date_leave_start']); ?></td>
                    <td><?php echo htmlentities($row_leave['date_leave_to']); ?></td>
                    <td><?php echo $status_desc; ?></td>
                    <td><?php echo htmlentities($row_leave['remark']); ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="card-body">
        <h4><span class="badge bg-warning">แสดงข้อมูลการเปลี่ยนวันหยุด พนักงาน</span></h4>
        <table id="example" class="display table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th>#</th>
                <th>วันที่เอกสาร</th>
                <th>รหัสพนักงาน</th>
                <th>ชื่อพนักงาน</th>
                <th>หน่วยงาน</th>
                <th>ประเภท</th>
                <th>วันที่หยุดปกติ</th>
                <th>วันที่ต้องการหยุด</th>
                <th>สถานะ</th>
                <th>หมายเหตุ</th>
            </tr>
            </thead>
            <tfoot></tfoot>
            <tbody>
            <?php

            $sql_leave = " SELECT v_dchange_event.* , em.status AS emp_status FROM v_dchange_event 
               LEFT JOIN memployee em on em.emp_id = v_dchange_event.emp_id
               WHERE v_dchange_event.doc_year = :year
               AND v_dchange_event.doc_month BETWEEN :month_id_start AND :month_id_to
               AND v_dchange_event.dept_id = :branch   
               ";

            if (!empty($f_name)) {
                $sql_leave_addition1 = " AND v_dchange_event.f_name = :f_name";
            }

            if (!empty($l_name)) {
                $sql_leave_addition2 = " AND v_dchange_event.l_name = :l_name";
            }

            $sql_oder = " ORDER BY v_dchange_event.f_name,v_dchange_event.doc_date ";
            $sql_leave = $sql_leave . $sql_leave_addition1 . $sql_leave_addition2 . $sql_oder;
            $statement_leave = $conn->prepare($sql_leave);
            $statement_leave->bindParam(':year', $year);
            $statement_leave->bindParam(':month_id_start', $month_id_start);
            $statement_leave->bindParam(':month_id_to', $month_id_to);
            $statement_leave->bindParam(':branch', $branch);

            if (!empty($f_name)) {
                $statement_leave->bindParam(':f_name', $f_name);
            }

            if (!empty($l_name)) {
                $statement_leave->bindParam(':l_name', $l_name);
            }

            $statement_leave->execute();
            $results_leave = $statement_leave->fetchAll(PDO::FETCH_ASSOC);
            $line_no = 0;
            foreach ($results_leave as $row_leave) {
                $line_no++;
                $status = $row_leave["status"];
                switch ($status) {
                    case 'A':
                        $status_desc = "<span style='color: green;'>อนุมัติ</span>";
                        break;
                    case 'R':
                        $status_desc = "<span style='color: red;'>ไม่อนุมัติ</span>";
                        break;
                    default:
                        $status_desc = "<span style='color: black;'>รอพิจารณา</span>";
                        break;
                }
                ?>
                <tr>
                    <td><?php echo htmlentities($line_no); ?></td>
                    <td><?php echo htmlentities($row_leave['doc_date']); ?></td>
                    <td><?php echo htmlentities($row_leave['emp_id']); ?></td>
                    <td><?php echo htmlentities($row_leave['f_name'] . " " . $row_leave['l_name']); ?></td>
                    <td><?php echo htmlentities($row_leave['department_id']); ?></td>
                    <td><?php echo htmlentities($row_leave['leave_type_detail']); ?></td>
                    <td><?php echo htmlentities($row_leave['date_leave_start']); ?></td>
                    <td><?php echo htmlentities($row_leave['date_leave_to']); ?></td>
                    <td><?php echo htmlentities($row_leave['date_leave_to']); ?></td>
                    <td><?php echo $status_desc; ?></td>
                    <td><?php echo htmlentities($row_leave['remark']); ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>

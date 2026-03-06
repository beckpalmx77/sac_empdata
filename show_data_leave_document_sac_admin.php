<?php

include 'config/connect_db.php';

$year = date("Y");
$month_start = "01";
$month_to = "12";

$sql_start_month = "SELECT month_name FROM ims_month WHERE month = :month_start LIMIT 1";
$stmt_start_month = $conn->prepare($sql_start_month);
$stmt_start_month->bindParam(':month_start', $month_start);
$stmt_start_month->execute();
$row_start = $stmt_start_month->fetch(PDO::FETCH_ASSOC);
$month_name_start = ($row_start) ? $row_start["month_name"] : "มกราคม";

$sql_to_month = "SELECT month_name FROM ims_month WHERE month = :month_to LIMIT 1";
$stmt_to_month = $conn->prepare($sql_to_month);
$stmt_to_month->bindParam(':month_to', $month_to);
$stmt_to_month->execute();
$row_to = $stmt_to_month->fetch(PDO::FETCH_ASSOC);
$month_name_to = ($row_to) ? $row_to["month_name"] : "ธันวาคม";
?>

<!DOCTYPE html>
<html lang="th">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ระบบแสดงข้อมูลการลา</title>

    <!--link href="bootstrap/css/bootstrap.min.css" rel="stylesheet"-->

    <!-- link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet"-->
    <!--link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet"-->

    <!--link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css" rel="stylesheet"-->

    <style>

        body {
            background: #f5f6fa;
        }

        .card-header {
            font-weight: bold;
        }

        .img-thumbnail-custom {
            width: 50px;
            height: 50px;
            cursor: pointer;
            object-fit: cover;
            border-radius: 5px;
        }

        /* Pagination styling */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 6px !important;
            margin: 0 2px !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: #0d6efd !important;
            border-color: #0d6efd !important;
            color: #fff !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #e9ecef !important;
            border-color: #dee2e6 !important;
            color: #0d6efd !important;
        }

        .dataTables_wrapper .dataTables_info {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 4px 8px;
        }

    </style>

</head>

<body>

<div class="container-fluid mt-4">

    <div class="card shadow">

        <div class="card-header bg-primary text-white">

            <i class="fa fa-signal"></i>
            แสดงข้อมูลการลา - เปลี่ยนวันหยุด ที่กำลังรอพิจารณา

            <?php
            echo " | ปี " . $year .
                " (" . $month_name_start . " - " . $month_name_to . ")";
            ?>

        </div>

        <div class="card-body">

            <!-- TABLE LEAVE -->

            <h5 class="mb-3">

<span class="badge bg-success">
<i class="fas fa-calendar-check"></i>
ข้อมูลการลาพนักงาน
</span>

            </h5>

            <div class="table-responsive">

                <table id="leaveTable" class="table table-bordered table-hover align-middle">

                    <thead class="table-dark">

                    <tr>
                        <th>#</th>
                        <th>วันที่เอกสาร</th>
                        <th>รหัส</th>
                        <th>ชื่อพนักงาน</th>
                        <th>ประเภทลา</th>
                        <th>เริ่ม</th>
                        <th>สิ้นสุด</th>
                        <th>วัน</th>
                        <th>ชม</th>
                        <th>สถานะ</th>
                        <th>รูป</th>
                        <th>หมายเหตุ</th>
                    </tr>

                    </thead>

                    <tbody>

                    <?php

                    $sql_leave = "

SELECT v.*, em.status emp_status
FROM v_dleave_event v
LEFT JOIN memployee em
ON em.emp_id = v.emp_id

WHERE v.doc_year = :year
AND v.doc_month BETWEEN :m_start AND :m_to AND v.status NOT IN ('A','R')

ORDER BY v.f_name ASC , v.create_date DESC

";

                    $stmt = $conn->prepare($sql_leave);

                    $stmt->execute([
                        ':year' => $year,
                        ':m_start' => $month_start,
                        ':m_to' => $month_to,
                        ':dept' => $dept_id_approve
                    ]);

                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $i = 1;

                    foreach ($rows as $row) {

                        $status_html = '';

                        if ($row['status'] == 'A')
                            $status_html = '<span class="text-success" style="font-weight:bold;">อนุมัติ</span>';

                        elseif ($row['status'] == 'R')
                            $status_html = '<span class="text-danger" style="font-weight:bold;">ไม่อนุมัติ</span>';

                        else
                            $status_html = '<span class="text-muted" style="font-weight:bold;">รอพิจารณา</span>';

                        ?>

                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlentities($row['doc_date']) ?></td>
                            <td><?= htmlentities($row['emp_id']) ?></td>
                            <td><?= htmlentities($row['f_name'] . " " . $row['l_name']) ?></td>
                            <td style="color:<?= $row['color'] ?>;font-weight:bold;">
                                <?= htmlentities($row['leave_type_detail']) ?>
                            </td>
                            <td><?= htmlentities($row['date_leave_start']) ?></td>
                            <td><?= htmlentities($row['date_leave_to']) ?></td>
                            <td><?= htmlentities($row['leave_day']) ?></td>
                            <td><?= htmlentities($row['leave_hour']) ?></td>
                            <td><?= $status_html ?></td>
                            <td>
                                <?php if (!empty($row['picture'])) { ?>
                                    <img src="img_doc/<?= $row['picture'] ?>"
                                         class="img-thumbnail-custom"
                                         onclick="openImage(this.src)">
                                <?php } ?>
                            </td>
                            <td><small><?= htmlentities($row['remark']) ?></small></td>
                        </tr>
                    <?php } ?>
                    </tbody>

                </table>

            </div>

            <hr>

            <!-- HOLIDAY TABLE -->

            <h5 class="mb-3">

<span class="badge bg-info text-dark">
<i class="fas fa-umbrella-beach"></i>
ข้อมูลวันหยุดนักขัตฤกษ์
</span>

            </h5>

            <div class="table-responsive">

                <table id="holidayTable" class="table table-bordered table-hover align-middle">

                    <thead class="table-secondary">

                    <tr>
                        <th>#</th>
                        <th>วันที่เอกสาร</th>
                        <th>รหัส</th>
                        <th>ชื่อพนักงาน</th>
                        <th>ประเภท</th>
                        <th>เริ่ม</th>
                        <th>สิ้นสุด</th>
                        <th>สถานะ</th>
                        <th>หมายเหตุ</th>
                    </tr>

                    </thead>

                    <tbody>

                    <?php

                    $sql_holiday = " SELECT h.*, em.status emp_status
                                     FROM vdholiday_event h
                                     LEFT JOIN memployee em
                                     ON em.emp_id = h.emp_id
                                     WHERE h.doc_year = :year
                                     AND h.month BETWEEN :m_start AND :m_to
                                     AND h.status NOT IN ('A','R')
                                     ORDER BY h.f_name ASC , h.create_date DESC ";

                    $stmt_h = $conn->prepare($sql_holiday);

                    $stmt_h->execute([
                        ':year' => $year,
                        ':m_start' => $month_start,
                        ':m_to' => $month_to,
                        ':dept' => $dept_id_approve
                    ]);

                    $rows_h = $stmt_h->fetchAll(PDO::FETCH_ASSOC);

                    $j = 1;

                    foreach ($rows_h as $row_h) {

                        $h_status = '';

                        if ($row_h['status'] == 'A')
                            $h_status = '<span class="text-success">อนุมัติ</span>';

                        elseif ($row_h['status'] == 'R')
                            $h_status = '<span class="text-danger">ไม่อนุมัติ</span>';

                        else
                            $h_status = '<span class="text-muted">รอพิจารณา</span>';

                        ?>

                        <tr>

                            <td><?= $j++ ?></td>

                            <td><?= htmlentities($row_h['doc_date']) ?></td>

                            <td><?= htmlentities($row_h['emp_id']) ?></td>

                            <td><?= htmlentities($row_h['f_name'] . " " . $row_h['l_name']) ?></td>

                            <td><?= htmlentities($row_h['leave_type_detail']) ?></td>

                            <td><?= htmlentities($row_h['date_leave_start']) ?></td>

                            <td><?= htmlentities($row_h['date_leave_to']) ?></td>

                            <td><?= $h_status ?></td>

                            <td><small><?= htmlentities($row_h['remark']) ?></small></td>

                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<!--script src="js/jquery-3.6.0.js"></script>
<script src="bootstrap/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script-- src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script-->

<script>

    const dtLanguage = {
        search: "ค้นหา:",
        lengthMenu: "แสดง _MENU_ รายการ",
        info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
        infoEmpty: "แสดง 0 ถึง 0 จาก 0 รายการ",
        infoFiltered: "(กรองจากทั้งหมด _MAX_ รายการ)",
        paginate: {
            first: "«",
            last: "»",
            next: "›",
            previous: "‹"
        },
        zeroRecords: "ไม่พบข้อมูล",
        emptyTable: "ไม่มีข้อมูลในตาราง"
    };

    $(document).ready(function () {

        $('#leaveTable').DataTable({
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            responsive: true,
            order: [[1, 'desc']],
            pagingType: 'full_numbers',
            language: dtLanguage
        });

        $('#holidayTable').DataTable({
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            responsive: true,
            order: [[1, 'desc']],
            pagingType: 'full_numbers',
            language: dtLanguage
        });

    });

    function openImage(url) {
        window.open(url, '_blank');
    }

</script>

</body>
</html>
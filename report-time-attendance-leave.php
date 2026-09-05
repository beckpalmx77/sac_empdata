<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['department_id']) == "") {
    header("Location: index.php");
} else {
    $current_first_date = date('01-m-Y');
    $current_last_date = date('d-m-Y'); // วันที่ปัจจุบัน
    $page_title = !empty($_GET['s']) ? urldecode($_GET['s']) : "รายงานการ เข้า - ออก และการลาประจำวัน";
    $menu_title = !empty($_GET['m']) ? urldecode($_GET['m']) : "รายงานและบันทึกเวลา";
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <head>
        <link href="vendor/fontawesome-free-5.15.4-web/css/all.min.css" rel="stylesheet" type="text/css">
        <link href="vendor/fontawesome-free-5.15.4-web/css/v4-shims.min.css" rel="stylesheet" type="text/css">
    </head>
    <body id="page-top">
    <div id="wrapper">
        <?php
        include('includes/Side-Bar.php');
        ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php
                include('includes/Top-Bar.php');
                ?>
                <!-- Container Fluid-->
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?php echo htmlspecialchars($page_title); ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                            <li class="breadcrumb-item"><?php echo htmlspecialchars($menu_title); ?></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($page_title); ?></li>
                        </ol>
                    </div>

                    <!-- Filter Card -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4 shadow-sm border-left-primary">
                                <div class="card-header py-3 bg-light">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fa fa-filter"></i> เงื่อนไขการค้นหา
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form id="filterForm" method="post" action="export_process/export_time_attendance_leave.php">
                                        <div class="form-row align-items-end">
                                            <div class="form-group col-md-3">
                                                <label for="doc_date_start" class="font-weight-bold text-gray-700">จากวันที่ :</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control datepicker" id="doc_date_start" name="doc_date_start"
                                                           value="<?php echo $current_first_date; ?>" readonly style="background-color: #fff; cursor: pointer;">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text"><i class="fa fa-calendar text-primary"></i></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group col-md-3">
                                                <label for="doc_date_to" class="font-weight-bold text-gray-700">ถึงวันที่ :</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control datepicker" id="doc_date_to" name="doc_date_to"
                                                           value="<?php echo $current_last_date; ?>" readonly style="background-color: #fff; cursor: pointer;">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text"><i class="fa fa-calendar text-primary"></i></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group col-md-3">
                                                <label for="employeeSelect" class="font-weight-bold text-gray-700">เลือกพนักงาน :</label>
                                                <select id="employeeSelect" name="employeeSelect" class="form-control">
                                                    <option value="-">-- พนักงานทุกคน --</option>
                                                </select>
                                            </div>

                                            <div class="form-group col-md-3 d-flex justify-content-end">
                                                <input type="hidden" name="search_keyword" id="search_keyword" value="">
                                                <button type="button" id="btnSearch" class="btn btn-primary mr-2 shadow-sm">
                                                    <i class="fa fa-search"></i> ค้นหา
                                                </button>
                                                <button type="button" id="btnReset" class="btn btn-secondary mr-2 shadow-sm">
                                                    <i class="fa fa-refresh"></i> รีเซ็ต
                                                </button>
                                                <button type="submit" id="btnExport" class="btn btn-success shadow-sm">
                                                    <i class="fa fa-file-excel-o"></i> Excel
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Card -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4 shadow">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fa fa-table"></i> ตารางรายงานการ เข้า - ออก และการลาประจำวัน
                                    </h6>
                                    <span class="badge badge-light border text-dark p-2" id="filterInfoBadge">
                                        กำลังแสดง: ทั้งหมด
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id='TableRecordList' class='display table table-striped table-bordered table-hover' width="100%">
                                            <thead class="thead-light">
                                            <tr>
                                                <th width="9%">รหัสพนักงาน</th>
                                                <th width="11%">ชื่อ</th>
                                                <th width="11%">นามสกุล</th>
                                                <th width="13%">แผนก</th>
                                                <th width="10%">วันที่</th>
                                                <th width="9%">เวลาเข้า</th>
                                                <th width="9%">เวลาออก</th>
                                                <th width="14%">การลา / สถานะ</th>
                                                <th width="14%">เลขที่เอกสาร / หมายเหตุ</th>
                                                <th width="8%">รายละเอียด</th>
                                            </tr>
                                            </thead>
                                            <tfoot>
                                            <tr>
                                                <th>รหัสพนักงาน</th>
                                                <th>ชื่อ</th>
                                                <th>นามสกุล</th>
                                                <th>แผนก</th>
                                                <th>วันที่</th>
                                                <th>เวลาเข้า</th>
                                                <th>เวลาออก</th>
                                                <th>การลา / สถานะ</th>
                                                <th>เลขที่เอกสาร / หมายเหตุ</th>
                                                <th>รายละเอียด</th>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Record Detail Modal -->
                <div class="modal fade" id="recordModal" tabindex="-1" role="dialog" aria-labelledby="recordModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title font-weight-bold" id="recordModalLabel">
                                    <i class="fa fa-id-card-o"></i> รายละเอียดบันทึกเวลาและการลาประจำวัน
                                </h5>
                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body p-4">
                                <!-- Employee Info Card -->
                                <div class="card mb-3 border-left-info shadow-sm">
                                    <div class="card-header py-2 bg-light">
                                        <h6 class="m-0 font-weight-bold text-info"><i class="fa fa-user"></i> ข้อมูลพนักงาน</h6>
                                    </div>
                                    <div class="card-body py-2">
                                        <div class="row">
                                            <div class="col-sm-4 mb-2">
                                                <small class="text-muted d-block">รหัสพนักงาน</small>
                                                <span class="font-weight-bold text-dark" id="modal_emp_id">-</span>
                                            </div>
                                            <div class="col-sm-4 mb-2">
                                                <small class="text-muted d-block">ชื่อ - นามสกุล</small>
                                                <span class="font-weight-bold text-dark" id="modal_full_name">-</span>
                                            </div>
                                            <div class="col-sm-4 mb-2">
                                                <small class="text-muted d-block">แผนก / หน่วยงาน</small>
                                                <span class="font-weight-bold text-dark" id="modal_department">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Attendance Info Card -->
                                <div class="card mb-3 border-left-success shadow-sm">
                                    <div class="card-header py-2 bg-light">
                                        <h6 class="m-0 font-weight-bold text-success"><i class="fa fa-clock-o"></i> ข้อมูลการบันทึกเวลาเข้า - ออก</h6>
                                    </div>
                                    <div class="card-body py-2">
                                        <div class="row">
                                            <div class="col-sm-3 mb-2">
                                                <small class="text-muted d-block">วันที่</small>
                                                <span class="font-weight-bold text-dark" id="modal_work_date">-</span>
                                            </div>
                                            <div class="col-sm-3 mb-2">
                                                <small class="text-muted d-block">เวลาเข้า (เช้า)</small>
                                                <span class="font-weight-bold text-success" id="modal_start_time">-</span>
                                            </div>
                                            <div class="col-sm-3 mb-2">
                                                <small class="text-muted d-block">เวลาออก (เย็น)</small>
                                                <span class="font-weight-bold text-primary" id="modal_end_time">-</span>
                                            </div>
                                            <div class="col-sm-3 mb-2">
                                                <small class="text-muted d-block">เครื่องสแกน</small>
                                                <span class="font-weight-bold text-dark" id="modal_device">-</span>
                                            </div>
                                        </div>
                                        <div class="row pt-2 border-top">
                                            <div class="col-12">
                                                <small class="text-muted d-block">ประวัติการสแกนทั้งหมดของวัน:</small>
                                                <span class="badge badge-light border text-dark p-2" id="modal_all_scans">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Leave Details Card -->
                                <div class="card border-left-warning shadow-sm" id="modal_leave_section">
                                    <div class="card-header py-2 bg-light d-flex justify-content-between align-items-center">
                                        <h6 class="m-0 font-weight-bold text-warning"><i class="fa fa-calendar-check-o"></i> ข้อมูลเอกสารการลา / วันหยุด / เปลี่ยนกะ</h6>
                                        <span class="badge badge-warning" id="modal_leave_count">0 รายการ</span>
                                    </div>
                                    <div class="card-body py-2" id="modal_leave_content">
                                        <p class="text-muted mb-0">ไม่มีข้อมูลการลาในวันนี้</p>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    <i class="fa fa-times"></i> ปิดหน้าต่าง (Close)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                include('includes/Footer.php');
                ?>

            </div>
        </div>
    </div>

    <?php
    include('includes/Modal-Logout.php');
    ?>

    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fa fa-angle-up"></i>
    </a>

    <!-- Scripts -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>

    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

    <style>
        .quick-filter.active {
            font-weight: bold;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
        }
        #TableRecordList th {
            text-align: center;
            vertical-align: middle;
        }
        #TableRecordList td {
            vertical-align: middle;
        }
    </style>

    <script>
        $(document).ready(function () {
            // Initialize Datepickers
            $('.datepicker').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true,
                endDate: new Date()
            });

            // Load Employees Dropdown
            $.ajax({
                type: "POST",
                url: "model/report_time_attendance_leave_process.php",
                data: {action: "GET_EMPLOYEE"},
                dataType: "json",
                success: function (data) {
                    $.each(data, function (i, item) {
                        $('#employeeSelect').append($('<option>', {
                            value: item.emp_id,
                            text: item.emp_id + ' ' + item.f_name + ' ' + item.l_name + ' (' + item.department_id + ')'
                        }));
                    });
                }
            });

            // Initialize DataTable
            let dataRecords = $('#TableRecordList').DataTable({
                'searchDelay': 500,
                'order': [[4, 'desc'], [0, 'desc']],
                'lengthMenu': [[10, 25, 50, 100], [10, 25, 50, 100]],
                'language': {
                    search: 'ค้นหาด่วน:',
                    lengthMenu: 'แสดง _MENU_ รายการ',
                    info: 'หน้าที่ _PAGE_ จากทั้งหมด _PAGES_ หน้า (ทั้งหมด _TOTAL_ รายการ)',
                    infoEmpty: 'ไม่พบรายการข้อมูล',
                    zeroRecords: "ไม่มีข้อมูลตามเงื่อนไขที่เลือก",
                    infoFiltered: '(กรองจากทั้งหมด _MAX_ รายการ)',
                    paginate: {
                        previous: 'ก่อนหน้า',
                        next: 'ถัดไป',
                        first: 'หน้าแรก',
                        last: 'หน้าสุดท้าย'
                    }
                },
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                'ajax': {
                    'url': 'model/report_time_attendance_leave_process.php',
                    'data': function (d) {
                        d.action = "GET_ATTENDANCE_LEAVE";
                        d.start_date = $('#doc_date_start').val();
                        d.end_date = $('#doc_date_to').val();
                        d.emp_id = $('#employeeSelect').val();
                        d.filter_status = 'ALL';
                        $('#search_keyword').val(d.search.value);
                    }
                },
                'columns': [
                    {data: 'emp_id', className: 'text-center font-weight-bold'},
                    {data: 'f_name'},
                    {data: 'l_name'},
                    {data: 'department_id'},
                    {data: 'work_date', className: 'text-center'},
                    {data: 'start_time', className: 'text-center'},
                    {data: 'end_time', className: 'text-center'},
                    {data: 'leave_type_detail', className: 'text-center'},
                    {data: 'remark'},
                    {data: 'action', className: 'text-center', orderable: false}
                ]
            });

            // Update badge info
            function updateBadge() {
                let empText = ($('#employeeSelect').val() && $('#employeeSelect').val() !== '-') ? $('#employeeSelect option:selected').text() : 'พนักงานทุกคน';
                let dateRange = $('#doc_date_start').val() + ' ถึง ' + $('#doc_date_to').val();
                $('#filterInfoBadge').html('ช่วงวันที่: <strong>' + dateRange + '</strong> | พนักงาน: <strong>' + empText + '</strong>');
            }
            updateBadge();

            // Employee select change
            $('#employeeSelect').change(function () {
                updateBadge();
                dataRecords.ajax.reload();
            });

            // Search Button
            $('#btnSearch').click(function () {
                updateBadge();
                dataRecords.ajax.reload();
            });

            // Reset Button
            $('#btnReset').click(function () {
                $('#doc_date_start').val('<?php echo $current_first_date; ?>');
                $('#doc_date_to').val('<?php echo $current_last_date; ?>');
                $('#employeeSelect').val('-');
                updateBadge();
                dataRecords.search('').draw();
                dataRecords.ajax.reload();
            });

            // Show Detail Modal
            $("#TableRecordList").on('click', '.detail', function () {
                let id = $(this).attr("id");
                $.ajax({
                    type: "POST",
                    url: 'model/report_time_attendance_leave_process.php',
                    dataType: "json",
                    data: {action: "GET_DATA", id: id},
                    success: function (response) {
                        if (response.length > 0) {
                            let rec = response[0];
                            $('#modal_emp_id').text(rec.emp_id || '-');
                            $('#modal_full_name').text((rec.f_name || '') + ' ' + (rec.l_name || ''));
                            $('#modal_department').text(rec.department_id || '-');
                            $('#modal_work_date').text(rec.work_date || '-');
                            let inText = rec.start_time ? rec.start_time.substring(0, 5) + ' น.' : 'ไม่มีบันทึกเวลาเข้า';
                            if (rec.att_start_time) {
                                inText += ' <small class="badge badge-success">เวลาสแกน</small>';
                            } else if (rec.start_time) {
                                inText += ' <small class="badge badge-info">เวลาตามเอกสาร</small>';
                            }
                            $('#modal_start_time').html(inText);

                            let outText = rec.end_time ? rec.end_time.substring(0, 5) + ' น.' : 'ไม่มีบันทึกเวลาออก';
                            if (rec.att_end_time) {
                                outText += ' <small class="badge badge-primary">เวลาสแกน</small>';
                            } else if (rec.end_time) {
                                outText += ' <small class="badge badge-info">เวลาตามเอกสาร</small>';
                            }
                            $('#modal_end_time').html(outText);

                            $('#modal_device').text(rec.device || 'ไม่ระบุเครื่อง');
                            $('#modal_all_scans').text(rec.all_scans || 'ไม่มีการสแกนนิ้ว/บัตรในวันดังกล่าว');

                            // Leave Section
                            let leaveHtml = '';
                            if (rec.leaves && rec.leaves.length > 0) {
                                $('#modal_leave_count').text(rec.leaves.length + ' รายการ').removeClass('badge-secondary').addClass('badge-warning');
                                leaveHtml += '<div class="list-group">';
                                $.each(rec.leaves, function(idx, lv) {
                                    let sourceName = 'ใบลาทั่วไป (v_dleave_event)';
                                    if (lv.source_type === 'HOLIDAY') sourceName = 'วันหยุดนักขัตฤกษ์/วันหยุดบริษัท (vdholiday_event)';
                                    if (lv.source_type === 'CHANGE') sourceName = 'ใบเปลี่ยนวันหยุด/สลับกะ (v_dchange_event)';

                                    let statusText = (lv.status === 'A') ? '<span class="badge badge-success">อนุมัติแล้ว</span>' : '<span class="badge badge-warning">รอพิจารณา</span>';

                                    leaveHtml += '<div class="list-group-item list-group-item-action flex-column align-items-start mb-2 border rounded shadow-sm">';
                                    leaveHtml += '  <div class="d-flex w-100 justify-content-between align-items-center mb-1">';
                                    leaveHtml += '    <h6 class="mb-1 font-weight-bold text-primary"><i class="fa fa-file-text-o"></i> เลขที่: ' + (lv.doc_id || '-') + ' (' + (lv.leave_type_detail || '-') + ')</h6>';
                                    leaveHtml += '    <span>' + statusText + '</span>';
                                    leaveHtml += '  </div>';
                                    if (lv.source_type === 'CHANGE') {
                                        leaveHtml += '  <p class="mb-1 text-dark"><strong>วันที่หยุดปกติ:</strong> ' + lv.date_leave_start + ' | <strong>วันที่ต้องการหยุด:</strong> ' + lv.date_leave_to + '</p>';
                                    } else {
                                        leaveHtml += '  <p class="mb-1 text-dark"><strong>ระยะเวลา:</strong> จากวันที่ ' + lv.date_leave_start + ' ถึง ' + lv.date_leave_to + ' (จำนวน ' + lv.leave_day + ' วัน ' + lv.leave_hour + ' ชม.)</p>';
                                    }
                                    if (lv.time_leave_start || lv.time_leave_to) {
                                        leaveHtml += '  <p class="mb-1 text-muted"><small><i class="fa fa-clock-o"></i> ช่วงเวลา: ' + (lv.time_leave_start || '-') + ' ถึง ' + (lv.time_leave_to || '-') + '</small></p>';
                                    }
                                    if (lv.remark) {
                                        leaveHtml += '  <p class="mb-1 text-muted"><small><strong>เหตุผล / หมายเหตุ:</strong> ' + lv.remark + '</small></p>';
                                    }
                                    leaveHtml += '  <small class="text-secondary"><i class="fa fa-database"></i> แหล่งที่มา: ' + sourceName + '</small>';
                                    leaveHtml += '</div>';
                                });
                                leaveHtml += '</div>';
                            } else {
                                $('#modal_leave_count').text('0 รายการ').removeClass('badge-warning').addClass('badge-secondary');
                                leaveHtml = '<div class="alert alert-light border text-muted mb-0"><i class="fa fa-info-circle"></i> ไม่พบเอกสารการลาหรือวันหยุดที่บันทึกในระบบสำหรับวันนี้</div>';
                            }
                            $('#modal_leave_content').html(leaveHtml);

                            $('#recordModal').modal('show');
                        }
                    },
                    error: function (xhr, status, error) {
                        alertify.error("ไม่สามารถโหลดรายละเอียดได้: " + error);
                    }
                });
            });
        });
    </script>
    </body>
    </html>
<?php } ?>

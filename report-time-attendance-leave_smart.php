<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    $current_first_date = date('01-m-Y');
    $current_last_date = date('d-m-Y'); // วันที่ปัจจุบัน
    $page_title = !empty($_GET['s']) ? urldecode($_GET['s']) : "รายงานการ เข้า - ออก และการลาประจำวัน";
    $is_supervisor = (isset($_SESSION['role']) && strtoupper($_SESSION['role']) === 'SUPERVISOR') ||
                     (isset($_SESSION['account_type']) && strtolower($_SESSION['account_type']) === 'supervisor');
    $my_emp_id = $_SESSION['emp_id'] ?? '';
    $my_emp_name = trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? ''));
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <body id="page-top">
    <div id="wrapper">
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php
                include('includes/Top-Bar_Mobile.php');
                ?>
                <!-- Container Fluid-->
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-3 mt-2">
                        <h1 class="h4 mb-0 text-gray-800"><?php echo htmlspecialchars($page_title); ?></h1>
                    </div>
                    <button type="button" id="backBtn" name="backBtn" class="btn btn-danger btn-sm mb-3">
                        <i class="fa fa-reply"></i> กลับหน้าแรก
                    </button>

                    <!-- Filter Card (Mobile Friendly) -->
                    <div class="card mb-3 shadow-sm border-left-primary">
                        <div class="card-header py-2 bg-light d-flex justify-content-between align-items-center" data-toggle="collapse" data-target="#filterCollapse" style="cursor: pointer;">
                            <span class="font-weight-bold text-primary"><i class="fa fa-filter"></i> ตัวกรองข้อมูล</span>
                            <i class="fa fa-chevron-down text-primary"></i>
                        </div>
                        <div id="filterCollapse" class="collapse show">
                            <div class="card-body py-2">
                                <form id="filterForm" method="post" action="export_process/export_time_attendance_leave.php">
                                    <div class="form-row">
                                        <div class="form-group col-6 col-sm-3">
                                            <label for="doc_date_start" class="small font-weight-bold">จากวันที่:</label>
                                            <input type="text" class="form-control form-control-sm datepicker" id="doc_date_start" name="doc_date_start" value="<?php echo $current_first_date; ?>" readonly>
                                        </div>
                                        <div class="form-group col-6 col-sm-3">
                                            <label for="doc_date_to" class="small font-weight-bold">ถึงวันที่:</label>
                                            <input type="text" class="form-control form-control-sm datepicker" id="doc_date_to" name="doc_date_to" value="<?php echo $current_last_date; ?>" readonly>
                                        </div>
                                        <div class="form-group col-12 col-sm-6">
                                            <label for="employeeSelect" class="small font-weight-bold">เลือกพนักงาน:</label>
                                            <select id="employeeSelect" name="employeeSelect" class="form-control form-control-sm">
                                                <option value="-">-- พนักงานทุกคน --</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end mb-2 flex-wrap">
                                        <?php if ($is_supervisor) { ?>
                                            <button type="button" id="btnMyData" class="btn btn-info btn-sm mr-2 mb-1">
                                                <i class="fa fa-user"></i> ข้อมูลตนเอง
                                            </button>
                                        <?php } ?>
                                        <button type="button" id="btnSearch" class="btn btn-primary btn-sm mr-2 mb-1">
                                            <i class="fa fa-search"></i> ค้นหา
                                        </button>
                                        <button type="button" id="btnReset" class="btn btn-secondary btn-sm mb-1">
                                            <i class="fa fa-refresh"></i> รีเซ็ต
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Table Card -->
                    <div class="card mb-4 shadow">
                        <div class="card-body p-2">
                            <div class="table-responsive">
                                <table id='TableRecordList' class='display table table-striped table-bordered table-sm' width="100%">
                                    <thead>
                                    <tr>
                                        <th>ชื่อ-นามสกุล</th>
                                        <th>วันที่</th>
                                        <th>เวลาเข้า</th>
                                        <th>เวลาออก</th>
                                        <th>สถานะ/การลา</th>
                                        <th>ดู</th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Record Detail Modal -->
                <div class="modal fade" id="recordModal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-primary text-white py-2">
                                <h6 class="modal-title font-weight-bold">
                                    <i class="fa fa-id-card-o"></i> ข้อมูลบันทึกเวลาและการลา
                                </h6>
                                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body p-3">
                                <p class="mb-1"><strong>พนักงาน:</strong> <span id="modal_full_name">-</span> (<span id="modal_emp_id">-</span>)</p>
                                <p class="mb-1"><strong>แผนก:</strong> <span id="modal_department">-</span></p>
                                <p class="mb-1"><strong>วันที่:</strong> <span id="modal_work_date">-</span></p>
                                <hr class="my-2">
                                <p class="mb-1"><strong>เวลาเข้า:</strong> <span id="modal_start_time" class="text-success font-weight-bold">-</span></p>
                                <p class="mb-1"><strong>เวลาออก:</strong> <span id="modal_end_time" class="text-primary font-weight-bold">-</span></p>
                                <p class="mb-2"><strong>ประวัติสแกน:</strong> <span id="modal_all_scans">-</span></p>
                                <div id="modal_leave_section">
                                    <div class="alert alert-warning p-2 mb-0" id="modal_leave_content">
                                        กำลังโหลด...
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer py-1">
                                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">ปิด</button>
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

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

    <script>
        $(document).ready(function () {
            $('#backBtn').click(function () {
                window.location.href = "<?php echo $_SESSION['dashboard_page']; ?>";
            });

            $('.datepicker').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true,
                endDate: new Date()
            });

            // Load Employees
            $.ajax({
                type: "POST",
                url: "model/report_time_attendance_leave_process.php",
                data: {action: "GET_EMPLOYEE"},
                dataType: "json",
                success: function (data) {
                    $.each(data, function (i, item) {
                        $('#employeeSelect').append($('<option>', {
                            value: item.emp_id,
                            text: item.emp_id + ' ' + item.f_name + ' ' + item.l_name
                        }));
                    });
                }
            });

            let dataRecords = $('#TableRecordList').DataTable({
                'searchDelay': 500,
                'order': [[1, 'desc'], [2, 'desc']],
                'lengthMenu': [[10, 20, 50], [10, 20, 50]],
                'language': {
                    search: 'ค้นหา:',
                    lengthMenu: '_MENU_',
                    info: '_PAGE_/_PAGES_',
                    zeroRecords: "ไม่พบข้อมูล",
                    paginate: { previous: '‹', next: '›' }
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
                    }
                },
                'columns': [
                    {data: 'full_name'},
                    {data: 'work_date'},
                    {data: 'start_time'},
                    {data: 'end_time'},
                    {data: 'leave_type_detail'},
                    {data: 'action', orderable: false}
                ]
            });

            let myEmpId = '<?php echo $my_emp_id; ?>';
            let myEmpName = '<?php echo htmlspecialchars($my_emp_name); ?>';

            $('#btnMyData').click(function () {
                if (!myEmpId) return;
                if ($('#employeeSelect option[value="' + myEmpId + '"]').length === 0) {
                    $('#employeeSelect').append($('<option>', {
                        value: myEmpId,
                        text: myEmpId + (myEmpName ? ' ' + myEmpName : '')
                    }));
                }
                $('#employeeSelect').val(myEmpId);
                dataRecords.search('').draw();
                dataRecords.ajax.reload();
            });

            $('#btnSearch').click(function () { dataRecords.ajax.reload(); });
            $('#employeeSelect').change(function () { dataRecords.ajax.reload(); });
            $('#btnReset').click(function () {
                $('#doc_date_start').val('<?php echo $current_first_date; ?>');
                $('#doc_date_to').val('<?php echo $current_last_date; ?>');
                $('#employeeSelect').val('-');
                dataRecords.search('').draw();
                dataRecords.ajax.reload();
            });

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
                            let inText = rec.start_time ? rec.start_time.substring(0, 5) + ' น.' : '-';
                            if (rec.att_start_time) {
                                inText += ' <span class="badge badge-success" style="font-size:10px;">สแกน</span>';
                            } else if (rec.start_time) {
                                inText += ' <span class="badge badge-info" style="font-size:10px;">ตามเอกสาร</span>';
                            }
                            $('#modal_start_time').html(inText);

                            let outText = rec.end_time ? rec.end_time.substring(0, 5) + ' น.' : '-';
                            if (rec.att_end_time) {
                                outText += ' <span class="badge badge-primary" style="font-size:10px;">สแกน</span>';
                            } else if (rec.end_time) {
                                outText += ' <span class="badge badge-info" style="font-size:10px;">ตามเอกสาร</span>';
                            }
                            $('#modal_end_time').html(outText);

                            $('#modal_all_scans').text(rec.all_scans || 'ไม่มีการสแกน');

                            let lHtml = '';
                            if (rec.leaves && rec.leaves.length > 0) {
                                $.each(rec.leaves, function(idx, lv) {
                                    lHtml += '<div class="mb-2 pb-1 border-bottom">';
                                    lHtml += '  <strong><i class="fa fa-calendar-check-o"></i> ' + (lv.leave_type_detail || '-') + '</strong> (' + (lv.doc_id || '-') + ')<br>';
                                    if (lv.source_type === 'CHANGE') {
                                        lHtml += '  <small class="text-muted">วันหยุดปกติ: ' + lv.date_leave_start + ' | วันที่ต้องการหยุด: ' + lv.date_leave_to + '</small>';
                                    } else {
                                        lHtml += '  <small class="text-muted">' + lv.date_leave_start + ' ถึง ' + lv.date_leave_to + ' (' + lv.leave_day + ' วัน ' + lv.leave_hour + ' ชม.)</small>';
                                    }
                                    if (lv.time_leave_start || lv.time_leave_to) {
                                        lHtml += '<br><small class="text-info"><i class="fa fa-clock-o"></i> เวลา: ' + (lv.time_leave_start || '-') + ' ถึง ' + (lv.time_leave_to || '-') + '</small>';
                                    }
                                    if (lv.remark) lHtml += '<br><small>' + lv.remark + '</small>';
                                    lHtml += '</div>';
                                });
                            } else {
                                lHtml = '<small class="text-muted">ไม่มีข้อมูลการลาในวันนี้</small>';
                            }
                            $('#modal_leave_content').html(lHtml);
                            $('#recordModal').modal('show');
                        }
                    }
                });
            });
        });
    </script>
    </body>
    </html>
<?php } ?>

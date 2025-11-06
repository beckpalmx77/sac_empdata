<?php
session_start();
error_reporting(0);

$curr_date = date("d-m-Y");
$leave_type_id = "H2";
$leave_type_detail = "วันหยุดนักขัตฤกษ์-ประจำปี";

include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['department_id']) == "") {
    header("Location: index.php");
} else {
    ?>

    <!DOCTYPE html>
    <html lang="th">
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
                        <h1 class="h3 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                            <li class="breadcrumb-item active"
                                aria-current="page"><?php echo urldecode($_GET['s']) ?></li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">

                                        <div class="col-md-12 col-md-offset-2">
                                            <label for="name_t"
                                                   class="control-label"><b>เพิ่ม <?php echo urldecode($_GET['s']) ?></b></label>
                                            <button type='button' name='btnAdd' id='btnAdd'
                                                    class='btn btn-primary btn-xs'>Add
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>ปี</th>
                                                    <th>วันที่เอกสาร</th>
                                                    <th>ชื่อ - นามสกุล</th>
                                                    <th>แผนก</th>
                                                    <th>วันที่หยุด</th>
                                                    <th>ประเภทวันหยุด</th>
                                                    <th>หมายเหตุ</th>
                                                    <th>สถานะ</th>
                                                    <th>รูปภาพ</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>ปี</th>
                                                    <th>วันที่เอกสาร</th>
                                                    <th>ชื่อ - นามสกุล</th>
                                                    <th>แผนก</th>
                                                    <th>วันที่หยุด</th>
                                                    <th>ประเภทวันหยุด</th>
                                                    <th>หมายเหตุ</th>
                                                    <th>สถานะ</th>
                                                    <th>รูปภาพ</th>
                                                    <th>Action</th>
                                                </tr>
                                                </tfoot>
                                            </table>

                                            <div id="result"></div>

                                        </div>

                                        <div class="modal fade" id="recordModal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Modal title</h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-hidden="true">×
                                                        </button>
                                                    </div>
                                                    <form method="post" id="recordForm">
                                                        <div class="modal-body">
                                                            <div class="modal-body">

                                                                <div class="form-group">
                                                                    <label for="doc_id"
                                                                           class="control-label"></label>
                                                                    <input type="hidden" class="form-control"
                                                                           id="doc_id" name="doc_id"
                                                                           readonly="true"
                                                                           placeholder="สร้างอัตโนมัติ">
                                                                </div>

                                                                <input type="hidden" class="form-control"
                                                                       id="page_manage" name="page_manage"
                                                                       readonly="true"
                                                                       value="USER"
                                                                       placeholder="page_manage">

                                                                <input type="hidden" class="form-control"
                                                                       id="department" name="department"
                                                                       readonly="true"
                                                                       value="<?php echo $_SESSION['department_id'] ?>"
                                                                       placeholder="department">

                                                                <div class="form-group row">
                                                                    <div class="col-sm-3">
                                                                        <label for="doc_date"
                                                                               class="control-label">วันที่เอกสาร</label>
                                                                        <input type="text" class="form-control"
                                                                               id="doc_date"
                                                                               name="doc_date"
                                                                               required="required"
                                                                               readonly="true"
                                                                               value="<?php echo $curr_date ?>"
                                                                               placeholder="วันที่เอกสาร">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-4">
                                                                        <label for="text"
                                                                               class="control-label">รหัสพนักงาน</label>
                                                                        <input type="text" class="form-control"
                                                                               id="emp_id" name="emp_id"
                                                                               readonly="true"
                                                                               required="required"
                                                                               value="<?php echo $_SESSION['emp_id']; ?>"
                                                                               placeholder="">
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <label for="text"
                                                                               class="control-label">ชื่อ -
                                                                            นามสกุล</label>
                                                                        <input type="hidden" id="f_name" name="f_name"
                                                                               value="<?php echo $_SESSION['first_name']; ?>">
                                                                        <input type="hidden" id="l_name" name="l_name"
                                                                               value="<?php echo $_SESSION['last_name']; ?>">
                                                                        <input type="text" class="form-control"
                                                                               id="full_name" name="full_name"
                                                                               readonly="true"
                                                                               value="<?php echo $_SESSION['first_name'] . " " . $_SESSION['last_name']; ?>"
                                                                               placeholder="">
                                                                    </div>
                                                                    <?php if ($_SESSION['role'] !== "EMPLOYEE") { ?>
                                                                        <div class="col-sm-2">
                                                                            <label for="emp_id"
                                                                                   class="control-label">เลือก</label>
                                                                            <a data-toggle="modal"
                                                                               href="#SearchEmployeeModal"
                                                                               class="btn btn-primary">
                                                                                Click <i class="fa fa-search"
                                                                                         aria-hidden="true"></i>
                                                                            </a>
                                                                        </div>
                                                                    <?php } ?>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <input type="hidden" class="form-control"
                                                                           id="leave_type_id"
                                                                           name="leave_type_id"
                                                                           value="<?php echo $leave_type_id ?>">
                                                                    <div class="col-sm-10">
                                                                        <label for="leave_type_detail"
                                                                               class="control-label">ประเภทวันหยุด</label>
                                                                        <input type="text" class="form-control"
                                                                               id="leave_type_detail"
                                                                               name="leave_type_detail"
                                                                               required="required"
                                                                               readonly="true"
                                                                               value="<?php echo $leave_type_detail ?>"
                                                                               placeholder="ประเภทวันหยุด">
                                                                    </div>

                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-3">
                                                                        <label for="date_leave_start"
                                                                               class="control-label">วันที่ต้องการหยุด</label>
                                                                        <i class="fa fa-calendar"
                                                                           aria-hidden="true"></i>
                                                                        <input type="text" class="form-control"
                                                                               id="date_leave_start"
                                                                               name="date_leave_start"
                                                                               required="required"
                                                                               value="<?php echo $curr_date ?>"
                                                                               readonly="true"
                                                                               placeholder="วันที่หยุด">
                                                                    </div>
                                                                    <div class="col-sm-3">
                                                                        <label for="time_leave_start"
                                                                               class="control-label">เวลาเริ่มต้น</label>
                                                                        <input type="text" class="form-control"
                                                                               id="time_leave_start"
                                                                               name="time_leave_start"
                                                                               value="<?php echo $_SESSION['work_time_start'] ?>"
                                                                               required="required"
                                                                               placeholder="">
                                                                    </div>
                                                                    <div class="col-sm-3">
                                                                        <label for="time_leave_to"
                                                                               class="control-label">เวลาสิ้นสุด</label>
                                                                        <input type="text" class="form-control"
                                                                               id="time_leave_to"
                                                                               name="time_leave_to"
                                                                               required="required"
                                                                               value="<?php echo $_SESSION['work_time_stop'] ?>"
                                                                               placeholder="เวลาสิ้นสุด">
                                                                    </div>
                                                                    <div class="col-sm-3">
                                                                        <label for="date_leave_to"
                                                                               class="control-label"></label>
                                                                        <!--i class="fa fa-calendar"
                                                                           aria-hidden="true"></i-->
                                                                        <input type="hidden" class="form-control"
                                                                               id="date_leave_to"
                                                                               name="date_leave_to"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <div class="col-sm-3">
                                                                        <label for="leave_day"
                                                                               class="control-label">จำนวนวัน</label>
                                                                        <input type="text" class="form-control"
                                                                               id="leave_day"
                                                                               name="leave_day"
                                                                               value="1"
                                                                               placeholder="">
                                                                    </div>
                                                                    <div class="col-sm-3">
                                                                        <label for="leave_hour"
                                                                               class="control-label">จำนวนชั่วโมง</label>
                                                                        <input type="text" class="form-control"
                                                                               id="leave_hour"
                                                                               name="leave_hour"
                                                                               value="0"
                                                                               placeholder="เวลาสิ้นสุด">
                                                                    </div>
                                                                    <!--/div>

                                                                    <div class="form-group"-->
                                                                    <div class="col-sm-6">
                                                                        <label for="remark"
                                                                               class="control-label">หมายเหตุ</label>
                                                                        <textarea class="form-control"
                                                                                  id="remark"
                                                                                  name="remark"
                                                                                  rows="1"></textarea>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row" id="uploadSection">
                                                                    <div class="col-sm-6">
                                                                        <label for="upload_image" class="control-label">เอกสารแนบ
                                                                            (Upload รูปภาพ)</label>
                                                                        <input type="file" class="form-control-file"
                                                                               id="image_upload" name="image_upload"
                                                                               accept="image/*"
                                                                               onchange="previewImage(event)">
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <label class="control-label">เอกสารที่แนบ (Click
                                                                            ที่รูปเพื่อขยาย)</label>
                                                                        <br>
                                                                        <img id="preview" src="" alt="Preview"
                                                                             style="max-width: 100px; cursor: pointer; display: none;"
                                                                             data-toggle="modal"
                                                                             data-target="#imageModal">
                                                                    </div>
                                                                </div>

                                                                <?php if ($_SESSION['approve_permission'] === 'Y') { ?>
                                                                    <div class="form-group">
                                                                        <label for="status"
                                                                               class="control-label">สถานะเอกสาร</label>
                                                                        <!--N = รอพิจารณา , A = อนุมัติ , R = ไม่อนุมัติ -->
                                                                        <select id="status" name="status"
                                                                                class="form-control"
                                                                                data-live-search="true"
                                                                                title="Please select">
                                                                            <option value="N">รอพิจารณา</option>
                                                                            <option value="A">อนุมัติ</option>
                                                                            <option value="R">ไม่อนุมัติ</option>
                                                                        </select>
                                                                    </div>
                                                                <?php } else { ?>
                                                                    <div class="form-group">
                                                                        <label for="status" class="control-label">สถานะเอกสาร</label>
                                                                        <!--N = รอพิจารณา , A = อนุมัติ , R = ไม่อนุมัติ -->
                                                                        <select id="status" name="status"
                                                                                class="form-control"
                                                                                data-live-search="true"
                                                                                disabled="true"
                                                                        <!-- ใช้ disabled แทน readonly -->
                                                                        title="Please select">
                                                                        <option value="N">รอพิจารณา</option>
                                                                        <option value="A">อนุมัติ</option>
                                                                        <option value="R">ไม่อนุมัติ</option>
                                                                        </select>
                                                                    </div>

                                                                <?php } ?>

                                                            </div>
                                                        </div>

                                                        <div class="modal-footer">
                                                            <input type="hidden" name="id" id="id"/>
                                                            <input type="hidden" name="action" id="action" value=""/>
                                                            <span class="icon-input-btn">
                                                                <i class="fa fa-check"></i>
                                                            <input type="submit" name="save" id="save"
                                                                   class="btn btn-primary" value="Save"/>
                                                            </span>
                                                            <button type="button" class="btn btn-danger"
                                                                    data-dismiss="modal">Close <i
                                                                        class="fa fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </form>

                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="SearchEmployeeModal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Modal title</h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-hidden="true">×
                                                        </button>
                                                    </div>

                                                    <div class="container"></div>
                                                    <div class="modal-body">

                                                        <div class="modal-body">

                                                            <table cellpadding="0" cellspacing="0" border="0"
                                                                   class="display"
                                                                   id="TableEmployeeList"
                                                                   width="100%">
                                                                <thead>
                                                                <tr>
                                                                    <th>รหัสพนักงาน</th>
                                                                    <th>ชื่อพนักงาน</th>
                                                                    <th>ชื่อเล่น</th>
                                                                    <th>หน่วยงาน</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </thead>
                                                                <tfoot>
                                                                <tr>
                                                                    <th>รหัสพนักงาน</th>
                                                                    <th>ชื่อพนักงาน</th>
                                                                    <th>ชื่อเล่น</th>
                                                                    <th>หน่วยงาน</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="modal fade" id="SearchLeaveTypeModal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Modal title</h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-hidden="true">×
                                                        </button>
                                                    </div>

                                                    <div class="container"></div>
                                                    <div class="modal-body">

                                                        <div class="modal-body">

                                                            <table cellpadding="0" cellspacing="0" border="0"
                                                                   class="display"
                                                                   id="TableLeaveTypeList"
                                                                   width="100%">
                                                                <thead>
                                                                <tr>
                                                                    <th>รหัสประเภทการลา</th>
                                                                    <th>รายละเอียด</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </thead>
                                                                <tfoot>
                                                                <tr>
                                                                    <th>รหัสประเภทการลา</th>
                                                                    <th>รายละเอียด</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal แสดงภาพ -->
                                        <div class="modal fade" id="imageModal" tabindex="-1"
                                             aria-labelledby="imageModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">เอกสารแนบ</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body text-center">
                                                        <img id="modalImage" src="" alt="Full Image" class="img-fluid">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

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
    </div>

    <?php
    include('includes/Modal-Logout.php');
    ?>


    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>


    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <script src="js/modal/show_employee_modal.js"></script>
    <script src="js/modal/show_leave_type_modal.js"></script>

    <!-- Page level plugins -->

    <!--script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.5.2/bootbox.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.0/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.0/css/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.0/css/buttons.dataTables.min.css"/-->


    <script src="https://cdn.datatables.net/rowreorder/1.4.1/js/dataTables.rowReorder.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <link rel="stylesheet" href="https://cdn.datatables.net/rowreorder/1.4.1/css/rowReorder.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css"/>

    <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <!--link href="vendor/date-picker-1.9/css/date_picker_style.css" rel="stylesheet"/-->
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <script src="js/popup.js"></script>

    <style>

        .icon-input-btn {
            display: inline-block;
            position: relative;
        }

        .icon-input-btn input[type="submit"] {
            padding-left: 2em;
        }

        .icon-input-btn .fa {
            display: inline-block;
            position: absolute;
            left: 0.65em;
            top: 30%;
        }
    </style>
    <script>
        $(document).ready(function () {
            $(".icon-input-btn").each(function () {
                let btnFont = $(this).find(".btn").css("font-size");
                let btnColor = $(this).find(".btn").css("color");
                $(this).find(".fa").css({'font-size': btnFont, 'color': btnColor});
            });
        });
    </script>

    <!--script>
        $(document).ready(function () {

            let formData = {action: "GET_LEAVE_DOCUMENT", sub_action: "GET_MASTER", page_manage: "USER",};

            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[8, 10, 20, 50, 100], [8, 10, 20, 50, 100]],
                'language': {
                    search: 'ค้นหา', lengthMenu: 'แสดง _MENU_ รายการ',
                    info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                    infoEmpty: 'ไม่มีข้อมูล',
                    zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                    infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
                    paginate: {
                        previous: 'ก่อนหน้า',
                        last: 'สุดท้าย',
                        next: 'ต่อไป'
                    }
                },
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                'autoWidth': true,
                <?php if ($_SESSION['deviceType'] !== 'computer') {
        echo "'scrollX': true,";
    } ?>
                'ajax': {
                    'url': 'model/manage_holiday_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'doc_year'},
                    {data: 'doc_date'},
                    {data: 'full_name'},
                    {data: 'department_id'},
                    {data: 'date_leave_start'},
                    {data: 'leave_type_detail'},
                    {data: 'remark'},
                    {data: 'status'},
                    {data: 'image'},
                    {data: 'update'},
                ],
                'drawCallback': function (settings) {
                    // ตรวจสอบและเพิ่ม class 'blink'
                    $('#TableRecordList .image').each(function () {
                        let picture = $(this).data('picture'); // ดึงค่าจาก data-picture
                        if (picture) { // หากมีค่า picture
                            $(this).addClass('blink');
                        }
                    });
                }
            });

            // สร้าง animation กระพริบ
            $('<style>')
                .prop('type', 'text/css')
                .html(`
                .blink {
                    animation: blinker 1s linear infinite;
                }
                @keyframes blinker {
                    50% { opacity: 0; }
                }
            `)
                .appendTo('head');
        });
    </script-->

    <script>
        $(document).ready(function () {
            let formData = {action: "GET_HOLIDAY_DOCUMENT", sub_action: "GET_MASTER", page_manage: "USER"};

            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[8, 10, 20, 50, 100], [8, 10, 20, 50, 100]],
                'language': {
                    search: 'ค้นหา',
                    lengthMenu: 'แสดง _MENU_ รายการ',
                    info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                    infoEmpty: 'ไม่มีข้อมูล',
                    zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                    infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
                    paginate: {previous: 'ก่อนหน้า', last: 'สุดท้าย', next: 'ต่อไป'}
                },
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                'autoWidth': false, // ❌ ปิด autoWidth เพื่อให้ Responsive ดีขึ้น
                'searching': true,
                'scrollX': true, // ✅ เปิดใช้งาน scrollX เพื่อให้ Scroll ได้ในมือถือ
                'ajax': {
                    'url': 'model/manage_holiday_process.php',
                    'type': 'POST', // ✅ ระบุ Type ของ Request ชัดเจน
                    'data': formData
                },
                'columns': [
                    {data: 'doc_year'},
                    {data: 'doc_date'},
                    {data: 'full_name'},
                    {data: 'department_id'},
                    {data: 'date_leave_start'},
                    {data: 'leave_type_detail'},
                    {data: 'remark'},
                    {data: 'status'},
                    {data: 'image'},
                    {data: 'update'},
                ],
                'drawCallback': function (settings) {
                    $('#TableRecordList .image').each(function () {
                        let picture = $(this).data('picture');
                        if (picture) {
                            $(this).addClass('blink');
                        }
                    });
                }
            });

            // ✅ ปรับให้ Table รองรับ Responsive และ Scroll ได้ดีขึ้น
            $('#TableRecordList_wrapper').addClass('table-responsive');

            // ✅ CSS สำหรับกระพริบ
            $('<style>').prop('type', 'text/css').html(`
        .blink { animation: blinker 1s linear infinite; }
        @keyframes blinker { 50% { opacity: 0; } }
    `).appendTo('head');
        });

    </script>

    <script>
        $(document).ready(function () {
            // *** FOR SUBMIT FORM ***
            $("#recordModal").on('submit', '#recordForm', function (event) {

                event.preventDefault();  // ป้องกันการ submit แบบปกติ
                $('#save').attr('disabled', 'disabled'); // ปิดการใช้งานปุ่ม Save เพื่อไม่ให้กดหลายครั้ง

                let formData = new FormData(this);  // ใช้ FormData เพื่อส่งข้อมูลที่มีไฟล์
                formData.append("filename", $('#image_upload')[0].files[0]); // เพิ่มไฟล์ลงใน FormData

                $.ajax({
                    url: 'model/manage_holiday_process.php',  // URL ที่จะส่งข้อมูลไป
                    method: "POST",  // Method เป็น POST
                    data: formData,  // ข้อมูลที่จะส่งไป
                    contentType: false,  // ไม่ต้องตั้งค่า contentType เพราะ FormData จะจัดการให้เอง
                    processData: false,  // ไม่ต้องให้ jQuery ประมวลผลข้อมูลแบบพิเศษ
                    success: function (data) {
                        alertify.success(data);  // แสดงข้อความเมื่อบันทึกสำเร็จ
                        $('#recordForm')[0].reset();  // รีเซ็ตฟอร์มหลังจากส่งข้อมูลเสร็จ
                        $('#recordModal').modal('hide');  // ปิด Modal
                        $('#save').attr('disabled', false);  // เปิดปุ่ม Save ให้ใช้ได้อีกครั้ง
                        ReloadDataTable();  // รีเฟรช DataTable ถ้ามี
                    },
                    error: function (xhr, status, error) {
                        console.error(xhr.responseText);  // แสดงข้อผิดพลาดถ้ามี
                        alertify.error('เกิดข้อผิดพลาดในการบันทึกข้อมูล');
                        $('#save').attr('disabled', false);  // เปิดปุ่ม Save
                    }
                });
            });
            // *** END OF SUBMIT FORM ***
        });
    </script>


    <script>
        $(document).ready(function () {

            $("#btnAdd").click(function () {
                //alert(<?php echo $_SESSION['work_time_start']?>);
                $('#recordModal').modal('show');
                $('#id').val("");
                $('#doc_id').val("");
                $('#date_leave_start').val("");
                $('#date_leave_to').val("");
                $('#remark').val("");
                $('#status').val("N");
                $('.modal-title').html("<i class='fa fa-plus'></i> ADD Record");
                $('#action').val('ADD');
                $('#save').val('Save');
                $('#uploadSection').show();
            });
        });
    </script>

    <script>

        $("#TableRecordList").on('click', '.update', function () {
            let id = $(this).attr("id");
            //alert(id);
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_holiday_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let doc_id = response[i].doc_id;
                        let doc_date = response[i].doc_date;
                        let emp_id = response[i].emp_id;
                        let full_name = response[i].full_name;
                        let leave_type_id = response[i].leave_type_id;
                        let leave_type_detail = response[i].leave_type_detail;
                        let date_leave_start = response[i].date_leave_start;
                        let date_leave_to = response[i].date_leave_to;
                        let time_leave_start = response[i].time_leave_start;
                        let time_leave_to = response[i].time_leave_to;
                        let remark = response[i].remark;
                        let status = response[i].status;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#doc_id').val(doc_id);
                        $('#doc_date').val(doc_date);
                        $('#emp_id').val(emp_id);
                        $('#full_name').val(full_name);
                        $('#leave_type_id').val(leave_type_id);
                        $('#leave_type_detail').val(leave_type_detail);
                        $('#date_leave_start').val(date_leave_start);
                        $('#date_leave_to').val(date_leave_to);
                        $('#time_leave_start').val(time_leave_start);
                        $('#time_leave_to').val(time_leave_to);
                        $('#remark').val(remark);
                        $('#status').val(status);
                        $('.modal-title').html("<i class='fa fa-plus'></i> Edit Record");
                        $('#action').val('UPDATE');
                        $('#save').val('Save');
                        $('#uploadSection').hide();
                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        });

    </script>

    <script>

        $("#TableRecordList").on('click', '.image', function () {
            let id = $(this).attr("id");
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_holiday_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let doc_id = response[i].doc_id;
                        let doc_date = response[i].doc_date;
                        let emp_id = response[i].emp_id;
                        let full_name = response[i].full_name;
                        let leave_type_id = response[i].leave_type_id;
                        let leave_type_detail = response[i].leave_type_detail;
                        let date_leave_start = response[i].date_leave_start;
                        let date_leave_to = response[i].date_leave_to;
                        let time_leave_start = response[i].time_leave_start;
                        let time_leave_to = response[i].time_leave_to;
                        let picture = response[i].picture;
                        let remark = response[i].remark;
                        let status = response[i].status;

                        let main_menu = "บันทึกข้อมูลหลัก";
                        let sub_menu = "เอกสารขอใช้นักขัตฤกษ์-ประจำปี (พนักงาน)";

                        let originalURL = "upload_holiday_data.php?title=เอกสารการขอใช้นักขัตฤกษ์-ประจำปี (Document)"
                            + '&main_menu=' + main_menu + '&sub_menu=' + sub_menu
                            + '&id=' + id
                            + '&doc_id=' + doc_id + '&doc_date=' + doc_date
                            + '&emp_id=' + emp_id + '&full_name=' + full_name
                            + '&leave_type_id=' + leave_type_id
                            + '&leave_type_detail=' + leave_type_detail
                            + '&date_leave_start=' + date_leave_start
                            + '&date_leave_to=' + date_leave_to
                            + '&time_leave_start=' + time_leave_start
                            + '&time_leave_to=' + time_leave_to
                            + '&picture=' + picture
                            + '&remark=' + remark
                            + '&status=' + status
                            + '&action=UPDATE';

                        //OpenPopupCenter(originalURL, "", "");
                        window.open(originalURL, '_blank');

                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        });

    </script>

    <script>
        $(document).ready(function () {
            // กำหนดวันที่ปัจจุบัน
            let today = new Date();

            // เพิ่มล่วงหน้า 3 วัน
            today.setDate(today.getDate() + 3);

            $('#date_leave_start').datepicker({
                startDate: today, // เริ่มต้นให้เลือกได้จากวันที่ล่วงหน้า 3 วัน
                format: "dd-mm-yyyy", // รูปแบบวันที่
                todayHighlight: true, // ไฮไลต์วันที่ปัจจุบัน
                language: "th", // ภาษาไทย (ถ้าเพิ่มไฟล์ภาษาไว้)
                autoclose: true // ปิดปฏิทินอัตโนมัติเมื่อเลือกวันที่
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            // กำหนดวันที่ปัจจุบัน
            let today = new Date();

            // เพิ่มล่วงหน้า 3 วัน
            today.setDate(today.getDate() + 3);

            $('#date_leave_to').datepicker({
                startDate: today, // เริ่มต้นให้เลือกได้จากวันที่ล่วงหน้า 3 วัน
                format: "dd-mm-yyyy", // รูปแบบวันที่
                todayHighlight: true, // ไฮไลต์วันที่ปัจจุบัน
                language: "th", // ภาษาไทย (ถ้าเพิ่มไฟล์ภาษาไว้)
                autoclose: true // ปิดปฏิทินอัตโนมัติเมื่อเลือกวันที่
            });
        });
    </script>

    <script>
        function ReloadDataTable() {
            $('#TableRecordList').DataTable().ajax.reload();
        }
    </script>

    <script>
        function previewImage(event) {
            let input = event.target;
            let reader = new FileReader();

            reader.onload = function () {
                let preview = document.getElementById('preview');
                let modalImage = document.getElementById('modalImage');

                preview.src = reader.result;
                modalImage.src = reader.result;

                preview.style.display = "block";
            };

            reader.readAsDataURL(input.files[0]);
        }

    </script>

    </body>
    </html>

<?php } ?>
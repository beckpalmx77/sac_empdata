<?php
session_start();
error_reporting(0);
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['department_id']) == "") {
    header("Location: index.php");
} else {

    ?>

    <!DOCTYPE html>
    <html lang="th">

    <body id="page-top">
    <div id="wrapper">


        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Container Fluid-->
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><span id="title"></span></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item"><span id="main_menu"></li>
                            <li class="breadcrumb-item active"
                                aria-current="page"><span id="sub_menu"></li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">

                                <div class="card-body">
                                    <section class="container-fluid">

                                        <form method="post" id="MainrecordForm">
                                            <input type="hidden" class="form-control" id="KeyAddData" name="KeyAddData"
                                                   value="">
                                            <div class="modal-body">
                                                <div class="modal-body">
                                                    <div class="form-group row">
                                                        <div class="col-sm-4">
                                                            <label for="emp_id"
                                                                   class="control-label">รหัสพนักงาน</label>
                                                            <input type="text" class="form-control"
                                                                   id="emp_id" name="emp_id"
                                                                   readonly="true"
                                                                   placeholder="รหัสพนักงาน">
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <label for="f_name"
                                                                   class="control-label">ชื่อ</label>
                                                            <input type="text" class="form-control"
                                                                   id="f_name"
                                                                   name="f_name"
                                                                   readonly="true"
                                                                   required="required"
                                                                   placeholder="ชื่อ">
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <label for="l_name"
                                                                   class="control-label">นามสกุล</label>
                                                            <input type="text" class="form-control"
                                                                   id="l_name"
                                                                   name="l_name"
                                                                   readonly="true"
                                                                   required="required"
                                                                   placeholder="นามสกุล">
                                                        </div>
                                                    </div>

                                        </form>

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>ปี</th>
                                                    <th>วันที่หยุด</th>
                                                    <th>เวลา</th>
                                                    <th>ประเภทวันหยุด</th>
                                                    <th>วันที่บันทึก</th>
                                                    <th>หมายเหตุ</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>

                                            </table>

                                            <div id="result"></div>

                                    </section>


                                </div>

                            </div>

                        </div>

                    </div>
                    <!--Row-->

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

                                            <input type="hidden" class="form-control"
                                                   id="emp_id" name="emp_id"
                                                   readonly="true"
                                                   value="<?php echo $_SESSION['emp_id'] ?>"
                                                   placeholder="emp_id">

                                            <div class="form-group row">
                                                <div class="col-sm-3">
                                                    <label for="doc_date"
                                                           class="control-label">วันที่เอกสาร</label>
                                                    <input type="text" class="form-control"
                                                           id="doc_date"
                                                           name="doc_date"
                                                           required="required"
                                                           value="<?php echo $curr_date ?>"
                                                           placeholder="วันที่เอกสาร">
                                                </div>
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
                                                    <label for="date_leave_start"
                                                           class="control-label"></label>
                                                    <input type="hidden" class="form-control"
                                                           id="time_leave_start"
                                                           name="time_leave_start"
                                                           value="<?php echo $_SESSION['work_time_start'] ?>"
                                                           required="required"
                                                           readonly="true"
                                                           placeholder="">
                                                </div>
                                                <div class="col-sm-3">
                                                    <label for="date_leave_start"
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
                                                <div class="col-sm-3">
                                                    <label for="time_leave_to"
                                                           class="control-label"></label>
                                                    <input type="hidden" class="form-control"
                                                           id="time_leave_to"
                                                           name="time_leave_to"
                                                           required="required"
                                                           value="<?php echo $_SESSION['work_time_stop'] ?>"
                                                           placeholder="เวลาสิ้นสุด">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="remark"
                                                       class="control-label">หมายเหตุ</label>
                                                <textarea class="form-control"
                                                          id="remark"
                                                          name="remark"
                                                          rows="3"></textarea>
                                            </div>

                                            <?php if ($_SESSION['approve_permission'] === 'Y') { ?>
                                                <div class="form-group">
                                                    <label for="status"
                                                           class="control-label">Status</label>
                                                    N = รอพิจารณา , A = อนุมัติ , R = ไม่อนุมัติ
                                                    <select id="status" name="status"
                                                            class="form-control"
                                                            data-live-search="true"
                                                            title="Please select">
                                                        <option>N</option>
                                                        <option>A</option>
                                                        <option>R</option>
                                                    </select>
                                                </div>
                                            <?php } else { ?>
                                                <div class="form-group">
                                                    <label for="status"
                                                           class="control-label">Status</label>
                                                    N = รอพิจารณา , A = อนุมัติ , R = ไม่อนุมัติ
                                                    <select id="status" name="status"
                                                            class="form-control"
                                                            data-live-search="true"
                                                            readonly="true"
                                                            title="Please select">
                                                        <option>N</option>
                                                        <option>A</option>
                                                        <option>R</option>
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
                                                    class="fa fa-window-close"></i>
                                        </button>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>

                    <!-- Row -->

                </div>

                <!---Container Fluid-->

            </div>

            <?php
            include('includes/Modal-Logout.php');
            include('includes/Footer.php');
            ?>

        </div>
    </div>

    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Select2 -->
    <script src="vendor/select2/dist/js/select2.min.js"></script>


    <!-- Bootstrap Touchspin -->
    <script src="vendor/bootstrap-touchspin/js/jquery.bootstrap-touchspin.js"></script>
    <!-- ClockPicker -->

    <!-- RuangAdmin Javascript -->
    <script src="js/myadmin.min.js"></script>
    <script src="js/util.js"></script>
    <script src="js/Calculate.js"></script>

    <script src="js/modal/show_customer_modal.js"></script>
    <script src="js/modal/show_product_modal.js"></script>
    <script src="js/modal/show_unit_modal.js"></script>
    <!-- Javascript for this page -->

    <!--script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.5.2/bootbox.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.0/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.0/css/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.0/css/buttons.dataTables.min.css"/-->

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <!--link href="vendor/date-picker-1.9/css/date_picker_style.css" rel="stylesheet"/-->
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

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

    <!--script>
        $(document).ready(function () {
            $('#doc_date').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });
        });
    </script-->

    <script>
        $(document).ready(function () {
            $(".icon-input-btn").each(function () {
                let btnFont = $(this).find(".btn").css("font-size");
                let btnColor = $(this).find(".btn").css("color");
                $(this).find(".fa").css({'font-size': btnFont, 'color': btnColor});
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $("#btnClose").click(function () {
                if ($('#save_status').val() !== '') {
                    window.opener = self;
                    window.close();
                } else {
                    alertify.error("กรุณากด save อีกครั้ง");
                }
            });
        });
    </script>

    <script type="text/javascript">
        let queryString = new Array();
        $(function () {
            if (queryString.length == 0) {
                if (window.location.search.split('?').length > 1) {
                    let params = window.location.search.split('?')[1].split('&');
                    for (let i = 0; i < params.length; i++) {
                        let key = params[i].split('=')[0];
                        let value = decodeURIComponent(params[i].split('=')[1]);
                        queryString[key] = value;
                    }
                }
            }

            let data = "<b>" + queryString["title"] + "</b>";
            $("#title").html(data);
            $("#main_menu").html(queryString["main_menu"]);
            $("#sub_menu").html(queryString["sub_menu"]);
            $('#action').val(queryString["action"]);

            $('#save_status').val("before");

            if (queryString["action"] === 'ADD') {
                let KeyData = generate_token(15);
                $('#KeyAddData').val(KeyData + ":" + Date.now());
                $('#save_status').val("add");
            }

            if (queryString["emp_id"] != null && queryString["f_name"] != null) {
                $('#emp_id').val(queryString["emp_id"]);
                $('#f_name').val(queryString["f_name"]);
                $('#l_name').val(queryString["l_name"]);

                Load_Data_Detail(queryString["emp_id"], "v_order_detail");

            }
        });
    </script>

    <script>
        function Load_Data_Detail(emp_id) {

            let formData = {
                action: "GET_HOLIDAY_DOCUMENT",
                sub_action: "GET_MASTER",
                page_manage: "ADMIN",
                emp_id: emp_id
            };
            let dataRecords = $('#TableRecordList').DataTable({
                "paging": false,
                "ordering": false,
                'info': false,
                "searching": false,
                'lengthMenu': [[8, 16, 32, 48, 100], [8, 16, 32, 48, 100]],
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
                'ajax': {
                    'url': 'model/manage_holiday_admin_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'doc_year'},
                    {data: 'date_leave_start'},
                    {data: 't_leave_start'},
                    {data: 'leave_type_detail'},
                    {data: 'doc_date'},
                    {data: 'remark'},
                    {data: 'approve'},
                ]
            });

            <!-- *** FOR SUBMIT FORM *** -->
            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                $('#save').attr('disabled', 'disabled');
                let formData = $(this).serialize();
                $.ajax({
                    url: 'model/manage_holiday_admin_process.php',
                    method: "POST",
                    data: formData,
                    success: function (data) {
                        alertify.success(data);
                        $('#recordForm')[0].reset();
                        $('#recordModal').modal('hide');
                        $('#save').attr('disabled', false);
                        dataRecords.ajax.reload();
                    }
                })
            });
            <!-- *** FOR SUBMIT FORM *** -->
        }
    </script>

    <script>

        $("#TableRecordList").on('click', '.approve', function () {
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
                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        });

    </script>


    </body>

    </html>

<?php } ?>



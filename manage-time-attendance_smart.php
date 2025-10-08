<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['department_id']) == "") {
    header("Location: index.php");
} else {
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <body id="page-top">
    <div id="wrapper">
        <!--?php
        include('includes/Side-Bar.php');
        ?-->

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php
                include('includes/Top-Bar_Mobile.php');
                ?>
                <!-- Container Fluid-->
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
                        <!--ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                            <li class="breadcrumb-item active"
                                aria-current="page"><?php echo urldecode($_GET['s']) ?></li>
                        </ol-->
                    </div>
                    <button type="button" id="backBtn"
                            name="backBtn"
                            class="btn btn-danger mb-3">
                        กลับหน้าแรก <i class="fa fa-reply"></i>
                    </button>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">
                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>ชื่อ-นามสกุล</th>
                                                    <th>วันที่</th>
                                                    <th>เวลาเข้า</th>
                                                    <th>เวลาออก</th>
                                                </tr>
                                                </thead>
                                            </table>

                                            <div id="result"></div>

                                        </div>
                                </div>
                            </div>
                        </div>
                    </div>

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

                                        <div class="form-group row">
                                            <div class="col-sm-4">
                                                <label for="emp_id" class="control-label">รหัสพนักงาน</label>
                                                <input type="emp_id" class="form-control"
                                                       id="emp_id" name="emp_id"
                                                       readonly="true"
                                                       placeholder="">
                                            </div>

                                            <div class="col-sm-4">
                                                <label for="f_name"
                                                       class="control-label">ชื่อ</label>
                                                <input type="text" class="form-control"
                                                       id="f_name" name="f_name"
                                                       readonly="true"
                                                       placeholder="">
                                            </div>

                                            <div class="col-sm-4">
                                                <label for="l_name"
                                                       class="control-label">นามสกุล</label>
                                                <input type="text" class="form-control"
                                                       id="l_name" name="l_name"
                                                       readonly="true"
                                                       placeholder="">
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <div class="col-sm-4">
                                                <label for="dept_id_approve" class="control-label">แผนก</label>
                                                <input type="dept_id_approve" class="form-control"
                                                       id="dept_id_approve" name="dept_id_approve"
                                                       readonly="true"
                                                       placeholder="">
                                            </div>
                                            <div class="col-sm-8">
                                                <label for="department_id" class="control-label">ชื่อแผนก</label>
                                                <input type="department_id" class="form-control"
                                                       id="department_id" name="department_id"
                                                       readonly="true"
                                                       placeholder="">
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <div class="col-sm-4">
                                                <label for="work_date" class="control-label">วันที่</label>
                                                <input type="work_date" class="form-control"
                                                       id="work_date" name="work_date"
                                                       readonly="true"
                                                       placeholder="วันที่">
                                            </div>

                                            <div class="col-sm-4">
                                                <label for="start_time"
                                                       class="control-label">เวลาเข้า</label>
                                                <input type="text" class="form-control"
                                                       id="start_time" name="start_time"
                                                       readonly="true"
                                                       placeholder="">
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="end_time"
                                                       class="control-label">เวลาออก</label>
                                                <input type="text" class="form-control"
                                                       id="end_time" name="end_time"
                                                       readonly="true"
                                                       placeholder="">
                                            </div>
                                        </div>
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

            </div>
        </div>
    </div>

    <?php
    include('includes/Modal-Logout.php');
    include('includes/Footer.php');
    ?>


    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>


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
            $('#contact_date').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });
        });
    </script>

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

            let formData = {action: "GET_TIME_ATTENDANCE", sub_action: "GET_MASTER"};

            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[5, 10, 15, 20,50,100], [5, 10, 15, 20,50,100]],
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
                'autoWidth': false, // ❌ ปิด autoWidth เพื่อให้ Responsive ดีขึ้น
                'searching': true,
                'scrollX': true, // ✅ เปิดใช้งาน scrollX เพื่อให้ Scroll ได้ในมือถือ
                'ajax': {
                    'url': 'model/manage_time_attendance_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'full_name'},
                    {data: 'work_date'},
                    {data: 'start_time'},
                    {data: 'end_time'}
                ]
            });
        });

    </script>

    <script>

        $("#TableRecordList").on('click', '.detail', function () {
            let id = $(this).attr("id");
            //alert(id);
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_time_attendance_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let emp_id = response[i].emp_id;
                        let f_name = response[i].f_name;
                        let l_name = response[i].l_name;
                        let dept_id_approve = response[i].dept_id_approve;
                        let department_id = response[i].department_id;
                        let work_date = response[i].work_date;
                        let start_time = response[i].start_time;
                        let end_time = response[i].end_time;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#emp_id').val(emp_id);
                        $('#f_name').val(f_name);
                        $('#l_name').val(l_name);
                        $('#dept_id_approve').val(dept_id_approve);
                        $('#department_id').val(department_id);
                        $('#work_date').val(work_date);
                        $('#start_time').val(start_time);
                        $('#end_time').val(end_time);
                        $('.modal-title').html("<i class='fa fa-plus'></i> Detail Record");
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

    <script>
        $(document).ready(function () {
            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                $('#save').attr('disabled', 'disabled');
                let formData = $(this).serialize();
                $.ajax({
                    url: 'model/manage_time_attendance_process.php',
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
        });
    </script>

    <script>
        $('#backBtn').click(function () {
            window.location.href = "Dashboard_employee_smart";
        });
    </script>

    </body>
    </html>

<?php } ?>
<?php
session_start();
error_reporting(0);
$curr_date = date("d-m-Y");

$start_work_date = $_SESSION['start_work_date'];

$l_kij_max = $_SESSION['L1'];
$l_pak_ron_max = $_SESSION['L3'];
$l_holiday_max = $_SESSION['H3'];

include('includes/Header.php');
include('config/connect_db.php');

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
                                                    <th>ชื่อ-นามสกุล</th>
                                                    <th>หน่วยงาน</th>
                                                    <th>ประเภทการลา</th>
                                                    <th>วันที่ลาเริ่มต้น</th>
                                                    <th>วันที่ลาสิ้นสุด</th>
                                                    <th>จำนวนวัน</th>
                                                    <th>จำนวน ช.ม.</th>
                                                    <th>สถานะ</th>
                                                    <th>รูปภาพ</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>ปี</th>
                                                    <th>วันที่เอกสาร</th>
                                                    <th>ชื่อ-นามสกุล</th>
                                                    <th>หน่วยงาน</th>
                                                    <th>ประเภทการลา</th>
                                                    <th>วันที่ลาเริ่มต้น</th>
                                                    <th>วันที่ลาสิ้นสุด</th>
                                                    <th>จำนวนวัน</th>
                                                    <th>จำนวน ช.ม.</th>
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
                                                    <!--div class="modal-header">
                                                        <h4 class="modal-title">Modal title</h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-hidden="true">×
                                                        </button>
                                                    </div-->
                                                    <form method="post" id="recordForm" enctype="multipart/form-data">
                                                        <div class="modal-body">
                                                            <div class="modal-body">

                                                                <!--div class="form-group">
                                                                    <label for="doc_id"
                                                                           class="control-label">เลขที่เอกสาร</label>
                                                                    <input type="text" class="form-control"
                                                                           id="doc_id" name="doc_id"
                                                                           readonly="true"
                                                                           placeholder="สร้างอัตโนมัติ">
                                                                </div-->

                                                                <input type="hidden" class="form-control"
                                                                       id="doc_id" name="doc_id"
                                                                       readonly="true"
                                                                       placeholder="สร้างอัตโนมัติ">


                                                                <input type="hidden" class="form-control"
                                                                       id="page_manage" name="page_manage"
                                                                       readonly="true"
                                                                       value="USER"
                                                                       placeholder="page_manage">

                                                                <input type="hidden" class="form-control"
                                                                       id="work_time_start" name="work_time_start"
                                                                       readonly="true"
                                                                       value="<?php echo $start_work_date ?>"
                                                                       placeholder="">

                                                                <input type="hidden" class="form-control"
                                                                       id="leave_before" name="leave_before"
                                                                       readonly="true"
                                                                       value=""
                                                                       placeholder="">

                                                                <input type="hidden" class="form-control"
                                                                       id="department" name="department"
                                                                       readonly="true"
                                                                       value="<?php echo $_SESSION['department_id'] ?>"
                                                                       placeholder="department">

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
                                                                    <div class="col-sm-3">
                                                                        <label for="doc_date"
                                                                               class="control-label">วันที่เอกสาร</label>
                                                                        <i class="fa fa-calendar"
                                                                           aria-hidden="true"></i>
                                                                        <input type="text" class="form-control"
                                                                               id="doc_date"
                                                                               name="doc_date"
                                                                               required="required"
                                                                               value="<?php echo $curr_date ?>"
                                                                               readonly="true"
                                                                               placeholder="วันที่เอกสาร">
                                                                    </div>
                                                                    <div class="col-sm-9">
                                                                        <label for="leave_case" class="control-label">
                                                                            <span style="color: darkred;">* ลากิจ/ลาพักผ่อน ต้องลาไม่น้อยกว่าครึ่งวัน 8.30-12.00 หรือ 13.00-17.30</span>
                                                                            <br>
                                                                            <span style="color: blue;">* ลาไม่รับค่าจ้าง ลาแบบเป็นครึ่งชั่วโมงได้ เช่น 8.30-9.00 / 10.30-11.00</span>
                                                                        </label>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <!--input type="hidden" class="form-control"
                                                                           id="leave_type_id"
                                                                           required="required"
                                                                           name="leave_type_id">
                                                                    <div class="col-sm-8">
                                                                        <label for="leave_type_detail"
                                                                               class="control-label">ประเภทการลา</label>
                                                                        <input type="text" class="form-control"
                                                                               id="leave_type_detail"
                                                                               name="leave_type_detail"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>
                                                                    <div class="col-sm-2">
                                                                        <label for="leave_type_id"
                                                                               class="control-label">เลือก</label>
                                                                        <a data-toggle="modal"
                                                                           href="#SearchLeaveTypeModal"
                                                                           class="btn btn-primary">
                                                                            Click <i class="fa fa-search"
                                                                                     aria-hidden="true"></i>
                                                                        </a>
                                                                    </div-->
                                                                    <div class="col-sm-8">
                                                                        <label for="date_leave_start"
                                                                               class="control-label">ประเภทการลา (เลือก)</label>
                                                                        <select class="form-control" id="leave_type_id" name="leave_type_id" required onchange="showSelectedValue()">
                                                                            <?php
                                                                            $sql = "SELECT leave_type_id, leave_type_detail FROM mleave_type WHERE day_flag = 'L' AND leave_type_id <> 'L2' ORDER BY leave_type_id ";
                                                                            $stmt = $conn->query($sql);
                                                                            while ($row = $stmt->fetch()) {
                                                                                echo "<option value='{$row['leave_type_id']}'>{$row['leave_type_detail']}</option>";
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-sm-2">
                                                                        <label for="search_data"
                                                                               class="control-label">ข้อมูลการลา</label>
                                                                        <button type="button" class="btn btn-info"
                                                                                id="search_data" name="search_data">
                                                                            Click
                                                                        </button>
                                                                    </div>

                                                                </div>
                                                                <div class="form-group row">
                                                                    <div class="col-sm-3">
                                                                        <label for="date_leave_start"
                                                                               class="control-label">วันที่ลาเริ่มต้น</label>
                                                                        <i class="fa fa-calendar"
                                                                           aria-hidden="true"></i>
                                                                        <input type="text" class="form-control"
                                                                               id="date_leave_start"
                                                                               name="date_leave_start"
                                                                               value="<?php //echo $curr_date ?>"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="วันที่ลาเริ่มต้น">
                                                                    </div>
                                                                    <div class="col-sm-3">
                                                                        <label for="date_leave_start"
                                                                               class="control-label">เวลาเริ่มต้น</label>
                                                                        <input type="text" class="form-control"
                                                                               id="time_leave_start"
                                                                               name="time_leave_start"
                                                                               value="<?php echo $_SESSION['work_time_start'] ?>"
                                                                               required="required"
                                                                               placeholder="hh:mm">
                                                                    </div>
                                                                    <div class="col-sm-3">
                                                                        <label for="date_leave_start"
                                                                               class="control-label">วันที่ลาสิ้นสุด</label>
                                                                        <i class="fa fa-calendar"
                                                                           aria-hidden="true"></i>
                                                                        <input type="text" class="form-control"
                                                                               id="date_leave_to"
                                                                               name="date_leave_to"
                                                                               value="<?php //echo $curr_date ?>"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="วันที่ลาสิ้นสุด">
                                                                    </div>
                                                                    <div class="col-sm-3">
                                                                        <label for="time_leave_to"
                                                                               class="control-label">เวลาสิ้นสุด</label>
                                                                        <input type="text" class="form-control"
                                                                               id="time_leave_to"
                                                                               name="time_leave_to"
                                                                               required="required"
                                                                               value="<?php echo $_SESSION['work_time_stop'] ?>"
                                                                               placeholder="hh:mm">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row d-flex align-items-center">
                                                                    <div class="col-sm-3">
                                                                        <label for="leave_day" class="control-label">จำนวนวันที่ลา</label>
                                                                        <input type="text" class="form-control"
                                                                               id="leave_day" name="leave_day" value=""
                                                                               required="required" placeholder=""
                                                                               oninput="validateInput(this)">
                                                                    </div>
                                                                    <div class="col-sm-3">
                                                                        <label for="leave_hour" class="control-label">จำนวนชั่วโมงที่ลา</label>
                                                                        <input type="text" class="form-control"
                                                                               id="leave_hour" name="leave_hour"
                                                                               value=""
                                                                               required="required" placeholder=""
                                                                               oninput="validateInput(this)">
                                                                    </div>

                                                                    <div class="col-sm-6">
                                                                        <label for="remark" class="control-label">หมายเหตุ</label>
                                                                        <textarea class="form-control" id="remark"
                                                                                  name="remark" rows="1"></textarea>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row" id="uploadSection">
                                                                    <div class="col-sm-6">
                                                                        <label for="upload_image" class="control-label">เอกสารแนบ (Upload รูปภาพ)</label>
                                                                        <input type="file" class="form-control-file" id="image_upload" name="image_upload" accept="image/*" onchange="previewImage(event)">
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <label class="control-label">เอกสารที่แนบ (Click ที่รูปเพื่อขยาย)</label>
                                                                        <br>
                                                                        <img id="preview" src="" alt="Preview" style="max-width: 100px; cursor: pointer; display: none;" data-toggle="modal" data-target="#imageModal">
                                                                    </div>
                                                                </div>

                                                                <?php if ($_SESSION['approve_permission'] === 'Y') { ?>
                                                                    <div class="form-group">
                                                                        <label for="status"
                                                                               class="control-label">สถานะเอกสาร</label>
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
                                                            <input type="hidden" name="picture" id="picture"/>
                                                            <input type="hidden" name="action" id="action" value=""/>
                                                            <input type="hidden" name="leave_use_before"
                                                                   id="leave_use_before" value=""/>
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

                                        <div class="modal fade" id="GetLeaveDataModal">
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
                                                                   id="TableDataLeaveList"
                                                                   width="100%">
                                                                <thead>
                                                                <tr>
                                                                    <th>วันที่ลา</th>
                                                                    <th>เวลา</th>
                                                                    <th>ประเภทการลา</th>
                                                                </tr>
                                                                </thead>
                                                                <tfoot>
                                                                <tr>
                                                                    <th>วันที่ลา</th>
                                                                    <th>เวลา</th>
                                                                    <th>ประเภทการลา</th>
                                                                </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal แสดงภาพ -->
                                        <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">เอกสารแนบ</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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

    <script src="js/modal/show_employee_modal.js"></script>
    <script src="js/modal/show_leave_type_modal_sick.js"></script>

    <!--script src="js/modal/show_data_leave_modal.js"></script-->

    <script src="js/util/calculate_datetime.js"></script>

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

    <style>
        body.modal-open {
            overflow-y: hidden;
            padding-right: 0 !important;
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

    <script>
        function encodeURL(url) {
            return encodeURIComponent(url);
        }

        function decodeURL(url) {
            return encodeURIComponent(url);
        }

    </script>

    <script>
        function readURL(input) {
            if (input.files && input.files[0]) {
                let reader = new FileReader();
                reader.onload = function (e) {
                    $('#ImgFile')
                        .attr('src', e.target.result);
                };

                reader.readAsDataURL(input.files[0]);

            }
        }
    </script>

    <script>
        $(document).ready(function () {
            let formData = {action: "GET_LEAVE_DOCUMENT", sub_action: "GET_MASTER", page_manage: "USER"};

            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
                'language': {
                    search: 'ค้นหา', lengthMenu: 'แสดง _MENU_ รายการ',
                    info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                    infoEmpty: 'ไม่มีข้อมูล',
                    zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                    infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
                    paginate: {previous: 'ก่อนหน้า', last: 'สุดท้าย', next: 'ต่อไป'}
                },
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                'autoWidth': false, // ❌ ปิด autoWidth เพื่อให้รองรับ Responsive ดีขึ้น
                'searching': true,
                'scrollX': true, // ✅ เปิด scrollX เพื่อให้ Scroll ได้ในมือถือ
                'ajax': {
                    'url': 'model/manage_leave_document_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'doc_year'},
                    {data: 'doc_date'},
                    {data: 'full_name'},
                    {data: 'department_id'},
                    {data: 'leave_type_detail'},
                    {data: 'dt_leave_start'},
                    {data: 'dt_leave_to'},
                    {data: 'leave_day'},
                    {data: 'leave_hour'},
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

            // ✅ ปรับปรุงการทำให้ Table สามารถ Scroll ได้ในมือถือ
            $('#TableRecordList_wrapper').addClass('table-responsive');

            // CSS สำหรับกระพริบ
            $('<style>').prop('type', 'text/css').html(`
        .blink { animation: blinker 1s linear infinite; }
        @keyframes blinker { 50% { opacity: 0; } }
    `).appendTo('head');
        });

    </script>

    <script>
        $(document).ready(function () {
            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                $('#save').attr('disabled', 'disabled'); // Disable the save button while processing

                if (chkTime($('#time_leave_start').val()) && chkTime($('#time_leave_to').val())) {
                    if ($('#date_leave_start').val() !== '' && $('#date_leave_to').val() !== '' && ($('#leave_day').val() !== '' || $('#leave_day').val() !== '0')) {

                        let date_leave_1 = $('#doc_date').val().substr(3, 2) + "/" + $('#doc_date').val().substr(0, 2) + "/" + $('#doc_date').val().substr(6, 10);
                        let date_leave_2 = $('#date_leave_start').val().substr(3, 2) + "/" + $('#date_leave_start').val().substr(0, 2) + "/" + $('#date_leave_start').val().substr(6, 10);

                        let check_day = CalDay(date_leave_1, date_leave_2); // Check Date
                        let l_before = $('#leave_before').val();

                        let formData = new FormData(this); // ใช้ FormData เพื่ออัปโหลดไฟล์
                        formData.append("filename", $('#image_upload')[0].files[0]); // เพิ่มไฟล์ลงใน FormData

                        $.ajax({
                            url: 'model/manage_leave_document_process.php',
                            method: "POST",
                            data: formData,
                            contentType: false, // ต้องการแจ้งว่าไม่ใช้การส่งแบบ content-type แบบปกติ
                            processData: false, // ป้องกันการแปลงข้อมูล FormData
                            success: function (data) {
                                if (data.includes("Over")) {
                                    alertify.error(data);
                                } else {
                                    alertify.success(data);
                                }

                                $('#recordForm')[0].reset();
                                $('#recordModal').modal('hide');
                                $('#save').attr('disabled', false); // เปิดปุ่ม save เมื่อเสร็จสิ้น
                                ReloadDataTable();
                            },
                            error: function (xhr, status, error) {
                                // การจัดการกับข้อผิดพลาด
                                alertify.error("เกิดข้อผิดพลาดในการส่งข้อมูล: " + error);
                                $('#save').attr('disabled', false); // เปิดปุ่ม save หากเกิดข้อผิดพลาด
                            }
                        });
                    } else {
                        alertify.error("กรุณาป้อนวันที่ต้องการลา !!!");
                        $('#save').attr('disabled', false); // เปิดปุ่ม save ถ้าไม่ครบเงื่อนไข
                    }
                } else {
                    alertify.error("กรุณาป้อนวันที่ - เวลา ให้ถูกต้อง !!!");
                    $('#save').attr('disabled', false); // เปิดปุ่ม save ถ้าเวลาไม่ถูกต้อง
                }
            });
        });
    </script>

    <script>
        $(document).ready(function () {

            $("#btnAdd").click(function () {

                //alert(<?php echo $_SESSION['work_time_start']?>);
                let today = new Date();
                let day = String(today.getDate()).padStart(2, '0');
                let month = String(today.getMonth() + 1).padStart(2, '0'); // January is 0!
                let year = today.getFullYear();
                let formattedDate = day + '-' + month + '-' + year;

                $('#recordModal').modal('show');
                $('#id').val("");
                $('#doc_id').val("");
                $('#doc_date').val(formattedDate);
                $('#leave_type_id').val("");
                $('#leave_type_detail').val("");
                $('#date_leave_start').val("");
                $('#date_leave_to').val("");
                $('#leave_day').val(1);
                $('#leave_hour').val(0);
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
                url: 'model/manage_leave_document_process.php',
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
                        let f_name = response[i].f_name;
                        let l_name = response[i].l_name;
                        let leave_type_id = response[i].leave_type_id;
                        let leave_type_detail = response[i].leave_type_detail;
                        let date_leave_start = response[i].date_leave_start;
                        let date_leave_to = response[i].date_leave_to;
                        let time_leave_start = response[i].time_leave_start;
                        let time_leave_to = response[i].time_leave_to;
                        let leave_before = response[i].leave_before;
                        let leave_day = response[i].leave_day;
                        let leave_hour = response[i].leave_hour;
                        let remark = response[i].remark;
                        let status = response[i].status;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#doc_id').val(doc_id);
                        $('#doc_date').val(doc_date);
                        $('#emp_id').val(emp_id);
                        $('#full_name').val(full_name);
                        $('#f_name').val(f_name);
                        $('#l_name').val(l_name);
                        $('#leave_type_id').val(leave_type_id);
                        $('#leave_type_detail').val(leave_type_detail);
                        $('#date_leave_start').val(date_leave_start);
                        $('#date_leave_to').val(date_leave_to);
                        $('#time_leave_start').val(time_leave_start);
                        $('#time_leave_to').val(time_leave_to);
                        $('#leave_before').val(leave_before);
                        $('#leave_day').val(leave_day);
                        $('#leave_hour').val(leave_hour);
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
                url: 'model/manage_leave_document_process.php',
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
                        let leave_before = response[i].leave_before;
                        let leave_day = response[i].leave_day;
                        let leave_hour = response[i].leave_hour;
                        let picture = response[i].picture;
                        let remark = response[i].remark;
                        let status = response[i].status;

                        let main_menu = "บันทึกข้อมูลหลัก";
                        let sub_menu = "เอกสารการลางาน (พนักงาน)";

                        let originalURL = "upload_leave_data.php?title=เอกสารการลา (Document)"
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
                            + '&leave_before=' + leave_before
                            + '&leave_day=' + leave_day
                            + '&leave_hour=' + leave_hour
                            + '&picture=' + picture
                            + '&remark=' + remark
                            + '&status=' + status
                            + '&action=UPDATE';

                        // ใช้ window.open แทนการใช้ OpenPopupCenter
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
            let today = new Date(); // Get today's date
            $('#date_leave_start').datepicker({
                startDate: today,
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });
        });
    </script>

    <script>
        $('#date_leave_start').change(function () {
            if ($('#leave_type_id').val() !== '') {
                check_before_leave();
            } else {
                $('#date_leave_start').val('');
                alertify.error("กรุณาเลือกประเภทการลา");
            }
        });
    </script>

    <script>
        function check_before_leave() {

            let leave_type_id = $('#leave_type_id').val();

            let formData = {action: "SEARCH_DATA", leave_type_id: leave_type_id};
            $.ajax({
                type: "POST",
                url: 'model/manage_leave_type_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let leave_before = response[i].leave_before;
                        $('#leave_before').val(leave_before);
                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });

        }
    </script>

    <script>
        $(document).ready(function () {
            let today = new Date(); // Get today's date
            $('#date_leave_to').datepicker({
                startDate: today,
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $('#time_leave_start').on('change', function () {
                chkTime($(this).val());
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $('#time_leave_to').on('change', function () {
                chkTime($(this).val());
            });
        });
    </script>

    <script>

        function chkTime(TimeInput) {
            let timeFormat = /^([01]\d|2[0-3]):([0-5]\d)$/; // Regular expression for 24-hour HH:MM format
            if (timeFormat.test(TimeInput)) {
                $(this).removeClass('invalid');
                return true;
            } else {
                $(this).addClass('invalid');
                alertify.error("ป้อนเวลาตามรูปแบบ ชั่วโมง:นาที เท่่านั้น");
                return false;
            }
        }

    </script>

    <script>
        $(document).ready(function () {
            $('#search_data').on('click', function (event) {
                event.preventDefault(); // Prevent the default form submission

                const myArray = $('#full_name').val().split(" ");
                $('#f_name').val(myArray[0]);
                $('#l_name').val(myArray[1]);

                let form = $(this).closest('form');
                let formData = form.serialize(); // Serialize the form data

                if ($('#leave_type_id').val() !== '' && $('#emp_id').val() !== '' && $('#doc_date').val() !== '') {
                    // Get the form that contains the button
                    let newWindow = window.open('', '_blank');
                    $.ajax({
                        type: 'POST',
                        url: 'show_data_employee_leave_document.php',
                        data: formData,
                        success: function (response) {
                            // Write the response to the new window
                            newWindow.document.write(response);
                        }
                    });
                } else {
                    alertify.alert("กรุณาเลือก พนักงาน , ป้อนวันที่ , ประเภทการลา");
                }
            });

        });
    </script>

    <script>
        $(document).ready(function () {
            function setDatePicker() {
                let leave_type_id = $('#leave_type_id').val(); // ดึงค่าที่เลือกจาก select
                let startDate = new Date(); // วันที่ปัจจุบัน

                // กำหนดการตั้งค่าเริ่มต้นของวันที่
                if (leave_type_id === 'L6') {
                    // ถ้าเลือก leave_type_id = 'L6' สามารถเลือกได้ทุกวันที่ผ่านมาและวันปัจจุบัน
                    startDate = new Date('2000-01-01'); // ไม่จำกัดวันเริ่มต้น (ให้ย้อนกลับได้)
                } else if (leave_type_id === 'L1' || leave_type_id === 'L3') {
                    // ถ้าเลือก L1 หรือ L3 ให้เริ่มเลือกได้จาก 3 วันหลังจากวันนี้
                    startDate.setDate(startDate.getDate() + 3);
                } else if (leave_type_id === 'S') {
                    // ถ้าเลือก S ให้เลือกวันนี้ได้เลย
                    startDate.setDate(startDate.getDate());
                }

                // ทำลาย datepicker ก่อนสร้างใหม่
                $('#date_leave_start').datepicker('destroy').datepicker({
                    format: "dd-mm-yyyy",
                    todayHighlight: true,
                    language: "th",
                    autoclose: true,
                    startDate: startDate // กำหนดวันที่เริ่มต้น
                });

                $('#date_leave_to').datepicker('destroy').datepicker({
                    format: "dd-mm-yyyy",
                    todayHighlight: true,
                    language: "th",
                    autoclose: true,
                    startDate: startDate // กำหนดวันที่เริ่มต้น
                });

                console.log("ประเภทการลา: " + leave_type_id + " | วันที่เริ่มต้น: " + startDate);
            }

            // เมื่อเลือกประเภทการลาให้ตั้งค่า datepicker ใหม่
            $('#leave_type_id').on('change', function () {
                setDatePicker();
            });

            // เมื่อปิด Modal ให้รีเซ็ตวันที่ลา
            $('#SearchLeaveTypeModal').on('hidden.bs.modal', function () {
                setDatePicker();
                $('#date_leave_start').val('');
            });
        });
    </script>


    <script>
        function ReloadDataTable() {
            $('#TableRecordList').DataTable().ajax.reload();
        }
    </script>

    <script>
        function showSelectedValue() {
            let select = document.getElementById("leave_type_id");
            let selectedValue = select.value;
            let selectedText = select.options[select.selectedIndex].text;
            //alert("ค่า: " + selectedValue + "\nข้อความ: " + selectedText);
        }
    </script>

    <script>
        function previewImage(event) {
            let input = event.target;
            let reader = new FileReader();

            reader.onload = function() {
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
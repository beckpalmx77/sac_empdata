<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index");
} else {

    include("config/connect_db.php");

    $month_num_start = "1";
    $month_num_to = str_replace('0', '', date('m'));

    $sql_start_month = " SELECT * FROM ims_month WHERE month = '" . $month_num_start . "'";
    $sql_curr_month = " SELECT * FROM ims_month WHERE month = '" . $month_num_to . "'";

    $stmt_start_month = $conn->prepare($sql_start_month);
    $stmt_start_month->execute();
    $MonthStart = $stmt_start_month->fetchAll();
    foreach ($MonthStart as $row_start) {
        $month_name_start = $row_start["month_name"];
    }

    $stmt_curr_month = $conn->prepare($sql_curr_month);
    $stmt_curr_month->execute();
    $MonthCurr = $stmt_curr_month->fetchAll();
    foreach ($MonthCurr as $row_curr) {
        $month_name_to = $row_curr["month_name"];
    }

    $sql_month = " SELECT * FROM ims_month ";
    $stmt_month = $conn->prepare($sql_month);
    $stmt_month->execute();
    $MonthRecords = $stmt_month->fetchAll();

    $sql_year = " SELECT DISTINCT(doc_year) AS doc_year FROM dleave_event ORDER BY doc_year DESC ";
    $stmt_year = $conn->prepare($sql_year);
    $stmt_year->execute();
    $YearRecords = $stmt_year->fetchAll();

    $sql_branch = " SELECT * FROM ims_branch ";
    $stmt_branch = $conn->prepare($sql_branch);
    $stmt_branch->execute();
    $BranchRecords = $stmt_branch->fetchAll();

    ?>

    <!DOCTYPE html>
    <html lang="th">
    <body id="page-top">
    <div id="wrapper">
        <?php include('includes/Side-Bar.php'); ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('includes/Top-Bar.php'); ?>

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
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between"></div>
                                <div class="card-body">
                                    <section class="container-fluid">
                                        <div class="row">
                                            <div class="col-md-12 col-md-offset-2">
                                                <div class="panel">
                                                    <div class="panel-body">
                                                        <form id="myform" name="myform" method="post">

                                                            <div class="row">
                                                                <div class="col-sm-6">
                                                                    <label for="month_start">เลือกเดือน (เริ่มต้น)
                                                                        :</label>
                                                                    <select name="month_start" id="month_start"
                                                                            class="form-control" required
                                                                            onchange="validateMonths()">
                                                                        <option value="<?php echo $month_num_start; ?>"
                                                                                selected><?php echo $month_name_start; ?></option>
                                                                        <?php foreach ($MonthRecords as $row) { ?>
                                                                            <option value="<?php echo $row["month"]; ?>"><?php echo $row["month_name"]; ?></option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <label for="month_to">เลือกเดือน (ถึง) :</label>
                                                                    <select name="month_to" id="month_to"
                                                                            class="form-control" required
                                                                            onchange="validateMonths()">
                                                                        <option value="<?php echo $month_num_to; ?>"
                                                                                selected><?php echo $month_name_to; ?></option>
                                                                        <?php foreach ($MonthRecords as $row) { ?>
                                                                            <option value="<?php echo $row["month"]; ?>"><?php echo $row["month_name"]; ?></option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-sm-12">
                                                                    <label for="year">เลือกปี :</label>
                                                                    <select name="year" id="year" class="form-control"
                                                                            required>
                                                                        <?php foreach ($YearRecords as $row) { ?>
                                                                            <option value="<?php echo $row["doc_year"]; ?>"><?php echo $row["doc_year"]; ?></option>
                                                                        <?php } ?>
                                                                    </select>

                                                                    <input type="hidden" id="document_dept_cond" name="document_dept_cond"
                                                                           value="<?php echo $_SESSION['document_dept_cond']; ?>">
                                                                    <input type="hidden" id="dept_id_approve" name="dept_id_approve"
                                                                           value="<?php echo $_SESSION['dept_id_approve']; ?>">
                                                                    <input type="hidden" id="emp_id" name="emp_id"
                                                                           value="<?php echo $_SESSION['emp_id']; ?>">

                                                                    <label for="employee">เลือกพนักงาน :</label>
                                                                    <select name="employee" id="employee" class="form-control" required>
                                                                        <option value="">กรุณาเลือกพนักงาน</option>
                                                                    </select>

                                                                    <br>
                                                                    <div class="row">
                                                                        <div class="col-sm-12">
                                                                            <input type="hidden" id="form_type"
                                                                                   name="form_type" value="employee">
                                                                            <button type="submit" id="BtnData"
                                                                                    name="BtnData"
                                                                                    class="btn btn-primary mb-3">
                                                                                สรุปข้อมูล
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </section>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                include('includes/Modal-Logout.php');
                include('includes/Footer.php');
                ?>

            </div>
        </div>

        <a class="scroll-to-top rounded" href="#page-top">
            <i class="fas fa-angle-up"></i>
        </a>

        <script src="vendor/jquery/jquery.min.js"></script>
        <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
        <script src="vendor/select2/dist/js/select2.min.js"></script>
        <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
        <script src="vendor/bootstrap-touchspin/js/jquery.bootstrap-touchspin.js"></script>
        <script src="vendor/clock-picker/clockpicker.js"></script>
        <script src="js/myadmin.min.js"></script>
        <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
        <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
        <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

        <script>
            function validateMonths() {
                const startMonth = parseInt($('#month_start').val());
                const endMonth = parseInt($('#month_to').val());

                if (endMonth < startMonth) {
                    alertify.alert("เดือนสิ้นสุดไม่ควรอยู่ก่อนเดือนเริ่มต้น");
                    $('#month_to').val(startMonth);
                }
            }

            $(document).ready(function () {
                $('#myform').on('submit', function (e) {
                    e.preventDefault(); // Prevent the form from submitting normally

                    const startMonth = parseInt($('#month_start').val());
                    const endMonth = parseInt($('#month_to').val());

                    if (endMonth < startMonth) {
                        alert("เดือนสิ้นสุดไม่ควรอยู่ก่อนเดือนเริ่มต้น");
                        return false;
                    }

                    // Serialize the form data
                    let formData = $(this).serialize();

                    // Open a new window
                    let newWindow = window.open('', '_blank');

                    // Perform the AJAX request
                    $.ajax({
                        type: 'POST',
                        url: 'show_data_leave_document_sac.php',
                        data: formData,
                        success: function (response) {
                            // Write the response to the new window
                            newWindow.document.write(response);
                        }
                    });
                });
            });
        </script>

        <script>
            $(document).ready(function() {
                fetchEmployees();

                // Initialize Select2 for the employee select element
                $('#employee').select2({
                    placeholder: 'กรุณาเลือกพนักงาน',
                    allowClear: true,
                    minimumInputLength: 2, // ค้นหาเมื่อผู้ใช้พิมพ์อย่างน้อย 2 ตัวอักษร
                    width: '100%', // กำหนดความกว้างของ select2
                });
            });

            function fetchEmployees() {
                let document_dept_cond = $('#document_dept_cond').val();
                let dept_id_approve = $('#dept_id_approve').val();
                let emp_id = $('#emp_id').val();

                $.ajax({
                    url: 'model/manage_employee_process.php',
                    method: 'POST',
                    data: {
                        action: 'GET_SELECT_EMP_BY_DEPT',
                        document_dept_cond: document_dept_cond,
                        dept_id_approve: dept_id_approve,
                        emp_id: emp_id
                    },
                    success: function(data) {
                        let employeeSelect = $('#employee');
                        employeeSelect.empty();
                        employeeSelect.append('<option value="">กรุณาเลือกพนักงาน</option>');
                        employeeSelect.append(data);

                        // Re-initialize Select2 after options are updated
                        employeeSelect.select2({
                            placeholder: 'กรุณาเลือกพนักงาน',
                            allowClear: true
                        });
                    },
                    error: function() {
                        console.error('Error fetching employees.');
                    }
                });
            }
        </script>

    </body>
    </html>

<?php } ?>

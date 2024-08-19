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
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo urldecode($_GET['s']) ?></li>
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
                                                                    <label for="month_start">เลือกเดือน (เริ่มต้น) :</label>
                                                                    <select name="month_start" id="month_start" class="form-control" required onchange="validateMonths()">
                                                                        <option value="<?php echo $month_num_start; ?>" selected><?php echo $month_name_start; ?></option>
                                                                        <?php foreach ($MonthRecords as $row) { ?>
                                                                            <option value="<?php echo $row["month"]; ?>"><?php echo $row["month_name"]; ?></option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <label for="month_to">เลือกเดือน (ถึง) :</label>
                                                                    <select name="month_to" id="month_to" class="form-control" required onchange="validateMonths()">
                                                                        <option value="<?php echo $month_num_to; ?>" selected><?php echo $month_name_to; ?></option>
                                                                        <?php foreach ($MonthRecords as $row) { ?>
                                                                            <option value="<?php echo $row["month"]; ?>"><?php echo $row["month_name"]; ?></option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-sm-12">
                                                                    <label for="year">เลือกปี :</label>
                                                                    <select name="year" id="year" class="form-control" required>
                                                                        <?php foreach ($YearRecords as $row) { ?>
                                                                            <option value="<?php echo $row["doc_year"]; ?>"><?php echo $row["doc_year"]; ?></option>
                                                                        <?php } ?>
                                                                    </select>

                                                                    <?php if ($_SESSION['document_dept_cond'] !== "A") { ?>
                                                                        <input type="hidden" name="branch" id="branch" value="<?php echo $_SESSION['department_id']?>">
                                                                    <?php } else { ?>
                                                                        <label for="branch">เลือกสาขา :</label>
                                                                        <select name="branch" id="branch" class="form-control" required>
                                                                            <?php foreach ($BranchRecords as $row) { ?>
                                                                                <option value="<?php echo $row["branch"]; ?>"><?php echo $row["branch_name"]; ?></option>
                                                                            <?php } ?>
                                                                        </select>
                                                                    <?php } ?>

                                                                    <br>
                                                                    <div class="row">
                                                                        <div class="col-sm-12">
                                                                            <button type="submit" id="BtnData" name="BtnData" class="btn btn-primary mb-3">สรุปข้อมูล</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                        <br>

                                                        <!-- DataTable Container -->
                                                        <div class="table-responsive">
                                                            <table id="dataTable" class="table table-bordered">
                                                                <thead>
                                                                <tr>
                                                                    <th>Column 1</th>
                                                                    <th>Column 2</th>
                                                                    <th>Column 3</th>
                                                                    <!-- Add more columns as needed -->
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                <!-- Data will be loaded here by DataTables -->
                                                                </tbody>
                                                            </table>
                                                        </div>
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
                    alert("เดือนสิ้นสุดไม่ควรอยู่ก่อนเดือนเริ่มต้น");
                    $('#month_to').val(startMonth);
                }
            }

            $(document).ready(function() {
                $('#myform').on('submit', function(e) {
                    e.preventDefault(); // ป้องกันการส่งฟอร์มตามปกติ

                    const startMonth = parseInt($('#month_start').val());
                    const endMonth = parseInt($('#month_to').val());

                    if (endMonth < startMonth) {
                        alert("เดือนสิ้นสุดไม่ควรอยู่ก่อนเดือนเริ่มต้น");
                        return false;
                    }

                    // Destroy existing DataTable instance if exists
                    if ($.fn.DataTable.isDataTable('#dataTable')) {
                        $('#dataTable').DataTable().destroy();
                    }

                    // Initialize DataTable
                    $('#dataTable').DataTable({
                        "processing": true,
                        "serverSide": true,
                        "ajax": {
                            "url": "fetch_data.php",
                            "type": "POST",
                            "data": function (d) {
                                return $.extend({}, d, {
                                    "month_start": $('#month_start').val(),
                                    "month_to": $('#month_to').val(),
                                    "year": $('#year').val(),
                                    "branch": $('#branch').val()
                                });
                            }
                        },
                        "columns": [
                            { "data": "column1" },
                            { "data": "column2" },
                            { "data": "column3" }
                            // Add more columns as needed
                        ]
                    });
                });
            });
        </script>

    </body>
    </html>

    <?php
}
?>


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
        <?php
        include('includes/Side-Bar.php');
        ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php
                include('includes/Top-Bar.php');
                ?>
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-12">
                        <h4 class="h5 mb-0 text-gray-800">แสดงข้อมูลการใช้วันหยุด / การลา พนักงาน
                        <?php echo "ปี " . date("Y");?></h4>
                    </div>

                    <div class="row mb-3">
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <input type="hidden" id="emp_id" name="emp_id"
                                           value="<?php echo $_SESSION['emp_id']; ?>">
                                    <div class="row align-items-center">
                                        <div class="col mr-2">
                                            <div style="font-size: 15px; font-weight: bold; text-transform: uppercase; margin-bottom: 1rem;">
                                                การใช้วันหยุดประจำปี/นักขัตฤกษ์
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <p class="text-primary" id="Text1"></p>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-file fa-2x text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Earnings (Annual) Card Example -->
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div style="font-size: 15px; font-weight: bold; text-transform: uppercase; margin-bottom: 1rem;">
                                                เอกสารใบลากิจ ลาสูงสุดได้ 3 วัน/ปี
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <p class="text-success" id="Text2"></p>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-file fa-2x text-success"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- New User Card Example -->
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div style="font-size: 15px; font-weight: bold; text-transform: uppercase; margin-bottom: 1rem;">
                                                เอกสารการลาพักผ่อน ลาสูงสุดได้ 6 วัน/ปี (อายุงานครบ 1 ปี)
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <p class="text-info" id="Text3"></p>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-file fa-2x text-info"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Pending Requests Card Example -->
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div style="font-size: 15px; font-weight: bold; text-transform: uppercase; margin-bottom: 1rem;">
                                                เอกสารการลาป่วย ลาสูงสุดได้ 30 วัน/ปี
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <p class="text-warning" id="Text4"></p>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-file fa-2x text-warning"></i>
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
    <script src="vendor/chart.js/Chart.min.js"></script>
    <script src="js/chart/chart-area-demo.js"></script>

    <link href='vendor/calendar/main.css' rel='stylesheet'/>
    <script src='vendor/calendar/main.js'></script>
    <script src='vendor/calendar/locales/th.js'></script>

    <script src='js/clock_time.js'></script>

    <script>

        $(document).ready(function () {
            for (let i = 1; i <= 4; i++) {
                GET_DATA("dleave_event", i);
            }

            GET_DATA("ot_request", 5);
            GET_DATA("dchange_event", 6);
            GET_DATA("dtime_change_event", 7);
            GET_DATA("dholiday_event", 8);

            setInterval(function () {
                for (let i = 1; i <= 4; i++) {
                    GET_DATA("dleave_event", i);
                }

                GET_DATA("ot_request", 5);
                GET_DATA("dchange_event", 6);
                GET_DATA("dtime_change_event", 7);
                GET_DATA("dholiday_event", 8);
            }, 5000);
        });

    </script>

    <script>
        function GET_DATA(table_name, idx) {
            const current_date = "<?php echo str_replace('/', '-', $current_date); ?>";
            const d = new Date();
            let year = d.getFullYear();
            let emp_id = $("#emp_id").val();
            let where_emp_id = " And emp_id = '" + emp_id + "' ";
            let where_year = " And doc_year = '" + year + "' ";
            let input_text = document.getElementById("Text" + idx);
            let action = "GET_SUM_RESULT_COND";
            let cond = "";
            let field = "leave_day";
            switch (idx) {
                case 1:
                    cond = where_year;
                    break;
                case 2:
                    cond = " Where leave_type_id = 'L1' " + where_emp_id + where_year;
                    break;
                case 3:
                    cond = " Where leave_type_id = 'L3' " + where_emp_id + where_year;
                    break;
                case 4:
                    cond = " Where leave_type_id = 'L2' " + where_emp_id + where_year;
                    break;
                case 5:
                    cond = " Where leave_type_id = 'O' " + where_emp_id + where_year;
                    break;
                case 6:
                    cond = " Where leave_type_id = 'C' " + where_emp_id + where_year;
                    break;
                case 7:
                    cond = " Where leave_type_id = 'S' " + where_emp_id + where_year;
                    break;
                case 8:
                    cond = " Where leave_type_id = 'H2' " + where_emp_id + where_year;
                    break;
            }
            let formData = {action: action, table_name: table_name, field: field, cond: cond};
            $.ajax({
                type: "POST",
                url: 'model/manage_general_data.php',
                data: formData,
                success: function (response) {
                    // ใช้การใส่วงเล็บเพื่อแก้ไขปัญหาใน ternary operator
                    input_text.innerHTML = "ใช้ไปแล้วจำนวน " + (response < 1 ? '0' : response) + " วัน";
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        }
    </script>

    </body>
    </html>

<?php } ?>

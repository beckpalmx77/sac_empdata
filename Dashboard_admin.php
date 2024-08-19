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
                    <div class="row mb-3">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col mr-2">
                                            <div style="font-size: 15px; font-weight: bold; text-transform: uppercase; margin-bottom: 1rem;">เอกสารการลา
                                                ทั้งหมด
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><p class="text-primary"
                                                                                                   id="Text1"></p></div>

                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-file fa-2x text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Earnings (Annual) Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div style="font-size: 15px; font-weight: bold; text-transform: uppercase; margin-bottom: 1rem;">เอกสารใบลากิจ ลาสูงสุดได้  <?php echo $_SESSION['L1']?> วัน/ปี
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><p class="text-success"
                                                                                                   id="Text2"></p></div>

                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-file fa-2x text-success"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- New User Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div style="font-size: 15px; font-weight: bold; text-transform: uppercase; margin-bottom: 1rem;">เอกสารการลาพักผ่อน ลาสูงสุดได้  <?php echo $_SESSION['L3']?> วัน/ปี
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><p class="text-info"
                                                                                                   id="Text3"></p></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-file fa-2x text-info"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Pending Requests Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div style="font-size: 15px; font-weight: bold; text-transform: uppercase; margin-bottom: 1rem;">เอกสารการลาป่วย ลาสูงสุดได้  <?php echo $_SESSION['L2']?> วัน/ปี
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><p class="text-warning"
                                                                                                   id="Text4"></p></div>
                                            <div class="mt-2 mb-0 text-muted text-xs">

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

                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div style="font-size: 15px; font-weight: bold; text-transform: uppercase; margin-bottom: 1rem;">เอกสารการขอทำงานล่วงเวลา
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><p class="text-success"
                                                                                                   id="Text5"></p></div>
                                            <div class="mt-2 mb-0 text-muted text-xs">

                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-file fa-2x text-success"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div style="font-size: 15px; font-weight: bold; text-transform: uppercase; margin-bottom: 1rem;">เอกสารการแจ้งเปลี่ยนวันหยุด
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><p class="text-info"
                                                                                                   id="Text6"></p></div>

                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-file fa-2x text-info"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div style="font-size: 15px; font-weight: bold; text-transform: uppercase; margin-bottom: 1rem;">เอกสารการแจ้งเปลี่ยนเวลาการทำงาน
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><p class="text-warning"
                                                                                                   id="Text7"></p></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-file fa-2x text-warning"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col mr-2">
                                            <div style="font-size: 15px; font-weight: bold; text-transform: uppercase; margin-bottom: 1rem;">บันทึกวันหยุด (นักขัตฤกษ์-ประจำปี) ใช้สิทธิ์ได้  <?php echo $_SESSION['H1']?> วัน/ปี
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><p class="text-primary"
                                                                                                   id="Text8"></p></div>

                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-file fa-2x text-primary"></i>
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
            }, 3000);

        });

    </script>

    <script>

        function GET_DATA(table_name, idx) {
            const current_date = "<?php echo str_replace('/', '-', $current_date); ?>";
            let where_date = "And doc_date = '" + current_date + "'";
            //alert(where_date);
            let input_text = document.getElementById("Text" + idx);
            let action = "GET_COUNT_RECORDS_COND";
            let cond = "";
            switch (idx) {
                case 1:
                    cond = " Where doc_date = '" + current_date + "'";
                    break;
                case 2:
                    cond = " Where leave_type_id = 'L1' " + where_date;
                    break;
                case 3:
                    cond = " Where leave_type_id = 'L3' " + where_date;
                    break;
                case 4:
                    cond = " Where leave_type_id = 'L2' " + where_date;
                    break;
                case 5:
                    cond = " Where leave_type_id = 'O' " + where_date;
                    break;
                case 6:
                    cond = " Where leave_type_id = 'C' " + where_date;
                    break;
                case 7:
                    cond = " Where leave_type_id = 'S' " + where_date;
                    break;
                case 8:
                    cond = " Where leave_type_id = 'H2' " + where_date;
                    break;
            }
            //alert(cond);
            let formData = {action: action, table_name: table_name, cond: cond};
            $.ajax({
                type: "POST",
                url: 'model/manage_general_data.php',
                data: formData,
                success: function (response) {
                    input_text.innerHTML = response;
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


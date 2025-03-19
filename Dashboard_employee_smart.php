<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['department_id']) == "") {
    header("Location: index.php");
} else {
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Icon Navigation</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

        <style>
            body {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                background-color: #f8f9fa;
            }

            .container {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                text-align: center;
            }

            .icon-container {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
                justify-content: center;
                margin-top: 50px;
            }

            .icon-box {
                width: 100px;
                height: 100px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                background-color: #e3f2fd;
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                transition: transform 0.2s;
                text-decoration: none;
                color: #0275d8;
            }

            .icon-box:hover {
                transform: scale(1.1);
            }

            .icon-box i {
                font-size: 2rem;
            }

            .icon-box span {
                margin-top: 5px;
                font-size: 14px;
            }

            @media (max-width: 768px) {
                .icon-container {
                    grid-template-columns: repeat(2, 1fr);
                }
            }
        </style>
    </head>
    <body>
    <div class="container text-center">
        <div><img src="img/logo/logo text-01.png" width="200" height="79"/></div>
        <div class="icon-container">
            <a href="employees.html" class="icon-box">
                <i class="fas fa-users"></i>
                <span>พนักงาน</span>
            </a>
            <a href="attendance.html" class="icon-box">
                <i class="fas fa-calendar-check"></i>
                <span>การเข้าออกงาน</span>
            </a>
            <a href="payroll.html" class="icon-box">
                <i class="fas fa-money-check-alt"></i>
                <span>เงินเดือน</span>
            </a>
            <a href="recruitment.html" class="icon-box">
                <i class="fas fa-user-plus"></i>
                <span>การรับสมัคร</span>
            </a>
            <a href="training.html" class="icon-box">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>การฝึกอบรม</span>
            </a>
            <a href="benefits.html" class="icon-box">
                <i class="fas fa-heart"></i>
                <span>สวัสดิการ</span>
            </a>
            <!-- ไอคอนการลางาน -->
            <a href="vacation.html" class="icon-box">
                <i class="fas fa-beach-umbrella"></i>
                <span>ลาพักร้อน</span>
            </a>
            <a href="personal-leave.html" class="icon-box">
                <i class="fas fa-calendar-day"></i>
                <span>ลากิจ</span>
            </a>
            <a href="sick-leave.html" class="icon-box">
                <i class="fas fa-syringe"></i>
                <span>ลาป่วย</span>
            </a>
            <a href="holiday.html" class="icon-box">
                <i class="fas fa-cogs"></i>
                <span>ใช้วันหยุด</span>
            </a>
        </div>
    </div>
    <br>
    </body>
    </html>

    <?php
    include('includes/Modal-Logout.php');
    //include('includes/Footer.php');
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

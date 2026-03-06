<?php
include('includes/Header.php');
include('config/connect_db.php');
if (empty($_SESSION['alogin']) || empty($_SESSION['dept_id_approve'])) {
    header("Location: index.php");
    exit;
}

$day_max_value = 0;
$dept_chk = substr($_SESSION['dept_id_approve'], 0, 2);

// ✅ เลือก field ให้เหมาะกับ dept
$sql_get = ($dept_chk === 'CP')
    ? "SELECT day_max_ext AS day_max FROM mleave_type WHERE leave_type_id = 'H2'"
    : "SELECT day_max AS day_max FROM mleave_type WHERE leave_type_id = 'H2'";

try {
    $stmt = $conn->query($sql_get);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $day_max_value = $result ? (int)$result['day_max'] : 0;
} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
    $day_max_value = 0;
}

// ✅ กำหนดประเภทการลา (ที่ต้องการดึง max day)
$max_days_map = ['L1', 'L3', 'L2'];
$leave_limits = [];

$sql_get = "SELECT day_max AS day_max_leave FROM mleave_type WHERE leave_type_id = :leave_type";
$stmt = $conn->prepare($sql_get);

foreach ($max_days_map as $leave_type) {
    $stmt->bindParam(':leave_type', $leave_type);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $leave_limits[$leave_type] = $result ? (int)$result['day_max_leave'] : 0;
}


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
                    <h5 class="h5 mb-0 text-gray-800">แสดงข้อมูลการใช้วันหยุด / การลา พนักงาน
                        <?php echo "ปี " . date("Y"); ?></h5>
                </div>
                <br>
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
                                            ใช้ได้ <?php echo $day_max_value ?? 0; ?> วัน/ปี
                                        </div>
                                        <div class="h6 mb-0 font-weight-bold text-gray-800">
                                            <p class="text-primary" id="Text8"></p>
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
                                            ลากิจ ลาสูงสุดได้ <?php echo $leave_limits['L1'] ?? 0; ?> วัน/ปี
                                        </div>
                                        <div class="h6 mb-0 font-weight-bold text-gray-800">
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
                                            ลาพักผ่อน ลาสูงสุดได้ <?php echo $leave_limits['L3'] ?? 0; ?> วัน/ปี
                                            (อายุงานครบ 1 ปี)
                                        </div>
                                        <div class="h6 mb-0 font-weight-bold text-gray-800">
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
                                            ลาป่วย ลาสูงสุดได้ <?php echo $leave_limits['L2'] ?? 0; ?> วัน/ปี
                                        </div>
                                        <div class="h6 mb-0 font-weight-bold text-gray-800">
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

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-3">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            </div>
                            <div class="card-body">
                                <div class="container-fluid">
                                    <div id="calendar"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-3">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            </div>
                            <div class="card-body">
                                <div class="container-fluid">
                                    <?php include 'show_data_leave_document_sac_supervisor.php'; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php include('includes/Footer.php'); ?>

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
<script src="vendor/chart.js/Chart.min.js"></script>
<script src="js/chart/chart-area-demo.js"></script>

<link href='vendor/calendar/main.css' rel='stylesheet'/>
<script src='vendor/calendar/main.js'></script>
<script src='vendor/calendar/locales/th.js'></script>

<script src='js/clock_time.js'></script>

<script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.19/index.global.js'></script>

<style>
    /* ทำให้ Calendar ขยายเต็มพื้นที่ Card อย่างสวยงาม */
    .card-body {
        padding: 5px !important; /* ลบ padding จาก card-body เพื่อให้ calendar ชิดขอบพอดี */
        overflow: hidden; /* กันไม่ให้มี scrollbar/hide overflow */
    }

    #calendar {
        width: 100% !important;
        max-width: 100%;
        box-sizing: border-box;
        padding: 15px; /* เว้นระยะภายใน ให้ดูไม่ชิดจนเกินไป */
    }
</style>

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
                cond = " Where leave_type_id = 'L1' AND status = 'A' " + where_emp_id + where_year;
                break;
            case 3:
                cond = " Where leave_type_id = 'L3' AND status = 'A' " + where_emp_id + where_year;
                break;
            case 4:
                cond = " Where leave_type_id = 'L2' AND status = 'A' " + where_emp_id + where_year;
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
                cond = " Where leave_type_id = 'H2' AND status = 'A' " + where_emp_id + where_year;
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

<!-- ✅ FullCalendar Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let calendarEl = document.getElementById('calendar');

        let calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'th',
            timeZone: 'local',
            initialView: 'dayGridMonth',
            height: 550,

            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth'
            },
            buttonText: {
                today: 'วันนี้', // เปลี่ยน "today" เป็น "วันนี้"
                month: 'เดือน',  // เปลี่ยน "month" เป็น "เดือน" (หรือ "เดือน")
                week: 'สัปดาห์', // เผื่อต้องการใช้ปุ่ม week ในอนาคต
                day: 'วัน',      // เผื่อต้องการใช้ปุ่ม day ในอนาคต
            },

            events: {
                url: 'model/calendar_leave_load.php',
                method: 'GET',
                failure: function () {
                    console.error("❌ ไม่สามารถโหลดข้อมูลจาก calendar_leave_load");
                },
                success: function (data) {
                    console.log("✅ Event Loaded:", data);
                }
            },
            editable: true,
            selectable: true,

            eventClick: function (info) {
                info.jsEvent.preventDefault();  // ✅ ปิด default

                // change the border color
                info.el.style.borderColor = 'blue';

                let main_menu = document.getElementById("main_menu")?.value || "";
                let sub_menu = document.getElementById("sub_menu")?.value || "";
                let doc_date = info.event.id || info.event.startStr;

                let url = "manage_leave_calendar_data.php?title=รายการข้อมูลการลางาน"
                    + "&main_menu=" + encodeURIComponent(main_menu)
                    + "&sub_menu=" + encodeURIComponent(sub_menu)
                    + "&doc_date=" + encodeURIComponent(doc_date);

                console.log("🔗 ไปที่ URL:", url);
                window.open(url, "_blank");  // ✅ ป้องกัน popup block
            }
        });

        calendar.render();
    });
</script>

</body>
</html>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <link href="../img/logo/logo.png" rel="icon">
    <title>สงวนออโต้คาร์ | SANGUAN AUTO CAR</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding-top: 20px; /* ลดระยะห่างด้านบน */
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            flex-grow: 1;
        }

        .icon-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 20px;
        }

        .icon-box {
            width: 120px;
            height: 120px;
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

        .icon-box img {
            width: 50px;
            height: 50px;
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
<div class="container">
    <div>
        <img src="img/logo/logo text-02.png" style="width: auto; height: 79px;">
    </div>
    <div class="icon-container">
        <a href="manage_sick_leave_document_smart?m=บันทึกข้อมูลหลัก&s=เอกสารการลาป่วย" class="icon-box">
            <img src="img/icon_app/L2.png" alt="ลาป่วย">
            <span>ลาป่วย</span>
        </a>
        <a href="manage_leave_document_smart?m=บันทึกข้อมูลหลัก&s=เอกสารการลาประเภทอื่น" class="icon-box">
            <img src="img/icon_app/L1.png" alt="ลาอื่นๆ">
            <span>ลาอื่นๆ</span>
        </a>
        <a href="manage_holiday_document_emp_smart?m=บันทึกข้อมูลหลัก&s=บันทึกวันหยุด+(นักขัตฤกษ์-ประจำปี)" class="icon-box">
            <img src="img/icon_app/H2.png" alt="วันหยุดนักขัตฤกษ์-ประจำปี">
            <span>ใช้วันหยุด</span>
        </a>
        <a href="display_data_emp_leave_document_sac_smart?m=รายงาน&s=รายละเอียดการลางาน+-+การใช้วันหยุด+พนักงาน" class="icon-box">
            <img src="img/icon_app/report_leave_holiday.png" alt="รายละเอียดการลา">
            <span>รายละเอียดการลา</span>
        </a>
        <a href="manage_employee_self_smart?m=ทะเบียนหลัก&s=ประวัติพนักงาน" class="icon-box">
            <img src="img/icon_app/emp_info.png" alt="ประวัติพนักงาน">
            <span>ประวัติพนักงาน</span>
        </a>
        <a href="index" class="icon-box">
            <img src="img/icon_app/exit.png" alt="ออกจากระบบ">
            <span>ออกจากระบบ</span>
        </a>
    </div>
</div>
</body>
</html>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Icon Navigation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        /* ตั้งค่าหน้าจอให้จัดตรงกลาง */
        html, body {
            height: 100%;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .icon-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 30px;
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
    <div><img src="img/logo/blank.png" width="100" height="10"/></div>
    <div><img src="img/logo/logo text-01.png" width="200" height="79"/></div>
    <div class="icon-container">
        <a href="employees.html" class="icon-box">
            <img src="img/icon_app/L1.png" alt="ลาป่วย">
            <span>ลาป่วย</span>
        </a>
        <a href="attendance.html" class="icon-box">
            <img src="img/icon_app/L2.png" alt="การเข้าออกงาน">
            <span>การเข้าออกงาน</span>
        </a>
        <a href="payroll.html" class="icon-box">
            <img src="img/icon_app/L3.png" alt="เงินเดือน">
            <span>เงินเดือน</span>
        </a>
        <a href="recruitment.html" class="icon-box">
            <img src="img/icon_app/H2.png" alt="การรับสมัคร">
            <span>การรับสมัคร</span>
        </a>
    </div>
</div>
</body>
</html>

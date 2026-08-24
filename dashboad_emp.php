<?php
session_start();
error_reporting(0);

// Default user data if session is empty
$user_first_name = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : 'admin';
$user_last_name = isset($_SESSION['last_name']) ? $_SESSION['last_name'] : 'admin';
$full_name = trim($user_first_name . ' ' . $user_last_name);
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'ผู้ดูแลระบบ';
$user_dept = isset($_SESSION['dept_name']) ? $_SESSION['dept_name'] : 'การตลาด';

// Attempt DB connection safely for dynamic stats if available
$emp_count = 30;
$dept_count = 9;
$leave_pending = 0;
$doc_pending = 0;
$checkin_count = 0;
$checkout_count = 0;

try {
    if (file_exists('config/connect_db.php')) {
        include_once('config/connect_db.php');
        if (isset($conn) && $conn) {
            // Employee count
            $stmt_emp = $conn->query("SELECT COUNT(*) AS total FROM memployee WHERE status = 'Y'");
            if ($stmt_emp) {
                $row = $stmt_emp->fetch(PDO::FETCH_ASSOC);
                if ($row && isset($row['total']) && $row['total'] > 0) {
                    $emp_count = (int)$row['total'];
                }
            }
            // Department count
            $stmt_dept = $conn->query("SELECT COUNT(*) AS total FROM mdepartment");
            if ($stmt_dept) {
                $row = $stmt_dept->fetch(PDO::FETCH_ASSOC);
                if ($row && isset($row['total']) && $row['total'] > 0) {
                    $dept_count = (int)$row['total'];
                }
            }
        }
    }
} catch (Exception $e) {
    // Fail silent and use default initial values matching mockup
}

// Format Thai date & time greeting
$thai_months = [
    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
    5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
    9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
];
$day = date('j');
$month_num = (int)date('n');
$year_be = (int)date('Y') + 543;
$thai_date_str = "วันนี้ {$day} " . $thai_months[$month_num] . " {$year_be}";

$hour = (int)date('H');
if ($hour >= 5 && $hour < 12) {
    $time_greeting = "สวัสดียามเช้า";
} else if ($hour >= 12 && $hour < 16) {
    $time_greeting = "สวัสดียามบ่าย";
} else if ($hour >= 16 && $hour < 19) {
    $time_greeting = "สวัสดียามเย็น";
} else {
    $time_greeting = "สวัสดียามค่ำ";
}

$welcome_subtext = "{$thai_date_str} • {$time_greeting} • {$user_dept}";
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>ระบบ HR • HR System - Dashboard</title>

    <!-- Google Fonts Prompt -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

    <!-- FontAwesome CSS -->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Bootstrap 4 / 5 standard stylesheet fallback -->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        * {
            box-sizing: border-box;
        }
        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Prompt', sans-serif !important;
            background-color: #eef2f6;
            color: #334155;
            font-size: 14px;
        }

        /* 1. Main Header Navy Bar */
        .hr-topbar {
            background-color: #0d3c6c;
            color: #ffffff;
            height: 46px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 15px;
            position: sticky;
            top: 0;
            z-index: 1030;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .hr-topbar .brand-title {
            font-size: 15px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #ffffff;
        }
        .hr-topbar .brand-title i {
            font-size: 16px;
        }
        .hr-topbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .icon-btn-badge {
            position: relative;
            color: #ffffff;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
        }
        .icon-btn-badge .badge-count {
            position: absolute;
            top: -6px;
            right: -8px;
            background-color: #ef4444;
            color: #fff;
            font-size: 10px;
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 50%;
            line-height: 1;
        }
        .user-pill {
            background-color: #ffffff;
            color: #1e293b;
            padding: 2px 10px 2px 3px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid #cbd5e1;
        }
        .user-avatar-sq {
            background-color: #0284c7;
            color: #ffffff;
            width: 24px;
            height: 24px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 13px;
        }

        /* 2. Top Sub Header Navigation Icon Bar */
        .hr-subnav {
            background-color: #f1f5f9;
            border-bottom: 1px solid #cbd5e1;
            padding: 8px 15px;
            display: flex;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 25px;
            box-shadow: inset 0 -1px 0 rgba(0,0,0,0.05);
        }
        .nav-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            border-right: 1px dashed #cbd5e1;
            padding-right: 20px;
        }
        .nav-group:last-child {
            border-right: none;
            padding-right: 0;
        }
        .nav-group-items {
            display: flex;
            align-items: center;
            gap: 18px;
        }
        .nav-item-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none !important;
            color: #475569;
            transition: color 0.15s ease-in-out;
        }
        .nav-item-btn:hover {
            color: #0284c7;
        }
        .nav-item-btn i {
            font-size: 18px;
            margin-bottom: 3px;
            color: #0284c7;
        }
        .nav-item-btn span {
            font-size: 12px;
            font-weight: 400;
            white-space: nowrap;
        }
        .nav-group-label {
            font-size: 10px;
            color: #94a3b8;
            margin-top: 4px;
            font-weight: 400;
        }

        /* Layout Main Container */
        .app-body {
            display: flex;
            min-height: calc(100vh - 84px);
            padding-bottom: 38px;
        }

        /* 3. Left Sidebar Column */
        .sidebar-left {
            width: 220px;
            background-color: #ffffff;
            border-right: 1px solid #e2e8f0;
            flex-shrink: 0;
        }
        .sidebar-header {
            background-color: #ffffff;
            padding: 10px 14px;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .sidebar-sec-title {
            padding: 8px 14px 4px 14px;
            font-size: 12px;
            font-weight: 600;
            color: #0284c7;
            display: flex;
            align-items: center;
            gap: 5px;
            border-bottom: 1px solid #f1f5f9;
            background-color: #f8fafc;
        }
        .sidebar-menu-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .sidebar-menu-item a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 14px 7px 22px;
            color: #475569;
            text-decoration: none !important;
            font-size: 12.5px;
            transition: all 0.15s ease;
        }
        .sidebar-menu-item a:hover {
            background-color: #f1f5f9;
            color: #0284c7;
        }
        .sidebar-menu-item.active a {
            background-color: #0d3c6c;
            color: #ffffff !important;
            font-weight: 500;
            border-radius: 0;
        }
        .sidebar-menu-item.active a i {
            color: #ffffff !important;
        }
        .sidebar-submenu {
            list-style: none;
            padding: 0;
            margin: 0;
            background-color: #fafafa;
        }
        .sidebar-submenu li a {
            padding-left: 36px;
            font-size: 12px;
            color: #64748b;
        }
        .sidebar-submenu li a:hover {
            color: #0284c7;
        }

        /* 4. Center Dashboard Area */
        .content-center {
            flex: 1;
            padding: 15px;
            overflow-x: hidden;
        }

        .breadcrumb-box {
            background: #ffffff;
            padding: 6px 12px;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            font-size: 13px;
            color: #475569;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Welcome Banner Card */
        .welcome-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 16px 20px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }
        .welcome-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .avatar-circle-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background-color: #0284c7;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }
        .welcome-title {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 2px 0;
        }
        .welcome-sub {
            font-size: 12.5px;
            color: #64748b;
            margin: 0;
        }
        .btn-checkin {
            background-color: #0f172a;
            color: #ffffff !important;
            padding: 7px 16px;
            border-radius: 5px;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s ease;
            text-decoration: none !important;
        }
        .btn-checkin:hover {
            background-color: #1e293b;
        }

        /* KPI Cards Grid */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 15px;
        }
        .kpi-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.06);
        }
        .kpi-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        .kpi-icon-box.blue {
            background-color: #e0f2fe;
            color: #0284c7;
            border: 1px solid #bae6fd;
        }
        .kpi-icon-box.cyan {
            background-color: #e0f2fe;
            color: #0891b2;
            border: 1px solid #bae6fd;
        }
        .kpi-icon-box.sky {
            background-color: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }
        .kpi-icon-box.indigo {
            background-color: #e0f2fe;
            color: #1d4ed8;
            border: 1px solid #bae6fd;
        }
        .kpi-content {
            display: flex;
            flex-direction: column;
        }
        .kpi-label {
            font-size: 11.5px;
            color: #64748b;
            font-weight: 400;
            margin-bottom: 2px;
        }
        .kpi-value {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1;
        }

        /* Section Cards */
        .sec-card {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }
        .sec-card-header {
            background-color: #0d3c6c;
            color: #ffffff;
            padding: 8px 14px;
            font-size: 13.5px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .sec-card-body {
            padding: 15px 20px;
        }

        /* Attendance Status Display */
        .attendance-row {
            display: flex;
            align-items: center;
            justify-content: space-around;
            padding: 10px 0;
        }
        .att-box {
            text-align: center;
        }
        .att-title {
            font-size: 13px;
            color: #475569;
            margin-bottom: 6px;
            font-weight: 400;
        }
        .att-num-green {
            font-size: 32px;
            font-weight: 700;
            color: #16a34a;
        }
        .att-num-red {
            font-size: 32px;
            font-weight: 700;
            color: #dc2626;
        }
        .att-num-sub {
            font-size: 16px;
            color: #64748b;
            font-weight: 500;
        }

        /* Quick Access Shortcut Grid */
        .qa-grid {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 10px;
            padding: 10px 0;
        }
        .qa-item {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 12px 6px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none !important;
            color: #334155;
            transition: all 0.15s ease;
        }
        .qa-item:hover {
            border-color: #0284c7;
            box-shadow: 0 2px 6px rgba(2,132,199,0.15);
            transform: translateY(-2px);
            color: #0284c7;
        }
        .qa-icon-sq {
            width: 40px;
            height: 40px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #0284c7;
            margin-bottom: 8px;
            background-color: #f8fafc;
        }
        .qa-item span {
            font-size: 12px;
            font-weight: 500;
        }

        /* 5. Right Column Panel (กิจกรรมล่าสุด) */
        .sidebar-right {
            width: 280px;
            padding: 15px 15px 15px 0;
            flex-shrink: 0;
        }
        .activity-card {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            height: 100%;
        }
        .activity-header {
            background-color: #0d3c6c;
            color: #ffffff;
            padding: 8px 14px;
            font-size: 13.5px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .activity-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .activity-item {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .act-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background-color: #e0f2fe;
            color: #0284c7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            flex-shrink: 0;
            margin-top: 2px;
        }
        .act-details {
            display: flex;
            flex-direction: column;
        }
        .act-title {
            font-size: 12.5px;
            font-weight: 600;
            color: #1e293b;
        }
        .act-desc {
            font-size: 11px;
            color: #64748b;
        }
        .act-time {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 1px;
        }

        /* 6. Footer Fixed Navy Bar */
        .hr-footer {
            background-color: #0d3c6c;
            color: #ffffff;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 15px;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1040;
            font-size: 12px;
        }
        .footer-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .status-pulse {
            width: 8px;
            height: 8px;
            background-color: #22c55e;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 2px rgba(34,197,94,0.3);
        }
        .footer-divider {
            color: #475569;
        }

        /* Responsive Breakpoints */
        @media (max-width: 1200px) {
            .qa-grid {
                grid-template-columns: repeat(4, 1fr);
            }
            .kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 992px) {
            .app-body {
                flex-direction: column;
            }
            .sidebar-left, .sidebar-right {
                width: 100%;
                padding: 15px;
            }
            .qa-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        @media (max-width: 576px) {
            .kpi-grid {
                grid-template-columns: 1fr;
            }
            .qa-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .welcome-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
        }
    </style>
</head>
<body>

    <!-- 1. Top Navigation Bar (Navy Blue) -->
    <header class="hr-topbar">
        <div class="brand-title">
            <i class="fas fa-th-large"></i>
            <span>ระบบ HR • HR System</span>
        </div>
        <div class="hr-topbar-right">
            <a href="manage-message.php" class="icon-btn-badge" title="การแจ้งเตือน">
                <i class="fas fa-bell"></i>
                <span class="badge-count">1</span>
            </a>
            <a href="change-password.php" class="icon-btn-badge" title="เปลี่ยนรหัสผ่าน">
                <i class="fas fa-key"></i>
            </a>
            <div class="user-pill" onclick="window.location.href='manage_employee_self.php'">
                <div class="user-avatar-sq">A</div>
                <span><?php echo htmlspecialchars($full_name); ?></span>
                <i class="fas fa-chevron-down" style="font-size: 10px; color: #64748b;"></i>
            </div>
        </div>
    </header>

    <!-- 2. Sub Header Quick Access Nav Icons -->
    <nav class="hr-subnav">
        <!-- Group 1 -->
        <div class="nav-group">
            <div class="nav-group-items">
                <a href="Dashboard_admin.php" class="nav-item-btn">
                    <i class="fas fa-home"></i>
                    <span>หน้าหลัก</span>
                </a>
                <a href="manage_employee.php" class="nav-item-btn">
                    <i class="fas fa-user-plus"></i>
                    <span>เพิ่มพนักงาน</span>
                </a>
                <a href="manage-time-attendance.php" class="nav-item-btn">
                    <i class="fas fa-clock"></i>
                    <span>เช็คอิน</span>
                </a>
                <a href="manage_leave_document.php" class="nav-item-btn">
                    <i class="fas fa-file-signature"></i>
                    <span>ขอลา</span>
                </a>
            </div>
            <span class="nav-group-label">เริ่มต้นใช้งาน</span>
        </div>

        <!-- Group 2 -->
        <div class="nav-group">
            <div class="nav-group-items">
                <a href="manage_job_payment.php" class="nav-item-btn">
                    <i class="fas fa-university"></i>
                    <span>เงินเดือน</span>
                </a>
                <a href="display_data_job_payment.php" class="nav-item-btn">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>สลิป</span>
                </a>
                <a href="display_emp_document.php" class="nav-item-btn">
                    <i class="fas fa-folder-open"></i>
                    <span>เอกสาร</span>
                </a>
                <a href="javascript:void(0)" class="nav-item-btn" onclick="Swal.fire('ข้อมูล','โมดูลภาครัฐกำลังปรับปรุง','info')">
                    <i class="fas fa-building"></i>
                    <span>ภาครัฐ</span>
                </a>
            </div>
            <span class="nav-group-label">การเงิน-เอกสาร</span>
        </div>

        <!-- Group 3 -->
        <div class="nav-group">
            <div class="nav-group-items">
                <a href="report_leave.php" class="nav-item-btn">
                    <i class="fas fa-chart-bar"></i>
                    <span>รายงาน</span>
                </a>
                <a href="javascript:void(0)" class="nav-item-btn" onclick="Swal.fire('ข้อมูล','ผังองค์กรอยู่ระหว่างการจัดทำ','info')">
                    <i class="fas fa-sitemap"></i>
                    <span>ผังองค์กร</span>
                </a>
                <a href="permission_management.php" class="nav-item-btn">
                    <i class="fas fa-cog"></i>
                    <span>ตั้งค่า</span>
                </a>
            </div>
            <span class="nav-group-label">รายงาน-ตั้งค่า</span>
        </div>

        <!-- Group 4 -->
        <div class="nav-group">
            <div class="nav-group-items">
                <a href="javascript:void(0)" class="nav-item-btn" onclick="Swal.fire('คู่มือ','ดาวน์โหลดคู่มือการใช้งานระบบ HR','info')">
                    <i class="fas fa-book-reader"></i>
                    <span>คู่มือ ระบบ HR</span>
                </a>
            </div>
            <span class="nav-group-label">คู่มือ</span>
        </div>

        <!-- Group 5 -->
        <div class="nav-group">
            <div class="nav-group-items">
                <a href="index.php" class="nav-item-btn" onclick="return confirm('คุณต้องการออกจากระบบหรือไม่?');">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>ออกจากระบบ</span>
                </a>
            </div>
            <span class="nav-group-label">บัญชี</span>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="app-body">

        <!-- 3. Left Sidebar Column -->
        <aside class="sidebar-left">
            <div class="sidebar-header">
                <i class="fas fa-th-large"></i>
                <span>เมนูลัด</span>
            </div>

            <div class="sidebar-sec-title">
                <i class="fas fa-angle-right"></i> หน้าหลัก
            </div>
            <ul class="sidebar-menu-list">
                <li class="sidebar-menu-item active">
                    <a href="dashboad_emp.php">
                        <i class="fas fa-home"></i>
                        <span>หน้าหลัก</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="manage_employee_self.php">
                        <i class="fas fa-user-circle"></i>
                        <span>ข้อมูลประจำตัว</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-sec-title">
                <i class="fas fa-angle-right"></i> โมดูลพนักงาน
            </div>
            <ul class="sidebar-menu-list">
                <li class="sidebar-menu-item">
                    <a href="manage_employee.php">
                        <i class="fas fa-users"></i>
                        <span>1. ข้อมูลพนักงาน</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="manage-time-attendance.php">
                        <i class="fas fa-clock"></i>
                        <span>2. เช็คอิน/เช็คเอ้าท์</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <li><a href="manage-time-attendance.php"><i class="fas fa-angle-right"></i> ดูประวัติย้อนหลัง</a></li>
                        <li><a href="empl_calendar.php"><i class="fas fa-angle-right"></i> ตารางการทำงาน</a></li>
                        <li><a href="manage_change_worktime_document.php"><i class="fas fa-angle-right"></i> คำขอปรับปรุงเวลา</a></li>
                        <li><a href="display_data_job_daily_payment.php"><i class="fas fa-angle-right"></i> รายงานเข้างานวันนี้</a></li>
                        <li><a href="report_leave_all.php"><i class="fas fa-angle-right"></i> ประมวลผลสาย/ขาด (รอบเดือน)</a></li>
                        <li><a href="show_data_employee_leave_document.php"><i class="fas fa-angle-right"></i> รายงานพนักงาน</a></li>
                    </ul>
                </li>
                <li class="sidebar-menu-item">
                    <a href="manage_leave_document.php">
                        <i class="fas fa-file-alt"></i>
                        <span>3.1 การลา</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="manage_ot_request_document.php" class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-user-clock"></i>
                            <span>3.2 ขอ OT</span>
                        </div>
                        <span class="badge badge-danger" style="font-size: 10px; padding: 2px 6px;">1</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="manage_leave_document_smart.php">
                        <i class="fas fa-laptop-house"></i>
                        <span>3.3 ขอ WFH</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-sec-title">
                <i class="fas fa-angle-right"></i> การเงิน-เอกสาร
            </div>
            <div class="sidebar-sec-title">
                <i class="fas fa-angle-right"></i> ราชการ
            </div>
            <div class="sidebar-sec-title">
                <i class="fas fa-angle-right"></i> ระบบ
            </div>
        </aside>

        <!-- 4. Center Dashboard Content -->
        <main class="content-center">
            <!-- Breadcrumb -->
            <div class="breadcrumb-box">
                <i class="fas fa-home" style="color: #0284c7;"></i>
                <span>หน้าหลัก</span>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: #94a3b8;"></i>
                <span style="color: #64748b;">Dashboard</span>
            </div>

            <!-- Welcome Banner -->
            <div class="welcome-card">
                <div class="welcome-left">
                    <div class="avatar-circle-icon">
                        <i class="far fa-smile"></i>
                    </div>
                    <div>
                        <h1 class="welcome-title">สวัสดีคุณ <?php echo htmlspecialchars($full_name); ?></h1>
                        <p class="welcome-sub"><?php echo htmlspecialchars($welcome_subtext); ?></p>
                    </div>
                </div>
                <button class="btn-checkin" onclick="doCheckIn()">
                    <i class="far fa-clock"></i>
                    <span>เช็คอินวันนี้</span>
                </button>
            </div>

            <!-- KPI Row (4 Stat Cards) -->
            <div class="kpi-grid">
                <!-- Card 1 -->
                <div class="kpi-card">
                    <div class="kpi-icon-box blue">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="kpi-content">
                        <span class="kpi-label">พนักงานที่ยังทำงาน</span>
                        <span class="kpi-value"><?php echo $emp_count; ?></span>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="kpi-card">
                    <div class="kpi-icon-box cyan">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="kpi-content">
                        <span class="kpi-label">แผนก</span>
                        <span class="kpi-value"><?php echo $dept_count; ?></span>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="kpi-card">
                    <div class="kpi-icon-box sky">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="kpi-content">
                        <span class="kpi-label">ใบลารออนุมัติ</span>
                        <span class="kpi-value"><?php echo $leave_pending; ?></span>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="kpi-card">
                    <div class="kpi-icon-box indigo">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="kpi-content">
                        <span class="kpi-label">คำขอเอกสารรออนุมัติ</span>
                        <span class="kpi-value"><?php echo $doc_pending; ?></span>
                    </div>
                </div>
            </div>

            <!-- Attendance Today Section -->
            <div class="sec-card">
                <div class="sec-card-header">
                    <i class="far fa-clock"></i>
                    <span>การเข้างานวันนี้</span>
                </div>
                <div class="sec-card-body">
                    <div class="attendance-row">
                        <div class="att-box">
                            <div class="att-title">เช็คอินแล้ว</div>
                            <div>
                                <span class="att-num-green" id="checkinValue"><?php echo $checkin_count; ?></span>
                                <span class="att-num-sub">/ <?php echo $emp_count; ?></span>
                            </div>
                        </div>
                        <div style="border-right: 1px solid #e2e8f0; height: 45px;"></div>
                        <div class="att-box">
                            <div class="att-title">เช็คเอาท์แล้ว</div>
                            <div>
                                <span class="att-num-red" id="checkoutValue"><?php echo $checkout_count; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Access Section -->
            <div class="sec-card">
                <div class="sec-card-header">
                    <i class="fas fa-th-large"></i>
                    <span>Quick Access</span>
                </div>
                <div class="sec-card-body">
                    <div class="qa-grid">
                        <a href="manage_employee.php" class="qa-item">
                            <div class="qa-icon-sq">
                                <i class="fas fa-users"></i>
                            </div>
                            <span>พนักงาน</span>
                        </a>

                        <a href="manage-time-attendance.php" class="qa-item">
                            <div class="qa-icon-sq">
                                <i class="fas fa-clock"></i>
                            </div>
                            <span>เวลา</span>
                        </a>

                        <a href="manage_leave_document.php" class="qa-item">
                            <div class="qa-icon-sq">
                                <i class="fas fa-calendar-minus"></i>
                            </div>
                            <span>ลา</span>
                        </a>

                        <a href="manage_job_payment.php" class="qa-item">
                            <div class="qa-icon-sq">
                                <i class="fas fa-university"></i>
                            </div>
                            <span>เงินเดือน</span>
                        </a>

                        <a href="display_data_job_payment.php" class="qa-item">
                            <div class="qa-icon-sq">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <span>สลิป</span>
                        </a>

                        <a href="display_emp_document.php" class="qa-item">
                            <div class="qa-icon-sq">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <span>เอกสาร</span>
                        </a>

                        <a href="javascript:void(0)" onclick="Swal.fire('ข้อมูล','ระบบติดต่อภาครัฐ','info')" class="qa-item">
                            <div class="qa-icon-sq">
                                <i class="fas fa-building"></i>
                            </div>
                            <span>ภาครัฐ</span>
                        </a>

                        <a href="permission_management.php" class="qa-item">
                            <div class="qa-icon-sq">
                                <i class="fas fa-cog"></i>
                            </div>
                            <span>ตั้งค่า</span>
                        </a>
                    </div>
                </div>
            </div>
        </main>

        <!-- 5. Right Sidebar (กิจกรรมล่าสุด) -->
        <aside class="sidebar-right">
            <div class="activity-card">
                <div class="activity-header">
                    <i class="fas fa-list-ul"></i>
                    <span>กิจกรรมล่าสุด</span>
                </div>
                <ul class="activity-list">
                    <li class="activity-item">
                        <div class="act-icon"><i class="fas fa-chart-line"></i></div>
                        <div class="act-details">
                            <span class="act-title">LOGIN โดย user1</span>
                            <span class="act-desc">login success</span>
                            <span class="act-time">29 กรกฎาคม 2026, 20:01</span>
                        </div>
                    </li>
                    <li class="activity-item">
                        <div class="act-icon"><i class="fas fa-chart-line"></i></div>
                        <div class="act-details">
                            <span class="act-title">LOGOUT โดย EMP9001</span>
                            <span class="act-desc">logout</span>
                            <span class="act-time">29 กรกฎาคม 2026, 20:01</span>
                        </div>
                    </li>
                    <li class="activity-item">
                        <div class="act-icon"><i class="fas fa-chart-line"></i></div>
                        <div class="act-details">
                            <span class="act-title">LOGIN โดย EMP9001</span>
                            <span class="act-desc">login success</span>
                            <span class="act-time">29 กรกฎาคม 2026, 20:00</span>
                        </div>
                    </li>
                    <li class="activity-item">
                        <div class="act-icon"><i class="fas fa-chart-line"></i></div>
                        <div class="act-details">
                            <span class="act-title">PASSWORD_CHANGE โดย EMP9001</span>
                            <span class="act-desc">change password</span>
                            <span class="act-time">29 กรกฎาคม 2026, 20:00</span>
                        </div>
                    </li>
                    <li class="activity-item">
                        <div class="act-icon"><i class="fas fa-chart-line"></i></div>
                        <div class="act-details">
                            <span class="act-title">LOGIN โดย EMP9001</span>
                            <span class="act-desc">login success</span>
                            <span class="act-time">29 กรกฎาคม 2026, 20:00</span>
                        </div>
                    </li>
                    <li class="activity-item">
                        <div class="act-icon"><i class="fas fa-chart-line"></i></div>
                        <div class="act-details">
                            <span class="act-title">LOGOUT โดย DEMO002</span>
                            <span class="act-desc">logout</span>
                            <span class="act-time">29 กรกฎาคม 2026, 19:59</span>
                        </div>
                    </li>
                    <li class="activity-item">
                        <div class="act-icon"><i class="fas fa-chart-line"></i></div>
                        <div class="act-details">
                            <span class="act-title">LOGIN โดย DEMO002</span>
                            <span class="act-desc">login success</span>
                            <span class="act-time">29 กรกฎาคม 2026, 19:57</span>
                        </div>
                    </li>
                    <li class="activity-item">
                        <div class="act-icon"><i class="fas fa-chart-line"></i></div>
                        <div class="act-details">
                            <span class="act-title">PASSWORD_CHANGE โดย DEMO002</span>
                            <span class="act-desc">change password</span>
                            <span class="act-time">29 กรกฎาคม 2026, 19:57</span>
                        </div>
                    </li>
                </ul>
            </div>
        </aside>

    </div>

    <!-- 6. Bottom Fixed Status Footer Bar -->
    <footer class="hr-footer">
        <div class="footer-left">
            <span class="status-pulse"></span>
            <span>พร้อมใช้งาน</span>
            <span class="footer-divider">|</span>
            <span><i class="far fa-user"></i> <?php echo htmlspecialchars($user_first_name); ?> (<?php echo htmlspecialchars($user_role); ?>)</span>
            <span class="footer-divider">|</span>
            <span><i class="fas fa-server"></i> HR_System</span>
        </div>
        <div class="footer-right">
            <i class="far fa-clock"></i>
            <span id="footerClock">11/08/2026 17:04:40</span>
        </div>
    </footer>

    <!-- JavaScript & Interactivity -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script>
        // Real-time Footer Clock
        function updateClock() {
            const now = new Date();
            const day = String(now.getDate()).padStart(2, '0');
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const year = now.getFullYear();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            
            document.getElementById('footerClock').textContent = `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Check-in Action
        function doCheckIn() {
            Swal.fire({
                title: 'ยืนยันการลงเวลาเข้างาน?',
                text: 'บันทึกเวลาเข้างานปัจจุบัน',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d3c6c',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'บันทึกเช็คอิน',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'เช็คอินสำเร็จ!',
                        text: 'ลงเวลาเข้างานเรียบร้อยแล้ว',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    document.getElementById('checkinValue').textContent = '1';
                }
            });
        }
    </script>
</body>
</html>

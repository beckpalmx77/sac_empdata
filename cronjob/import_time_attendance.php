<?php

include('../config/connect_db.php');

// ชื่อไฟล์ที่ต้องการอ่าน
$filename = 'd:\ims_time_attendance.txt';

// ตรวจสอบว่าไฟล์มีอยู่หรือไม่
if (file_exists($filename)) {
    // อ่านข้อมูลจากไฟล์
    $fileContent = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    try {
        // เตรียมคำสั่ง SQL สำหรับเพิ่มข้อมูล
        $insertStmt = $conn->prepare("
            INSERT INTO ims_time_attendance (employee_code, full_code, date, time, status, device)
            VALUES (:employee_code, :full_code, :date, :time, :status, :device)
        ");

        // เตรียมคำสั่ง SQL สำหรับตรวจสอบข้อมูลซ้ำ
        $checkStmt = $conn->prepare("
            SELECT COUNT(*) FROM ims_time_attendance
            WHERE employee_code = :employee_code AND date = :date AND time = :time
        ");

        // เก็บผลลัพธ์ใน array
        $result = [];

        foreach ($fileContent as $line) {
            // ตรวจสอบว่าบรรทัดเริ่มต้นด้วยตัวเลขหรือไม่
            if (!preg_match('/^\d/', $line)) {
                continue; // ข้ามบรรทัดที่ไม่ได้เริ่มต้นด้วยตัวเลข
            }

            // รหัสพนักงาน 5 ตัวแรก
            $employeeCode = substr($line, 0, 5);

            // แยกคอลัมน์ตามช่องว่าง
            $columns = preg_split('/\s+/', trim($line));

            // สร้าง array สำหรับแถวปัจจุบัน
            $row = [
                'employee_code' => $employeeCode,
                'full_code' => $columns[0] ?? '',
                'date' => $columns[2] ?? '',
                'time' => $columns[3] ?? '',
                'status' => $columns[4] ?? '',
                'device' => $columns[5] ?? '',
            ];

            // ตรวจสอบข้อมูลซ้ำในฐานข้อมูล
            $checkStmt->execute([
                ':employee_code' => $row['employee_code'],
                ':date' => $row['date'],
                ':time' => $row['time'],
            ]);

            // หากข้อมูลซ้ำ ให้ข้ามไป
            if ($checkStmt->fetchColumn() > 0) {
                continue;
            }

            // เพิ่มข้อมูลในผลลัพธ์
            $result[] = $row;

            // บันทึกข้อมูลลงฐานข้อมูล
            $insertStmt->execute([
                ':employee_code' => $row['employee_code'],
                ':full_code' => $row['full_code'],
                ':date' => $row['date'],
                ':time' => $row['time'],
                ':status' => $row['status'],
                ':device' => $row['device'],
            ]);
        }

        // แสดงผลข้อมูล
        echo "<pre>";
        print_r($result);
        echo "</pre>";

        echo "บันทึกข้อมูลลงฐานข้อมูลเรียบร้อยแล้ว!";
    } catch (PDOException $e) {
        echo "เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล: " . $e->getMessage();
    }
} else {
    echo "ไม่พบไฟล์: $filename";
}

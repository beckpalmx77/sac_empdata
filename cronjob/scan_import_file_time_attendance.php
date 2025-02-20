<?php

include('../config/connect_db.php');

date_default_timezone_set("Asia/Bangkok");

// กำหนดโฟลเดอร์ที่ต้องการค้นหา

//$directory = 'D:\finger';

$directory = 'H:\FingerScan';

// ดึงวันที่ปัจจุบันในรูปแบบ Y-m-d
$currentDate = date('Y-m-d');

// จับเวลาเริ่มต้นกระบวนการ
$startTime = microtime(true);

// ตรวจสอบว่าโฟลเดอร์มีอยู่หรือไม่
if (is_dir($directory)) {
    // อ่านไฟล์ทั้งหมดในโฟลเดอร์
    $files = scandir($directory);

    $filesForToday = [];

    foreach ($files as $file) {
        $filePath = $directory . DIRECTORY_SEPARATOR . $file;

        // ตรวจสอบว่าเป็นไฟล์ ไม่ใช่โฟลเดอร์
        if (is_file($filePath)) {
            // ดึงเวลาแก้ไขไฟล์ล่าสุด
            $modifiedTime = filemtime($filePath);

            // แปลงเวลาแก้ไขไฟล์เป็นวันที่
            $fileDate = date('Y-m-d', $modifiedTime);

            // ถ้าวันที่ตรงกับวันที่ปัจจุบัน
            if ($fileDate === $currentDate) {
                $filesForToday[] = $filePath;
            }
        }
    }

    // ตรวจสอบว่าพบไฟล์สำหรับวันที่ปัจจุบันหรือไม่
    if (!empty($filesForToday)) {
        echo "เริ่มประมวลผลไฟล์สำหรับวันที่ $currentDate:\n";

        foreach ($filesForToday as $filename) {
            echo "กำลังประมวลผลไฟล์: $filename\n";

            if (file_exists($filename)) {
                // อ่านข้อมูลจากไฟล์และเรียงลำดับย้อนกลับ
                $fileContent = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $fileContent = array_reverse($fileContent); // กลับลำดับจากท้าย
                $fileContent = array_slice($fileContent, 0, 500); // อ่านเฉพาะ 300 บรรทัดสุดท้าย

                try {
                    // เตรียมคำสั่ง SQL สำหรับเพิ่มข้อมูล
                    $insertStmt = $conn->prepare("
                        INSERT INTO ims_time_attendance (emp_id, full_code, date, time, status, device)
                        VALUES (:emp_id, :full_code, :date, :time, :status, :device)
                    ");

                    // เตรียมคำสั่ง SQL สำหรับตรวจสอบข้อมูลซ้ำ
                    $checkStmt = $conn->prepare("
                        SELECT COUNT(*) FROM ims_time_attendance
                        WHERE emp_id = :emp_id AND date = :date AND time = :time
                    ");

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
                            'emp_id' => $employeeCode,
                            'full_code' => $columns[0] ?? '',
                            'date' => $columns[2] ?? '',
                            'time' => $columns[3] ?? '',
                            'status' => $columns[4] ?? '',
                            'device' => $columns[5] ?? '',
                        ];

                        // ตรวจสอบข้อมูลซ้ำในฐานข้อมูล
                        $checkStmt->execute([
                            ':emp_id' => $row['emp_id'],
                            ':date' => $row['date'],
                            ':time' => $row['time'],
                        ]);

                        echo "กำลังประมวลผลไฟล์: $filename import = " . $row['emp_id'] . " | " . $row['date'] . " | " . $row['time'] . "\n\r";

                        // หากข้อมูลซ้ำ ให้ข้ามไป
                        if ($checkStmt->fetchColumn() > 0) {
                            continue;
                        }

                        // บันทึกข้อมูลลงฐานข้อมูล
                        $insertStmt->execute([
                            ':emp_id' => $row['emp_id'],
                            ':full_code' => $row['full_code'],
                            ':date' => $row['date'],
                            ':time' => $row['time'],
                            ':status' => $row['status'],
                            ':device' => $row['device'],
                        ]);
                    }

                    echo "บันทึกข้อมูลจากไฟล์ $filename เรียบร้อยแล้ว!\n";
                } catch (PDOException $e) {
                    echo "เกิดข้อผิดพลาดในการบันทึกข้อมูลจากไฟล์ $filename: " . $e->getMessage();
                }
            } else {
                echo "ไม่พบไฟล์: $filename\n";
            }
        }
    } else {
        echo "ไม่มีไฟล์ที่ถูกแก้ไขในวันที่ $currentDate\n";
    }
} else {
    echo "ไม่พบโฟลเดอร์: $directory\n";
}

// จับเวลาเสร็จสิ้นกระบวนการ
$endTime = microtime(true);

// คำนวณระยะเวลาในการประมวลผล
$duration = $endTime - $startTime;

$date_write = date("Y-m-d H:i:s");

$txt = "Write File At " . $date_write . "\nเริ่มต้นประมวลผลเวลา: " . date('H:i:s', (int)$startTime) . "\n"
    . "สิ้นสุดประมวลผลเวลา: " . date('H:i:s', (int)$endTime) . "\n"
    . "ระยะเวลาในการประมวลผลทั้งหมด: " . round($duration, 2) . " วินาที\n";

$my_file = fopen("success-scan.txt", "w") or die("Unable to open file!");
fwrite($my_file, $txt);
fclose($my_file);
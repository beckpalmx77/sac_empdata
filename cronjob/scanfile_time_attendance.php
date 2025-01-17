<?php

date_default_timezone_set("Asia/Bangkok");
// กำหนดโฟลเดอร์ที่ต้องการค้นหา
$directory = 'T:\FingerScan';

// ดึงวันที่ปัจจุบันในรูปแบบ Y-m-d
$currentDate = date('Y-m-d');

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
                // เพิ่มข้อมูลไฟล์ลงในอาเรย์
                $filesForToday[] = [
                    'name' => $file,
                    'path' => $filePath,
                    'modified_time' => $modifiedTime,
                ];
            }
        }
    }

    // แสดงผลลัพธ์ไฟล์ทั้งหมดที่ถูกแก้ไขในวันที่ปัจจุบัน
    if (!empty($filesForToday)) {
        echo "ไฟล์ทั้งหมดสำหรับวันที่ $currentDate:\n";
        foreach ($filesForToday as $file) {
            echo "ชื่อไฟล์: " . $file['name'] . "\n";
            echo "ตำแหน่งไฟล์: " . $file['path'] . "\n";
            echo "เวลาแก้ไข: " . date('Y-m-d H:i:s', $file['modified_time']) . "\n";
            echo "--------------------------\n";
        }
    } else {
        echo "ไม่มีไฟล์ที่ถูกแก้ไขในวันที่ $currentDate";
    }
} else {
    echo "ไม่พบโฟลเดอร์: $directory";
}


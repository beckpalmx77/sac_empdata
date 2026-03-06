<?php
require_once 'config/connect_db.php';

// ตั้งค่าพื้นฐาน
set_time_limit(0);
date_default_timezone_set('Asia/Bangkok');
$logFile = 'db_optimize_log_' . date('Y-m-d') . '.log';
$processOutput = ""; // สำหรับเก็บข้อความไปแสดงในกล่อง Result

// ฟังก์ชันบันทึก Log และเก็บค่าไว้แสดงผล
function handleLog($message, $file) {
    $timestamp = date('H:i:s');
    $logEntry = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($file, $logEntry, FILE_APPEND);
    return $logEntry;
}

// ตรวจสอบว่ามีการกดปุ่มเริ่มทำงานหรือไม่
if (isset($_POST['start_process'])) {
    try {
        $dbName = $conn->query("SELECT DATABASE()")->fetchColumn();
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $processOutput .= handleLog("--- เริ่มต้น Optimize Database: $dbName ---", $logFile);
        $processOutput .= handleLog("พบทั้งหมด " . count($tables) . " ตาราง", $logFile);

        foreach ($tables as $table) {
            try {
                $conn->query("OPTIMIZE TABLE `$table` ");
                $processOutput .= handleLog("สำเร็จ: `$table` ถูกจัดระเบียบแล้ว", $logFile);
            } catch (PDOException $e) {
                $processOutput .= handleLog("ผิดพลาด: `$table` - " . $e->getMessage(), $logFile);
            }
        }
        $processOutput .= handleLog("--- สิ้นสุดกระบวนการทั้งหมด ---", $logFile);
    } catch (PDOException $e) {
        $processOutput .= handleLog("CRITICAL ERROR: " . $e->getMessage(), $logFile);
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MySQL Reindex Tool</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; padding-top: 50px; }
        .log-container {
            background-color: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            border-radius: 8px;
            height: 400px;
            overflow-y: scroll;
            font-family: 'Courier New', Courier, monospace;
            white-space: pre-wrap;
            font-size: 0.9rem;
        }
        .success-text { color: #4caf50; }
        .error-text { color: #f44336; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">MySQL Database Optimizer / Reindex</h4>
                </div>
                <div class="card-body text-center">
                    <p class="text-muted">คำสั่งนี้จะทำการ `OPTIMIZE TABLE` ทุกตารางในฐานข้อมูลของคุณเพื่อจัดระเบียบ Index ใหม่</p>

                    <form method="post">
                        <button type="submit" name="start_process" class="btn btn-success btn-lg px-5 mb-4"
                                onclick="return confirm('คำเตือน: ตารางขนาดใหญ่อาจถูก Lock ชั่วคราว ต้องการดำเนินการต่อหรือไม่?')">
                            เริ่มกระบวนการ Reindex ทั้งหมด
                        </button>
                    </form>

                    <?php if ($processOutput !== ""): ?>
                        <div class="text-start">
                            <h6>ผลการทำงาน (Process Log):</h6>
                            <div class="log-container">
                                <?php
                                // ทำให้ Log มีสีสัน
                                $styledOutput = str_replace("สำเร็จ:", "<span class='success-text'>สำเร็จ:</span>", $processOutput);
                                $styledOutput = str_replace("ผิดพลาด:", "<span class='error-text'>ผิดพลาด:</span>", $styledOutput);
                                echo $styledOutput;
                                ?>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted">บันทึกประวัติการทำงานไว้ที่: <code><?php echo $logFile; ?></code></small>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
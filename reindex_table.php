<?php
require_once 'config/connect_db.php';

// ส่วนประมวลผล API
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $table = isset($_GET['table']) ? $_GET['table'] : '';
    
    // ตรวจสอบตารางเพื่อป้องกัน SQL Injection
    if (!empty($table)) {
        try {
            $stmt = $conn->prepare("
                SELECT TABLE_NAME, TABLE_TYPE, ENGINE 
                FROM information_schema.TABLES 
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table
            ");
            $stmt->execute(['table' => $table]);
            $tableInfo = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$tableInfo) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => '❌ ไม่พบตารางที่ระบุในฐานข้อมูล']);
                exit;
            }
        } catch (PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => '❌ เกิดข้อผิดพลาดในการตรวจสอบตาราง: ' . $e->getMessage()]);
            exit;
        }
    }

    // 1. Action: แปลง Engine เป็น InnoDB และ Optimize Table
    if ($action == 'optimize' && !empty($table)) {
        $response = [
            'success' => false,
            'skipped' => false,
            'converted' => false,
            'orig_engine' => '',
            'new_engine' => 'INNODB',
            'before' => 0,
            'after' => 0,
            'message' => ''
        ];
        try {
            // ข้าม VIEW
            if ($tableInfo['TABLE_TYPE'] === 'VIEW') {
                $response['skipped']  = true;
                $response['success']  = true;
                $response['message']  = "⏭️ ข้าม: `$table` [VIEW]";
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }

            // ดึง ENGINE ตารางล่าสุด
            $infoStmt = $conn->prepare("
                SELECT ENGINE 
                FROM information_schema.TABLES 
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table
            ");
            $infoStmt->execute(['table' => $table]);
            $engineInfo = $infoStmt->fetch(PDO::FETCH_ASSOC);
            $origEngine = strtoupper($engineInfo['ENGINE'] ?? 'INNODB');
            $response['orig_engine'] = $origEngine;

            // วัดขนาดก่อน
            $sizeQuery = "SELECT ROUND(((data_length + index_length) / 1024 / 1024), 2) 
                          FROM information_schema.TABLES 
                          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table";
            $stmtSize = $conn->prepare($sizeQuery);
            $stmtSize->execute(['table' => $table]);
            $response['before'] = (float)$stmtSize->fetchColumn();

            $converted = false;
            // หากตารางไม่ใช่ InnoDB ให้แปลง Engine เป็น InnoDB
            if ($origEngine !== 'INNODB') {
                $conn->exec("ALTER TABLE `$table` ENGINE = InnoDB");
                $converted = true;
                $response['converted'] = true;
            }

            // ANALYZE TABLE (อัปเดต index statistics)
            $analyzeResult = $conn->query("ANALYZE TABLE `$table`")->fetchAll(PDO::FETCH_ASSOC);
            $analyzeMsg    = $analyzeResult[0]['Msg_text'] ?? 'OK';

            // OPTIMIZE TABLE (rebuild/defragment)
            $optResult = $conn->query("OPTIMIZE TABLE `$table`")->fetchAll(PDO::FETCH_ASSOC);
            $optMsg    = $optResult[0]['Msg_text'] ?? 'OK';

            // วัดขนาดหลัง
            $stmtSize->execute(['table' => $table]);
            $response['after'] = (float)$stmtSize->fetchColumn();

            $response['success'] = true;
            $saved = max(0, $response['before'] - $response['after']);
            
            if ($converted) {
                $response['message'] = "🔄 [$origEngine ➔ InnoDB] `$table` แปลง Engine สำเร็จ! [{$response['before']} MB → {$response['after']} MB] คืนพื้นที่: " . round($saved, 2) . " MB | ANALYZE: $analyzeMsg | OPTIMIZE: $optMsg";
            } else {
                $response['message'] = "✅ [InnoDB] `$table` [{$response['before']} MB → {$response['after']} MB] คืนพื้นที่: " . round($saved, 2) . " MB | ANALYZE: $analyzeMsg | OPTIMIZE: $optMsg";
            }

        } catch (PDOException $e) {
            $response['message'] = "❌ ผิดพลาด: `$table` - " . $e->getMessage();
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // 2. Action: แปลง Engine เป็น InnoDB เฉพาะตาราง (Convert Engine Only)
    if ($action == 'convert_engine' && !empty($table)) {
        $response = [
            'success' => false,
            'skipped' => false,
            'converted' => false,
            'orig_engine' => '',
            'new_engine' => 'INNODB',
            'message' => ''
        ];
        try {
            if ($tableInfo['TABLE_TYPE'] === 'VIEW') {
                $response['skipped'] = true;
                $response['success'] = true;
                $response['message'] = "⏭️ ข้าม: `$table` เป็น VIEW ไม่สามารถแปลง ENGINE ได้";
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }

            $infoStmt = $conn->prepare("
                SELECT ENGINE 
                FROM information_schema.TABLES 
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table
            ");
            $infoStmt->execute(['table' => $table]);
            $engineInfo = $infoStmt->fetch(PDO::FETCH_ASSOC);
            $origEngine = strtoupper($engineInfo['ENGINE'] ?? '');
            $response['orig_engine'] = $origEngine;

            if ($origEngine === 'INNODB') {
                $response['success'] = true;
                $response['converted'] = false;
                $response['message'] = "ℹ️ ตาราง `$table` เป็น InnoDB อยู่แล้ว";
            } else {
                $conn->exec("ALTER TABLE `$table` ENGINE = InnoDB");
                $conn->query("ANALYZE TABLE `$table`");
                $response['success'] = true;
                $response['converted'] = true;
                $response['message'] = "✅ แปลง ENGINE ของตาราง `$table` จาก $origEngine เป็น InnoDB เรียบร้อยแล้ว";
            }
        } catch (PDOException $e) {
            $response['message'] = "❌ ผิดพลาดในการแปลง ENGINE `$table`: " . $e->getMessage();
        }
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // 3. Action: ดึงข้อมูลรายละเอียดของตาราง (Table Info)
    if ($action == 'get_table_info' && !empty($table)) {
        try {
            $stmt = $conn->prepare("
                SELECT TABLE_NAME, ENGINE, TABLE_ROWS, TABLE_COLLATION, TABLE_TYPE,
                       ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) AS TOTAL_SIZE_MB,
                       ROUND((DATA_LENGTH / 1024 / 1024), 2) AS DATA_SIZE_MB,
                       ROUND((INDEX_LENGTH / 1024 / 1024), 2) AS INDEX_SIZE_MB
                FROM information_schema.TABLES 
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table
            ");
            $stmt->execute(['table' => $table]);
            $info = $stmt->fetch(PDO::FETCH_ASSOC);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $info]);
        } catch (PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => '❌ ไม่สามารถดึงข้อมูลตาราง: ' . $e->getMessage()]);
        }
        exit;
    }

    // 4. Action: ดึงดัชนี (Indexes) ของตาราง
    if ($action == 'get_indexes' && !empty($table)) {
        try {
            $stmt = $conn->prepare("SHOW INDEX FROM `$table`");
            $stmt->execute();
            $rawIndexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $indexes = [];
            foreach ($rawIndexes as $row) {
                $keyName = $row['Key_name'];
                if (!isset($indexes[$keyName])) {
                    $indexes[$keyName] = [
                        'name' => $keyName,
                        'unique' => $row['Non_unique'] == 0,
                        'type' => $row['Index_type'],
                        'columns' => [],
                        'cardinality' => $row['Cardinality'] ?? '-',
                        'comment' => $row['Index_comment'] ?? ''
                    ];
                }
                $indexes[$keyName]['columns'][(int)$row['Seq_in_index'] - 1] = $row['Column_name'];
            }
            foreach ($indexes as &$idx) {
                ksort($idx['columns']);
                $idx['columns'] = array_values($idx['columns']);
            }
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => array_values($indexes)]);
        } catch (PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ไม่สามารถดึงข้อมูลดัชนีได้: ' . $e->getMessage()]);
        }
        exit;
    }

    // 5. Action: ดึงคอลัมน์ของตาราง
    if ($action == 'get_columns' && !empty($table)) {
        try {
            $stmt = $conn->prepare("SHOW COLUMNS FROM `$table`");
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $data = [];
            foreach ($columns as $col) {
                $data[] = [
                    'name' => $col['Field'],
                    'type' => $col['Type'],
                    'null' => $col['Null'],
                    'key' => $col['Key'],
                    'default' => $col['Default'],
                    'extra' => $col['Extra']
                ];
            }
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ไม่สามารถดึงข้อมูลคอลัมน์ได้: ' . $e->getMessage()]);
        }
        exit;
    }

    // 6. Action: สร้าง Index
    if ($action == 'create_index' && !empty($table)) {
        $index_name = isset($_GET['index_name']) ? trim($_GET['index_name']) : '';
        $index_type = isset($_GET['index_type']) ? trim($_GET['index_type']) : 'INDEX';
        $cols = isset($_GET['columns']) ? $_GET['columns'] : [];

        if (empty($cols) || !is_array($cols)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => '❌ กรุณาเลือกคอลัมน์อย่างน้อย 1 คอลัมน์']);
            exit;
        }

        // ตรวจสอบความถูกต้องของชื่อคอลัมน์เพื่อความปลอดภัย
        foreach ($cols as $col) {
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $col)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => '❌ ชื่อคอลัมน์ไม่ถูกต้อง: ' . $col]);
                exit;
            }
        }

        // ตั้งชื่อ Index อัตโนมัติถ้าไม่ได้ใส่
        if (empty($index_name)) {
            $index_name = 'idx_' . $table . '_' . implode('_', $cols);
            if (strlen($index_name) > 60) {
                $index_name = 'idx_' . substr(md5($index_name), 0, 10);
            }
        }

        // ตรวจสอบความถูกต้องของชื่อ Index เพื่อความปลอดภัย
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $index_name)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => '❌ ชื่อ Index ต้องประกอบด้วยตัวอักษรภาษาอังกฤษ ตัวเลข หรือ _ เท่านั้น']);
            exit;
        }

        $escaped_cols = array_map(function($c) { return "`$c`"; }, $cols);
        $cols_sql = implode(', ', $escaped_cols);

        $type_sql = 'INDEX';
        if ($index_type === 'UNIQUE') {
            $type_sql = 'UNIQUE INDEX';
        } elseif ($index_type === 'FULLTEXT') {
            $type_sql = 'FULLTEXT INDEX';
        }

        try {
            $sql = "ALTER TABLE `$table` ADD $type_sql `$index_name` ($cols_sql)";
            $conn->exec($sql);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => "✅ สร้าง Index `$index_name` ในตาราง `$table` เรียบร้อยแล้ว"]);
        } catch (PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => '❌ ไม่สามารถสร้าง Index: ' . $e->getMessage()]);
        }
        exit;
    }

    // 7. Action: ลบ Index
    if ($action == 'drop_index' && !empty($table)) {
        $index_name = isset($_GET['index_name']) ? trim($_GET['index_name']) : '';

        if (empty($index_name)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => '❌ กรุณาระบุชื่อ Index']);
            exit;
        }

        if ($index_name !== 'PRIMARY' && !preg_match('/^[a-zA-Z0-9_]+$/', $index_name)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => '❌ ชื่อ Index ไม่ถูกต้อง']);
            exit;
        }

        try {
            if ($index_name === 'PRIMARY') {
                $sql = "ALTER TABLE `$table` DROP PRIMARY KEY";
            } else {
                $sql = "ALTER TABLE `$table` DROP INDEX `$index_name`";
            }
            $conn->exec($sql);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => "✅ ลบ Index `$index_name` ออกจากตาราง `$table` เรียบร้อยแล้ว"]);
        } catch (PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => '❌ ไม่สามารถลบ Index: ' . $e->getMessage()]);
        }
        exit;
    }

    // 8. Action: Analyze Table
    if ($action == 'analyze_table' && !empty($table)) {
        try {
            $stmt = $conn->query("ANALYZE TABLE `$table`");
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $msg = $res[0]['Msg_text'] ?? 'OK';
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => "✅ ปรับปรุงสถิติ Index (ANALYZE: $msg) เรียบร้อยแล้ว"]);
        } catch (PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => '❌ ไม่สามารถปรับปรุงสถิติ Index: ' . $e->getMessage()]);
        }
        exit;
    }
}

// ส่วนการแสดงผล
include('includes/Header.php');

if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
    exit;
} else {
    // ดึงตารางทั้งหมดในฐานข้อมูล
    $stmt = $conn->query(
        "SELECT TABLE_NAME, TABLE_TYPE, ENGINE, TABLE_ROWS, 
                ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) AS TOTAL_SIZE_MB
         FROM information_schema.TABLES 
         WHERE TABLE_SCHEMA = DATABASE() 
         ORDER BY TABLE_NAME"
    );
    $allTables  = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $tableNames = array_column($allTables, 'TABLE_NAME');
    $totalCount = count($tableNames);
    $viewCount  = count(array_filter($allTables, fn($t) => $t['TABLE_TYPE'] === 'VIEW'));
    $baseCount  = $totalCount - $viewCount;
    $innodbCount = count(array_filter($allTables, fn($t) => $t['TABLE_TYPE'] !== 'VIEW' && strtoupper($t['ENGINE'] ?? '') === 'INNODB'));
    $nonInnodbCount = $baseCount - $innodbCount;

    $dashboard_url = isset($_SESSION['dashboard_page']) ? $_SESSION['dashboard_page'] : 'dashboard.php';
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <head>
        <style>
            .sidebar-lock {
                position: fixed;
                top: 0; left: 0;
                width: 250px; height: 100%;
                background: rgba(0,0,0,0.1);
                z-index: 9999;
                cursor: not-allowed;
                display: none;
            }
            .working-overlay {
                pointer-events: none;
                opacity: 0.7;
            }
            /* ดีไซน์พรีเมียมและเอฟเฟกต์สำหรับ Index & Engine Manager */
            .card {
                border-radius: 12px;
                border: none;
                transition: transform 0.2s, box-shadow 0.2s;
            }
            .card-header {
                border-top-left-radius: 12px !important;
                border-top-right-radius: 12px !important;
            }
            .btn-premium {
                border-radius: 8px;
                font-weight: 600;
                transition: all 0.2s;
            }
            .btn-premium:hover:not(:disabled) {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            }
            .table-hover tbody tr:hover {
                background-color: rgba(78, 115, 223, 0.04) !important;
            }
            .custom-checkbox .custom-control-label::before {
                border-radius: 4px;
            }
            .badge-column {
                font-size: 85%;
                font-family: 'Consolas', monospace;
                padding: 4px 8px;
            }
            #columns-checkbox-list {
                border-radius: 8px;
                scrollbar-width: thin;
            }
            .index-badge {
                font-size: 80%;
                padding: 4px 6px;
                font-weight: 600;
            }
            .cursor-pointer {
                cursor: pointer;
            }
            .engine-tag {
                font-size: 82%;
                font-weight: 600;
                padding: 3px 8px;
                border-radius: 6px;
            }
        </style>
    </head>
    <body id="page-top">
    <div id="lock-overlay" class="sidebar-lock"></div>

    <div id="wrapper">
        <?php include('includes/Side-Bar.php'); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('includes/Top-Bar.php'); ?>
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h4 mb-0 text-gray-800">Database Optimization & InnoDB Engine Converter</h1>
                    </div>

                    <!-- CARD 1: MySQL Table Optimizer & Engine Converter -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card shadow mb-4 border-left-primary">
                                <div class="card-header py-3 bg-primary text-white d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold"><i class="fas fa-database mr-2"></i>MySQL Table Optimizer & Engine Converter</h6>
                                    <a href="<?php echo $dashboard_url; ?>" class="btn btn-sm btn-light shadow-sm text-primary">
                                        <i class="fas fa-home fa-sm"></i> Home
                                    </a>
                                </div>
                                <div class="card-body">
                                    <!-- Summary Statistics -->
                                    <div class="row text-center mb-4">
                                        <div class="col-md-2 col-6 border-right mb-2 mb-md-0">
                                            <span class="text-muted small font-weight-bold">ตารางทั้งหมด (BASE)</span>
                                            <div class="h4 font-weight-bold text-dark mb-0"><?php echo $baseCount; ?></div>
                                        </div>
                                        <div class="col-md-2 col-6 border-right mb-2 mb-md-0">
                                            <span class="text-muted small font-weight-bold">InnoDB</span>
                                            <div class="h4 font-weight-bold text-success mb-0"><span id="innodb-count"><?php echo $innodbCount; ?></span></div>
                                        </div>
                                        <div class="col-md-3 col-6 border-right mb-2 mb-md-0">
                                            <span class="text-muted small font-weight-bold">Non-InnoDB (เช่น MyISAM)</span>
                                            <div class="h4 font-weight-bold <?php echo $nonInnodbCount > 0 ? 'text-warning' : 'text-muted'; ?> mb-0"><span id="non-innodb-count"><?php echo $nonInnodbCount; ?></span></div>
                                        </div>
                                        <div class="col-md-2 col-6 border-right mb-2 mb-md-0">
                                            <span class="text-muted small font-weight-bold">VIEW (ข้าม)</span>
                                            <div class="h4 font-weight-bold text-secondary mb-0"><?php echo $viewCount; ?></div>
                                        </div>
                                        <div class="col-md-3 col-12">
                                            <span class="text-muted small font-weight-bold">Total Space Saved</span>
                                            <div class="h4 font-weight-bold text-info mb-0"><span id="total-saved">0.00</span> MB</div>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="text-center mb-4">
                                        <div id="main-action-buttons">
                                            <button id="start-btn" class="btn btn-primary btn-lg px-4 mr-2 mb-2 btn-premium">
                                                <i class="fas fa-magic mr-2"></i>แปลงเป็น InnoDB & Optimize ทุกตาราง
                                            </button>
                                            <button id="convert-only-btn" class="btn btn-info btn-lg px-4 mb-2 btn-premium">
                                                <i class="fas fa-exchange-alt mr-2"></i>แปลงเป็น InnoDB ทุกตาราง (โหมดเร็ว)
                                            </button>
                                        </div>
                                        <div id="after-action-btns" class="d-none mt-2">
                                            <button id="reset-btn" class="btn btn-warning btn-lg px-4 mr-2 mb-2 btn-premium">
                                                <i class="fas fa-undo mr-2"></i>Reset หน้าจอ
                                            </button>
                                            <button id="download-btn" class="btn btn-outline-info btn-lg px-4 mr-2 mb-2 btn-premium">
                                                <i class="fas fa-file-alt mr-2"></i>ดาวน์โหลดผลลัพธ์
                                            </button>
                                            <a href="<?php echo $dashboard_url; ?>" class="btn btn-outline-secondary btn-lg px-4 mb-2 btn-premium">
                                                <i class="fas fa-home mr-2"></i>กลับหน้าหลัก
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Progress & Log -->
                                    <div id="ui-section" class="d-none">
                                        <div class="progress mb-3" style="height: 25px;">
                                            <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 0%;">0%</div>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span id="status-text" class="font-weight-bold text-primary small">รอดำเนินการ...</span>
                                            <span id="count-text" class="text-muted small">0 / <?php echo $totalCount; ?></span>
                                        </div>
                                        <div id="log-window" style="background-color: #1e1e1e; color: #dcdccc; padding: 20px; border-radius: 8px; height: 350px; overflow-y: auto; font-family: 'Consolas', monospace; font-size: 13px; line-height: 1.5; text-align: left;">
                                            <div style="color: #666;">--- กดปุ่มด้านบนเพื่อเริ่มกระบวนการ ---</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CARD 2: Table Index & Engine Manager -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card shadow mb-4 border-left-success">
                                <div class="card-header py-3 bg-success text-white d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold"><i class="fas fa-key mr-2"></i>ระบบจัดการ Index & แปลง Engine รายตาราง (Table & Index Manager)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3 align-items-center">
                                        <div class="col-md-5">
                                            <label for="select-table" class="font-weight-bold text-dark">เลือกตารางที่ต้องการจัดการ:</label>
                                            <select id="select-table" class="form-control">
                                                <option value="">-- กรุณาเลือกตาราง --</option>
                                                <?php foreach ($allTables as $tbl): 
                                                    $isView = ($tbl['TABLE_TYPE'] === 'VIEW');
                                                    $tblEng = strtoupper($tbl['ENGINE'] ?? '');
                                                    $badgeText = $isView ? 'VIEW' : ($tblEng ?: 'TABLE');
                                                ?>
                                                    <option value="<?php echo htmlspecialchars($tbl['TABLE_NAME']); ?>" data-engine="<?php echo htmlspecialchars($tblEng); ?>" data-type="<?php echo htmlspecialchars($tbl['TABLE_TYPE']); ?>">
                                                        <?php echo htmlspecialchars($tbl['TABLE_NAME']); ?> [<?php echo $badgeText; ?>]
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-7 d-flex flex-wrap align-items-end justify-content-md-end mt-3 mt-md-0">
                                            <button id="btn-convert-single" class="btn btn-outline-warning mr-2 mb-2 btn-premium" disabled>
                                                <i class="fas fa-exchange-alt mr-1"></i> แปลงเป็น InnoDB
                                            </button>
                                            <button id="btn-analyze-table" class="btn btn-outline-primary mr-2 mb-2 btn-premium" disabled>
                                                <i class="fas fa-sync mr-1"></i> ปรับปรุงสถิติ Index (ANALYZE)
                                            </button>
                                            <button id="btn-optimize-table" class="btn btn-outline-success mb-2 btn-premium" disabled>
                                                <i class="fas fa-tools mr-1"></i> Rebuild & Optimize
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Table Info Box -->
                                    <div id="table-info-box" class="d-none alert alert-light border mb-3 py-2 px-3">
                                        <div class="row align-items-center small">
                                            <div class="col-md-3"><strong>ตาราง:</strong> <span id="info-table-name" class="text-primary font-weight-bold">-</span></div>
                                            <div class="col-md-3"><strong>Engine:</strong> <span id="info-engine-badge" class="badge badge-info">-</span></div>
                                            <div class="col-md-3"><strong>จำนวนแถว:</strong> <span id="info-table-rows" class="text-dark font-weight-bold">-</span></div>
                                            <div class="col-md-3"><strong>ขนาดรวม:</strong> <span id="info-table-size" class="text-success font-weight-bold">-</span> MB</div>
                                        </div>
                                    </div>

                                    <div id="index-manager-section" class="d-none">
                                        <hr>
                                        <div class="row">
                                            <!-- List of existing indexes -->
                                            <div class="col-lg-7 border-right">
                                                <h5 class="h6 font-weight-bold text-primary mb-3"><i class="fas fa-list mr-1"></i> Index ปัจจุบันในตาราง</h5>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped table-hover" id="indexes-table">
                                                        <thead class="bg-gray-100 text-dark font-weight-bold">
                                                            <tr>
                                                                <th>ชื่อ Index</th>
                                                                <th>คอลัมน์ที่ใช้งาน</th>
                                                                <th>ประเภท</th>
                                                                <th>Unique</th>
                                                                <th>Cardinality</th>
                                                                <th>การจัดการ</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="indexes-list-body">
                                                            <!-- Will be filled by JS -->
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <!-- Create new index form -->
                                            <div class="col-lg-5 pl-lg-4 mt-4 mt-lg-0">
                                                <h5 class="h6 font-weight-bold text-success mb-3"><i class="fas fa-plus-circle mr-1"></i> สร้าง Index ใหม่</h5>
                                                <div class="card bg-light p-3 border">
                                                    <div class="form-group">
                                                        <label for="new-index-name" class="small font-weight-bold text-dark">ชื่อ Index (ถ้าเว้นว่างจะตั้งให้อัตโนมัติ):</label>
                                                        <input type="text" id="new-index-name" class="form-control form-control-sm" placeholder="เช่น idx_emp_name">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="new-index-type" class="small font-weight-bold text-dark">ประเภท Index:</label>
                                                        <select id="new-index-type" class="form-control form-control-sm">
                                                            <option value="INDEX">INDEX (ดัชนีทั่วไป)</option>
                                                            <option value="UNIQUE">UNIQUE (ค่าห้ามซ้ำ)</option>
                                                            <option value="FULLTEXT">FULLTEXT (ค้นหาข้อความเต็ม)</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="small font-weight-bold text-dark mb-1">เลือกคอลัมน์ (เลือกหลายคอลัมน์เพื่อทำ Composite Index):</label>
                                                        <div style="max-height: 200px; overflow-y: auto; background: white; padding: 10px; border-radius: 4px; border: 1px solid #d1d3e2;" id="columns-checkbox-list">
                                                            <!-- Will be filled by JS -->
                                                        </div>
                                                    </div>
                                                    <button id="btn-create-index" class="btn btn-success btn-sm btn-block btn-premium">
                                                        <i class="fas fa-save mr-1"></i> สร้าง Index ใหม่
                                                    </button>
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
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tables        = <?php echo json_encode($tableNames); ?>;
            const startBtn      = document.getElementById('start-btn');
            const convertOnlyBtn = document.getElementById('convert-only-btn');
            const resetBtn      = document.getElementById('reset-btn');
            const downloadBtn   = document.getElementById('download-btn');
            const mainActionBtns = document.getElementById('main-action-buttons');
            const afterActionBtns = document.getElementById('after-action-btns');
            const progressBar   = document.getElementById('progress-bar');
            const uiSection     = document.getElementById('ui-section');
            const logWindow     = document.getElementById('log-window');
            const statusText    = document.getElementById('status-text');
            const countText     = document.getElementById('count-text');
            const totalSavedLabel = document.getElementById('total-saved');
            const innodbCountLabel = document.getElementById('innodb-count');
            const nonInnodbCountLabel = document.getElementById('non-innodb-count');
            const lockOverlay   = document.getElementById('lock-overlay');
            const sidebar       = document.getElementById('accordionSidebar');

            // Elements ของ Index & Engine Manager
            const selectTbl         = document.getElementById('select-table');
            const btnConvertSingle  = document.getElementById('btn-convert-single');
            const btnAnalyze        = document.getElementById('btn-analyze-table');
            const btnOptimizeTbl    = document.getElementById('btn-optimize-table');
            const tableInfoBox      = document.getElementById('table-info-box');
            const infoTableName     = document.getElementById('info-table-name');
            const infoEngineBadge   = document.getElementById('info-engine-badge');
            const infoTableRows     = document.getElementById('info-table-rows');
            const infoTableSize     = document.getElementById('info-table-size');
            const idxManagerSec     = document.getElementById('index-manager-section');
            const indexesListBody   = document.getElementById('indexes-list-body');
            const colCheckboxList   = document.getElementById('columns-checkbox-list');
            const newIdxNameInput   = document.getElementById('new-index-name');
            const newIdxTypeSelect  = document.getElementById('new-index-type');
            const btnCreateIdx      = document.getElementById('btn-create-index');

            let logContent = "";
            let totalSaved = 0;
            let currentInnodbCount = parseInt(innodbCountLabel.innerText) || 0;
            let currentNonInnodbCount = parseInt(nonInnodbCountLabel.innerText) || 0;

            function updateEngineCounters(convertedCount) {
                if (convertedCount > 0) {
                    currentInnodbCount += convertedCount;
                    currentNonInnodbCount = Math.max(0, currentNonInnodbCount - convertedCount);
                    innodbCountLabel.innerText = currentInnodbCount;
                    nonInnodbCountLabel.innerText = currentNonInnodbCount;
                    if (currentNonInnodbCount === 0) {
                        nonInnodbCountLabel.classList.remove('text-warning');
                        nonInnodbCountLabel.classList.add('text-muted');
                    }
                }
            }

            function setInterfaceLock(isLocked) {
                lockOverlay.style.display = isLocked ? 'block' : 'none';
                if (sidebar) sidebar.classList.toggle('working-overlay', isLocked);
                
                startBtn.disabled = isLocked;
                if (convertOnlyBtn) convertOnlyBtn.disabled = isLocked;
                if (selectTbl) selectTbl.disabled = isLocked;
                if (btnCreateIdx) btnCreateIdx.disabled = isLocked;
                
                if (btnConvertSingle) {
                    btnConvertSingle.disabled = isLocked || !selectTbl.value;
                }
                if (btnAnalyze) {
                    btnAnalyze.disabled = isLocked || !selectTbl.value;
                }
                if (btnOptimizeTbl) {
                    btnOptimizeTbl.disabled = isLocked || !selectTbl.value;
                }

                document.querySelectorAll('.btn-drop-idx').forEach(btn => {
                    btn.disabled = isLocked;
                });
            }

            function appendLog(message, color = '#dcdccc', isSkipped = false) {
                const time    = new Date().toLocaleTimeString();
                const logLine = `[${time}] ${message}`;
                const div     = document.createElement('div');
                div.style.color        = color;
                div.style.marginBottom = '3px';
                div.style.opacity      = isSkipped ? '0.5' : '1';
                div.innerText          = logLine;
                logWindow.appendChild(div);
                logWindow.scrollTop    = logWindow.scrollHeight;
                logContent            += logLine + "\n";
            }

            // 1. ปุ่มเริ่มรัน แปลง Engine เป็น InnoDB & Optimize ทุกตาราง
            startBtn.addEventListener('click', async () => {
                if (!confirm('ยืนยันการเริ่มทำงาน? ระบบจะแปลงทุกตารางเป็น InnoDB และทำการ Optimize & Reindex ข้อมูลทั้งหมด')) return;

                setInterfaceLock(true);
                mainActionBtns.classList.add('d-none');
                afterActionBtns.classList.add('d-none');
                uiSection.classList.remove('d-none');
                logWindow.innerHTML = '';
                totalSaved = 0;
                totalSavedLabel.innerText = "0.00";
                logContent = "Database Optimization & InnoDB Conversion Report\nDate: " + new Date().toLocaleString() + "\n" + "=".repeat(70) + "\n";

                let completed = 0;
                let convertedTotal = 0;
                const total   = tables.length;

                for (const table of tables) {
                    statusText.innerText = `กำลังจัดการ: ${table}...`;
                    try {
                        const res    = await fetch(`?action=optimize&table=${encodeURIComponent(table)}`);
                        const result = await res.json();

                        if (result.skipped) {
                            appendLog(result.message, '#888888', true);
                        } else if (result.success) {
                            const saved = Math.max(0, (result.before || 0) - (result.after || 0));
                            totalSaved += saved;
                            totalSavedLabel.innerText = totalSaved.toFixed(2);
                            
                            if (result.converted) {
                                convertedTotal++;
                                updateEngineCounters(1);
                                appendLog(result.message, '#ffc107'); // สีเหลืองทองเมื่อแปลง Engine สำเร็จ
                                updateDropdownOptionEngine(table, 'INNODB');
                            } else {
                                appendLog(result.message, '#8cf68c'); // สีเขียวปกติ
                            }
                        } else {
                            appendLog(result.message, '#ff6b6b');
                        }

                    } catch (error) {
                        appendLog(`❌ ไม่สามารถประมวลผลตาราง: ${table}`, '#ff6b6b');
                    }

                    completed++;
                    const percent = Math.round((completed / total) * 100);
                    progressBar.style.width  = percent + '%';
                    progressBar.innerText    = percent + '%';
                    countText.innerText      = `${completed} / ${total}`;
                }

                logContent += "=".repeat(70) + "\nTotal Space Saved: " + totalSaved.toFixed(2) + " MB\nTotal Tables Converted to InnoDB: " + convertedTotal + "\n";
                statusText.innerText = `✅ เสร็จสมบูรณ์! (แปลงเป็น InnoDB: ${convertedTotal} ตาราง, คืนพื้นที่รวม: ${totalSaved.toFixed(2)} MB)`;
                afterActionBtns.classList.remove('d-none');
                setInterfaceLock(false);

                if (selectTbl.value) {
                    loadTableDetails(selectTbl.value);
                }
            });

            // 2. ปุ่มแปลงเป็น InnoDB ทุกตาราง (โหมดเร็ว)
            convertOnlyBtn.addEventListener('click', async () => {
                if (!confirm('ยืนยันการแปลง Engine ของทุกตารางให้เป็น InnoDB (โหมดเร็ว)?')) return;

                setInterfaceLock(true);
                mainActionBtns.classList.add('d-none');
                afterActionBtns.classList.add('d-none');
                uiSection.classList.remove('d-none');
                logWindow.innerHTML = '';
                logContent = "Fast Engine Conversion to InnoDB Report\nDate: " + new Date().toLocaleString() + "\n" + "=".repeat(70) + "\n";

                let completed = 0;
                let convertedTotal = 0;
                const total   = tables.length;

                for (const table of tables) {
                    statusText.innerText = `กำลังแปลงตาราง: ${table}...`;
                    try {
                        const res    = await fetch(`?action=convert_engine&table=${encodeURIComponent(table)}`);
                        const result = await res.json();

                        if (result.skipped) {
                            appendLog(result.message, '#888888', true);
                        } else if (result.success) {
                            if (result.converted) {
                                convertedTotal++;
                                updateEngineCounters(1);
                                appendLog(result.message, '#ffc107');
                                updateDropdownOptionEngine(table, 'INNODB');
                            } else {
                                appendLog(result.message, '#8cf68c');
                            }
                        } else {
                            appendLog(result.message, '#ff6b6b');
                        }

                    } catch (error) {
                        appendLog(`❌ ไม่สามารถแปลง Engine ของตาราง: ${table}`, '#ff6b6b');
                    }

                    completed++;
                    const percent = Math.round((completed / total) * 100);
                    progressBar.style.width  = percent + '%';
                    progressBar.innerText    = percent + '%';
                    countText.innerText      = `${completed} / ${total}`;
                }

                logContent += "=".repeat(70) + "\nTotal Tables Converted to InnoDB: " + convertedTotal + "\n";
                statusText.innerText = `✅ แปลง Engine เป็น InnoDB เสร็จสมบูรณ์! (${convertedTotal} ตาราง)`;
                afterActionBtns.classList.remove('d-none');
                setInterfaceLock(false);

                if (selectTbl.value) {
                    loadTableDetails(selectTbl.value);
                }
            });

            // 3. Reset Button
            resetBtn.addEventListener('click', () => {
                mainActionBtns.classList.remove('d-none');
                afterActionBtns.classList.add('d-none');
                uiSection.classList.add('d-none');
                totalSavedLabel.innerText = "0.00";
                progressBar.style.width   = '0%';
                progressBar.innerText     = '0%';
                logWindow.innerHTML       = '<div style="color: #666;">--- กดปุ่มด้านบนเพื่อเริ่มกระบวนการ ---</div>';
            });

            // 4. Download Report Button
            downloadBtn.addEventListener('click', () => {
                const blob = new Blob([logContent], { type: 'text/plain;charset=utf-8' });
                const url  = window.URL.createObjectURL(blob);
                const a    = document.createElement('a');
                a.href     = url;
                a.download = `db_innodb_optimize_report_${new Date().toISOString().slice(0,10)}.txt`;
                a.click();
                window.URL.revokeObjectURL(url);
            });

            // อัปเดตข้อความใน Dropdown option เมื่อ Engine เปลี่ยน
            function updateDropdownOptionEngine(tableName, newEngine) {
                const opt = selectTbl.querySelector(`option[value="${tableName}"]`);
                if (opt) {
                    opt.setAttribute('data-engine', newEngine);
                    opt.textContent = `${tableName} [${newEngine}]`;
                }
            }

            // โหลดข้อมูลรวมของตาราง
            async function loadTableDetails(tableName) {
                if (!tableName) {
                    tableInfoBox.classList.add('d-none');
                    idxManagerSec.classList.add('d-none');
                    btnConvertSingle.disabled = true;
                    btnAnalyze.disabled = true;
                    btnOptimizeTbl.disabled = true;
                    return;
                }

                tableInfoBox.classList.remove('d-none');
                idxManagerSec.classList.remove('d-none');
                infoTableName.innerText = tableName;
                infoEngineBadge.className = 'badge badge-secondary';
                infoEngineBadge.innerText = 'กำลังโหลด...';

                try {
                    const res = await fetch(`?action=get_table_info&table=${encodeURIComponent(tableName)}`);
                    const result = await res.json();
                    if (result.success && result.data) {
                        const d = result.data;
                        const eng = (d.ENGINE || '').toUpperCase();
                        const isView = (d.TABLE_TYPE === 'VIEW');

                        if (isView) {
                            infoEngineBadge.className = 'badge badge-secondary';
                            infoEngineBadge.innerText = 'VIEW';
                            btnConvertSingle.disabled = true;
                            btnAnalyze.disabled = true;
                            btnOptimizeTbl.disabled = true;
                        } else {
                            if (eng === 'INNODB') {
                                infoEngineBadge.className = 'badge badge-success';
                                infoEngineBadge.innerHTML = '<i class="fas fa-check-circle mr-1"></i>InnoDB';
                                btnConvertSingle.disabled = true;
                                btnConvertSingle.innerHTML = '<i class="fas fa-check mr-1"></i> เป็น InnoDB แล้ว';
                            } else {
                                infoEngineBadge.className = 'badge badge-warning text-dark';
                                infoEngineBadge.innerHTML = `<i class="fas fa-exclamation-circle mr-1"></i>${eng || 'NON-INNODB'}`;
                                btnConvertSingle.disabled = false;
                                btnConvertSingle.innerHTML = '<i class="fas fa-exchange-alt mr-1"></i> แปลงเป็น InnoDB';
                            }
                            btnAnalyze.disabled = false;
                            btnOptimizeTbl.disabled = false;
                        }

                        infoTableRows.innerText = d.TABLE_ROWS ? Number(d.TABLE_ROWS).toLocaleString() : '0';
                        infoTableSize.innerText = d.TOTAL_SIZE_MB || '0.00';
                    }
                } catch (e) {
                    infoEngineBadge.innerText = 'Error';
                }

                loadTableIndexes(tableName);
                loadTableColumns(tableName);
            }

            // ฟังก์ชันโหลด Index ของตาราง
            async function loadTableIndexes(tableName) {
                indexesListBody.innerHTML = '<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin mr-1"></i>กำลังโหลดข้อมูล Index...</td></tr>';
                try {
                    const res = await fetch(`?action=get_indexes&table=${encodeURIComponent(tableName)}`);
                    const result = await res.json();
                    if (result.success) {
                        indexesListBody.innerHTML = '';
                        if (result.data.length === 0) {
                            indexesListBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">ไม่พบดัชนี (Index) ในตารางนี้</td></tr>';
                            return;
                        }
                        result.data.forEach(idx => {
                            const tr = document.createElement('tr');
                            
                            let uniqueBadge = '';
                            if (idx.name === 'PRIMARY') {
                                uniqueBadge = '<span class="badge badge-danger index-badge">PRIMARY KEY</span>';
                            } else if (idx.unique) {
                                uniqueBadge = '<span class="badge badge-success index-badge">UNIQUE</span>';
                            } else {
                                uniqueBadge = '<span class="badge badge-secondary index-badge">NORMAL</span>';
                            }

                            const colBadges = idx.columns.map(c => `<span class="badge badge-info badge-column mr-1">${c}</span>`).join('');

                            let actionBtn = `<button class="btn btn-danger btn-sm btn-premium btn-drop-idx" data-idx-name="${idx.name}"><i class="fas fa-trash-alt mr-1"></i> ลบ</button>`;
                            if (idx.name === 'PRIMARY') {
                                actionBtn = `<button class="btn btn-danger btn-sm btn-premium btn-drop-idx" data-idx-name="PRIMARY"><i class="fas fa-trash-alt mr-1"></i> ลบ PK</button>`;
                            }

                            tr.innerHTML = `
                                <td class="align-middle font-weight-bold text-dark">${idx.name}</td>
                                <td class="align-middle">${colBadges}</td>
                                <td class="align-middle"><span class="badge badge-light border text-dark">${idx.type}</span></td>
                                <td class="align-middle">${uniqueBadge}</td>
                                <td class="align-middle">${idx.cardinality}</td>
                                <td class="align-middle text-center">${actionBtn}</td>
                            `;
                            indexesListBody.appendChild(tr);
                        });

                        // จัดการปุ่มลบ Index
                        document.querySelectorAll('.btn-drop-idx').forEach(btn => {
                            btn.addEventListener('click', async () => {
                                const idxName = btn.getAttribute('data-idx-name');
                                const confirmMsg = idxName === 'PRIMARY' 
                                    ? `⚠️ คำเตือน: คุณต้องการลบ PRIMARY KEY ของตาราง '${tableName}' ใช่หรือไม่? การกระทำนี้มีความเสี่ยงสูงต่อระบบและโครงสร้างข้อมูล!` 
                                    : `คุณต้องการลบ Index '${idxName}' ออกจากตาราง '${tableName}' ใช่หรือไม่?`;
                                
                                if (!confirm(confirmMsg)) return;

                                setInterfaceLock(true);
                                try {
                                    const res = await fetch(`?action=drop_index&table=${encodeURIComponent(tableName)}&index_name=${encodeURIComponent(idxName)}`);
                                    const result = await res.json();
                                    alert(result.message);
                                    if (result.success) {
                                        loadTableDetails(tableName);
                                    }
                                } catch (err) {
                                    alert('เกิดข้อผิดพลาดในการลบ Index');
                                } finally {
                                    setInterfaceLock(false);
                                }
                            });
                        });
                    } else {
                        indexesListBody.innerHTML = `<tr><td colspan="6" class="text-danger text-center py-3"><i class="fas fa-exclamation-triangle mr-1"></i> ${result.message}</td></tr>`;
                    }
                } catch (error) {
                    indexesListBody.innerHTML = '<tr><td colspan="6" class="text-danger text-center py-3"><i class="fas fa-exclamation-triangle mr-1"></i> เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>';
                }
            }

            // ฟังก์ชันโหลดคอลัมน์ของตาราง
            async function loadTableColumns(tableName) {
                colCheckboxList.innerHTML = '<div class="text-center py-2"><i class="fas fa-spinner fa-spin mr-1"></i>กำลังโหลดข้อมูลคอลัมน์...</div>';
                try {
                    const res = await fetch(`?action=get_columns&table=${encodeURIComponent(tableName)}`);
                    const result = await res.json();
                    if (result.success) {
                        colCheckboxList.innerHTML = '';
                        result.data.forEach(col => {
                            const div = document.createElement('div');
                            div.className = 'custom-control custom-checkbox py-1';
                            
                            let keyBadge = '';
                            if (col.key === 'PRI') {
                                keyBadge = '<span class="badge badge-danger text-white ml-1" style="font-size: 70%">PRI</span>';
                            } else if (col.key === 'UNI') {
                                keyBadge = '<span class="badge badge-success text-white ml-1" style="font-size: 70%">UNI</span>';
                            } else if (col.key === 'MUL') {
                                keyBadge = '<span class="badge badge-secondary text-white ml-1" style="font-size: 70%">MUL</span>';
                            }

                            div.innerHTML = `
                                <input type="checkbox" class="custom-control-input column-chk" id="col-chk-${col.name}" value="${col.name}">
                                <label class="custom-control-label small cursor-pointer w-100" for="col-chk-${col.name}">
                                    <strong class="text-dark">${col.name}</strong> 
                                    <span class="text-muted">(${col.type})</span>
                                    ${keyBadge}
                                </label>
                            `;
                            colCheckboxList.appendChild(div);
                        });
                    } else {
                        colCheckboxList.innerHTML = `<div class="text-danger text-center">${result.message}</div>`;
                    }
                } catch (error) {
                    colCheckboxList.innerHTML = '<div class="text-danger text-center">เกิดข้อผิดพลาดในการโหลดคอลัมน์</div>';
                }
            }

            // เมื่อเปลี่ยนตารางที่เลือก
            selectTbl.addEventListener('change', () => {
                loadTableDetails(selectTbl.value);
            });

            // ปุ่มแปลง Engine เป็น InnoDB สำหรับตารางที่เลือก
            btnConvertSingle.addEventListener('click', async () => {
                const tableName = selectTbl.value;
                if (!tableName) return;

                if (!confirm(`ยืนยันการแปลง Engine ของตาราง '${tableName}' ให้เป็น InnoDB?`)) return;

                setInterfaceLock(true);
                btnConvertSingle.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> กำลังแปลง...';

                try {
                    const res = await fetch(`?action=convert_engine&table=${encodeURIComponent(tableName)}`);
                    const result = await res.json();
                    alert(result.message);
                    if (result.success) {
                        if (result.converted) {
                            updateEngineCounters(1);
                            updateDropdownOptionEngine(tableName, 'INNODB');
                        }
                        loadTableDetails(tableName);
                    }
                } catch (error) {
                    alert('เกิดข้อผิดพลาดในการส่งคำสั่งแปลง Engine');
                } finally {
                    btnConvertSingle.innerHTML = '<i class="fas fa-exchange-alt mr-1"></i> แปลงเป็น InnoDB';
                    setInterfaceLock(false);
                }
            });

            // ปุ่มปรับปรุงสถิติ Index (ANALYZE)
            btnAnalyze.addEventListener('click', async () => {
                const tableName = selectTbl.value;
                if (!tableName) return;
                
                setInterfaceLock(true);
                btnAnalyze.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> กำลังวิเคราะห์...';
                
                try {
                    const res = await fetch(`?action=analyze_table&table=${encodeURIComponent(tableName)}`);
                    const result = await res.json();
                    alert(result.message);
                    if (result.success) {
                        loadTableDetails(tableName);
                    }
                } catch (error) {
                    alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                } finally {
                    btnAnalyze.innerHTML = '<i class="fas fa-sync mr-1"></i> ปรับปรุงสถิติ Index (ANALYZE)';
                    setInterfaceLock(false);
                }
            });

            // ปุ่มจัดดัชนีใหม่ (OPTIMIZE)
            btnOptimizeTbl.addEventListener('click', async () => {
                const tableName = selectTbl.value;
                if (!tableName) return;
                
                if (!confirm(`ต้องการ Rebuild และ Optimize ตาราง '${tableName}' ใช่หรือไม่?`)) return;

                setInterfaceLock(true);
                btnOptimizeTbl.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> กำลังปรับปรุง...';
                
                try {
                    const res = await fetch(`?action=optimize&table=${encodeURIComponent(tableName)}`);
                    const result = await res.json();
                    alert(result.message);
                    if (result.success) {
                        if (result.converted) {
                            updateEngineCounters(1);
                            updateDropdownOptionEngine(tableName, 'INNODB');
                        }
                        loadTableDetails(tableName);
                    }
                } catch (error) {
                    alert('เกิดข้อผิดพลาดในการส่งคำสั่ง OPTIMIZE');
                } finally {
                    btnOptimizeTbl.innerHTML = '<i class="fas fa-tools mr-1"></i> Rebuild & Optimize';
                    setInterfaceLock(false);
                }
            });

            // ปุ่มสร้าง Index ใหม่
            btnCreateIdx.addEventListener('click', async () => {
                const tableName = selectTbl.value;
                if (!tableName) return;

                const indexName = newIdxNameInput.value.trim();
                const indexType = newIdxTypeSelect.value;
                
                const checkedCols = [];
                document.querySelectorAll('.column-chk:checked').forEach(chk => {
                    checkedCols.push(chk.value);
                });

                if (checkedCols.length === 0) {
                    alert('กรุณาเลือกคอลัมน์อย่างน้อย 1 คอลัมน์');
                    return;
                }

                if (!confirm(`ยืนยันการสร้าง Index ในตาราง '${tableName}'?`)) return;

                setInterfaceLock(true);
                btnCreateIdx.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> กำลังสร้าง...';

                let url = `?action=create_index&table=${encodeURIComponent(tableName)}&index_name=${encodeURIComponent(indexName)}&index_type=${encodeURIComponent(indexType)}`;
                checkedCols.forEach(col => {
                    url += `&columns[]=${encodeURIComponent(col)}`;
                });

                try {
                    const res = await fetch(url);
                    const result = await res.json();
                    alert(result.message);
                    if (result.success) {
                        newIdxNameInput.value = '';
                        loadTableDetails(tableName);
                    }
                } catch (error) {
                    alert('เกิดข้อผิดพลาดในการสร้าง Index');
                } finally {
                    btnCreateIdx.innerHTML = '<i class="fas fa-save mr-1"></i> สร้าง Index ใหม่';
                    setInterfaceLock(false);
                }
            });
        });
    </script>

    <?php include('includes/Modal-Logout.php'); ?>

    <!-- Core Javascript (Do NOT load vendor/jquery/jquery.min.js here because it is already loaded in Header.php. Loading it again will overwrite the jQuery object and break Select2/DataTables plugins) -->
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>
    </body>
    </html>
<?php } ?>
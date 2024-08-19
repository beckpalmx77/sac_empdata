<?php
// Database configuration
$host = 'localhost';
$dbname = 'your_database_name';
$username = 'your_username';
$password = 'your_password';
$port = 3307;

try {
    // Create PDO instance
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_POST['leave_type_id']) && isset($_POST['leaves_requested']) && isset($_POST['leave_date'])) {
        $leaveTypeId = $_POST['leave_type_id'];
        $leavesRequested = $_POST['leaves_requested'];
        $leaveDate = $_POST['leave_date'];

        // ตรวจสอบว่าวันที่ลามากกว่าหรือเท่ากับ 3 วันจากวันนี้หรือไม่
        $currentDate = new DateTime();
        $selectedLeaveDate = new DateTime($leaveDate);
        $interval = $currentDate->diff($selectedLeaveDate)->days;

        if ($interval >= 3) {
            // ตรวจสอบสิทธิ์การลาต่อจากที่เคยทำไว้ในตัวอย่างก่อนหน้า
            $stmt = $pdo->prepare("SELECT max_leaves_allowed FROM leave_types WHERE leave_type_id = :leave_type_id");
            $stmt->bindParam(':leave_type_id', $leaveTypeId, PDO::PARAM_INT);
            $stmt->execute();
            $leaveType = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($leaveType) {
                $stmt = $pdo->prepare("SELECT SUM(leaves_taken) AS total_leaves_taken FROM leave_records WHERE leave_type_id = :leave_type_id");
                $stmt->bindParam(':leave_type_id', $leaveTypeId, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $totalLeavesTaken = $result['total_leaves_taken'] ?? 0;

                $maxLeavesAllowed = $leaveType['max_leaves_allowed'];
                $totalLeavesAfterRequest = $totalLeavesTaken + $leavesRequested;

                if ($totalLeavesAfterRequest <= $maxLeavesAllowed) {
                    // บันทึกข้อมูล
                    $stmt = $pdo->prepare("INSERT INTO leave_records (employee_id, leave_type_id, leaves_taken, leave_date) VALUES (:employee_id, :leave_type_id, :leaves_taken, :leave_date)");
                    $stmt->bindParam(':employee_id', $employeeId); // เปลี่ยนให้ตรงกับระบบของคุณ
                    $stmt->bindParam(':leave_type_id', $leaveTypeId, PDO::PARAM_INT);
                    $stmt->bindParam(':leaves_taken', $leavesRequested, PDO::PARAM_INT);
                    $stmt->bindParam(':leave_date', $leaveDate);
                    $stmt->execute();

                    echo "Leave request approved and saved successfully.";
                } else {
                    echo "Leave request exceeds the allowed limit.";
                }
            } else {
                echo "Invalid Leave Type ID.";
            }
        } else {
            echo "The leave date must be at least 3 days from today.";
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

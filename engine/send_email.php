<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // โหลด PHPMailer (ใช้ Composer)
require '../config/email_config.php'; // ตั้งค่า SMTP

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $to_email = $_POST['to_email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    $mail = new PHPMailer(true);

    try {
        // ตั้งค่า SMTP
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;

        // ตั้งค่าผู้ส่งและผู้รับ
        $mail->setFrom(SMTP_USER, 'My Website');
        $mail->addAddress($to_email);

        // ตั้งค่าหัวข้อและเนื้อหา
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->isHTML(true);

        // ส่งอีเมล
        $mail->send();
        echo "<script>alert('ส่งอีเมลสำเร็จ!'); window.location.href='email_form.php';</script>";
    } catch (Exception $e) {
        echo "เกิดข้อผิดพลาด: {$mail->ErrorInfo} {$mail->Port}";
    }
} else {
    echo "ไม่อนุญาตให้เข้าถึงหน้านี้โดยตรง";
}
?>

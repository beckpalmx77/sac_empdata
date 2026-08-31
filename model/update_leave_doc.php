<?php
include('../config/connect_db.php');

if($_POST['action'] == "UPDATE_SLIP") {
    $id = $_POST['payment_id'];

    if(isset($_FILES['new_slip']['name']) && $_FILES['new_slip']['name'] != "") {
        $ext = strtolower(pathinfo($_FILES['new_slip']['name'], PATHINFO_EXTENSION));
        $upload_dir = __DIR__ . '/../img_doc/'; // ใช้ __DIR__ เพื่อให้ Path ถูกต้องเสมอ
        $tmp = $_FILES['new_slip']['tmp_name'];

        if ($ext === 'heic' || $ext === 'heif') {
            $base_no_ext = pathinfo($_FILES['new_slip']['name'], PATHINFO_FILENAME);
            $file_name = time() . "_" . $base_no_ext . ".jpg";
            $target = $upload_dir . $file_name;
            
            $converted = false;
            if (extension_loaded('imagick')) {
                try {
                    $imagick = new Imagick();
                    $imagick->readImage($tmp);
                    $imagick->setImageFormat('jpeg');
                    $imagick->writeImage($target);
                    $imagick->clear();
                    $imagick->destroy();
                    $converted = true;
                } catch (Exception $e) {
                    error_log("Imagick error: " . $e->getMessage());
                }
            }
            if (!$converted) {
                move_uploaded_file($tmp, $target);
            }
            $uploaded = true;
        } else {
            $file_name = time() . "_" . $_FILES['new_slip']['name'];
            $target = $upload_dir . $file_name;
            $uploaded = move_uploaded_file($tmp, $target);
        }

        if($uploaded) {
            $sql = "UPDATE ims_house_payment SET picture_payment = :slip WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':slip', $file_name);
            $stmt->bindParam(':id', $id);
            if($stmt->execute()){
                echo "success";
            } else {
                echo "db_fail";
            }
        } else {
            echo "upload_fail";
        }
    }
}

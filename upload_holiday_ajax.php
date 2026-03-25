<?php

include('config/connect_db.php');

$valid_extensions = array('jpeg', 'jpg', 'png', 'gif', 'bmp', 'pdf', 'doc', 'docx');
$path = 'img_doc/';

if(!empty($_POST['id']) || $_FILES['image'])
{
    $id = $_POST['id'];
    $filenames = [];
    
    if (!empty($_FILES['image']['name'][0])) {
        foreach ($_FILES['image']['name'] as $key => $img) {
            if (!empty($img)) {
                $tmp = $_FILES['image']['tmp_name'][$key];
                $ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));
                
                if(in_array($ext, $valid_extensions)) {
                    $final_image = time() . "_" . $key . "_" . basename($img);
                    $uploadPath = $path . strtolower($final_image);
                    
                    if(move_uploaded_file($tmp, $uploadPath)) {
                        $filenames[] = strtolower($final_image);
                    }
                }
            }
        }
    }
    
    if (count($filenames) > 0) {
        $picture = implode(",", $filenames);
        
        $sql_update = "UPDATE dholiday_event SET picture=:picture
                       WHERE id = :id";
        $query = $conn->prepare($sql_update);
        $query->bindParam(':picture', $picture, PDO::PARAM_STR);
        $query->bindParam(':id', $id, PDO::PARAM_STR);
        $query->execute();
        
        echo $picture;
    } else {
        echo 'invalid';
    }
}

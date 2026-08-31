<?php
header('Content-Type: application/json; charset=utf-8');
include('config/connect_db.php');

$upload_dir = __DIR__ . '/img_doc/';
$valid_extensions = array('jpeg', 'jpg', 'png', 'gif', 'bmp', 'webp', 'heic', 'heif', 'pdf', 'doc', 'docx');

$action = $_POST['action'] ?? $_GET['action'] ?? 'upload';

if ($action === 'get_recent_files') {
    $files = [];
    if (is_dir($upload_dir)) {
        $scanned = scandir($upload_dir, SCANDIR_SORT_DESCENDING);
        $count = 0;
        foreach ($scanned as $file) {
            if ($file === '.' || $file === '..' || is_dir($upload_dir . $file)) continue;
            if ($file === 'doc_leave_api.php' || $file === 'image_doc.psd') continue;
            
            $filePath = $upload_dir . $file;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
            
            $files[] = [
                'name' => $file,
                'url' => 'img_doc/' . rawurlencode($file),
                'size' => filesize($filePath),
                'size_formatted' => formatBytes(filesize($filePath)),
                'time' => date("Y-m-d H:i:s", filemtime($filePath)),
                'is_image' => $isImage,
                'ext' => $ext
            ];
            
            $count++;
            if ($count >= 15) break;
        }
    }
    echo json_encode(['status' => 'success', 'files' => $files]);
    exit;
}

if ($action === 'upload') {
    $uploaded_files = [];
    $errors = [];

    if (!empty($_FILES['images'])) {
        $filesArray = $_FILES['images'];
        $fileCount = is_array($filesArray['name']) ? count($filesArray['name']) : 1;

        for ($i = 0; $i < $fileCount; $i++) {
            $name = is_array($filesArray['name']) ? $filesArray['name'][$i] : $filesArray['name'];
            $tmp = is_array($filesArray['tmp_name']) ? $filesArray['tmp_name'][$i] : $filesArray['tmp_name'];
            $error = is_array($filesArray['error']) ? $filesArray['error'][$i] : $filesArray['error'];
            $size = is_array($filesArray['size']) ? $filesArray['size'][$i] : $filesArray['size'];

            if ($error !== UPLOAD_ERR_OK || empty($name)) {
                $errors[] = "Error uploading $name (Code: $error)";
                continue;
            }

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $base_name = pathinfo($name, PATHINFO_FILENAME);

            if (!in_array($ext, $valid_extensions)) {
                $errors[] = "File type not allowed: $ext for file $name";
                continue;
            }

            // If server received raw HEIC file, attempt conversion
            if ($ext === 'heic' || $ext === 'heif') {
                $final_name = "test_" . time() . "_" . $i . "_" . preg_replace('/[^a-zA-Z0-9_-]/', '_', $base_name) . ".jpg";
                $target = $upload_dir . $final_name;
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
                        error_log("Imagick conversion failed: " . $e->getMessage());
                    }
                }

                if (!$converted) {
                    // Save as is if Imagick not installed
                    move_uploaded_file($tmp, $target);
                }

                $uploaded_files[] = [
                    'original_name' => $name,
                    'saved_name' => $final_name,
                    'url' => 'img_doc/' . rawurlencode($final_name),
                    'size' => file_exists($target) ? filesize($target) : $size,
                    'size_formatted' => formatBytes(file_exists($target) ? filesize($target) : $size),
                    'note' => $converted ? 'Converted on server with Imagick' : 'Saved from client-side conversion / upload',
                    'is_image' => true
                ];
            } else {
                // Normal file or client-converted JPG
                $final_name = "test_" . time() . "_" . $i . "_" . basename($name);
                $target = $upload_dir . $final_name;

                if (move_uploaded_file($tmp, $target)) {
                    $uploaded_files[] = [
                        'original_name' => $name,
                        'saved_name' => $final_name,
                        'url' => 'img_doc/' . rawurlencode($final_name),
                        'size' => filesize($target),
                        'size_formatted' => formatBytes(filesize($target)),
                        'note' => 'Uploaded successfully',
                        'is_image' => in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'])
                    ];
                } else {
                    $errors[] = "Failed to move uploaded file $name";
                }
            }
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No files provided']);
        exit;
    }

    echo json_encode([
        'status' => count($uploaded_files) > 0 ? 'success' : 'error',
        'files' => $uploaded_files,
        'errors' => $errors
    ]);
    exit;
}

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

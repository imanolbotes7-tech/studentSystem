<?php
ob_start();

header('Content-Type: application/json');

// 1. Include Database Connection
$connectionPath = __DIR__ . '/../core/connection.php';
if (!file_exists($connectionPath)) {
    $connectionPath = __DIR__ . '/../../core/connection.php';
}

if (file_exists($connectionPath)) {
    require_once $connectionPath;
}

$response = array();

// 2. Absolute path to target folder: htdocs/school_system-main/uploads/
$targetDir = __DIR__ . '/../../uploads/';

// Create uploads folder if it doesn't exist
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}

// 3. Process File Upload
if (isset($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    
    $fileTmpPath = $_FILES['image']['tmp_name'];
    $fileName = basename($_FILES['image']['name']);
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'webp');
    
    if (in_array($fileExtension, $allowTypes)) {
        
        // Generate unique filename to prevent overwriting
        $newFileName = "STUDENT_" . time() . "_" . rand(1000, 9999) . "." . $fileExtension;
        $targetFilePath = $targetDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $targetFilePath)) {
            
            // Path saved to database & rendered in HTML <img> tags
            $imageUrl = "uploads/" . $newFileName;

            // If student_id is passed, update MySQL record immediately
            if (isset($_POST['student_id']) && !empty($_POST['student_id']) && isset($conn)) {
                $studentId = mysqli_real_escape_string($conn, $_POST['student_id']);
                $cleanImageUrl = mysqli_real_escape_string($conn, $imageUrl);

               $updateSql = "UPDATE students SET image_url = '$cleanImageUrl' WHERE student_id = '$studentId'";
                
                if (!mysqli_query($conn, $updateSql)) {
                    ob_clean();
                    echo json_encode(array(
                        'status' => 'error',
                        'message' => 'Image saved to disk, but database update failed: ' . mysqli_error($conn)
                    ));
                    exit();
                }
            }

            ob_clean();
            $response['status'] = 'success';
            $response['message'] = 'Image uploaded successfully';
            $response['image_url'] = $imageUrl;
            echo json_encode($response);
            exit();

        } else {
            ob_clean();
            http_response_code(500);
            $response['status'] = 'error';
            $response['message'] = 'Failed to save uploaded file to uploads directory';
            echo json_encode($response);
            exit();
        }

    } else {
        ob_clean();
        http_response_code(400);
        $response['status'] = 'error';
        $response['message'] = 'Only JPG, JPEG, PNG, WEBP, & GIF files are allowed';
        echo json_encode($response);
        exit();
    }

} else {
    ob_clean();
    http_response_code(400);
    $response['status'] = 'error';
    $response['message'] = 'No valid image file uploaded';
    echo json_encode($response);
    exit();
}
?>
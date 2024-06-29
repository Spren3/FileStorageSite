<?php

if (!defined('IN_INDEX')) { exit("This file can only be included in index.php."); }

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$uploadDir = 'uploads/';



// Create directory if it does not exist
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        die("Failed to create directory: $uploadDir");
    }
}

// Handle single file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $originalName = $_FILES['file']['name'];
    $description = $_POST['description'];
    $isPublic = isset($_POST['is_public']) ? 1 : 0;
    $uploadDate = date('Y-m-d H:i:s');
    $savedName = generateSessionId() . '.' . pathinfo($originalName, PATHINFO_EXTENSION);
    $path = $uploadDir . $savedName;

    // Check if file was successfully uploaded
    if (move_uploaded_file($_FILES['file']['tmp_name'], $path)) {
        try {
            $db = new PDO('sqlite:php.sqlite');
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            echo "Connected to SQLite successfully.";
            
            $stmt = $db->prepare("INSERT INTO files (original_name, description, upload_date, saved_name, path, is_private) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$originalName, $description, $uploadDate, $savedName, $path, $isPublic]);

            $message = 'File uploaded successfully.';
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Database error: ' . $e->getMessage();
            $messageType = 'error';
        }
    } else {
        $message = 'Error uploading file.';
        $messageType = 'error';
    }
}

// Render upload.html template with message
print TwigHelper::getInstance()->render('upload.html', [
    'message' => $message ?? null,
    'messageType' => $messageType ?? null,
]);


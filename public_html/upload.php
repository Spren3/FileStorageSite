<?php

if (!defined('IN_INDEX')) {
    exit("This file can only be included in index.php.");
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$uploadDir = 'uploads/';

if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        die("Failed to create directory: $uploadDir");
    }
}

// Pobranie wszystkich folderów z bazy danych
try {
    //$db = new PDO('sqlite:C:\Users\Thinkpad\Documents\GitHub\FileStorageSite\instance\php.sqlite');
    $db = new PDO('sqlite:D:\GitHub Desktop\FileStorageSite\instance\php.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->query("SELECT id, name FROM directories ORDER BY name ASC");
    $directories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    array_unshift($directories, ['id' => null, 'name' => 'NULL']);
    
} catch (PDOException $e) {
    $message = 'Database error: ' . $e->getMessage();
    $messageType = 'error';
    $directories = [];
}


$maxFileSize = 10 * 1024 * 1024; // 10 MB

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $fileSize = $_FILES['file']['size'];
    $originalName = $_FILES['file']['name'];
    $description = $_POST['description'];
    $isPublic = isset($_POST['is_public']) ? 1 : 0;
    $uploadDate = date('Y-m-d H:i:s');
    $savedName = generateSessionId() . '.' . pathinfo($originalName, PATHINFO_EXTENSION);
    $path = $uploadDir . $savedName;
    
    // Pobranie directory_id z formularza POST lub parametrów GET
    $directoryId = isset($_POST['directory_id']) ? $_POST['directory_id'] : (isset($_GET['directory_id']) ? $_GET['directory_id'] : null);

    if ($fileSize > $maxFileSize) {
        $message = 'File size exceeds the maximum allowed limit.';
        $messageType = 'error';
    } else {
        if (move_uploaded_file($_FILES['file']['tmp_name'], $path)) {
            try {
                $db = new PDO('sqlite:D:\GitHub Desktop\FileStorageSite\instance\php.sqlite');                
                //$db = new PDO('sqlite:D:\GitHub Desktop\FileStorageSite\instance\php.sqlite');
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $stmt = $db->prepare("INSERT INTO files (original_name, description, upload_date, saved_name, path, is_public, directory_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$originalName, $description, $uploadDate, $savedName, $path, $isPublic, $directoryId]);

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
}

print TwigHelper::getInstance()->render('upload.html', [
    'message' => $message ?? null,
    'messageType' => $messageType ?? null,
    'directories' => $directories ?? null
]);


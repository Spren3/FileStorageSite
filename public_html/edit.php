<?php
if (!defined('IN_INDEX')) { exit("This file can only be included in index.php."); }

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$db = new PDO('sqlite:D:\GitHub Desktop\FileStorageSite\instance\php.sqlite');
//$db = new PDO('sqlite:C:\Users\Thinkpad\Documents\GitHub\FileStorageSite\instance\php.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete']) && isset($_POST['file_id']) && intval($_POST['file_id']) > 0) {
        // Usuwanie pliku
        $file_id = intval($_POST['file_id']);
        
        $stmt = $db->prepare('SELECT path FROM files WHERE id = :id');
        $stmt->bindParam(':id', $file_id, PDO::PARAM_INT);
        $stmt->execute();
        $file = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($file) {
            $file_path = $file['path'];

            // Usunięcie pliku z dysku
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Usunięcie wpisu z bazy danych
            $stmt = $db->prepare('DELETE FROM files WHERE id = :id');
            $stmt->bindParam(':id', $file_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $message = 'File deleted successfully.';
                $messageType = 'success';
                header("Location: /");
            } else {
                $message = 'Error deleting file from database.';
                $messageType = 'error';
            }
        } else {
            $message = 'File not found.';
            $messageType = 'error';
        }

        print TwigHelper::getInstance()->render('edit.html', [
            'message' => $message,
            'messageType' => $messageType,
        ]);

    } else if (isset($_POST['file_id']) && intval($_POST['file_id']) > 0) {
        // Aktualizacja pliku
        $file_id = intval($_POST['file_id']);
        $original_name = $_POST['original_name'];
        $description = $_POST['description'];
        $is_public = isset($_POST['is_public']) ? 1 : 0;

        $stmt = $db->prepare('UPDATE files SET original_name = :original_name, description = :description, is_public = :is_public WHERE id = :id');
        $stmt->bindParam(':original_name', $original_name, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':is_public', $is_public, PDO::PARAM_INT);
        $stmt->bindParam(':id', $file_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $message = 'File updated successfully.';
            $messageType = 'success';
        } else {
            $message = 'Error updating file.';
            $messageType = 'error';
        }
        header("Location: /");
        print TwigHelper::getInstance()->render('edit.html', [
            'message' => $message,
            'messageType' => $messageType,
        ]);
    } else {
        $message = 'Invalid file ID.';
        $messageType = 'error';

        print TwigHelper::getInstance()->render('edit.html', [
            'message' => $message,
            'messageType' => $messageType,
        ]);
    }
} else {
    if (isset($_GET['edit']) && intval($_GET['edit']) > 0) {
        $file_id = intval($_GET['edit']);

        $stmt = $db->prepare('SELECT * FROM files WHERE id = :id');
        $stmt->bindParam(':id', $file_id, PDO::PARAM_INT);
        $stmt->execute();

        $file = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($file) {
            $original_name = htmlspecialchars($file['original_name']);
            $description = htmlspecialchars($file['description']);
            $is_public = $file['is_public'];

            print TwigHelper::getInstance()->render('edit.html', [
                'original_name' => $original_name,
                'description' => $description,
                'is_public' => $is_public,
                'file_id' => $file_id,
            ]);
        } else {
            $message = 'File not found.';
            $messageType = 'error';

            print TwigHelper::getInstance()->render('edit.html', [
                'message' => $message,
                'messageType' => $messageType,
            ]);
        }
    } else {
        $message = 'Invalid request.';
        $messageType = 'error';

        print TwigHelper::getInstance()->render('edit.html', [
            'message' => $message,
            'messageType' => $messageType,
        ]);
    }
}
?>

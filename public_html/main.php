<?php

if (!defined('IN_INDEX')) { exit("This file can only be included in index.php."); }
/*
* Wyrenderowanie podstrony z przekazaniem do niej.
*/
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $originalName = $_FILES['file']['name'];
    $description = $_POST['description'];
    $isPublic = isset($_POST['is_public']) ? 1 : 0;
    $uploadDate = date('Y-m-d H:i:s');
    $savedName = generateSessionId() . '.' . pathinfo($originalName, PATHINFO_EXTENSION);
    $uploadDir = 'uploads/';
    $path = $uploadDir . $savedName;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $path)) {
        try {
            $db = new PDO('sqlite:php.sqlite');
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $db->prepare("INSERT INTO files (original_name, description, upload_date, saved_name, path, is_public) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$originalName, $description, $uploadDate, $savedName, $path, $isPublic]);

            TwigHelper::addMsg('Pomyślnie wrzuciłeś swój plik.', 'success');
        } catch (PDOException $e) {
            TwigHelper::addMsg('Przesłanie pliku nie powiodło się.', 'error');
        }
    } else {
        TwigHelper::addMsg('Wystąpił błąd podczas przesyłania pliku.', 'error');
    }
}

print TwigHelper::getInstance()->render('main.html', [
]);
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$fileId = $_GET['file_id'];

try {
    //$db = new PDO('sqlite:C:\Users\Thinkpad\Documents\GitHub\FileStorageSite\instance\php.sqlite');
    $db = new PDO('sqlite:instance/php.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Pobranie informacji o pliku z bazy danych
    $stmt = $db->prepare("SELECT * FROM files WHERE id = ?");
    $stmt->execute([$fileId]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$file) {
        die("Plik nie istnieje.");
    }

    $filePath = 'uploads/' . $file['saved_name'];
    // Sprawdzenie, czy plik fizycznie istnieje
    if (!file_exists($filePath)) {
        die("Plik nie istnieje na serwerze.");
    }

    // Sprawdzenie uprawnień użytkownika
    if (!$file['is_public'] && !isset($_SESSION['id'])) {
        die("Nie masz uprawnień do pobrania tego pliku.");
    } elseif (!$file['is_public'] && isset($_SESSION['logged_in'])) {
        echo "Masz uprawnienia jako zalogowany użytkownik.";
        download($file, $db, $filePath);
    }

    // Ustawienie odpowiednich nagłówków HTTP i wysłanie pliku
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file['original_name']) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));

    readfile($filePath);
    exit();
} catch (PDOException $e) {
    die("Błąd bazy danych: " . $e->getMessage());
}

function download($file, $db, $filePath)
{
    //$filePath = 'uploads/' . $file['saved_name']; // Upewnij się, że ścieżka jest poprawna i pliki są przechowywane poza publicznym katalogiem
    $filepath = $file['path'];
    // Ustawienie odpowiednich nagłówków HTTP i wysłanie pliku
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file['original_name']) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));

    readfile($filePath);
    exit();
}

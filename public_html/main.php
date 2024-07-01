<?php
if (!defined('IN_INDEX')) { exit("This file can only be included in index.php."); }

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$db = new PDO('sqlite:D:\GitHub Desktop\FileStorageSite\instance\php.sqlite');
//$db = new PDO('sqlite:C:\Users\Thinkpad\Documents\GitHub\FileStorageSite\instance\php.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//session_start();

$is_logged_in = isset($_SESSION['id']);

if ($is_logged_in) {
    $stmt = $db->query('SELECT * FROM files ORDER BY id ASC');
} else {
    $stmt = $db->query('SELECT * FROM files WHERE is_public = 1 ORDER BY id ASC');
}

$files = $stmt->fetchAll(PDO::FETCH_ASSOC);

print TwigHelper::getInstance()->render('main.html', [
    'files' => $files,
    '_session' => $_SESSION,
    'is_logged_in' => $is_logged_in,
]);
?>

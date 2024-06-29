<?php

if (!defined('IN_INDEX')) { exit("This file can only be included in index.php."); }
$uploadDir = 'uploads/';

try {
    $db = new PDO('sqlite:php.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to SQLite successfully.";
    
    $stmt = $db->query("SELECT * FROM files WHERE is_public = 1");
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

print TwigHelper::getInstance()->render('main.html', [
    'files' => $files ?? []
]);



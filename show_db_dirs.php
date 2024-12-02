<?php

$db = new PDO('sqlite:instance/php.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $db->query('SELECT * FROM directories');
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($files);

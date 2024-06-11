<?php

const CONFIG = [
    'db_path'  => __DIR__ . '/../instance/php.sqlite', // stała __DIR__ zawsze zawiera ścieżkę, w której znajduje się plik skryptu


];

define('GLOBAL_PASSWORD_HASH', password_hash('MbappeDurant', PASSWORD_DEFAULT));

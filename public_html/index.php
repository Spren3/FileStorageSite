<?php

session_start();

ini_set('display_errors', 1); /// bledy od razu widoczne
error_reporting(E_ALL); //to zniknie jak puscimy na serwer

define("IN_INDEX", true);

require __DIR__ . '/vendor/autoload.php';

include("config.inc.php");

include("helpers.inc.php"); //tu mamy jakies swoje funkcje albo uproszczenia twiga 

if (isset($_POST['password'])) {

    if (password_verify($_POST['password'], GLOBAL_PASSWORD_HASH)) {
        $_SESSION['id'] = generateSessionId();
        TwigHelper::getInstance()->addGlobal('_session', $_SESSION);
        TwigHelper::addMsg('Pomyślnie się zalogowałeś.', 'success');
    } else {
        TwigHelper::addMsg('Niewłaściwe hasło dostępu.', 'error');
    }
}
//wylogowanie
if (isset($_GET['page']) && $_GET['page'] == 'logout') {
    unset($_SESSION['id']);
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
        TwigHelper::addMsg('Pomyślnie wylogowano.', 'success');
    } else {
        TwigHelper::addMsg('Wystąpił błąd.', 'error');
    }

    session_destroy();
    header('Location: /');
    exit();
}


$allowed_pages = ['main', 'upload', 'edit', 'download',/*'register'*/];

///  weryfikacja
if (
    isset($_GET['page'])
    && $_GET['page']
    && in_array($_GET['page'], $allowed_pages)
    && file_exists($_GET['page'] . '.php')
) {
    // Użytkownik podał prawidłową nazwę podstrony.
    include($_GET['page'] . '.php');
} elseif (!isset($_GET['page'])) {
    // Użytkownik nie przekazał żadnej nazwy podstrony.
    include('main.php');
} else {
    // Użytkownik podał nieprawidłową nazwę podstrony. Następuje dodanie komunikatu błędu, który wyświetli się w ramce.
    // Więcej informacji na temat wyświetlania komunikatów znajdziesz w pliku helpers.inc.php.
    TwigHelper::addMsg('Page "' . $_GET['page'] . '" not found.', 'error');
    // Renderujemy tylko główny szablon bez podstrony.
    print TwigHelper::getInstance()->render('base.html');
}

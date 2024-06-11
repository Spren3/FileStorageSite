<?php

if (!defined('IN_INDEX')) { exit("This file can only be included in index.php."); }
///bezposrednio z labow, bo dobre
function get_domain(): string {
    /*
     * użyto funkcji preg_replace, która za pomocą wyrażenia regularnego wyrzuca z ciągu znaków wszystko co nie jest
     * literami, cyframi i znakiem kropki. Biblioteka szablonów Twig dodatkowo wykorzystuje
     * escapowanie danych
     */
    $domena = preg_replace('/[^a-zA-Z0-9\.]/', '', $_SERVER['HTTP_HOST']);
    return $domena;
}

class DB {
    private static $dbh = null; // tutaj będziemy przechowywać obiekt PDO

    public static function getInstance(): PDO {

        // Jeśli w zmiennej statycznej self::$dbh nie mamy jeszcze utworzonego połączenia do bazy danych to je tworzymy.
        if (!self::$dbh) {

            try {
                /*
                 * Tworzymy nowy obiekt wbudowanej w PHP klasy PDO, służącej do komunikacji z bazą danych, przekazując
                 * do jego konstruktora parametry połączenia do bazy danych. Obiekt PDO reprezentujący połączenie z bazą
                 * danych przypisujemy do zmiennej statycznej self::$dbh. Dzięki temu w dowolnym miejscu kodu możemy
                 * uzyskać połączenie do bazy danych za pomocą DB::getInstance().
                 * Za pomocą metody "setAttribute" ustawiamy na utworzonym obiekcie, aby PDO obsługiwało błędy za pomocą
                 * wyjątków (są tez inne tryby, na przykład zwracanie standardowych błędów PHP).
                 * PDO jest potężną biblioteką. Potrafi obsługiwać różne typy baz w uniwersalny sposób, dzięki temu
                 * zmiana oprogramowania serwera bazy danych nie musi oznaczać zmiany kodu/zapytań w PHP.
                 * Opis klasy PDO i jej metod: https://www.php.net/manual/en/class.pdo
                */
                self::$dbh = new PDO("sqlite:" . CONFIG['db_path']);
                self::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                exit("Cannot connect to the database: " . $e->getMessage());
            }
        }
        // Zwracamy obiekt PDO, reprezentujący połączenie z bazą danych.
        return self::$dbh;
    }
}

class TwigHelper {
    private static $twig = null; // tutaj będziemy przechowywać obiekt Twiga
    private static $msg = []; // tutaj będziemy przechowywać komunikaty do wyświetlenia w szablonie base.html

    public static function getInstance(): \Twig\Environment {
        // Jeśli w zmiennej statycznej self::$twig nie mamy jeszcze utworzonego obiektu Twiga to go tworzymy.
        if (!self::$twig) {
            /*
             * Inicjalizujemy obiekt biblioteki Twig zgodnie z dokumentacją ze strony:
             * https://twig.symfony.com/doc/3.x/api.html
             * Obiekt przypisujemy do zmiennej statycznej self::$twig. Dzięki temu w dowolnym miejscu kodu możemy
             * uzyskać do niego dostęp za pomocą TwigSingleton::getInstance().
             */
            $twig_loader = new \Twig\Loader\FilesystemLoader('templates');
            self::$twig = new \Twig\Environment($twig_loader);
        }
        // Zwracamy obiekt Twiga
        return self::$twig;
    }

    /*
     * Klasa TwigHelper dostarczy nam jeszcze jednej przydatnej metody. W tablicy self::$msg trzymane będą
     * komunikaty, które będzie wyświetlać szablon base.html nad treścią podstrony. Możesz dodawać dowolne komunikaty
     * za pomocą TwigHelper::getInstance()->addMsg($text, $type). Przykłady użycia znajdziesz w pliku main.php.
     * Jako typ możesz przekazać ciąg tekstowy: success, info, error. Typ decyduje o kolorze tła komunikatu.
     */
    public static function addMsg(string $text, string $type): void {
        self::$msg[] = [
            'text' => $text,
            'type' => $type
        ];
    }

    // Metoda zwraca całą tablicę z komunikatami - będzie jej używać szablon base.html.
    public static function getMsg(): array {
        return self::$msg;
    }
}

function generateSessionId($length = 16) {
    return bin2hex(random_bytes($length));
}

TwigHelper::getInstance()->addGlobal('CONFIG', CONFIG);
TwigHelper::getInstance()->addFunction(new \Twig\TwigFunction('get_domain', 'get_domain'));
TwigHelper::getInstance()->addFunction(new \Twig\TwigFunction('get_msg', 'TwigHelper::getMsg'));
TwigHelper::getInstance()->addGlobal('_session', $_SESSION);
TwigHelper::getInstance()->addGlobal('_post', $_POST);
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'vendor/autoload.php'; // Ścieżka do autoloadera Twig

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

// Funkcja do połączenia z bazą danych SQLite
function connectToDatabase()
{
    //$pdo = new PDO('sqlite:C:\Users\Thinkpad\Documents\GitHub\FileStorageSite\instance\php.sqlite');
    $pdo = new PDO('sqlite:D:\GitHub Desktop\FileStorageSite\instance\php.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

// Funkcja do dodawania nowego katalogu
function addDirectory($pdo, $name, $parentId = null)
{
    $stmt = $pdo->prepare("INSERT INTO directories (name, parent_id) VALUES (?, ?)");
    $stmt->execute([$name, $parentId]);
}

// Funkcja do pobierania listy katalogów
function getDirectories($pdo, $parentId = null)
{
    if ($parentId === null) {
        $stmt = $pdo->query("SELECT * FROM directories WHERE parent_id IS NULL");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM directories WHERE parent_id = ?");
        $stmt->execute([$parentId]);
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obsługa żądań POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_directory') {
            if (isset($_POST['directory_name']) && !empty($_POST['directory_name'])) {
                $directoryName = $_POST['directory_name'];
                $parentId = $_POST['parent_id'] ?? null;

                try {
                    $pdo = connectToDatabase();
                    addDirectory($pdo, $directoryName, $parentId);
                    echo json_encode(['success' => true]);
                    exit();
                } catch (PDOException $e) {
                    echo json_encode(['success' => false, 'error' => 'Błąd bazy danych: ' . $e->getMessage()]);
                    exit();
                }
            }
        } elseif ($_POST['action'] === 'get_directories') {
            try {
                $pdo = connectToDatabase();
                $directories = getDirectories($pdo);
                echo json_encode(['success' => true, 'directories' => $directories]);
                exit();
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'error' => 'Błąd bazy danych: ' . $e->getMessage()]);
                exit();
            }
        }
    }
}

// Pobranie listy katalogów głównych
try {
    $pdo = connectToDatabase();
    $directories = getDirectories($pdo);
} catch (PDOException $e) {
    die("Błąd bazy danych: " . $e->getMessage());
}

print TwigHelper::getInstance()->render('directories.html', [
    'directories' => $directories
]);
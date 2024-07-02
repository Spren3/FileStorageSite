<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Połączenie z bazą danych SQLite
function connectToDatabase()
{
    //$pdo = new PDO('sqlite:C:\Users\Thinkpad\Documents\GitHub\FileStorageSite\instance\php.sqlite');
    $pdo = new PDO('sqlite:D:\GitHub Desktop\FileStorageSite\instance\php.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

// Funkcja do dodawania nowego katalogu
function addDirectory($pdo, $name, $parent_id = null)
{
    if ($parent_id === '') {
        $parent_id = null; // Ustawienie na NULL, jeśli $parent_id jest pusty (brak wartości w formularzu)
    }

    $stmt = $pdo->prepare("INSERT INTO directories (name, parent_id) VALUES (?, ?)");
    $stmt->execute([$name, $parent_id]);
}

// Funkcja do pobierania listy katalogów
function getDirectories($pdo, $parent_id = null)
{
    if ($parent_id === null) {
        $stmt = $pdo->query("SELECT * FROM directories WHERE parent_id IS NULL");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM directories WHERE parent_id = ?");
        $stmt->execute([$parent_id]);
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Funkcja do usuwania katalogu
function deleteDirectory($pdo, $directory_id)
{
    $stmt = $pdo->prepare("DELETE FROM directories WHERE id = ?");
    $stmt->execute([$directory_id]);
}

// Obsługa żądań AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'add_directory' && !empty($_POST['directory_name'])) {
        $directoryName = $_POST['directory_name'];
        $parent_id = $_POST['parent_id'] ?? null;
        //$parent_id = isset($_GET['directory_id']) && !empty($_GET['directory_id']) ? $_GET['directory_id'] : null;

        try {
            $pdo = connectToDatabase();
            addDirectory($pdo, $directoryName, $parent_id);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Błąd bazy danych: ' . $e->getMessage()]);
        }
    } elseif ($action === 'get_directories') {
        $parent_id = $_POST['directory_id'] ?? null;
        try {
            $pdo = connectToDatabase();
            $directories = getDirectories($pdo, $parent_id);
            echo json_encode(['success' => true, 'directories' => $directories]);

        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Błąd bazy danych: ' . $e->getMessage()]);
        }
    } elseif ($action === 'delete_directory') {
        $directory_id = $_POST['directory_id'];
        try {
            $pdo = connectToDatabase();
            deleteDirectory($pdo, $directory_id);
            echo json_encode(['success' => true]);

        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Błąd bazy danych: ' . $e->getMessage()]);
        }
    }
   exit(); 
}

// Pobieranie listy katalogów na potrzeby renderowania strony
$parent_id = isset($_GET['directory_id']) && !empty($_GET['directory_id']) ? $_GET['directory_id'] : null;

try {
    $pdo = connectToDatabase();
    $directories = getDirectories($pdo, $parent_id);
} catch (PDOException $e) {
    die("Błąd bazy danych: " . $e->getMessage());
}

print TwigHelper::getInstance()->render('directories.html', [
    'directories' => $directories,
    'parent_id' => $parent_id
]);
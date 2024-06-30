<?php
// Sprawdzenie czy przekazano parametr edit w zapytaniu GET
if (isset($_GET['edit']) && intval($_GET['edit']) > 0) {
    // Przechwycenie id z parametru GET
    $file_id = $_GET['edit'];

    // Zabezpieczenie przed SQL Injection i połączenie z bazą danych
    $db = new PDO('sqlite:C:\Users\Thinkpad\Documents\GitHub\FileStorageSite\instance\php.sqlite');           

    // Przygotowanie zapytania
    $stmt = $db->prepare('SELECT * FROM files WHERE id = :id');
    $stmt->bindParam(':id', $file_id, PDO::PARAM_INT);
    $stmt->execute();

    // Pobranie danych pliku
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    // Sprawdzenie czy znaleziono plik o podanym id
    if ($file) {
        // Przekazanie danych do szablonu
        $original_name = htmlspecialchars($file['original_name']);
        $description = htmlspecialchars($file['description']);
        $upload_date = htmlspecialchars($file['upload_date']);
        $saved_name = htmlspecialchars($file['saved_name']);
        $path = htmlspecialchars($file['path']);
        $is_public = $file['is_public'];



         // Wywołanie szablonu z przekazanymi danymi
        print TwigHelper::getInstance()->render('edit.html', [
            'original_name' => $original_name,
            'description' => $description,
            'is_public' => $is_public,
            'file_id' => $file_id,
        ]);

    } else {
        echo '<p>Nie znaleziono pliku o podanym id.</p>';
    }

} else {
    echo '<p>Błąd: Brak parametru edit lub edit nie jest liczbą całkowitą dodatnią w zapytaniu GET.</p>';
}


<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/hec', '', $path);
$path = str_replace('/professeur', '', $path);

require_once __DIR__ . '/../controllers/ProfesseurController.php';
$controller = new ProfesseurController();

if (preg_match('/^\/classes\/(\d+)\/liste$/', $path, $m)) {
    $classeId = (int)$m[1];
    $data = $controller->listeClasse($classeId);
    $classe = $data['classe'] ?? null;
    $etudiants = $data['etudiants'] ?? [];
    include __DIR__ . '/../views/professeur/classes_liste.php';
    return;
}

if ($path === '/notes') {
    $data = $controller->notesIndex();
    $matieres = $data['matieres'] ?? [];
    $classesParMatiere = $data['classesParMatiere'] ?? [];
    include __DIR__ . '/../views/professeur/notes_index.php';
    return;
}

if (preg_match('/^\/notes\/(\d+)\/(\d+)$/', $path, $m)) {
    $classeId = (int)$m[1];
    $matiereId = (int)$m[2];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->notesSaisie($classeId, $matiereId);
        return;
    }
    $data = $controller->notesSaisie($classeId, $matiereId);
    $classe = $data['classe'] ?? null;
    $matiere = $data['matiere'] ?? null;
    $etudiants = $data['etudiants'] ?? [];
    include __DIR__ . '/../views/professeur/notes_saisie.php';
    return;
}

switch ($path) {
    case '':
    case '/':
    case '/dashboard':
        $data = $controller->dashboard();
        $professeur = $data['professeur'] ?? null;
        $matieres = $data['matieres'] ?? [];
        $classes = $data['classes'] ?? [];
        include __DIR__ . '/../views/professeur/dashboard.php';
        break;

    default:
        http_response_code(404);
        include __DIR__ . '/../views/errors/404.php';
        break;
}

<?php
// Routes étudiant

// Récupérer le chemin de la requête
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/hec', '', $path);
$path = str_replace('/etudiant', '', $path);

switch ($path) {
    case '':
    case '/':
    case '/dashboard':
        require_once __DIR__ . '/../controllers/EtudiantController.php';
        $controller = new EtudiantController();
        $controller->renderDashboard();
        break;

    case '/notes':
        require_once __DIR__ . '/../controllers/EtudiantController.php';
        $controller = new EtudiantController();
        $controller->renderNotes();
        break;


    default:
        http_response_code(404);
        include __DIR__ . '/../views/errors/404.php';
        break;
}
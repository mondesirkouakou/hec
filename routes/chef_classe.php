<?php
// Routes chef de classe

// Récupérer le chemin de la requête
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/hec', '', $path); // Enlever le sous-dossier si nécessaire
$path = str_replace('/chef-classe', '', $path); // Enlever le préfixe /chef-classe

// Gestion des routes chef de classe
switch ($path) {
    case '':
    case '/':
    case '/dashboard':
        // Tableau de bord chef de classe
        require_once __DIR__ . '/../controllers/ChefClasseController.php';
        $controller = new ChefClasseController();
        $controller->dashboard();
        break;
        
    // Gestion des étudiants
    case '/etudiants':
        require_once __DIR__ . '/../controllers/ChefClasseController.php';
        $controller = new ChefClasseController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->ajouterEtudiant();
        } else {
            $controller->listeEtudiants();
        }
        break;

    case '/etudiants/ajouter':
        require_once __DIR__ . '/../controllers/ChefClasseController.php';
        $controller = new ChefClasseController();
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->formAjouterEtudiant();
        }
        break;
        
    case '/etudiants/supprimer':
        require_once __DIR__ . '/../controllers/ChefClasseController.php';
        $controller = new ChefClasseController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->supprimerEtudiant();
        }
        break;
        
    // Gestion des professeurs
    case '/professeurs':
        require_once __DIR__ . '/../controllers/ChefClasseController.php';
        $controller = new ChefClasseController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->ajouterProfesseur();
        } else {
            $controller->listeProfesseurs();
        }
        break;

    case '/professeurs/ajouter':
        require_once __DIR__ . '/../controllers/ChefClasseController.php';
        $controller = new ChefClasseController();
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->formAjouterProfesseur();
        }
        break;
        
    case '/professeurs/supprimer':
        require_once __DIR__ . '/../controllers/ChefClasseController.php';
        $controller = new ChefClasseController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->supprimerProfesseur();
        }
        break;
        
    // Soumission des listes
    case '/soumettre':
        require_once __DIR__ . '/../controllers/ChefClasseController.php';
        $controller = new ChefClasseController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->soumettreListes();
        }
        break;
        
    // Route par défaut
    default:
        http_response_code(404);
        include __DIR__ . '/../views/errors/404.php';
        break;
}
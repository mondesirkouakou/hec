<?php
// Routes d'administration

// Récupérer le chemin de la requête
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/hec', '', $path); // Enlever le sous-dossier si nécessaire
$path = str_replace('/admin', '', $path); // Enlever le préfixe /admin

// Gestion des routes d'administration
switch ($path) {
    case '':
    case '/':
    case '/dashboard':
        // Tableau de bord administrateur
        require_once __DIR__ . '/../controllers/admin/DashboardController.php';
        $controller = new DashboardController();
        $controller->index();
        break;

    // Bulletin d'un étudiant (vue admin)
    case (preg_match('/^\/etudiants\/(\d+)\/bulletin$/', $path) ? true : false):
        require_once __DIR__ . '/../controllers/admin/EtudiantBulletinAdminController.php';
        $controller = new EtudiantBulletinAdminController();

        $parts = explode('/', trim($path, '/'));
        $id = isset($parts[1]) ? (int)$parts[1] : 0;
        if ($id > 0) {
            $controller->bulletin($id);
        } else {
            http_response_code(400);
            echo 'Bad Request: ID étudiant invalide.';
        }
        break;

    // Gestion des notes
    case (preg_match('/^\/notes(\/.*)?$/', $path) ? true : false):
        require_once __DIR__ . '/../controllers/admin/NotesController.php';
        $controller = new NotesController();

        // Extraire les parties de l'URL
        $parts = explode('/', trim($path, '/'));
        $action = $parts[1] ?? 'validation';

        // Appeler la méthode appropriée
        switch ($action) {
            case 'validation':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->valider();
                } else {
                    $controller->validation();
                }
                break;
            case 'saisie':
                $controller->saisie();
                break;
            case 'enregistrer':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->enregistrer();
                } else {
                    header('Location: ' . BASE_URL . 'admin/notes/saisie');
                    exit();
                }
                break;
            default:
                $controller->validation();
        }
        break;
        
    // Gestion des années universitaires
    case (preg_match('/^\/annees-universitaires(\/.*)?$/', $path) ? true : false):
        require_once __DIR__ . '/../controllers/AnneeUniversitaireController.php';
        $controller = new AnneeUniversitaireController();
        
        // Extraire les parties de l'URL
        $parts = explode('/', trim($path, '/'));
        $action = $parts[1] ?? 'index';
        $id = $parts[2] ?? null;
        
        // Appeler la méthode appropriée
        switch ($action) {
            case 'nouvelle':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->store();
                } else {
                    $controller->create();
                }
                break;
                
            case (is_numeric($action) ? true : false):
                $id = $action;
                if (isset($parts[2]) && $parts[2] === 'modifier') {
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->update($id);
                    } else {
                        $controller->edit($id);
                    }
                } elseif (isset($parts[2]) && $parts[2] === 'supprimer' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->delete($id);
                } elseif (isset($parts[2]) && $parts[2] === 'cloturer-et-creer' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->closeAndRollOver($id);
                } else {
                    $controller->show($id);
                }
                break;
                
            default:
                $controller->index();
        }
        break;
    
    // Gestion des chefs de classe
    case (preg_match('/^\/chefs-classe(\/.*)?$/', $path) ? true : false):
        require_once __DIR__ . '/../controllers/ChefClasseAdminController.php';
        $controller = new ChefClasseAdminController();

        $parts = explode('/', trim($path, '/'));
        $action = $parts[1] ?? 'index';

        switch ($action) {
            case 'actions':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->actionsGroupees();
                } else {
                    header('Location: ' . BASE_URL . 'admin/chefs-classe');
                    exit();
                }
                break;
            default:
                $controller->index();
        }
        break;
        
    // Gestion des semestres
    case (preg_match('/^\/semestres(\/.*)?$/', $path) ? true : false):
        require_once __DIR__ . '/../controllers/SemestreController.php';
        $controller = new SemestreController();
        
        // Extraire les parties de l'URL
        $parts = explode('/', trim($path, '/'));
        $action = $parts[1] ?? 'index';
        $id = $parts[2] ?? null;
        
        // Appeler la méthode appropriée
        switch ($action) {
            case 'nouveau':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->store();
                } else {
                    $controller->create();
                }
                break;
                
            case (is_numeric($action) ? true : false):
                $id = $action;
                if (isset($parts[2]) && $parts[2] === 'modifier') {
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->update($id);
                    } else {
                        $controller->edit($id);
                    }
                } elseif (isset($parts[2]) && $parts[2] === 'activer') {
                    $controller->activate($id);
                } elseif (isset($parts[2]) && $parts[2] === 'cloturer') {
                    $controller->close($id);
                } elseif (isset($parts[2]) && $parts[2] === 'supprimer' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->delete($id);
                } else {
                    $controller->show($id);
                }
                break;
                
            default:
                $controller->index();
        }
        break;
        
    // Gestion des classes
    case (preg_match('/^\/classes(\/.*)?$/', $path) ? true : false):
        require_once __DIR__ . '/../controllers/ClasseController.php';
        $controller = new ClasseController();
        
        // Extraire les parties de l'URL
        $parts = explode('/', trim($path, '/'));
        $action_segment = $parts[1] ?? 'index';
        $id_segment = $parts[2] ?? null;

        switch ($action_segment) {
            case 'nouvelle':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->store();
                } else {
                    $controller->create();
                }
                break;

            case 'modifier': // Gère /classes/modifier/{id}
                if ($id_segment && is_numeric($id_segment)) {
                    $id = $id_segment;
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->update($id);
                    } else {
                        $controller->edit($id);
                    }
                } else {
                    http_response_code(400);
                    echo "Bad Request: Missing or invalid class ID for modification.";
                    exit();
                }
                break;

            case 'assign-matiere':
                if ($id_segment && is_numeric($id_segment)) {
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->assignMatiere($id_segment);
                    } else {
                        $controller->assignMatiereForm($id_segment);
                    }
                } else {
                    http_response_code(400);
                    echo "Bad Request: Missing or invalid class ID for subject assignment.";
                    exit();
                }
                break;
            
            case 'valider-toutes-listes':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->validerToutesLesListes();
                } else {
                    header('Location: ' . BASE_URL . 'admin/classes?statut_listes=en_attente');
                    exit();
                }
                break;
                
            case (is_numeric($action_segment) ? true : false): // Gère /classes/{id} ou /classes/{id}/action
                $id = $action_segment;
                $sub_action = $parts[2] ?? null;

                if ($sub_action === 'supprimer' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->delete($id);
                } elseif ($sub_action === 'etudiants') {
                    if (isset($parts[3]) && $parts[3] === 'ajouter' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->addEtudiants($id);
                    } elseif (isset($parts[3]) && is_numeric($parts[3]) && isset($parts[4]) && $parts[4] === 'retirer' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->removeEtudiant($id, $parts[3]);
                    } else {
                        $controller->showEtudiants($id);
                    }
                } elseif ($sub_action === 'matieres') {
                    if (isset($parts[3]) && $parts[3] === 'ajouter' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->addMatieres($id);
                    } elseif (isset($parts[3]) && is_numeric($parts[3]) && isset($parts[4]) && $parts[4] === 'retirer' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->removeMatiere($id, $parts[3]);
                    }
                } elseif ($sub_action === 'professeurs') {
                    if (isset($parts[3]) && $parts[3] === 'ajouter' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->addProfesseurs($id);
                    } elseif (isset($parts[3]) && is_numeric($parts[3]) && isset($parts[4]) && is_numeric($parts[4]) && isset($parts[5]) && $parts[5] === 'retirer' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->removeProfesseurMatiere($id, $parts[3], $parts[4]);
                    } else {
                        $controller->showProfesseurs($id);
                    }
                } elseif ($sub_action === 'designer-chef') {
                    if (isset($parts[3]) && $parts[3] === 'nommer' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->nommerChef($id);
                    } else {
                        $controller->designerChef($id);
                    }
                } elseif ($sub_action === 'valider-listes' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->validerListes($id);
                } else {
                    $controller->show($id);
                }
                break;
                
            default:
                $controller->index();
        }
        break;
        
    // Route par défaut pour l'administration
    default:
        // Vérifier si c'est une requête AJAX
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint non trouvé']);
            exit();
        }
        
        // Sinon, afficher une page d'erreur 404
        http_response_code(404);
        include __DIR__ . '/../views/errors/404.php';
        break;
}

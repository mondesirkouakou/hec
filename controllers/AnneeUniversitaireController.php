<?php
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/AnneeUniversitaire.php';
require_once __DIR__ . '/../classes/Semestre.php';

class AnneeUniversitaireController {
    private $db;
    private $anneeModel;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->anneeModel = new AnneeUniversitaire($this->db);
    }
    
    /**
     * Affiche la liste des années universitaires
     */
    public function index() {
        $annees = $this->anneeModel->getAll();
        $anneeActive = $this->anneeModel->getActiveYear();
        
        include __DIR__ . '/../views/admin/annees/index.php';
    }
    
    /**
     * Affiche le formulaire de création d'une année universitaire
     */
    public function create() {
        include __DIR__ . '/../views/admin/annees/create.php';
    }
    
    /**
     * Enregistre une nouvelle année universitaire
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'annee_debut' => $_POST['annee_debut'] ?? '',
                'annee_fin' => $_POST['annee_fin'] ?? '',
                'est_active' => isset($_POST['est_active']) ? 1 : 0
            ];
            
            // Validation des données
            $errors = $this->validateYearData($data);
            
            if (empty($errors)) {
                // Vérifier si l'année existe déjà
                if ($this->anneeModel->yearExists($data['annee_debut'], $data['annee_fin'])) {
                    $errors[] = "Cette année universitaire existe déjà.";
                } else {
                    // Si c'est la première année, on l'active par défaut
                    $existingYears = $this->anneeModel->getAll();
                    if (empty($existingYears)) {
                        $data['est_active'] = 1;
                    }

                    // Créer l'année
                    $nouvelleAnneeId = $this->anneeModel->create($data);

                    if ($nouvelleAnneeId) {
                        // Activer si demandé
                        if ($data['est_active']) {
                            $this->anneeModel->setActiveYear($nouvelleAnneeId);
                        }

                        // Créer automatiquement les deux semestres pour cette année
                        $semestreModel = new Semestre($this->db);
                        $today = date('Y-m-d');

                        $idS1 = $semestreModel->create([
                            'numero' => 1,
                            'date_debut' => $today,
                            'date_fin' => $today,
                            'annee_universitaire_id' => $nouvelleAnneeId,
                            'est_ouvert' => 1,
                            'est_cloture' => 0,
                        ]);

                        $idS2 = $semestreModel->create([
                            'numero' => 2,
                            'date_debut' => $today,
                            'date_fin' => $today,
                            'annee_universitaire_id' => $nouvelleAnneeId,
                            'est_ouvert' => 0,
                            'est_cloture' => 0,
                        ]);

                        // S'assurer que le semestre 1 est actif
                        if ($idS1) {
                            $semestreModel->setActiveSemestre($idS1);
                        }

                        $_SESSION['success'] = "L'année universitaire a été créée avec succès.";
                        header('Location: ' . BASE_URL . 'admin/annees-universitaires');
                        exit();
                    } else {
                        $errors[] = "Une erreur est survenue lors de la création de l'année universitaire.";
                    }
                }
            }
            
            // Si on arrive ici, il y a eu une erreur
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            header('Location: ' . BASE_URL . 'admin/annees-universitaires/nouvelle');
            exit();
        }
    }
    
    /**
     * Active une année universitaire
     */
    public function activate($id) {
        if ($this->anneeModel->setActiveYear($id)) {
            $_SESSION['success'] = "L'année universitaire a été activée avec succès.";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de l'activation de l'année universitaire.";
        }
        
        header('Location: ' . BASE_URL . 'admin/annees-universitaires');
        exit();
    }

    /**
     * Clôture une année universitaire et crée automatiquement la suivante en reconduisant la structure.
     */
    public function closeAndRollOver($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/annees-universitaires');
            exit();
        }

        $annee = $this->anneeModel->getById($id);
        if (!$annee) {
            $_SESSION['error'] = "Année universitaire introuvable.";
            header('Location: ' . BASE_URL . 'admin/annees-universitaires');
            exit();
        }

        try {
            $this->db->beginTransaction();

            // Clôturer l'année actuelle (désactiver)
            $this->db->query("UPDATE annees_universitaires SET est_active = 0 WHERE id = :id", ['id' => $id]);

            // Créer la nouvelle année (N+1)
            $nouvelleDebut = (int)$annee['annee_fin'];
            $nouvelleFin = $nouvelleDebut + 1;
            
            // Vérifier si elle existe déjà
            $anneeExisteDeja = $this->anneeModel->yearExists($nouvelleDebut, $nouvelleFin);
            
            if ($anneeExisteDeja) {
                // Récupérer l'ID de l'année existante
                $existingYear = $this->db->fetch(
                    "SELECT id FROM annees_universitaires WHERE annee_debut = :debut AND annee_fin = :fin",
                    ['debut' => $nouvelleDebut, 'fin' => $nouvelleFin]
                );
                $nouvelleId = $existingYear ? (int)$existingYear['id'] : null;
            } else {
                $nouvelleId = $this->anneeModel->create([
                    'annee_debut' => $nouvelleDebut,
                    'annee_fin' => $nouvelleFin,
                    'est_active' => 1,
                ]);
            }

            if (!$nouvelleId) {
                throw new Exception("Impossible de créer ou récupérer la nouvelle année universitaire.");
            }

            // Activer la nouvelle année
            $this->anneeModel->setActiveYear($nouvelleId);

            $semestreModel = new Semestre($this->db);
            $nouveauxSemestresIds = [];

            // Vérifier si des semestres existent déjà pour la nouvelle année
            $semestresNouvelleAnnee = $semestreModel->getByAnneeUniversitaire($nouvelleId);
            
            if (empty($semestresNouvelleAnnee)) {
                // Créer les semestres seulement s'ils n'existent pas
                $anciensSemestres = $this->db->fetchAll(
                    "SELECT * FROM semestres WHERE annee_universitaire_id = :annee_id ORDER BY numero",
                    ['annee_id' => $id]
                );

                foreach ($anciensSemestres as $s) {
                    $dataSem = [
                        'numero' => $s['numero'],
                        'date_debut' => $s['date_debut'],
                        'date_fin' => $s['date_fin'],
                        'annee_universitaire_id' => $nouvelleId,
                        'est_ouvert' => ((int)$s['numero'] === 1) ? 1 : 0,
                        'est_cloture' => 0,
                    ];
                    $newSemId = $semestreModel->create($dataSem);
                    $nouveauxSemestresIds[(int)$s['numero']] = (int)$newSemId;
                }

                // Si aucun semestre n'existait avant, on en crée deux par défaut
                if (empty($anciensSemestres)) {
                    $idS1 = $semestreModel->create([
                        'numero' => 1,
                        'date_debut' => date('Y-m-d'),
                        'date_fin' => date('Y-m-d'),
                        'annee_universitaire_id' => $nouvelleId,
                        'est_ouvert' => 1,
                        'est_cloture' => 0,
                    ]);
                    $idS2 = $semestreModel->create([
                        'numero' => 2,
                        'date_debut' => date('Y-m-d'),
                        'date_fin' => date('Y-m-d'),
                        'annee_universitaire_id' => $nouvelleId,
                        'est_ouvert' => 0,
                        'est_cloture' => 0,
                    ]);
                    $nouveauxSemestresIds[1] = (int)$idS1;
                    $nouveauxSemestresIds[2] = (int)$idS2;
                }
            } else {
                // Récupérer les IDs des semestres existants
                foreach ($semestresNouvelleAnnee as $sem) {
                    $nouveauxSemestresIds[(int)$sem['numero']] = (int)$sem['id'];
                }
                // S'assurer que le semestre 1 est ouvert et non clôturé
                if (!empty($nouveauxSemestresIds[1])) {
                    $this->db->query(
                        "UPDATE semestres SET est_ouvert = 1, est_cloture = 0 WHERE id = :id",
                        ['id' => $nouveauxSemestresIds[1]]
                    );
                }
            }

            // Forcer le semestre 1 comme semestre actif de la nouvelle année
            if (!empty($nouveauxSemestresIds[1])) {
                $semestreModel->setActiveSemestre($nouveauxSemestresIds[1]);
            }

            // Reprendre les classes seulement si l'année n'existait pas déjà
            if (!$anneeExisteDeja) {
                $classes = $this->db->fetchAll("SELECT * FROM classes WHERE annee_universitaire_id = :annee_id", ['annee_id' => $id]);
                $mappingClasses = [];
                foreach ($classes as $c) {
                    // Générer un nouveau code unique pour la nouvelle année
                    // On retire l'éventuel suffixe d'année existant et on ajoute le nouveau
                    $baseCode = preg_replace('/_\d{4}$/', '', $c['code']);
                    $newCode = $baseCode . '_' . $nouvelleDebut;
                    
                    // Vérifier si ce code existe déjà, sinon utiliser le code original
                    $codeExists = $this->db->fetch(
                        "SELECT id FROM classes WHERE code = :code",
                        ['code' => $newCode]
                    );
                    if ($codeExists) {
                        // Si le code avec suffixe existe, ajouter un compteur
                        $counter = 1;
                        while ($codeExists) {
                            $newCode = $baseCode . '_' . $nouvelleDebut . '_' . $counter;
                            $codeExists = $this->db->fetch(
                                "SELECT id FROM classes WHERE code = :code",
                                ['code' => $newCode]
                            );
                            $counter++;
                        }
                    }
                    
                    $newId = (int)$this->db->insert(
                        "INSERT INTO classes (code, intitule, niveau, annee_universitaire_id, created_at)
                         VALUES (:code, :intitule, :niveau, :annee_id, NOW())",
                        [
                            'code' => $newCode,
                            'intitule' => $c['intitule'],
                            'niveau' => $c['niveau'],
                            'annee_id' => $nouvelleId,
                        ]
                    );
                    $mappingClasses[(int)$c['id']] = $newId;
                }

                // Reprendre les liaisons classe_matiere (sans colonnes de timestamps)
                foreach ($mappingClasses as $oldId => $newId) {
                    $liaisons = $this->db->fetchAll("SELECT * FROM classe_matiere WHERE classe_id = :cid", ['cid' => $oldId]);
                    foreach ($liaisons as $l) {
                        $this->db->insert(
                            "INSERT INTO classe_matiere (classe_id, matiere_id, coefficient, credits)
                             VALUES (:classe_id, :matiere_id, :coefficient, :credits)",
                            [
                                'classe_id' => $newId,
                                'matiere_id' => $l['matiere_id'],
                                'coefficient' => $l['coefficient'],
                                'credits' => $l['credits'],
                            ]
                        );
                    }
                }
            }

            // Supprimer les liens de chefs de classe (les comptes chefs pourront être recréés/activés pour la nouvelle année)
            $this->db->query(
                "DELETE FROM chef_classe WHERE classe_id IN (SELECT id FROM classes WHERE annee_universitaire_id = :annee_id)",
                ['annee_id' => $id]
            );

            $this->db->commit();

            // Mettre à jour la sélection dans la session admin pour pointer sur la nouvelle année / nouveau semestre 1
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['admin_annee_id'] = $nouvelleId;
            if (!empty($nouveauxSemestresIds[1])) {
                $_SESSION['admin_semestre_id'] = (int)$nouveauxSemestresIds[1];
            } else {
                unset($_SESSION['admin_semestre_id']);
            }

            $_SESSION['success'] = "L'année universitaire a été clôturée et la nouvelle année a été créée avec succès.";
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Erreur closeAndRollOver: ' . $e->getMessage());
            $_SESSION['error'] = "Erreur lors de la clôture de l'année universitaire : " . $e->getMessage();
        }

        header('Location: ' . BASE_URL . 'admin/dashboard');
        exit();
    }
    
    /**
     * Affiche le formulaire de modification d'une année universitaire
     */
    public function edit($id) {
        $annee = $this->anneeModel->getById($id);
        
        if (!$annee) {
            $_SESSION['error'] = "Année universitaire introuvable.";
            header('Location: ' . BASE_URL . 'admin/annees-universitaires');
            exit();
        }
        
        include __DIR__ . '/../views/admin/annees/edit.php';
    }
    
    /**
     * Met à jour une année universitaire existante
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id' => $id,
                'annee_debut' => $_POST['annee_debut'] ?? '',
                'annee_fin' => $_POST['annee_fin'] ?? '',
                'est_active' => isset($_POST['est_active']) ? 1 : 0
            ];
            
            // Validation des données
            $errors = $this->validateYearData($data);
            
            if (empty($errors)) {
                // Vérifier si l'année existe déjà (hors l'année courante)
                if ($this->anneeModel->yearExists($data['annee_debut'], $data['annee_fin'], $id)) {
                    $errors[] = "Cette année universitaire existe déjà.";
                } else {
                    $success = $this->anneeModel->update($data);
                    
                    if ($success && $data['est_active']) {
                        $this->anneeModel->setActiveYear($id);
                    }
                    
                    if ($success) {
                        $_SESSION['success'] = "L'année universitaire a été mise à jour avec succès.";
                        header('Location: ' . BASE_URL . 'admin/annees-universitaires');
                        exit();
                    } else {
                        $errors[] = "Une erreur est survenue lors de la mise à jour de l'année universitaire.";
                    }
                }
            }
            
            // Si on arrive ici, il y a eu une erreur
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            header('Location: ' . BASE_URL . 'admin/annees-universitaires/' . $id . '/modifier');
            exit();
        }
    }
    
    /**
     * Supprime une année universitaire
     */
    public function delete($id) {
        $success = $this->anneeModel->delete($id);
        
        if ($success) {
            $_SESSION['success'] = "L'année universitaire a été supprimée avec succès.";
        } else {
            $_SESSION['error'] = "Impossible de supprimer cette année universitaire car elle est liée à des classes existantes.";
        }
        
        header('Location: ' . BASE_URL . 'admin/annees-universitaires');
        exit();
    }
    
    /**
     * Valide les données d'une année universitaire
     */
    private function validateYearData($data) {
        $errors = [];
        
        if (empty($data['annee_debut'])) {
            $errors[] = "L'année de début est obligatoire.";
        } elseif (!is_numeric($data['annee_debut']) || strlen($data['annee_debut']) != 4) {
            $errors[] = "L'année de début doit être une année valide (4 chiffres).";
        }
        
        if (empty($data['annee_fin'])) {
            $errors[] = "L'année de fin est obligatoire.";
        } elseif (!is_numeric($data['annee_fin']) || strlen($data['annee_fin']) != 4) {
            $errors[] = "L'année de fin doit être une année valide (4 chiffres).";
        }
        
        if (!empty($data['annee_debut']) && !empty($data['annee_fin'])) {
            if ($data['annee_fin'] != $data['annee_debut'] + 1) {
                $errors[] = "L'année de fin doit être égale à l'année de début + 1 (ex: 2023-2024).";
            }
        }
        
        return $errors;
    }
}
?>

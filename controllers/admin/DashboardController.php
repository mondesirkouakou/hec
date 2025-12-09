<?php
class DashboardController {
    private $db;
    private $anneeModel;
    private $semestreModel;
    private $classeModel;
    private $etudiantModel;
    private $professeurModel;
    private $matiereModel;
    
    public function __construct() {
        // Initialiser la connexion à la base de données
        $this->db = Database::getInstance();
        
        // Charger les modèles nécessaires
        require_once __DIR__ . '/../../classes/AnneeUniversitaire.php';
        require_once __DIR__ . '/../../classes/Semestre.php';
        require_once __DIR__ . '/../../classes/Classe.php';
        require_once __DIR__ . '/../../classes/Etudiant.php';
        require_once __DIR__ . '/../../classes/Professeur.php';
        require_once __DIR__ . '/../../classes/Matiere.php';
        
        $this->anneeModel = new AnneeUniversitaire($this->db);
        $this->semestreModel = new Semestre($this->db);
        $this->classeModel = new Classe($this->db);
        $this->etudiantModel = new Etudiant($this->db);
        $this->professeurModel = new Professeur($this->db);
        $this->matiereModel = new Matiere($this->db);
    }
    
    /**
     * Affiche le tableau de bord de l'administrateur
     */
    public function index() {
        // Vérifier les autorisations
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = "Accès non autorisé.";
            header('Location: ' . BASE_URL . 'login');
            exit();
        }
        
        // Récupérer les statistiques
        $stats = [
            'annees' => $this->anneeModel->count(),
            'classes' => $this->classeModel->count(),
            'etudiants' => $this->etudiantModel->count(),
            'professeurs' => $this->professeurModel->count(),
            'matieres' => $this->matiereModel->count()
        ];
        
        // Récupérer les années / semestres et déterminer les sélections courantes
        $annees = $this->anneeModel->getAll();
        // Ordonner les années existantes de la plus ancienne à la plus récente
        if (is_array($annees)) {
            usort($annees, function($a, $b) {
                $ad = (int)($a['annee_debut'] ?? 0);
                $bd = (int)($b['annee_debut'] ?? 0);
                return $ad <=> $bd;
            });
        }

        // Construire une timeline continue d'années (même celles non présentes en base)
        $anneeTimeline = [];
        if (!empty($annees)) {
            $minDebut = null;
            $maxFin = null;
            $indexParDebut = [];

            foreach ($annees as $a) {
                $debut = (int)($a['annee_debut'] ?? 0);
                $fin = (int)($a['annee_fin'] ?? 0);
                if ($minDebut === null || $debut < $minDebut) {
                    $minDebut = $debut;
                }
                if ($maxFin === null || $fin > $maxFin) {
                    $maxFin = $fin;
                }
                // Indexer par année de début (on suppose une année par couple début/fin)
                $indexParDebut[$debut] = $a;
            }

            // On affiche toutes les années jusqu'à 2098-2099
            $upperDebut = 2098;

            for ($y = $minDebut; $y <= $upperDebut; $y++) {
                $exists = isset($indexParDebut[$y]);
                $record = $exists ? $indexParDebut[$y] : null;
                $anneeTimeline[] = [
                    'annee_debut' => $y,
                    'annee_fin'   => $y + 1,
                    'exists'      => $exists,
                    'record'      => $record,
                ];
            }
        }

        $anneeActive = $this->anneeModel->getActiveYear();

        // Année sélectionnée : GET > session > année active
        $selectedAnneeId = isset($_GET['annee_id']) ? (int)$_GET['annee_id'] : null;
        if ($selectedAnneeId) {
            $_SESSION['admin_annee_id'] = $selectedAnneeId;
        } elseif (isset($_SESSION['admin_annee_id'])) {
            $selectedAnneeId = (int)$_SESSION['admin_annee_id'];
        } elseif ($anneeActive) {
            $selectedAnneeId = (int)$anneeActive['id'];
        }

        $semestresAnnee = [];
        $selectedAnnee = null;
        if ($selectedAnneeId) {
            $selectedAnnee = $this->anneeModel->getById($selectedAnneeId);
            $semestresAnnee = $this->semestreModel->getByAnneeUniversitaire($selectedAnneeId);

            // Si aucune donnée de semestre pour cette année (anciennes années), on crée automatiquement S1 et S2
            if (empty($semestresAnnee)) {
                $today = date('Y-m-d');

                $idS1 = $this->semestreModel->create([
                    'numero' => 1,
                    'date_debut' => $today,
                    'date_fin' => $today,
                    'annee_universitaire_id' => $selectedAnneeId,
                    'est_ouvert' => 1,
                    'est_cloture' => 0,
                ]);

                $idS2 = $this->semestreModel->create([
                    'numero' => 2,
                    'date_debut' => $today,
                    'date_fin' => $today,
                    'annee_universitaire_id' => $selectedAnneeId,
                    'est_ouvert' => 0,
                    'est_cloture' => 0,
                ]);

                if ($idS1) {
                    $this->semestreModel->setActiveSemestre($idS1);
                }

                // Recharger les semestres de cette année après création
                $semestresAnnee = $this->semestreModel->getByAnneeUniversitaire($selectedAnneeId);
            }
        }

        $semestreActif = $this->semestreModel->getActiveSemestre();

        // Vérifier si tous les semestres "pédagogiques" (1 et 2) de l'année sélectionnée sont clôturés
        // ET que l'année sélectionnée est bien l'année active (pour proposer la bascule)
        $tousSemestresClotures = false;
        $isSelectedAnneeActive = $anneeActive && $selectedAnneeId && ((int)$anneeActive['id'] === (int)$selectedAnneeId);

        // DEBUG: log pour diagnostic
        error_log("[Dashboard] selectedAnneeId=$selectedAnneeId, anneeActive.id=" . ($anneeActive['id'] ?? 'null') . ", isSelectedAnneeActive=" . ($isSelectedAnneeActive ? '1' : '0'));
        error_log("[Dashboard] semestresAnnee count=" . count($semestresAnnee));
        foreach ($semestresAnnee as $idx => $sem) {
            error_log("[Dashboard] Semestre $idx: numero={$sem['numero']}, est_ouvert={$sem['est_ouvert']}, est_cloture={$sem['est_cloture']}");
        }

        if (!empty($semestresAnnee) && $isSelectedAnneeActive) {
            // Ne considérer que les semestres 1 et 2 pour déterminer la clôture d'année
            $semestresPedagogiques = array_filter($semestresAnnee, function($s) {
                return in_array((int)($s['numero'] ?? 0), [1, 2], true);
            });

            error_log("[Dashboard] semestresPedagogiques count=" . count($semestresPedagogiques));

            if (count($semestresPedagogiques) >= 2) {
                $ouverts = array_filter($semestresPedagogiques, function($s) { return ($s['est_ouvert'] ?? 0) == 1; });
                $nonClotures = array_filter($semestresPedagogiques, function($s) { return ($s['est_cloture'] ?? 0) == 0; });
                error_log("[Dashboard] ouverts=" . count($ouverts) . ", nonClotures=" . count($nonClotures));
                if (empty($ouverts) && empty($nonClotures)) {
                    $tousSemestresClotures = true;
                    error_log("[Dashboard] => tousSemestresClotures = TRUE");
                }
            }
        }

        error_log("[Dashboard] Final tousSemestresClotures=" . ($tousSemestresClotures ? '1' : '0'));

        // Semestre sélectionné : GET > session > semestre actif
        $selectedSemestreId = isset($_GET['semestre_id']) ? (int)$_GET['semestre_id'] : null;
        if ($selectedSemestreId) {
            $_SESSION['admin_semestre_id'] = $selectedSemestreId;
        } elseif (isset($_SESSION['admin_semestre_id'])) {
            $selectedSemestreId = (int)$_SESSION['admin_semestre_id'];
        } elseif ($semestreActif && $semestreActif['annee_universitaire_id'] == $selectedAnneeId) {
            $selectedSemestreId = (int)$semestreActif['id'];
        }

        // Statistiques agrégées par année universitaire sélectionnée
        $statsYear = [
            'classes_actives'      => 0,
            'etudiants_inscrits'   => 0,
            'enseignants'          => 0,
        ];

        if ($selectedAnneeId) {
            // Classes actives : classes de l'année sélectionnée dont la liste est validée
            $statsYear['classes_actives'] = (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM classes
                 WHERE annee_universitaire_id = :annee_id
                   AND (statut_listes = 'validee' OR statut_listes IS NULL OR statut_listes = '')",
                ['annee_id' => $selectedAnneeId]
            );

            // Étudiants inscrits : inscriptions actives sur l'année sélectionnée
            $statsYear['etudiants_inscrits'] = (int)$this->db->fetchColumn(
                "SELECT COUNT(DISTINCT etudiant_id) FROM inscriptions
                 WHERE annee_universitaire_id = :annee_id
                   AND (statut IS NULL OR statut = 'actif')",
                ['annee_id' => $selectedAnneeId]
            );

            // Enseignants : professeurs affectés au moins une fois sur cette année
            $statsYear['enseignants'] = (int)$this->db->fetchColumn(
                "SELECT COUNT(DISTINCT professeur_id) FROM affectation_professeur
                 WHERE annee_universitaire_id = :annee_id",
                ['annee_id' => $selectedAnneeId]
            );
        } else {
            // Fallback : statistiques globales si aucune année n'est sélectionnée
            $statsYear['classes_actives'] = (int)$this->classeModel->count();
            $statsYear['etudiants_inscrits'] = (int)$this->etudiantModel->count();
            $statsYear['enseignants'] = (int)$this->professeurModel->count();
        }

        // Récupérer les classes de l'année sélectionnée pour la section "Liste des classes" du dashboard
        $classesDashboard = [];
        if ($selectedAnneeId) {
            $classesByYear = $this->classeModel->getClassesByAnneeUniversitaire($selectedAnneeId);
            $classesDashboard = is_array($classesByYear) ? array_slice($classesByYear, 0, 5) : [];
        }

        $classesEnAttente = $this->classeModel->getClassesEnAttenteValidation();
        $nbListesEnAttente = is_array($classesEnAttente) ? count($classesEnAttente) : 0;
        
        // Inclure la vue du tableau de bord
        include __DIR__ . '/../../views/admin/dashboard.php';
    }
}

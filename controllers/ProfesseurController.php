<?php
require_once __DIR__ . '/../classes/Professeur.php';

class ProfesseurController {
    private $professeurModel;
    private $professeur;
    
    public function __construct() {
        $this->professeurModel = new Professeur();
        $this->checkAccess();
    }
    
    /**
     * Vérifie que l'utilisateur est un professeur
     */
    private function checkAccess() {
        if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 3) {
            header('HTTP/1.0 403 Forbidden');
            echo 'Accès refusé';
            exit();
        }
        
        // Charger les informations du professeur
        $this->professeur = $this->professeurModel->getByUserId($_SESSION['user_id']);
        
        if (!$this->professeur) {
            header('HTTP/1.0 403 Forbidden');
            echo 'Profil professeur non trouvé';
            exit();
        }
    }
    
    /**
     * Affiche le tableau de bord du professeur
     */
    public function dashboard() {
        $matieres = $this->professeurModel->getMatieresEnseignees($this->professeur['id']);
        $classes = $this->professeurModel->getClassesAssociees($this->professeur['id']);
        
        return [
            'professeur' => $this->professeur,
            'matieres' => $matieres,
            'classes' => $classes
        ];
    }
    
    /**
     * Récupère les classes pour une matière donnée
     */
    public function getClassesPourMatiere($matiereId) {
        return $this->professeurModel->getClassesPourMatiere($this->professeur['id'], $matiereId);
    }
    
    /**
     * Récupère les étudiants d'une classe pour une matière donnée,
     * en filtrant leurs notes sur le semestre actuellement ouvert.
     */
    public function getEtudiantsPourNote($classeId, $matiereId) {
        $db = Database::getInstance();
        $semestreActif = $db->fetch("SELECT id FROM semestres WHERE est_ouvert = 1 LIMIT 1");
        $semestreId = $semestreActif ? (int)$semestreActif['id'] : null;

        return $this->professeurModel->getEtudiantsPourNote($classeId, $matiereId, $semestreId);
    }
    
    /**
     * Enregistre les notes des étudiants
     */
    public function enregistrerNotes($notesData) {
        return $this->professeurModel->enregistrerNotes($this->professeur['id'], $notesData);
    }
    
    /**
     * Récupère l'historique des notes saisies
     */
    public function getHistoriqueNotes($matiereId = null, $classeId = null) {
        $params = ['professeur_id' => $this->professeur['id']];
        $where = [];
        
        if ($matiereId) {
            $where[] = 'n.matiere_id = :matiere_id';
            $params['matiere_id'] = $matiereId;
        }
        
        if ($classeId) {
            $where[] = 'e.classe_id = :classe_id';
            $params['classe_id'] = $classeId;
        }
        
        $whereClause = !empty($where) ? 'AND ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT n.*, m.nom as matiere_nom, c.intitule as classe_nom,
                       CONCAT(e.nom, ' ', e.prenom) as etudiant_nom
                FROM notes n
                JOIN matieres m ON n.matiere_id = m.id
                JOIN etudiants etu ON n.etudiant_id = etu.id
                JOIN inscriptions i ON etu.id = i.etudiant_id
                JOIN classes c ON i.classe_id = c.id
                WHERE n.professeur_id = :professeur_id
                $whereClause
                ORDER BY n.date_modification DESC, n.date_creation DESC";
        
        $db = Database::getInstance();
        return $db->fetchAll($sql, $params);
    }
    
    /**
     * Récupère les statistiques des notes pour une matière
     */
    public function getStatistiquesNotes($matiereId, $classeId = null) {
        $params = [
            'professeur_id' => $this->professeur['id'],
            'matiere_id' => $matiereId
        ];
        
        $whereClause = '';
        if ($classeId) {
            $whereClause = 'AND i.classe_id = :classe_id';
            $params['classe_id'] = $classeId;
        }
        
        $sql = "SELECT 
                    COUNT(*) as total_etudiants,
                    AVG(n.note) as moyenne,
                    MIN(n.note) as note_min,
                    MAX(n.note) as note_max,
                    SUM(CASE WHEN n.note >= 10 THEN 1 ELSE 0 END) as admis,
                    SUM(CASE WHEN n.note < 10 THEN 1 ELSE 0 END) as ajournes
                FROM notes n
                JOIN etudiants e ON n.etudiant_id = e.id
                JOIN inscriptions i ON e.id = i.etudiant_id
                WHERE n.matiere_id = :matiere_id
                AND n.professeur_id = :professeur_id
                $whereClause";
        
        $db = Database::getInstance();
        return $db->fetch($sql, $params);
    }
    
    /**
     * Exporte les notes au format CSV
     */
    public function exporterNotesCSV($matiereId, $classeId) {
        // Utilise la même logique que l'affichage : notes du semestre actuellement ouvert
        $etudiants = $this->getEtudiantsPourNote($classeId, $matiereId);
        $db = Database::getInstance();
        $matiere = $db->fetch("SELECT * FROM matieres WHERE id = :id", ['id' => $matiereId]);
        $classe = $db->fetch("SELECT * FROM classes WHERE id = :id", ['id' => $classeId]);
        
        // En-têtes HTTP pour forcer le téléchargement
        header('Content-Type: text/csv; charset=utf-8');
        $matiereCode = $matiere['code'] ?? ($matiere['intitule'] ?? 'matiere');
        $classeCode = $classe['code'] ?? ($classe['intitule'] ?? 'classe');
        header('Content-Disposition: attachment; filename=notes_' . $matiereCode . '_' . $classeCode . '.csv');
        
        // Création du fichier de sortie
        $output = fopen('php://output', 'w');
        
        // En-têtes du CSV
        fputcsv($output, [
            'Matricule', 'Nom', 'Prénom', 'Note', 'Appréciation', 'Statut'
        ]);
        
        // Données
        foreach ($etudiants as $etudiant) {
            fputcsv($output, [
                $etudiant['matricule'],
                $etudiant['nom'],
                $etudiant['prenom'],
                $etudiant['note'] ?? '',
                $etudiant['appreciation'] ?? '',
                $etudiant['statut'] ?? 'Non noté'
            ]);
        }
        
        fclose($output);
        exit();
    }

    public function listeClasse($classeId) {
        $db = Database::getInstance();
        $associe = $db->fetchColumn(
            "SELECT COUNT(*) FROM affectation_professeur WHERE professeur_id = :pid AND classe_id = :cid",
            ['pid' => $this->professeur['id'], 'cid' => $classeId]
        );
        if (!$associe) {
            header('HTTP/1.0 403 Forbidden');
            echo 'Accès refusé à cette classe';
            exit();
        }

        $classe = $db->fetch("SELECT * FROM classes WHERE id = :id", ['id' => $classeId]);
        $etudiants = $db->fetchAll(
            "SELECT e.*, u.email, u.is_active, CONCAT(e.nom, ' ', e.prenom) as nom_complet
             FROM etudiants e
             JOIN users u ON e.user_id = u.id
             JOIN inscriptions i ON e.id = i.etudiant_id
             WHERE i.classe_id = :classe_id
             ORDER BY e.nom, e.prenom",
            ['classe_id' => $classeId]
        );

        return [
            'classe' => $classe,
            'etudiants' => $etudiants
        ];
    }

    public function notesIndex() {
        $matieres = $this->professeurModel->getMatieresEnseignees($this->professeur['id']);
        $classesParMatiere = [];
        foreach ($matieres as $m) {
            $classesParMatiere[$m['id']] = $this->professeurModel->getClassesPourMatiere($this->professeur['id'], $m['id']);
        }
        return [
            'matieres' => $matieres,
            'classesParMatiere' => $classesParMatiere
        ];
    }

    public function notesSaisie($classeId, $matiereId) {
        $db = Database::getInstance();
        $aff = $db->fetchColumn(
            "SELECT COUNT(*) FROM affectation_professeur WHERE professeur_id = :pid AND classe_id = :cid AND matiere_id = :mid",
            ['pid' => $this->professeur['id'], 'cid' => $classeId, 'mid' => $matiereId]
        );
        if (!$aff) {
            header('HTTP/1.0 403 Forbidden');
            echo 'Vous n\'êtes pas affecté à cette classe/matière';
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Déterminer le semestre actuellement ouvert pour associer les notes
            $semestreActif = $db->fetch("SELECT id FROM semestres WHERE est_ouvert = 1 LIMIT 1");
            if (!$semestreActif) {
                $_SESSION['error'] = "Aucun semestre ouvert, impossible d'enregistrer les notes.";
                header('Location: ' . BASE_URL . 'professeur/notes/' . (int)$classeId . '/' . (int)$matiereId);
                exit();
            }
            $semestreId = (int)$semestreActif['id'];

            $notesData = [];
            $postedAvg = $_POST['notes'] ?? [];
            $appreciations = $_POST['appreciations'] ?? [];
            $postNotes = [
                1 => $_POST['note1'] ?? [],
                2 => $_POST['note2'] ?? [],
                3 => $_POST['note3'] ?? [],
                4 => $_POST['note4'] ?? [],
                5 => $_POST['note5'] ?? [],
            ];

            // Rassembler la liste des étudiants présents dans au moins un tableau
            $idsAssoc = [];
            foreach ($postNotes as $arr) {
                if (is_array($arr)) {
                    foreach ($arr as $sid => $_) { $idsAssoc[$sid] = true; }
                }
            }
            if (is_array($postedAvg)) { foreach ($postedAvg as $sid => $_) { $idsAssoc[$sid] = true; } }
            if (is_array($appreciations)) { foreach ($appreciations as $sid => $_) { $idsAssoc[$sid] = true; } }

            foreach (array_keys($idsAssoc) as $etudiantId) {
                $vals = [];
                for ($i = 1; $i <= 5; $i++) {
                    $v = $postNotes[$i][$etudiantId] ?? null;
                    if (is_string($v)) { $v = trim($v); }
                    if ($v === '' || $v === null || !is_numeric($v)) { $vals[$i] = null; continue; }
                    $f = (float)$v;
                    if ($f < 0 || $f > 20) { $vals[$i] = null; continue; }
                    $vals[$i] = $f;
                }

                // Calcul de la moyenne sur les valeurs valides
                $valids = array_values(array_filter($vals, function ($x) { return $x !== null; }));
                $avg = null;
                if (count($valids) > 0) {
                    $avg = array_sum($valids) / count($valids);
                } else {
                    $p = $postedAvg[$etudiantId] ?? null;
                    if (is_string($p)) { $p = trim($p); }
                    if ($p !== '' && $p !== null && is_numeric($p)) {
                        $pf = (float)$p;
                        if ($pf >= 0 && $pf <= 20) { $avg = $pf; }
                    }
                }
                if ($avg === null) { continue; }

                $notesData[] = [
                    'etudiant_id' => (int)$etudiantId,
                    'matiere_id' => (int)$matiereId,
                    'classe_id' => (int)$classeId,
                    'semestre_id' => $semestreId,
                    'note' => round($avg, 2),
                    'note1' => $vals[1],
                    'note2' => $vals[2],
                    'note3' => $vals[3],
                    'note4' => $vals[4],
                    'note5' => $vals[5],
                    'appreciation' => $appreciations[$etudiantId] ?? null
                ];
            }

            if (empty($notesData)) {
                $_SESSION["error"] = 'Aucune note valide à enregistrer.';
                header('Location: ' . BASE_URL . 'professeur/notes/' . (int)$classeId . '/' . (int)$matiereId);
                exit();
            }

            $ok = $this->professeurModel->enregistrerNotes($this->professeur['id'], $notesData);
            if ($ok) {
                $_SESSION['success'] = 'Notes enregistrées';
                unset($_SESSION['error']);
            } else {
                if (empty($_SESSION['error'])) {
                    $_SESSION['error'] = 'Erreur lors de l\'enregistrement des notes';
                }
            }
            header('Location: ' . BASE_URL . 'professeur/notes/' . (int)$classeId . '/' . (int)$matiereId);
            exit();
        }

        // Récupérer les étudiants avec leurs notes limitées au semestre actuellement ouvert
        $etudiantsBruts = $this->getEtudiantsPourNote($classeId, $matiereId);

        // Il peut exister plusieurs lignes de notes pour un même étudiant / matière / semestre
        // (anciennes saisies, corrections, etc.). Pour la saisie, on ne conserve qu'une seule
        // ligne par étudiant : celle qui contient le plus d'informations de notes de classe.
        $parEtudiant = [];
        foreach ($etudiantsBruts as $row) {
            $eid = isset($row['id']) ? (int)$row['id'] : 0;
            if ($eid === 0) {
                continue;
            }

            // Compter le nombre de notes partielles remplies
            $nbPartielles = 0;
            for ($i = 1; $i <= 5; $i++) {
                $key = 'note' . $i;
                if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
                    $nbPartielles++;
                }
            }
            $hasMoyenne = isset($row['note']) && $row['note'] !== null && $row['note'] !== '';

            // Score pour comparer deux lignes du même étudiant
            $score = $nbPartielles;
            if ($hasMoyenne) {
                $score += 10;
            }

            if (!isset($parEtudiant[$eid]) || $score > $parEtudiant[$eid]['score']) {
                $parEtudiant[$eid] = [
                    'row' => $row,
                    'score' => $score,
                ];
            }
        }

        // Extraire les lignes retenues et les trier par nom / prénom pour un affichage propre
        $etudiants = array_map(function($item) { return $item['row']; }, $parEtudiant);
        usort($etudiants, function($a, $b) {
            $na = $a['nom'] ?? '';
            $nb = $b['nom'] ?? '';
            $cmp = strcmp($na, $nb);
            if ($cmp !== 0) { return $cmp; }
            $pa = $a['prenom'] ?? '';
            $pb = $b['prenom'] ?? '';
            return strcmp($pa, $pb);
        });

        $classe = $db->fetch("SELECT * FROM classes WHERE id = :id", ['id' => $classeId]);
        $matiere = $db->fetch("SELECT * FROM matieres WHERE id = :id", ['id' => $matiereId]);
        return [
            'classe' => $classe,
            'matiere' => $matiere,
            'etudiants' => $etudiants
        ];
    }
}

<?php
require_once __DIR__ . '/../classes/Etudiant.php';

class EtudiantController {
    private $etudiant;
    
    public function __construct() {
        $this->etudiant = new Etudiant();
        $this->checkAccess();
    }
    
    /**
     * Vérifie que l'utilisateur est un étudiant
     */
    private function checkAccess() {
        if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 4) {
            header('HTTP/1.0 403 Forbidden');
            echo 'Accès refusé';
            exit();
        }
        
        // Charger les informations de l'étudiant
        $this->etudiant = $this->etudiant->getByUserId($_SESSION['user_id']);
        
        if (!$this->etudiant) {
            header('HTTP/1.0 403 Forbidden');
            echo 'Profil étudiant non trouvé';
            exit();
        }

        $dn = trim(($this->etudiant['prenom'] ?? '') . ' ' . ($this->etudiant['nom'] ?? ''));
        if (!empty($dn)) {
            $_SESSION['display_name'] = $dn;
        }
    }
    
    /**
     * Affiche le tableau de bord de l'étudiant
     */
    public function dashboardData() {
        $notes = $this->getNotes();
        $classe = $this->getClasse();
        $moyennes = $this->calculerMoyennes();
        $emploi = $this->getEmploiDuTemps();
        $prochain_cours = null;
        if (!empty($emploi)) {
            foreach ($emploi as $cours) {
                if (date('Y-m-d') === (isset($cours['date']) ? $cours['date'] : date('Y-m-d')) ) {
                    $prochain_cours = $cours;
                    break;
                }
            }
        }
        $dernieres_notes = [];
        foreach ($notes as $n) {
            $dernieres_notes[] = [
                'matiere_nom' => $n['matiere_nom'] ?? '',
                'valeur' => $n['note'] ?? 0,
                'coefficient' => $n['coefficient'] ?? 1,
                'type_evaluation' => $n['type'] ?? 'Contrôle',
                'date_evaluation' => $n['date_saisie'] ?? date('Y-m-d')
            ];
        }
        $documents_recents = [];
        $evenements = [];
        $stats = [
            'moyenne_generale' => count($notes) ? array_sum(array_column($notes, 'note'))/count($notes) : 0,
            'nb_matieres' => count($notes),
            'evolution_moyenne' => null
        ];
        // Regrouper les notes par matière pour l'affichage "Mes notes de classe"
        $notes_par_matiere = [];
        foreach ($notes as $n) {
            $mid = isset($n['matiere_id']) ? (int)$n['matiere_id'] : 0;
            if ($mid === 0) {
                continue;
            }
            // On conserve la dernière ligne rencontrée pour chaque matière (dernier semestre saisi)
            $notes_par_matiere[$mid] = [
                'id' => $mid,
                'nom' => $n['matiere_nom'] ?? '',
                'note1' => isset($n['note1']) ? (float)$n['note1'] : null,
                'note2' => isset($n['note2']) ? (float)$n['note2'] : null,
                'note3' => isset($n['note3']) ? (float)$n['note3'] : null,
                'note4' => isset($n['note4']) ? (float)$n['note4'] : null,
                'note5' => isset($n['note5']) ? (float)$n['note5'] : null,
                'moyenne' => isset($n['note']) ? (float)$n['note'] : null,
            ];
        }
        $notes_par_matiere = array_values($notes_par_matiere);
        $etudiant = $this->etudiant;
        return compact('notes','classe','moyennes','emploi','prochain_cours','dernieres_notes','documents_recents','evenements','stats','notes_par_matiere','etudiant');
    }

    public function renderDashboard() {
        $data = $this->dashboardData();
        extract($data);
        include __DIR__ . '/../views/etudiant/dashboard.php';
    }
    
    /**
     * Récupère les notes de l'étudiant
     */
    public function getNotes($semestreId = null) {
        $params = ['etudiant_id' => $this->etudiant['id']];
        $whereClause = '';
        
        if ($semestreId) {
            $whereClause = 'AND n.semestre_id = :semestre_id';
            $params['semestre_id'] = $semestreId;
        }
        
        $sql = "SELECT n.*, m.intitule AS matiere_nom, m.credits AS coefficient,
                       s.numero AS semestre_numero
                FROM notes n
                JOIN matieres m ON n.matiere_id = m.id
                JOIN semestres s ON n.semestre_id = s.id
                WHERE n.etudiant_id = :etudiant_id
                $whereClause
                ORDER BY s.numero, m.intitule";
        
        $db = Database::getInstance();
        return $db->fetchAll($sql, $params);
    }

    public function notesData() {
        $db = Database::getInstance();
        $filters = [
            'semestre_id' => isset($_GET['semestre_id']) && $_GET['semestre_id'] !== '' ? (int)$_GET['semestre_id'] : null,
            'matiere_id' => isset($_GET['matiere_id']) && $_GET['matiere_id'] !== '' ? (int)$_GET['matiere_id'] : null,
        ];
        $notes = $this->getNotes($filters['semestre_id']);
        if ($filters['matiere_id']) {
            $notes = array_values(array_filter($notes, function($n) use ($filters) { return (int)$n['matiere_id'] === $filters['matiere_id']; }));
        }
        $semestres = $db->fetchAll("SELECT id, CONCAT('Semestre ', numero) AS nom FROM semestres ORDER BY numero");
        $matieres = $db->fetchAll("SELECT id, intitule AS nom FROM matieres ORDER BY intitule");
        $stats = [
            'moyenne_generale' => count($notes) ? array_sum(array_map(function($n){return (float)$n['note'];}, $notes))/count($notes) : 0,
            'evolution_moyenne' => null,
            'meilleure_note' => ['moyenne' => 0, 'matiere_nom' => null],
            'rang' => null,
            'effectif_classe' => null,
        ];
        $group = [];
        foreach ($notes as $n) {
            $mid = (int)$n['matiere_id'];
            if (!isset($group[$mid])) {
                $group[$mid] = ['id'=>$mid,'nom'=>$n['matiere_nom'],'evaluations'=>[]];
            }
            $group[$mid]['evaluations'][] = [
                'type' => $n['type'] ?? 'Évaluation',
                'date_evaluation' => $n['date_saisie'] ?? date('Y-m-d'),
                'valeur' => (float)($n['note'] ?? 0),
                'coefficient' => (float)($n['coefficient'] ?? 1),
                'appreciation' => $n['appreciation'] ?? ''
            ];
        }
        $notes_par_matiere = [];
        foreach ($group as $g) {
            $moy = 0;
            if (count($g['evaluations'])) {
                $moy = array_sum(array_map(function($e){return (float)$e['valeur'];}, $g['evaluations']))/count($g['evaluations']);
            }
            if ($moy > ($stats['meilleure_note']['moyenne'] ?? 0)) {
                $stats['meilleure_note'] = ['moyenne'=>$moy,'matiere_nom'=>$g['nom']];
            }
            $notes_par_matiere[] = [
                'id' => $g['id'],
                'nom' => $g['nom'],
                'moyenne' => $moy,
                'evaluations' => $g['evaluations'],
                'moyenne_classe' => null,
                'rang' => null,
                'effectif' => null,
                'evolution' => null,
            ];
        }
        $bySem = [];
        foreach ($notes as $n) {
            $sn = $n['semestre_numero'] ?? null;
            if ($sn === null) { continue; }
            if (!isset($bySem[$sn])) { $bySem[$sn] = []; }
            $bySem[$sn][] = (float)($n['note'] ?? 0);
        }
        ksort($bySem);
        $graphique_evolution = [];
        foreach ($bySem as $numero => $arr) {
            $moy = count($arr) ? array_sum($arr)/count($arr) : 0;
            $graphique_evolution[] = [
                'periode' => 'Semestre '.$numero,
                'moyenne_etudiant' => $moy,
                'moyenne_classe' => null
            ];
        }
        return compact('semestres','matieres','filters','stats','notes_par_matiere','graphique_evolution');
    }

    public function renderNotes() {
        $data = $this->notesData();
        extract($data);
        include __DIR__ . '/../views/etudiant/notes/index.php';
    }
    
    /**
     * Calcule les moyennes de l'étudiant par semestre
     */
    public function calculerMoyennes() {
        $sql = "SELECT s.id, s.numero,
                       AVG(n.note) as moyenne,
                       COUNT(n.id) as nb_notes
                FROM semestres s
                LEFT JOIN notes n ON n.semestre_id = s.id
                    AND n.etudiant_id = :etudiant_id
                    AND n.statut = 'publie'
                GROUP BY s.id, s.numero
                ORDER BY s.numero";
        
        $db = Database::getInstance();
        return $db->fetchAll($sql, ['etudiant_id' => $this->etudiant['id']]);
    }
    
    /**
     * Récupère l'emploi du temps de l'étudiant
     */
    public function getEmploiDuTemps() {
        $classe = $this->getClasse();
        
        if (!$classe) {
            return [];
        }
        
        $sql = "SELECT edt.*, m.intitule as matiere_nom, 
                       p.nom as professeur_nom, p.prenom as professeur_prenom,
                       s.nom as salle_nom
                FROM emploi_du_temps edt
                JOIN matieres m ON edt.matiere_id = m.id
                JOIN professeurs p ON edt.professeur_id = p.id
                JOIN salles s ON edt.salle_id = s.id
                WHERE edt.classe_id = :classe_id
                ORDER BY edt.jour_semaine, edt.heure_debut";
        
        $db = Database::getInstance();
        try {
            return $db->fetchAll($sql, ['classe_id' => $classe['id']]);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Récupère la classe de l'étudiant
     */
    public function getClasse() {
        $sql = "SELECT c.* 
                FROM classes c
                JOIN inscriptions i ON c.id = i.classe_id
                WHERE i.etudiant_id = :etudiant_id";
        
        $db = Database::getInstance();
        return $db->fetch($sql, ['etudiant_id' => $this->etudiant['id']]);
    }
    
    /**
     * Récupère les absences de l'étudiant
     */
    public function getAbsences() {
        $sql = "SELECT a.*, m.nom as matiere_nom, 
                       p.nom as professeur_nom, p.prenom as professeur_prenom
                FROM absences a
                LEFT JOIN matieres m ON a.matiere_id = m.id
                LEFT JOIN professeurs p ON a.professeur_id = p.id
                WHERE a.etudiant_id = :etudiant_id
                ORDER BY a.date_absence DESC";
        
        $db = Database::getInstance();
        return $db->fetchAll($sql, ['etudiant_id' => $this->etudiant['id']]);
    }
    
    /**
     * Récupère les documents partagés avec l'étudiant
     */
    public function getDocuments() {
        $classe = $this->getClasse();
        
        if (!$classe) {
            return [];
        }
        
        $sql = "SELECT d.*, 
                       p.nom as professeur_nom, p.prenom as professeur_prenom
                FROM documents d
                LEFT JOIN professeurs p ON d.professeur_id = p.id
                WHERE d.classe_id = :classe_id
                OR d.etudiant_id = :etudiant_id
                ORDER BY d.date_ajout DESC";
        
        $db = Database::getInstance();
        return $db->fetchAll($sql, [
            'classe_id' => $classe['id'],
            'etudiant_id' => $this->etudiant['id']
        ]);
    }
}

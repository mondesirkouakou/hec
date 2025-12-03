<?php
require_once __DIR__ . '/../classes/Etudiant.php';
require_once __DIR__ . '/../classes/AnneeUniversitaire.php';
require_once __DIR__ . '/../classes/Semestre.php';
require_once __DIR__ . '/../classes/Database.php';

class EtudiantController {
    private $etudiant;
    private $anneeModel;
    private $semestreModel;
    
    public function __construct() {
        $this->etudiant = new Etudiant();
        $db = Database::getInstance();
        $this->anneeModel = new AnneeUniversitaire($db);
        $this->semestreModel = new Semestre($db);
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
        // Déterminer année / semestre / session sélectionnés
        $annees = $this->anneeModel->getAll();
        $anneeActive = $this->anneeModel->getActiveYear();

        // Construire une timeline d'années continues (comme sur le dashboard admin)
        $anneeTimeline = [];
        if (!empty($annees)) {
            // Ordonner les années existantes par année de début croissante
            usort($annees, function($a, $b) {
                $ad = (int)($a['annee_debut'] ?? 0);
                $bd = (int)($b['annee_debut'] ?? 0);
                return $ad <=> $bd;
            });

            $minDebut = null;
            $indexParDebut = [];
            foreach ($annees as $a) {
                $debut = (int)($a['annee_debut'] ?? 0);
                $fin = (int)($a['annee_fin'] ?? 0);
                if ($minDebut === null || $debut < $minDebut) {
                    $minDebut = $debut;
                }
                $indexParDebut[$debut] = $a;
            }

            if ($minDebut !== null) {
                $upperDebut = 2098; // aller jusqu'à 2098-2099
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
        }

        $selectedAnneeId = isset($_GET['annee_id']) ? (int)$_GET['annee_id'] : null;
        if ($selectedAnneeId) {
            $_SESSION['etu_annee_id'] = $selectedAnneeId;
        } elseif (isset($_SESSION['etu_annee_id'])) {
            $selectedAnneeId = (int)$_SESSION['etu_annee_id'];
        } elseif ($anneeActive) {
            $selectedAnneeId = (int)$anneeActive['id'];
        }

        $semestresAnnee = [];
        if ($selectedAnneeId) {
            $semestresAnnee = $this->semestreModel->getByAnneeUniversitaire($selectedAnneeId);
        }

        $semestreActif = $this->semestreModel->getActiveSemestre();
        $selectedSemestreId = isset($_GET['semestre_id']) ? (int)$_GET['semestre_id'] : null;
        if ($selectedSemestreId) {
            $_SESSION['etu_semestre_id'] = $selectedSemestreId;
        } elseif (isset($_SESSION['etu_semestre_id'])) {
            $selectedSemestreId = (int)$_SESSION['etu_semestre_id'];
        } elseif ($semestreActif && $semestreActif['annee_universitaire_id'] == $selectedAnneeId) {
            $selectedSemestreId = (int)$semestreActif['id'];
        }

        $selectedSession = isset($_GET['session']) ? (int)$_GET['session'] : (isset($_SESSION['etu_session']) ? (int)$_SESSION['etu_session'] : 1);
        if ($selectedSession < 1 || $selectedSession > 4) {
            $selectedSession = 1;
        }
        $_SESSION['etu_session'] = $selectedSession;

        // Notes et infos dépendantes du semestre / session sélectionnés
        $notes = $this->getNotes($selectedSemestreId, $selectedSession);
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
        return compact('notes','classe','moyennes','emploi','prochain_cours','dernieres_notes','documents_recents','evenements','stats','notes_par_matiere','etudiant','annees','anneeActive','selectedAnneeId','semestresAnnee','selectedSemestreId','selectedSession','anneeTimeline');
    }

    public function renderDashboard() {
        $data = $this->dashboardData();
        extract($data);
        include __DIR__ . '/../views/etudiant/dashboard.php';
    }
    
    /**
     * Récupère les notes de l'étudiant
     */
    public function getNotes($semestreId = null, $session = null) {
        $params = ['etudiant_id' => $this->etudiant['id']];
        $whereClause = '';
        
        if ($semestreId) {
            $whereClause .= ' AND n.semestre_id = :semestre_id';
            $params['semestre_id'] = $semestreId;
        }

        if ($session !== null) {
            $whereClause .= ' AND (n.session = :session OR n.session IS NULL)';
            $params['session'] = $session;
        }
        
        $sql = "SELECT n.*, m.intitule AS matiere_nom,
                       cm.credits AS credits,
                       cm.coefficient AS coefficient,
                       s.numero AS semestre_numero,
                       (SELECT AVG(n2.note)
                          FROM notes n2
                         WHERE n2.classe_id = n.classe_id
                           AND n2.matiere_id = n.matiere_id
                           AND n2.semestre_id = n.semestre_id) AS moyenne_classe
                FROM notes n
                JOIN matieres m ON n.matiere_id = m.id
                JOIN semestres s ON n.semestre_id = s.id
                LEFT JOIN classe_matiere cm ON cm.classe_id = n.classe_id AND cm.matiere_id = n.matiere_id
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
    
    public function renderBulletin() {
        // Année / semestre / session sélectionnés pour le bulletin
        $semestreId = isset($_GET['semestre_id']) ? (int)$_GET['semestre_id'] : (isset($_SESSION['etu_semestre_id']) ? (int)$_SESSION['etu_semestre_id'] : 1);
        $session = isset($_GET['session']) ? (int)$_GET['session'] : (isset($_SESSION['etu_session']) ? (int)$_SESSION['etu_session'] : null);
        if ($session !== null && ($session < 1 || $session > 4)) {
            $session = 1;
        }

        $notes = $this->getNotes($semestreId, $session);
        $classe = $this->getClasse();
        $etudiant = $this->etudiant;

        // Déterminer l'année académique et le numéro de semestre à partir des données réelles
        $annee_academique = null;
        $semestreNumero = null;

        $semestreInfos = null;
        if ($semestreId) {
            $semestreInfos = $this->semestreModel->getById($semestreId);
        } else {
            $semestreInfos = $this->semestreModel->getActiveSemestre();
        }

        if ($semestreInfos) {
            $anneeDebut = isset($semestreInfos['annee_debut']) ? (int)$semestreInfos['annee_debut'] : null;
            $anneeFin   = isset($semestreInfos['annee_fin']) ? (int)$semestreInfos['annee_fin'] : null;
            if ($anneeDebut && $anneeFin) {
                $annee_academique = $anneeDebut . '-' . $anneeFin;
            }
            if (isset($semestreInfos['numero'])) {
                $semestreNumero = (int)$semestreInfos['numero'];
            }
        }

        if ($annee_academique === null) {
            $anneeActive = $this->anneeModel->getActiveYear();
            if ($anneeActive) {
                $debut = isset($anneeActive['annee_debut']) ? (int)$anneeActive['annee_debut'] : (int)date('Y');
                $fin   = isset($anneeActive['annee_fin']) ? (int)$anneeActive['annee_fin'] : ($debut + 1);
                $annee_academique = $debut . '-' . $fin;
            } else {
                $annee_academique = date('Y') . '-' . (date('Y') + 1);
            }
        }

        if ($semestreNumero === null) {
            if (!empty($notes) && isset($notes[0]['semestre_numero'])) {
                $semestreNumero = (int)$notes[0]['semestre_numero'];
            } else {
                $semestreNumero = 1;
            }
        }

        $download = isset($_GET['download']) && $_GET['download'] === '1';

        if ($download) {
            $filename = 'bulletin_' . ($etudiant['matricule'] ?? 'etudiant') . '.html';
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }

        include __DIR__ . '/../views/etudiant/bulletin.php';
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
        $params = ['etudiant_id' => $this->etudiant['id']];
        $anneeFilter = '';

        // Si une année est sélectionnée sur le dashboard étudiant,
        // on ne considère que l'inscription de cette année universitaire
        if (!empty($_SESSION['etu_annee_id'])) {
            $anneeFilter = ' AND i.annee_universitaire_id = :annee_id';
            $params['annee_id'] = (int)$_SESSION['etu_annee_id'];
        }

        $sql = "SELECT c.* 
                FROM classes c
                JOIN inscriptions i ON c.id = i.classe_id
                WHERE i.etudiant_id = :etudiant_id" . $anneeFilter . "
                LIMIT 1";
        
        $db = Database::getInstance();
        return $db->fetch($sql, $params);
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

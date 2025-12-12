<?php
require_once __DIR__ . '/../classes/Etudiant.php';
require_once __DIR__ . '/../classes/AnneeUniversitaire.php';
require_once __DIR__ . '/../classes/Semestre.php';
require_once __DIR__ . '/../classes/Database.php';

class EtudiantController {
    private $etudiant;
    private $anneeModel;
    private $semestreModel;
    private $notesClasseDebugRows = [];
    
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
        // Notes de classe par matière pour "Mes notes de classe" :
        // uniquement les lignes de notes saisies par les professeurs (session NULL),
        // indépendamment des notes d'examen admin.
        $notes_par_matiere = $this->getNotesClasseParMatiere($selectedSemestreId);
        $notes_classe_debug_rows = $this->notesClasseDebugRows;
        $etudiant = $this->etudiant;
        return compact('notes','classe','moyennes','emploi','prochain_cours','dernieres_notes','documents_recents','evenements','stats','notes_par_matiere','notes_classe_debug_rows','etudiant','annees','anneeActive','selectedAnneeId','semestresAnnee','selectedSemestreId','selectedSession','anneeTimeline');
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
            // Session 1 : inclure les notes sans session (anciennes données) ou session=1
            // Sessions 2, 3, 4 : n'afficher que les matières qui ont une note pour cette session
            if ((int)$session === 1) {
                $whereClause .= ' AND (n.session = :session OR n.session IS NULL)';
            } else {
                $whereClause .= ' AND n.session = :session';
            }
            $params['session'] = $session;
        }
        
        $sql = "SELECT n.*, m.intitule AS matiere_nom,
                       cm.credits AS credits,
                       cm.coefficient AS coefficient,
                       s.numero AS semestre_numero,
                       s.annee_universitaire_id,
                       mc.moyenne_classe
                FROM notes n
                JOIN matieres m ON n.matiere_id = m.id
                JOIN semestres s ON n.semestre_id = s.id
                LEFT JOIN classe_matiere cm ON cm.classe_id = n.classe_id AND cm.matiere_id = n.matiere_id
                LEFT JOIN (
                    SELECT
                        t.matiere_id,
                        t.semestre_id,
                        AVG(t.note_classe) AS moyenne_classe
                    FROM (
                        SELECT
                            n2.etudiant_id,
                            n2.matiere_id,
                            n2.semestre_id,
                            MAX(n2.note) AS note_classe
                        FROM notes n2
                        WHERE n2.note IS NOT NULL
                        GROUP BY n2.etudiant_id, n2.matiere_id, n2.semestre_id
                    ) AS t
                    GROUP BY t.matiere_id, t.semestre_id
                ) AS mc
                  ON mc.matiere_id = n.matiere_id
                 AND mc.semestre_id = n.semestre_id
                WHERE n.etudiant_id = :etudiant_id
                $whereClause
                ORDER BY s.numero, m.intitule";
        
        $db = Database::getInstance();
        return $db->fetchAll($sql, $params);
    }

    /**
     * Récupère, par matière, la dernière note de CLASSE saisie par un professeur
     * (sessions null / 0) pour alimenter "Mes notes de classe - Détail".
     */
    private function getNotesClasseParMatiere($semestreId = null) {
        $db = Database::getInstance();
        $params = ['etudiant_id' => $this->etudiant['id']];
        $semestreClause = '';

        if ($semestreId) {
            $semestreClause = ' AND n.semestre_id = :semestre_id';
            $params['semestre_id'] = $semestreId;
        }

        $sql = "SELECT n.*, m.intitule AS matiere_nom
                FROM notes n
                JOIN matieres m ON n.matiere_id = m.id
                WHERE n.etudiant_id = :etudiant_id
                  $semestreClause
                  AND (
                        (n.note IS NOT NULL AND n.note <> 0)
                        OR n.note1 IS NOT NULL
                        OR n.note2 IS NOT NULL
                        OR n.note3 IS NOT NULL
                        OR n.note4 IS NOT NULL
                        OR n.note5 IS NOT NULL
                  )
                ORDER BY n.id DESC";

        $rows = $db->fetchAll($sql, $params);
        $this->notesClasseDebugRows = $rows;
        error_log('[EtudiantController] getNotesClasseParMatiere start — semestreId=' . ($semestreId ?? 'null') . ', rows=' . count($rows));
        $parMatiere = [];

        foreach ($rows as $row) {
            $matiereId = isset($row['matiere_id']) ? (int)$row['matiere_id'] : 0;
            if ($matiereId === 0) {
                continue;
            }

            $sessionVal = $row['session'] ?? null;
            $isClasse = ($sessionVal === null || $sessionVal === '' || (string)$sessionVal === '0');
            error_log(sprintf('[EtudiantController] Row matiere=%d session=%s note=%s n1=%s n2=%s n3=%s n4=%s n5=%s',
                $matiereId,
                var_export($sessionVal, true),
                var_export($row['note'] ?? null, true),
                var_export($row['note1'] ?? null, true),
                var_export($row['note2'] ?? null, true),
                var_export($row['note3'] ?? null, true),
                var_export($row['note4'] ?? null, true),
                var_export($row['note5'] ?? null, true)
            ));

            $current = $parMatiere[$matiereId]['row'] ?? null;
            $currentIsClasse = $parMatiere[$matiereId]['is_classe'] ?? false;
            $currentId = $current['id'] ?? 0;
            $rowId = isset($row['id']) ? (int)$row['id'] : 0;

            if ($current === null) {
                $parMatiere[$matiereId] = [
                    'row' => $row,
                    'is_classe' => $isClasse,
                ];
                continue;
            }

            if (!$currentIsClasse && $isClasse) {
                // Remplacer un fallback examen par une vraie note de classe
                $parMatiere[$matiereId] = [
                    'row' => $row,
                    'is_classe' => true,
                ];
                continue;
            }

            if ($currentIsClasse === $isClasse && $rowId > $currentId) {
                $parMatiere[$matiereId] = [
                    'row' => $row,
                    'is_classe' => $isClasse,
                ];
            }
        }

        $result = [];
        foreach ($parMatiere as $matiereId => $data) {
            $row = $data['row'];
            $result[] = [
                'id' => $matiereId,
                'nom' => $row['matiere_nom'] ?? '',
                'note1' => isset($row['note1']) ? (float)$row['note1'] : null,
                'note2' => isset($row['note2']) ? (float)$row['note2'] : null,
                'note3' => isset($row['note3']) ? (float)$row['note3'] : null,
                'note4' => isset($row['note4']) ? (float)$row['note4'] : null,
                'note5' => isset($row['note5']) ? (float)$row['note5'] : null,
                'moyenne' => $this->computeNoteClasseMoyenne($row),
            ];
            error_log(sprintf('[EtudiantController] Selected matiere=%d isClasse=%s moyenne=%s',
                $matiereId,
                $data['is_classe'] ? 'true' : 'false',
                var_export(end($result)['moyenne'], true)
            ));
        }

        error_log('[EtudiantController] getNotesClasseParMatiere done — returning ' . count($result) . ' matières');
        return $result;
    }

    private function computeNoteClasseMoyenne(array $row) {
        // 1) Si des notes partielles existent, on recalcule toujours la moyenne à partir de celles-ci
        $values = [];
        for ($i = 1; $i <= 5; $i++) {
            $key = 'note' . $i;
            if (isset($row[$key]) && $row[$key] !== null && $row[$key] !== '') {
                $values[] = (float)$row[$key];
            }
        }

        if (!empty($values)) {
            return array_sum($values) / count($values);
        }

        // 2) Sinon, on utilise la moyenne stockée dans note si elle existe
        if (isset($row['note']) && $row['note'] !== null && $row['note'] !== '') {
            return (float)$row['note'];
        }

        return null;
    }

    public function notesData() {
        $db = Database::getInstance();
        $anneeActive = $this->anneeModel->getActiveYear();
        $anneeActiveId = $anneeActive ? (int)$anneeActive['id'] : null;

        $filters = [
            'semestre_id' => isset($_GET['semestre_id']) && $_GET['semestre_id'] !== '' ? (int)$_GET['semestre_id'] : null,
            'matiere_id' => isset($_GET['matiere_id']) && $_GET['matiere_id'] !== '' ? (int)$_GET['matiere_id'] : null,
        ];

        $notes = $this->getNotes($filters['semestre_id']);

        if ($anneeActiveId !== null) {
            $notes = array_values(array_filter($notes, function ($n) use ($anneeActiveId) {
                if (!isset($n['annee_universitaire_id'])) {
                    return true;
                }
                return (int)$n['annee_universitaire_id'] === $anneeActiveId;
            }));
        }

        if ($filters['matiere_id']) {
            $notes = array_values(array_filter($notes, function($n) use ($filters) { return (int)$n['matiere_id'] === $filters['matiere_id']; }));
        }

        if ($anneeActiveId !== null) {
            $semestres = $db->fetchAll(
                "SELECT id, CONCAT('Semestre ', numero) AS nom FROM semestres WHERE annee_universitaire_id = :annee_id ORDER BY numero",
                ['annee_id' => $anneeActiveId]
            );
        } else {
            $semestres = $db->fetchAll("SELECT id, CONCAT('Semestre ', numero) AS nom FROM semestres ORDER BY numero");
        }

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
        // Préparer les données du graphique : une entrée par matière
        // (moyenne de classe et moyenne d'examen sur l'année en cours)
        $parMatiereGraph = [];
        foreach ($notes as $n) {
            $mid = isset($n['matiere_id']) ? (int)$n['matiere_id'] : 0;
            if ($mid === 0) {
                continue;
            }

            if (!isset($parMatiereGraph[$mid])) {
                $parMatiereGraph[$mid] = [
                    'nom' => $n['matiere_nom'] ?? ('Matière ' . $mid),
                    'notes_classe' => [],
                    'notes_examen' => [],
                ];
            }

            if (isset($n['note']) && $n['note'] !== null) {
                $parMatiereGraph[$mid]['notes_classe'][] = (float)$n['note'];
            }
            if (isset($n['note_examen']) && $n['note_examen'] !== null) {
                $parMatiereGraph[$mid]['notes_examen'][] = (float)$n['note_examen'];
            }
        }

        // Construit le tableau utilisé par la vue pour Chart.js
        $graphique_evolution = [];
        foreach ($parMatiereGraph as $mid => $dataGraph) {
            $moyenneClasse = count($dataGraph['notes_classe']) ? array_sum($dataGraph['notes_classe']) / count($dataGraph['notes_classe']) : 0;
            $moyenneExamen = count($dataGraph['notes_examen']) ? array_sum($dataGraph['notes_examen']) / count($dataGraph['notes_examen']) : 0;

            $graphique_evolution[] = [
                'periode' => $dataGraph['nom'],
                'moyenne_classe_etudiant' => $moyenneClasse,
                'moyenne_examen_etudiant' => $moyenneExamen,
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

        // Pour la session 1, il peut exister plusieurs lignes de notes pour une
        // même matière (notes de classe + notes d'examen, voire anciennes
        // saisies). On agrège ici ces lignes pour n'en conserver qu'une seule
        // par matière, en prenant la meilleure ligne de notes de classe
        // (notes1..5 / note) et en y fusionnant la note d'examen.
        if (!empty($notes)) {
            $sessionInt = ($session !== null) ? (int)$session : 1;
            if ($sessionInt === 1) {
                $grouped = [];
                foreach ($notes as $n) {
                    $mid = isset($n['matiere_id']) ? (int)$n['matiere_id'] : 0;
                    if ($mid === 0) {
                        continue;
                    }

                    // Score pour la qualité de la ligne de notes de CLASSE
                    $hasNotesPartielles = false;
                    for ($i = 1; $i <= 5; $i++) {
                        $key = 'note' . $i;
                        if (array_key_exists($key, $n) && $n[$key] !== null) {
                            $hasNotesPartielles = true;
                            break;
                        }
                    }
                    $hasMoyenneClasse = isset($n['note']) && $n['note'] !== null;

                    $score = 0;
                    if ($hasNotesPartielles) {
                        $score += 10;
                    }
                    if ($hasMoyenneClasse) {
                        $score += 5;
                    }
                    // On favorise les lignes sans session (vraies notes de classe).
                    $sessionNote = isset($n['session']) ? $n['session'] : null;
                    if ($sessionNote === null) {
                        $score += 2;
                    }

                    if (!isset($grouped[$mid])) {
                        $grouped[$mid] = [
                            'row' => $n,
                            'score' => $score,
                            'note_examen' => isset($n['note_examen']) ? $n['note_examen'] : null,
                        ];
                    } else {
                        // Mettre à jour la ligne de CLASSE si cette ligne est meilleure.
                        if ($score > $grouped[$mid]['score']) {
                            $oldExam = $grouped[$mid]['note_examen'];
                            $grouped[$mid]['row'] = $n;
                            $grouped[$mid]['score'] = $score;
                            if ($oldExam !== null) {
                                $grouped[$mid]['note_examen'] = $oldExam;
                            } elseif (isset($n['note_examen']) && $n['note_examen'] !== null) {
                                $grouped[$mid]['note_examen'] = $n['note_examen'];
                            }
                        } else {
                            // Conserver la meilleure ligne de CLASSE mais récupérer
                            // une éventuelle note d'examen manquante.
                            if ($grouped[$mid]['note_examen'] === null && isset($n['note_examen']) && $n['note_examen'] !== null) {
                                $grouped[$mid]['note_examen'] = $n['note_examen'];
                            }
                        }
                    }
                }

                // Reconstruire le tableau $notes agrégé.
                $notesAggreg = [];
                foreach ($grouped as $mid => $data) {
                    $row = $data['row'];
                    if (isset($data['note_examen']) && $data['note_examen'] !== null) {
                        $row['note_examen'] = $data['note_examen'];
                    }
                    $notesAggreg[] = $row;
                }
                $notes = $notesAggreg;
            }
        }

        // Recalculer systématiquement une moyenne de classe cohérente pour le bulletin
        if (!empty($notes)) {
            foreach ($notes as &$n) {
                $n['note_classe_calculee'] = $this->computeNoteClasseMoyenne($n);
            }
            unset($n);
        }

        $classe = $this->getClasse();
        $etudiant = $this->etudiant;

        // Calcul du total des crédits cumulés au semestre (toutes sessions jusqu'à la session actuelle)
        $totalCreditsCumul = 0;
        if ($semestreId) {
            $notesToutesSessions = $this->getNotes($semestreId, null); // sans filtrer par session
            if (is_array($notesToutesSessions)) {
                foreach ($notesToutesSessions as $n) {
                    // Session associée à cette note (1 par défaut si NULL)
                    $sessionNote = isset($n['session']) ? (int)$n['session'] : 1;
                    if ($sessionNote < 1 || $sessionNote > 4) {
                        $sessionNote = 1;
                    }

                    // Ne pas compter les sessions futures par rapport à la session affichée
                    if ($session !== null && $sessionNote > $session) {
                        continue;
                    }

                    $noteClasse = isset($n['note']) ? (float)$n['note'] : null;
                    $noteExamen = isset($n['note_examen']) ? (float)$n['note_examen'] : null;
                    $moyenneFinaleNote = null;

                    // Formule selon la session de la note : session 1 -> 40/60, sessions 2-4 -> examen seul
                    if ($sessionNote === 1) {
                        if ($noteClasse !== null && $noteExamen !== null) {
                            $moyenneFinaleNote = 0.4 * $noteClasse + 0.6 * $noteExamen;
                        } elseif ($noteClasse !== null) {
                            $moyenneFinaleNote = $noteClasse;
                        } elseif ($noteExamen !== null) {
                            $moyenneFinaleNote = $noteExamen;
                        }
                    } else {
                        if ($noteExamen !== null) {
                            $moyenneFinaleNote = $noteExamen;
                        }
                    }

                    if ($moyenneFinaleNote !== null && $moyenneFinaleNote >= 10 && isset($n['credits'])) {
                        $totalCreditsCumul += (float)$n['credits'];
                    }
                }
            }
        }

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

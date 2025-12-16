<?php
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/AnneeUniversitaire.php';
require_once __DIR__ . '/../../classes/Semestre.php';

class EtudiantBulletinAdminController {
    private $db;
    private $anneeModel;
    private $semestreModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->anneeModel = new AnneeUniversitaire($this->db);
        $this->semestreModel = new Semestre($this->db);

        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = "Accès non autorisé.";
            header('Location: ' . BASE_URL . 'login');
            exit();
        }
    }

    private function getEtudiantById($etudiantId) {
        $sql = "SELECT e.*, u.email, u.is_active, u.first_login
                FROM etudiants e
                JOIN users u ON e.user_id = u.id
                WHERE e.id = :id";
        return $this->db->fetch($sql, ['id' => (int)$etudiantId]);
    }

    private function getClasseForEtudiantAndAnnee($etudiantId, $anneeId) {
        $params = [
            'etudiant_id' => (int)$etudiantId,
            'annee_id' => (int)$anneeId,
        ];

        $sql = "SELECT c.*
                FROM classes c
                JOIN inscriptions i ON c.id = i.classe_id
                WHERE i.etudiant_id = :etudiant_id
                  AND i.annee_universitaire_id = :annee_id
                LIMIT 1";

        return $this->db->fetch($sql, $params);
    }

    private function getNotesForEtudiant($etudiantId, $semestreId = null, $session = null, $anneeUniversitaireId = null) {
        $params = ['etudiant_id' => (int)$etudiantId];
        $whereClause = '';

        if ($semestreId) {
            $whereClause .= ' AND n.semestre_id = :semestre_id';
            $params['semestre_id'] = (int)$semestreId;
        }

        if ($anneeUniversitaireId) {
            $whereClause .= ' AND s.annee_universitaire_id = :annee_universitaire_id';
            $params['annee_universitaire_id'] = (int)$anneeUniversitaireId;
        }

        if ($session !== null) {
            if ((int)$session === 1) {
                $whereClause .= ' AND (n.session = :session OR n.session IS NULL)';
            } else {
                $whereClause .= ' AND n.session = :session';
            }
            $params['session'] = (int)$session;
        }

        $sql = "SELECT n.*, m.intitule AS matiere_nom,
                       cm.credits AS credits,
                       cm.coefficient AS coefficient,
                       s.numero AS semestre_numero,
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

        return $this->db->fetchAll($sql, $params);
    }

    private function computeNoteClasseMoyenne(array $row) {
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

        if (isset($row['note']) && $row['note'] !== null && $row['note'] !== '') {
            return (float)$row['note'];
        }

        return null;
    }

    public function bulletin($etudiantId) {
        $etudiant = $this->getEtudiantById($etudiantId);
        if (!$etudiant) {
            $_SESSION['error'] = "Étudiant introuvable.";
            header('Location: ' . BASE_URL . 'admin/dashboard');
            exit();
        }

        $anneeActive = $this->anneeModel->getActiveYear();
        $anneeActiveId = $anneeActive ? (int)$anneeActive['id'] : null;

        // Année sélectionnée : GET > session > année active
        $selectedAnneeId = isset($_GET['annee_id']) ? (int)$_GET['annee_id'] : null;
        if ($selectedAnneeId) {
            $_SESSION['admin_annee_id'] = $selectedAnneeId;
        } elseif (isset($_SESSION['admin_annee_id'])) {
            $selectedAnneeId = (int)$_SESSION['admin_annee_id'];
        } elseif ($anneeActiveId !== null) {
            $selectedAnneeId = $anneeActiveId;
            $_SESSION['admin_annee_id'] = $selectedAnneeId;
        }

        $semestreId = isset($_GET['semestre_id']) ? (int)$_GET['semestre_id'] : null;
        $session = isset($_GET['session']) ? (int)$_GET['session'] : 1;
        if ($session < 1 || $session > 4) {
            $session = 1;
        }

        // Si un semestre est fourni mais ne correspond pas à l'année sélectionnée, on l'ignore
        // afin de retomber sur le comportement par défaut (Semestre 1 de l'année sélectionnée).
        if ($semestreId && $selectedAnneeId) {
            $semestreCheck = $this->semestreModel->getById((int)$semestreId);
            if (!$semestreCheck || (int)($semestreCheck['annee_universitaire_id'] ?? 0) !== (int)$selectedAnneeId) {
                $semestreId = null;
            }
        }

        if (!$semestreId) {
            // Par défaut, si une année est sélectionnée: prendre le semestre 1 de cette année.
            $semestresYear = $selectedAnneeId ? $this->semestreModel->getByAnneeUniversitaire($selectedAnneeId) : [];

            // Si aucune donnée de semestre pour cette année (anciennes années), créer automatiquement S1 et S2
            if ($selectedAnneeId && empty($semestresYear)) {
                $today = date('Y-m-d');

                $idS1 = $this->semestreModel->create([
                    'numero' => 1,
                    'date_debut' => $today,
                    'date_fin' => $today,
                    'annee_universitaire_id' => $selectedAnneeId,
                    'est_ouvert' => 0,
                    'est_cloture' => 1,
                ]);

                $idS2 = $this->semestreModel->create([
                    'numero' => 2,
                    'date_debut' => $today,
                    'date_fin' => $today,
                    'annee_universitaire_id' => $selectedAnneeId,
                    'est_ouvert' => 0,
                    'est_cloture' => 1,
                ]);

                if ($idS1 || $idS2) {
                    $semestresYear = $this->semestreModel->getByAnneeUniversitaire($selectedAnneeId);
                }
            }
            $semestre1 = null;
            if (!empty($semestresYear)) {
                foreach ($semestresYear as $s) {
                    if (isset($s['numero']) && (int)$s['numero'] === 1) {
                        $semestre1 = $s;
                        break;
                    }
                }
            }

            if ($semestre1 && isset($semestre1['id'])) {
                $semestreId = (int)$semestre1['id'];
            } else {
                // Fallback: semestre actif (si il est dans l'année sélectionnée), sinon 1.
                $semestreActif = $this->semestreModel->getActiveSemestre();
                if ($semestreActif && (!$selectedAnneeId || (int)$semestreActif['annee_universitaire_id'] === (int)$selectedAnneeId)) {
                    $semestreId = (int)$semestreActif['id'];
                } elseif (!empty($semestresYear) && isset($semestresYear[0]['id'])) {
                    $semestreId = (int)$semestresYear[0]['id'];
                } else {
                    $semestreId = 1;
                }
            }
        }

        $semestreInfos = $this->semestreModel->getById((int)$semestreId);
        $anneeIdBulletin = ($semestreInfos && isset($semestreInfos['annee_universitaire_id'])) ? (int)$semestreInfos['annee_universitaire_id'] : null;
        $notes = $this->getNotesForEtudiant($etudiantId, $semestreId, $session, $anneeIdBulletin);

        if (!empty($notes)) {
            $sessionInt = (int)$session;
            if ($sessionInt === 1) {
                $grouped = [];
                foreach ($notes as $n) {
                    $mid = isset($n['matiere_id']) ? (int)$n['matiere_id'] : 0;
                    if ($mid === 0) {
                        continue;
                    }

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
                            if ($grouped[$mid]['note_examen'] === null && isset($n['note_examen']) && $n['note_examen'] !== null) {
                                $grouped[$mid]['note_examen'] = $n['note_examen'];
                            }
                        }
                    }
                }

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

        if (!empty($notes)) {
            foreach ($notes as &$n) {
                $n['note_classe_calculee'] = $this->computeNoteClasseMoyenne($n);
            }
            unset($n);
        }

        $anneeId = $selectedAnneeId;
        $classe = null;
        $semestresDisponibles = [];
        if ($anneeId) {
            // Classe actuelle de l'étudiant pour l'année active
            $classe = $this->getClasseForEtudiantAndAnnee($etudiantId, $anneeId);
            // Semestres disponibles sur l'année active (pour permettre à l'admin de naviguer)
            $semestresDisponibles = $this->semestreModel->getByAnneeUniversitaire($anneeId);
        }

        $totalCreditsCumul = 0;
        if ($semestreId) {
            $notesToutesSessions = $this->getNotesForEtudiant($etudiantId, $semestreId, null, $anneeIdBulletin);
            if (is_array($notesToutesSessions)) {
                foreach ($notesToutesSessions as $n) {
                    $sessionNote = isset($n['session']) ? (int)$n['session'] : 1;
                    if ($sessionNote < 1 || $sessionNote > 4) {
                        $sessionNote = 1;
                    }

                    if ($session !== null && $sessionNote > $session) {
                        continue;
                    }

                    $noteClasse = isset($n['note']) ? (float)$n['note'] : null;
                    $noteExamen = isset($n['note_examen']) ? (float)$n['note_examen'] : null;
                    $moyenneFinaleNote = null;

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

        // Contexte spécifique admin : permettra à la vue d'afficher un sélecteur semestre/session
        $isAdminBulletin = true;
        $adminBulletinBaseUrl = BASE_URL . 'admin/etudiants/' . (int)$etudiantId . '/bulletin';

        // Réutiliser la vue du bulletin étudiant en lui fournissant les mêmes variables
        $sessionNumero = $session;
        include __DIR__ . '/../../views/etudiant/bulletin.php';
    }
}

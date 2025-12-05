<?php
require_once __DIR__ . '/../../classes/Database.php';

class NotesController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    private function checkAccess() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = "Accès non autorisé.";
            header('Location: ' . BASE_URL . 'login');
            exit();
        }
    }

    public function validation() {
        $this->checkAccess();

        $sql = "SELECT 
                    n.id as note_id,
                    n.note,
                    n.appreciation,
                    e.id as etudiant_id,
                    e.matricule,
                    e.nom,
                    e.prenom,
                    m.id as matiere_id,
                    m.intitule as matiere_nom,
                    c.id as classe_id,
                    c.intitule as classe_nom
                FROM notes n
                JOIN etudiants e ON n.etudiant_id = e.id
                JOIN matieres m ON n.matiere_id = m.id
                JOIN inscriptions i ON e.id = i.etudiant_id
                JOIN classes c ON i.classe_id = c.id
                WHERE n.statut = 'soumis'
                ORDER BY c.intitule, m.intitule, e.nom, e.prenom";

        $notes = $this->db->fetchAll($sql);

        include __DIR__ . '/../../views/admin/notes/validation.php';
    }

    public function saisie() {
        $this->checkAccess();

        // Charger uniquement les classes de l'année universitaire active et les semestres ouverts
        $classes = $this->db->fetchAll(
            "SELECT c.id, c.code, c.intitule
             FROM classes c
             JOIN annees_universitaires a ON c.annee_universitaire_id = a.id
             WHERE a.est_active = 1
             ORDER BY c.intitule"
        );

        $semestres = $this->db->fetchAll("SELECT id, numero FROM semestres WHERE est_ouvert = 1 ORDER BY numero");
        $matieres = [];
        $etudiants = [];

        $classeId = isset($_GET['classe_id']) ? (int)$_GET['classe_id'] : null;
        $semestreId = isset($_GET['semestre_id']) ? (int)$_GET['semestre_id'] : null;
        $matiereId = isset($_GET['matiere_id']) ? (int)$_GET['matiere_id'] : null;
        $session = isset($_GET['session']) ? (int)$_GET['session'] : 1;
        if ($session < 1 || $session > 4) {
            $session = 1;
        }

        if ($classeId) {
            $matieres = $this->db->fetchAll(
                "SELECT m.id, m.intitule 
                 FROM matieres m 
                 JOIN affectation_professeur ap ON ap.matiere_id = m.id 
                 WHERE ap.classe_id = :cid 
                 ORDER BY m.intitule",
                ['cid' => $classeId]
            );
        } else {
            $matieres = $this->db->fetchAll("SELECT id, intitule FROM matieres ORDER BY intitule");
        }

        if ($classeId && $semestreId && $matiereId) {
            // Charger les notes d'examen pour la session sélectionnée uniquement
            $etudiants = $this->db->fetchAll(
                "SELECT e.id, e.matricule, e.nom, e.prenom,
                        n.note_examen, n.appreciation
                 FROM inscriptions i
                 JOIN etudiants e ON e.id = i.etudiant_id
                 LEFT JOIN notes n ON n.etudiant_id = e.id
                                   AND n.matiere_id = :mid
                                   AND n.semestre_id = :sid
                                   AND n.session = :session
                 WHERE i.classe_id = :cid
                 ORDER BY e.nom, e.prenom",
                [
                    'cid' => $classeId,
                    'mid' => $matiereId,
                    'sid' => $semestreId,
                    'session' => $session,
                ]
            );
        }

        include __DIR__ . '/../../views/admin/notes/saisie.php';
    }

    public function enregistrer() {
        $this->checkAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/notes/saisie');
            exit();
        }

        $classeId = (int)($_POST['classe_id'] ?? 0);
        $semestreId = (int)($_POST['semestre_id'] ?? 0);
        $matiereId = (int)($_POST['matiere_id'] ?? 0);
        $notes = $_POST['notes'] ?? [];
        $session = isset($_POST['session']) ? (int)$_POST['session'] : 1;
        if ($session < 1 || $session > 4) {
            $session = 1;
        }

        $saisiePar = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

        if (!$classeId || !$semestreId || !$matiereId || empty($notes) || !$saisiePar) {
            $_SESSION['error'] = 'Veuillez compléter le formulaire.';
            header('Location: ' . BASE_URL . 'admin/notes/saisie');
            exit();
        }

        try {
            $this->db->beginTransaction();
            foreach ($notes as $etudiantId => $noteData) {
                // L'admin saisit ici la note d'examen
                $val = isset($noteData['note']) ? trim($noteData['note']) : null;

                $app = isset($noteData['appreciation']) ? trim($noteData['appreciation']) : null;
                if ($val === '' || $val === null) continue;
                $val = floatval($val);

                // Upsert la note en se basant sur la combinaison (étudiant, matière, semestre, session)
                $existing = $this->db->fetch(
                    "SELECT id FROM notes 
                     WHERE etudiant_id = :eid 
                       AND matiere_id = :mid 
                       AND semestre_id = :sid
                       AND session = :session",
                    [
                        'eid' => (int)$etudiantId,
                        'mid' => $matiereId,
                        'sid' => $semestreId,
                        'session' => $session,
                    ]
                );

                if ($existing) {
                    $this->db->execute(
                        "UPDATE notes 
                         SET note_examen = :note_examen, appreciation = :app, statut = 'soumis', session = :session, saisie_par = :saisie_par, date_saisie = NOW() 
                         WHERE id = :id",

                        [
                            'note_examen' => $val,
                            'app' => $app,
                            'session' => $session,
                            'saisie_par' => $saisiePar,
                            'id' => $existing['id']
                        ]
                    );
                } else {
                    $this->db->insert(
                        "INSERT INTO notes (etudiant_id, matiere_id, classe_id, semestre_id, session, note_examen, appreciation, statut, saisie_par, date_saisie)
                         VALUES (:eid, :mid, :cid, :sid, :session, :note_examen, :app, 'soumis', :saisie_par, NOW())",
                        [
                            'eid' => (int)$etudiantId,
                            'mid' => $matiereId,
                            'cid' => $classeId,
                            'sid' => $semestreId,
                            'session' => $session,
                            'note_examen' => $val,
                            'app' => $app,
                            'saisie_par' => $saisiePar,
                        ]
                    );
                }

            }
            $this->db->commit();
            $_SESSION['success'] = 'Notes enregistrées.';
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Erreur saisie notes: ' . $e->getMessage());
            $_SESSION['error'] = 'Erreur lors de l\'enregistrement des notes: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . 'admin/notes/saisie?classe_id=' . $classeId . '&semestre_id=' . $semestreId . '&matiere_id=' . $matiereId);
        exit();
    }

    public function valider() {
        $this->checkAccess();

        if (!isset($_POST['notes']) || !is_array($_POST['notes'])) {
            $_SESSION['error'] = "Aucune note sélectionnée";
            header('Location: ' . BASE_URL . 'admin/notes/validation');
            exit();
        }

        try {
            $this->db->beginTransaction();

            foreach ($_POST['notes'] as $noteId) {
                $this->db->execute(
                    "UPDATE notes SET statut = 'valide' WHERE id = :id",
                    ['id' => (int)$noteId]
                );
            }

            $this->db->commit();
            $_SESSION['success'] = "Notes validées avec succès";
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Erreur validation notes: ' . $e->getMessage());
            $_SESSION['error'] = "Erreur lors de la validation des notes";
        }

        header('Location: ' . BASE_URL . 'admin/notes/validation');
        exit();
    }
}
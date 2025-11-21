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

        // Charger classes et semestres ouverts
        $classes = $this->db->fetchAll("SELECT id, code, intitule FROM classes ORDER BY intitule");
        $semestres = $this->db->fetchAll("SELECT id, numero FROM semestres WHERE est_ouvert = 1 ORDER BY numero");
        $matieres = [];
        $etudiants = [];

        $classeId = isset($_GET['classe_id']) ? (int)$_GET['classe_id'] : null;
        $semestreId = isset($_GET['semestre_id']) ? (int)$_GET['semestre_id'] : null;
        $matiereId = isset($_GET['matiere_id']) ? (int)$_GET['matiere_id'] : null;

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

        if ($classeId) {
            $etudiants = $this->db->fetchAll(
                "SELECT e.id, e.matricule, e.nom, e.prenom
                 FROM inscriptions i
                 JOIN etudiants e ON e.id = i.etudiant_id
                 WHERE i.classe_id = :cid
                 ORDER BY e.nom, e.prenom",
                ['cid' => $classeId]
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

        if (!$classeId || !$semestreId || !$matiereId || empty($notes)) {
            $_SESSION['error'] = 'Veuillez compléter le formulaire.';
            header('Location: ' . BASE_URL . 'admin/notes/saisie');
            exit();
        }

        try {
            $this->db->beginTransaction();
            foreach ($notes as $etudiantId => $noteData) {
                $val = isset($noteData['note']) ? trim($noteData['note']) : null;
                $app = isset($noteData['appreciation']) ? trim($noteData['appreciation']) : null;
                if ($val === '' || $val === null) continue;
                $val = floatval($val);

                // Upsert la note
                $existing = $this->db->fetch(
                    "SELECT id FROM notes WHERE etudiant_id = :eid AND matiere_id = :mid",
                    ['eid' => (int)$etudiantId, 'mid' => $matiereId]
                );
                if ($existing) {
                    $this->db->execute(
                        "UPDATE notes SET note = :note, appreciation = :app, statut = 'soumis', date_saisie = NOW() WHERE id = :id",
                        ['note' => $val, 'app' => $app, 'id' => $existing['id']]
                    );
                } else {
                    $this->db->insert(
                        "INSERT INTO notes (etudiant_id, matiere_id, note, appreciation, statut, date_saisie) VALUES (:eid, :mid, :note, :app, 'soumis', NOW())",
                        ['eid' => (int)$etudiantId, 'mid' => $matiereId, 'note' => $val, 'app' => $app]
                    );
                }
            }
            $this->db->commit();
            $_SESSION['success'] = 'Notes enregistrées.';
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Erreur saisie notes: ' . $e->getMessage());
            $_SESSION['error'] = 'Erreur lors de l\'enregistrement des notes.';
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
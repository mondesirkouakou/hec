<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../classes/Note.php';
require_once __DIR__ . '/../../classes/Etudiant.php';
require_once __DIR__ . '/../../classes/Matiere.php';
require_once __DIR__ . '/../../classes/Semestre.php';

requireRole(ROLE_ADMIN);

$pageTitle = "Gestion des Notes";
include __DIR__ . '/../../includes/header.php';

$noteManager = new Note(getDbConnection());
$etudiantManager = new Etudiant(getDbConnection());
$matiereManager = new Matiere(getDbConnection());
$semestreManager = new Semestre(getDbConnection());

$notes = $noteManager->getAllNotes();
$etudiants = $etudiantManager->getAllEtudiants();
$matieres = $matiereManager->getAllMatieres();
$semestres = $semestreManager->getAllSemestres();

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $id_etudiant = $_POST['id_etudiant'];
                $id_matiere = $_POST['id_matiere'];
                $id_semestre = $_POST['id_semestre'];
                $note_valeur = $_POST['note_valeur'];
                $coefficient = $_POST['coefficient'];
                $date_saisie = $_POST['date_saisie'];
                $statut = $_POST['statut'];

                if ($noteManager->addNote($id_etudiant, $id_matiere, $id_semestre, $note_valeur, $coefficient, $date_saisie, $statut)) {
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Note ajoutée avec succès.'];
                } else {
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'Erreur lors de l\'ajout de la note.'];
                }
                break;
            case 'edit':
                $id_note = $_POST['id_note'];
                $id_etudiant = $_POST['id_etudiant'];
                $id_matiere = $_POST['id_matiere'];
                $id_semestre = $_POST['id_semestre'];
                $note_valeur = $_POST['note_valeur'];
                $coefficient = $_POST['coefficient'];
                $date_saisie = $_POST['date_saisie'];
                $statut = $_POST['statut'];

                if ($noteManager->updateNote($id_note, $id_etudiant, $id_matiere, $id_semestre, $note_valeur, $coefficient, $date_saisie, $statut)) {
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Note modifiée avec succès.'];
                } else {
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'Erreur lors de la modification de la note.'];
                }
                break;
            case 'delete':
                $id_note = $_POST['id_note'];
                if ($noteManager->deleteNote($id_note)) {
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Note supprimée avec succès.'];
                } else {
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'Erreur lors de la suppression de la note.'];
                }
                break;
        }
    }
    redirect(BASE_URL . 'pages/admin/gestion_notes.php');
}

?>

<h1 class="mt-4">Gestion des Notes</h1>

<?php if ($message): ?>
    <div class="message <?php echo $message['type']; ?>">
        <?php echo $message['text']; ?>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">Ajouter une nouvelle Note</div>
    <div class="card-body">
        <form action="" method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="id_etudiant">Étudiant:</label>
                <select class="form-control" id="id_etudiant" name="id_etudiant" required>
                    <?php foreach ($etudiants as $etudiant): ?>
                        <option value="<?php echo $etudiant['id_utilisateur']; ?>"><?php echo htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="id_matiere">Matière:</label>
                <select class="form-control" id="id_matiere" name="id_matiere" required>
                    <?php foreach ($matieres as $matiere): ?>
                        <option value="<?php echo $matiere['id_matiere']; ?>"><?php echo htmlspecialchars($matiere['nom_matiere']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="id_semestre">Semestre:</label>
                <select class="form-control" id="id_semestre" name="id_semestre" required>
                    <?php foreach ($semestres as $semestre): ?>
                        <option value="<?php echo $semestre['id_semestre']; ?>"><?php echo htmlspecialchars($semestre['nom_semestre'] . ' (' . $semestre['annee'] . ')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="note_valeur">Note:</label>
                <input type="number" step="0.01" class="form-control" id="note_valeur" name="note_valeur" min="0" max="20" required>
            </div>
            <div class="form-group">
                <label for="coefficient">Coefficient:</label>
                <input type="number" step="0.1" class="form-control" id="coefficient" name="coefficient" min="0.1" required>
            </div>
            <div class="form-group">
                <label for="date_saisie">Date de saisie:</label>
                <input type="date" class="form-control" id="date_saisie" name="date_saisie" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label for="statut">Statut:</label>
                <select class="form-control" id="statut" name="statut" required>
                    <option value="<?php echo NOTE_STATUS_DRAFT; ?>">Brouillon</option>
                    <option value="<?php echo NOTE_STATUS_PUBLISHED; ?>">Publiée</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Ajouter Note</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Liste des Notes</div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Étudiant</th>
                    <th>Matière</th>
                    <th>Semestre</th>
                    <th>Note</th>
                    <th>Coeff.</th>
                    <th>Date Saisie</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($notes)): ?>
                    <?php foreach ($notes as $note): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($note['id_note']); ?></td>
                            <td><?php echo htmlspecialchars($note['etudiant_nom'] . ' ' . $note['etudiant_prenom']); ?></td>
                            <td><?php echo htmlspecialchars($note['nom_matiere']); ?></td>
                            <td><?php echo htmlspecialchars($note['nom_semestre'] . ' (' . $note['annee'] . ')'); ?></td>
                            <td><?php echo htmlspecialchars($note['note_valeur']); ?></td>
                            <td><?php echo htmlspecialchars($note['coefficient']); ?></td>
                            <td><?php echo htmlspecialchars($note['date_saisie']); ?></td>
                            <td><?php echo htmlspecialchars($note['statut']); ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editNoteModal<?php echo $note['id_note']; ?>">Modifier</button>
                                <form action="" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id_note" value="<?php echo $note['id_note']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette note ?');">Supprimer</button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Note Modal -->
                        <div class="modal fade" id="editNoteModal<?php echo $note['id_note']; ?>" tabindex="-1" role="dialog" aria-labelledby="editNoteModalLabel<?php echo $note['id_note']; ?>" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <form action="" method="POST">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editNoteModalLabel<?php echo $note['id_note']; ?>">Modifier la Note</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="action" value="edit">
                                            <input type="hidden" name="id_note" value="<?php echo $note['id_note']; ?>">
                                            <div class="form-group">
                                                <label for="id_etudiant_edit<?php echo $note['id_note']; ?>">Étudiant:</label>
                                                <select class="form-control" id="id_etudiant_edit<?php echo $note['id_note']; ?>" name="id_etudiant" required>
                                                    <?php foreach ($etudiants as $etudiant): ?>
                                                        <option value="<?php echo $etudiant['id_utilisateur']; ?>" <?php echo ($etudiant['id_utilisateur'] == $note['id_etudiant']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="id_matiere_edit<?php echo $note['id_note']; ?>">Matière:</label>
                                                <select class="form-control" id="id_matiere_edit<?php echo $note['id_note']; ?>" name="id_matiere" required>
                                                    <?php foreach ($matieres as $matiere): ?>
                                                        <option value="<?php echo $matiere['id_matiere']; ?>" <?php echo ($matiere['id_matiere'] == $note['id_matiere']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($matiere['nom_matiere']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="id_semestre_edit<?php echo $note['id_note']; ?>">Semestre:</label>
                                                <select class="form-control" id="id_semestre_edit<?php echo $note['id_note']; ?>" name="id_semestre" required>
                                                    <?php foreach ($semestres as $semestre): ?>
                                                        <option value="<?php echo $semestre['id_semestre']; ?>" <?php echo ($semestre['id_semestre'] == $note['id_semestre']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($semestre['nom_semestre'] . ' (' . $semestre['annee'] . ')'); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="note_valeur_edit<?php echo $note['id_note']; ?>">Note:</label>
                                                <input type="number" step="0.01" class="form-control" id="note_valeur_edit<?php echo $note['id_note']; ?>" name="note_valeur" value="<?php echo htmlspecialchars($note['note_valeur']); ?>" min="0" max="20" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="coefficient_edit<?php echo $note['id_note']; ?>">Coefficient:</label>
                                                <input type="number" step="0.1" class="form-control" id="coefficient_edit<?php echo $note['id_note']; ?>" name="coefficient" value="<?php echo htmlspecialchars($note['coefficient']); ?>" min="0.1" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="date_saisie_edit<?php echo $note['id_note']; ?>">Date de saisie:</label>
                                                <input type="date" class="form-control" id="date_saisie_edit<?php echo $note['id_note']; ?>" name="date_saisie" value="<?php echo htmlspecialchars($note['date_saisie']); ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="statut_edit<?php echo $note['id_note']; ?>">Statut:</label>
                                                <select class="form-control" id="statut_edit<?php echo $note['id_note']; ?>" name="statut" required>
                                                    <option value="<?php echo NOTE_STATUS_DRAFT; ?>" <?php echo ($note['statut'] == NOTE_STATUS_DRAFT) ? 'selected' : ''; ?>>Brouillon</option>
                                                    <option value="<?php echo NOTE_STATUS_PUBLISHED; ?>" <?php echo ($note['statut'] == NOTE_STATUS_PUBLISHED) ? 'selected' : ''; ?>>Publiée</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">Aucune note trouvée.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
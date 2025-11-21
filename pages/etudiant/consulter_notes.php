<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../classes/Note.php';
require_once __DIR__ . '/../../classes/Semestre.php';

requireRole(ROLE_ETUDIANT);

$pageTitle = "Consulter mes Notes";
include __DIR__ . '/../../includes/header.php';

$noteManager = new Note(getDbConnection());
$semestreManager = new Semestre(getDbConnection());

$id_etudiant = $_SESSION['user_id'];
$semestres = $semestreManager->getAllSemestres();

$selected_semestre_id = $_GET['semestre_id'] ?? null;

$notes_etudiant = [];
$moyenne_generale_semestre = null;

if ($selected_semestre_id) {
    $notes_etudiant = $noteManager->getPublishedNotesByEtudiantAndSemestre($id_etudiant, $selected_semestre_id);

    $total_notes_ponderees = 0;
    $total_coefficients = 0;

    foreach ($notes_etudiant as $note) {
        $total_notes_ponderees += $note['note_valeur'] * $note['coefficient'];
        $total_coefficients += $note['coefficient'];
    }

    $moyenne_generale_semestre = ($total_coefficients > 0)
        ? round($total_notes_ponderees / $total_coefficients, 2)
        : 'N/A';
}

?>

<h1 class="mt-4">Consulter mes Notes</h1>

<div class="card mb-4">
    <div class="card-header">Sélectionner un Semestre</div>
    <div class="card-body">
        <form action="" method="GET">
            <div class="form-group">
                <label for="semestre_id">Semestre:</label>
                <select class="form-control" id="semestre_id" name="semestre_id" required>
                    <option value="">-- Sélectionner un semestre --</option>
                    <?php foreach ($semestres as $semestre): ?>
                        <option value="<?php echo $semestre['id_semestre']; ?>" <?php echo ($selected_semestre_id == $semestre['id_semestre']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($semestre['nom_semestre'] . ' (' . $semestre['annee'] . ')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Afficher mes notes</button>
        </form>
    </div>
</div>

<?php if ($selected_semestre_id): ?>
    <div class="card">
        <div class="card-header">Notes pour le semestre: <?php echo htmlspecialchars($semestreManager->getSemestreById($selected_semestre_id)['nom_semestre']); ?></div>
        <div class="card-body">
            <?php if (!empty($notes_etudiant)): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Matière</th>
                            <th>Note</th>
                            <th>Coefficient</th>
                            <th>Date de Saisie</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($notes_etudiant as $note): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($note['nom_matiere']); ?></td>
                                <td><?php echo htmlspecialchars($note['note_valeur']); ?></td>
                                <td><?php echo htmlspecialchars($note['coefficient']); ?></td>
                                <td><?php echo htmlspecialchars($note['date_saisie']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <h5 class="mt-4">Moyenne générale du semestre: <strong><?php echo htmlspecialchars($moyenne_generale_semestre); ?></strong></h5>
            <?php else: ?>
                <p>Aucune note publiée pour ce semestre.</p>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
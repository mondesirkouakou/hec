<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../classes/Note.php';
require_once __DIR__ . '/../../classes/Semestre.php';
require_once __DIR__ . '/../../classes/Etudiant.php';

requireRole(ROLE_ETUDIANT);

$pageTitle = "Mon Bulletin";
include __DIR__ . '/../../includes/header.php';

$noteManager = new Note(getDbConnection());
$semestreManager = new Semestre(getDbConnection());
$etudiantManager = new Etudiant(getDbConnection());

$id_etudiant = $_SESSION['user_id'];
$etudiant_info = $etudiantManager->getEtudiantById($id_etudiant);
$semestres = $semestreManager->getAllSemestres();

$selected_semestre_id = $_GET['semestre_id'] ?? null;

$notes_par_matiere = [];
$moyenne_generale_semestre = null;

if ($selected_semestre_id) {
    $notes_etudiant_semestre = $noteManager->getPublishedNotesByEtudiantAndSemestre($id_etudiant, $selected_semestre_id);

    $total_notes_ponderees_semestre = 0;
    $total_coefficients_semestre = 0;

    foreach ($notes_etudiant_semestre as $note) {
        $matiere_id = $note['id_matiere'];
        if (!isset($notes_par_matiere[$matiere_id])) {
            $notes_par_matiere[$matiere_id] = [
                'nom_matiere' => $note['nom_matiere'],
                'notes' => [],
                'total_pondere' => 0,
                'total_coeff' => 0,
                'moyenne_matiere' => 0
            ];
        }
        $notes_par_matiere[$matiere_id]['notes'][] = $note;
        $notes_par_matiere[$matiere_id]['total_pondere'] += $note['note_valeur'] * $note['coefficient'];
        $notes_par_matiere[$matiere_id]['total_coeff'] += $note['coefficient'];
    }

    foreach ($notes_par_matiere as $matiere_id => $data) {
        if ($data['total_coeff'] > 0) {
            $notes_par_matiere[$matiere_id]['moyenne_matiere'] = round($data['total_pondere'] / $data['total_coeff'], 2);
            $total_notes_ponderees_semestre += $notes_par_matiere[$matiere_id]['moyenne_matiere']; // Chaque matière compte pour 1 dans la moyenne générale
            $total_coefficients_semestre += 1;
        }
    }

    $moyenne_generale_semestre = ($total_coefficients_semestre > 0)
        ? round($total_notes_ponderees_semestre / $total_coefficients_semestre, 2)
        : 'N/A';
}

?>

<h1 class="mt-4">Bulletin de Notes</h1>

<?php if ($etudiant_info): ?>
    <p><strong>Étudiant:</strong> <?php echo htmlspecialchars($etudiant_info['nom'] . ' ' . $etudiant_info['prenom']); ?></p>
    <p><strong>Classe:</strong> <?php echo htmlspecialchars($etudiant_info['nom_classe'] ?? 'N/A'); ?></p>
<?php endif; ?>

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
            <button type="submit" class="btn btn-primary">Afficher le bulletin</button>
        </form>
    </div>
</div>

<?php if ($selected_semestre_id): ?>
    <div class="card">
        <div class="card-header">Bulletin pour le semestre: <?php echo htmlspecialchars($semestreManager->getSemestreById($selected_semestre_id)['nom_semestre']); ?></div>
        <div class="card-body">
            <?php if (!empty($notes_par_matiere)): ?>
                <?php foreach ($notes_par_matiere as $matiere_id => $data): ?>
                    <h5 class="mt-3">Matière: <?php echo htmlspecialchars($data['nom_matiere']); ?> (Moyenne: <?php echo htmlspecialchars($data['moyenne_matiere']); ?>)</h5>
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Note</th>
                                <th>Coefficient</th>
                                <th>Date de Saisie</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['notes'] as $note): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($note['note_valeur']); ?></td>
                                    <td><?php echo htmlspecialchars($note['coefficient']); ?></td>
                                    <td><?php echo htmlspecialchars($note['date_saisie']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endforeach; ?>
                <h4 class="mt-4">Moyenne Générale du Semestre: <strong><?php echo htmlspecialchars($moyenne_generale_semestre); ?></strong></h4>
            <?php else: ?>
                <p>Aucune note publiée pour ce semestre.</p>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
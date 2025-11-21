<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../classes/Professeur.php';
require_once __DIR__ . '/../../classes/Matiere.php';
require_once __DIR__ . '/../../classes/Classe.php';
require_once __DIR__ . '/../../classes/Etudiant.php';
require_once __DIR__ . '/../../classes/Note.php';
require_once __DIR__ . '/../../classes/Semestre.php';

requireRole(ROLE_PROFESSEUR);

$pageTitle = "Moyennes de Classe";
include __DIR__ . '/../../includes/header.php';

$professeurManager = new Professeur(getDbConnection());
$matiereManager = new Matiere(getDbConnection());
$classeManager = new Classe(getDbConnection());
$etudiantManager = new Etudiant(getDbConnection());
$noteManager = new Note(getDbConnection());
$semestreManager = new Semestre(getDbConnection());

$id_professeur = $_SESSION['user_id'];
$matieres_enseignees = $professeurManager->getMatieresEnseignees($id_professeur);
$classes_associees = $professeurManager->getClassesAssociees($id_professeur);
$semestres = $semestreManager->getAllSemestres();

$selected_matiere_id = $_GET['matiere_id'] ?? null;
$selected_classe_id = $_GET['classe_id'] ?? null;
$selected_semestre_id = $_GET['semestre_id'] ?? null;

$moyennes_etudiants = [];
$moyenne_generale_classe = null;

if ($selected_matiere_id && $selected_classe_id && $selected_semestre_id) {
    $etudiants_classe = $etudiantManager->getEtudiantsByClasse($selected_classe_id);
    $total_notes_ponderees_classe = 0;
    $total_coefficients_classe = 0;

    foreach ($etudiants_classe as $etudiant) {
        $notes_etudiant = $noteManager->getNotesByEtudiantMatiereSemestre(
            $etudiant['id_utilisateur'],
            $selected_matiere_id,
            $selected_semestre_id
        );

        $total_notes_ponderees_etudiant = 0;
        $total_coefficients_etudiant = 0;

        foreach ($notes_etudiant as $note) {
            $total_notes_ponderees_etudiant += $note['note_valeur'] * $note['coefficient'];
            $total_coefficients_etudiant += $note['coefficient'];
        }

        $moyenne_etudiant = ($total_coefficients_etudiant > 0)
            ? round($total_notes_ponderees_etudiant / $total_coefficients_etudiant, 2)
            : 'N/A';

        $moyennes_etudiants[] = [
            'etudiant' => $etudiant,
            'moyenne' => $moyenne_etudiant
        ];

        if (is_numeric($moyenne_etudiant)) {
            $total_notes_ponderees_classe += $moyenne_etudiant * 1; // Chaque étudiant compte pour 1 dans la moyenne de classe
            $total_coefficients_classe += 1;
        }
    }

    $moyenne_generale_classe = ($total_coefficients_classe > 0)
        ? round($total_notes_ponderees_classe / $total_coefficients_classe, 2)
        : 'N/A';
}

?>

<h1 class="mt-4">Moyennes de Classe par Matière et Semestre</h1>

<div class="card mb-4">
    <div class="card-header">Sélectionner une Matière, une Classe et un Semestre</div>
    <div class="card-body">
        <form action="" method="GET">
            <div class="form-group">
                <label for="matiere_id">Matière enseignée:</label>
                <select class="form-control" id="matiere_id" name="matiere_id" required>
                    <option value="">-- Sélectionner une matière --</option>
                    <?php foreach ($matieres_enseignees as $matiere): ?>
                        <option value="<?php echo $matiere['id_matiere']; ?>" <?php echo ($selected_matiere_id == $matiere['id_matiere']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($matiere['nom_matiere']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="classe_id">Classe associée:</label>
                <select class="form-control" id="classe_id" name="classe_id" required>
                    <option value="">-- Sélectionner une classe --</option>
                    <?php foreach ($classes_associees as $classe): ?>
                        <option value="<?php echo $classe['id_classe']; ?>" <?php echo ($selected_classe_id == $classe['id_classe']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($classe['nom_classe']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="semestre_id">Semestre:</label>
                <select class="form-control" id="semestre_id" name="semestre_id" required>
                    <option value="">-- Sélectionner un semestre --</option>
                    <?php foreach ($semestres as $semestre): ?>
                        <option value="<?php echo $semestre['id_semestre']; ?>" <?php echo ($selected_semestre_id == $semestre['id_semestre']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($semestre['nom_semestre'] . ' (' . $semestre['annee'] . ')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Afficher les moyennes</button>
        </form>
    </div>
</div>

<?php if ($selected_matiere_id && $selected_classe_id && $selected_semestre_id): ?>
    <div class="card">
        <div class="card-header">Moyennes pour la matière: <?php echo htmlspecialchars($matiereManager->getMatiereById($selected_matiere_id)['nom_matiere']); ?> et la classe: <?php echo htmlspecialchars($classeManager->getClasseById($selected_classe_id)['nom_classe']); ?> (Semestre: <?php echo htmlspecialchars($semestreManager->getSemestreById($selected_semestre_id)['nom_semestre']); ?>)</div>
        <div class="card-body">
            <?php if (!empty($moyennes_etudiants)): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Étudiant</th>
                            <th>Moyenne</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($moyennes_etudiants as $data): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($data['etudiant']['nom'] . ' ' . $data['etudiant']['prenom']); ?></td>
                                <td><?php echo htmlspecialchars($data['moyenne']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <h5 class="mt-4">Moyenne générale de la classe pour cette matière: <strong><?php echo htmlspecialchars($moyenne_generale_classe); ?></strong></h5>
            <?php else: ?>
                <p>Aucune moyenne trouvée pour cette sélection.</p>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
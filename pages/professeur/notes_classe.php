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

$pageTitle = "Saisie et Consultation des Notes";
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

$etudiants_classe = [];
$notes_existantes = [];

if ($selected_matiere_id && $selected_classe_id && $selected_semestre_id) {
    $etudiants_classe = $etudiantManager->getEtudiantsByClasse($selected_classe_id);
    foreach ($etudiants_classe as &$etudiant) {
        $note = $noteManager->getNoteByEtudiantMatiereSemestre(
            $etudiant['id_utilisateur'],
            $selected_matiere_id,
            $selected_semestre_id
        );
        $etudiant['note_valeur'] = $note['note_valeur'] ?? '';
        $etudiant['coefficient'] = $note['coefficient'] ?? '';
        $etudiant['id_note'] = $note['id_note'] ?? '';
    }
}

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'save_notes') {
        $notes_data = $_POST['notes'] ?? [];
        $matiere_id = $_POST['matiere_id'];
        $semestre_id = $_POST['semestre_id'];
        $date_saisie = date('Y-m-d'); // Date actuelle

        $success = true;
        foreach ($notes_data as $id_etudiant => $data) {
            $note_valeur = floatval($data['note_valeur']);
            $coefficient = floatval($data['coefficient']);
            $id_note = $data['id_note'];

            if ($note_valeur < 0 || $note_valeur > 20) {
                $_SESSION['message'] = ['type' => 'error', 'text' => 'La note doit être comprise entre 0 et 20.'];
                $success = false;
                break;
            }
            if ($coefficient <= 0) {
                $_SESSION['message'] = ['type' => 'error', 'text' => 'Le coefficient doit être supérieur à 0.'];
                $success = false;
                break;
            }

            if ($id_note) {
                // Mettre à jour la note existante
                if (!$noteManager->updateNote($id_note, $id_etudiant, $matiere_id, $semestre_id, $note_valeur, $coefficient, $date_saisie, NOTE_STATUS_DRAFT)) {
                    $success = false;
                }
            } else {
                // Ajouter une nouvelle note
                if (!$noteManager->addNote($id_etudiant, $matiere_id, $semestre_id, $note_valeur, $coefficient, $date_saisie, NOTE_STATUS_DRAFT)) {
                    $success = false;
                }
            }
        }

        if ($success) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Notes enregistrées avec succès.'];
        } else if (!isset($_SESSION['message'])) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Erreur lors de l\'enregistrement des notes.'];
        }
        redirect(BASE_URL . 'pages/professeur/notes_classe.php?matiere_id=' . $matiere_id . '&classe_id=' . $_POST['classe_id'] . '&semestre_id=' . $semestre_id);
    }
}

?>

<h1 class="mt-4">Saisie et Consultation des Notes</h1>

<?php if ($message): ?>
    <div class="message <?php echo $message['type']; ?>">
        <?php echo $message['text']; ?>
    </div>
<?php endif; ?>

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
            <button type="submit" class="btn btn-primary">Afficher les notes</button>
        </form>
    </div>
</div>

<?php if ($selected_matiere_id && $selected_classe_id && $selected_semestre_id): ?>
    <div class="card">
        <div class="card-header">Notes pour la matière: <?php echo htmlspecialchars($matiereManager->getMatiereById($selected_matiere_id)['nom_matiere']); ?> et la classe: <?php echo htmlspecialchars($classeManager->getClasseById($selected_classe_id)['nom_classe']); ?> (Semestre: <?php echo htmlspecialchars($semestreManager->getSemestreById($selected_semestre_id)['nom_semestre']); ?>)</div>
        <div class="card-body">
            <?php if (!empty($etudiants_classe)): ?>
                <form action="" method="POST" id="notes-form">

                    <input type="hidden" name="action" value="save_notes">
                    <input type="hidden" name="matiere_id" value="<?php echo $selected_matiere_id; ?>">
                    <input type="hidden" name="classe_id" value="<?php echo $selected_classe_id; ?>">
                    <input type="hidden" name="semestre_id" value="<?php echo $selected_semestre_id; ?>">
                    <table class="table table-bordered" id="notes-table">
                        <thead>
                            <tr id="notes-header-row">
                                <th>Étudiant</th>
                                <th class="note-col">Note 1</th>
                                <th class="note-col">Note 2</th>
                                <th class="note-col">Note 3</th>
                                <th id="th-add"><button type="button" id="add-note-col" class="btn btn-sm btn-outline-primary">+</button></th>
                                <th>Moyenne</th>
                            </tr>
                        </thead>
                        <tbody id="notes-tbody">
                            <?php foreach ($etudiants_classe as $etudiant): ?>
                                <tr class="student-row" data-student-id="<?php echo $etudiant['id_utilisateur']; ?>">
                                    <td><?php echo htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom']); ?></td>
                                    <td>
                                        <input type="hidden" name="notes[<?php echo $etudiant['id_utilisateur']; ?>][id_note]" value="<?php echo $etudiant['id_note']; ?>">
                                        <input type="number" step="0.01" class="form-control note-input" value="<?php echo htmlspecialchars($etudiant['note_valeur']); ?>" min="0" max="20">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" class="form-control note-input" min="0" max="20">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" class="form-control note-input" min="0" max="20">
                                    </td>
                                    <td class="add-placeholder"></td>
                                    <td>
                                        <span class="avg-display"></span>
                                        <input type="hidden" class="avg-input" name="notes[<?php echo $etudiant['id_utilisateur']; ?>][note_valeur]" value="<?php echo htmlspecialchars($etudiant['note_valeur']); ?>">
                                        <input type="hidden" class="coef-input" name="notes[<?php echo $etudiant['id_utilisateur']; ?>][coefficient]" value="<?php echo htmlspecialchars($etudiant['coefficient'] !== '' ? $etudiant['coefficient'] : 1); ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-success">Enregistrer les notes</button>
                </form>

            <?php else: ?>
                <p>Aucun étudiant trouvé pour cette classe.</p>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var headerRow = document.getElementById('notes-header-row');
  var addBtn = document.getElementById('add-note-col');
  var tbody = document.getElementById('notes-tbody');
  var form = document.getElementById('notes-form');
  if (!headerRow || !addBtn || !tbody || !form) return;

  function noteColsCount() {
    return headerRow.querySelectorAll('th.note-col').length;
  }

  function computeRowAverage(tr) {
    var inputs = tr.querySelectorAll('input.note-input');
    var sum = 0, count = 0;
    inputs.forEach(function (inp) {
      var v = parseFloat(inp.value);
      if (!isNaN(v) && v >= 0 && v <= 20) { sum += v; count += 1; }
    });
    var avgSpan = tr.querySelector('.avg-display');
    var avgInput = tr.querySelector('.avg-input');
    if (count > 0) {
      var avg = (sum / count);
      avgSpan.textContent = avg.toFixed(2);
      avgInput.value = avg.toFixed(2);
    } else {
      avgSpan.textContent = '';
      avgInput.value = '';
    }
  }

  function attachInputListeners(scope) {
    (scope || document).querySelectorAll('input.note-input').forEach(function (inp) {
      inp.addEventListener('input', function () {
        var tr = inp.closest('tr');
        if (tr) computeRowAverage(tr);
      });
    });
  }

  function addNoteColumn() {
    var nextIndex = noteColsCount() + 1;
    var th = document.createElement('th');
    th.className = 'note-col';
    th.textContent = 'Note ' + nextIndex;
    var addTh = document.getElementById('th-add');
    headerRow.insertBefore(th, addTh);

    tbody.querySelectorAll('tr.student-row').forEach(function (tr) {
      var td = document.createElement('td');
      var input = document.createElement('input');
      input.type = 'number';
      input.step = '0.01';
      input.min = '0';
      input.max = '20';
      input.className = 'form-control note-input';
      td.appendChild(input);
      var addPlaceholder = tr.querySelector('td.add-placeholder');
      tr.insertBefore(td, addPlaceholder);
    });

    attachInputListeners(tbody);
  }

  addBtn.addEventListener('click', addNoteColumn);
  attachInputListeners(document);
  tbody.querySelectorAll('tr.student-row').forEach(computeRowAverage);

  form.addEventListener('submit', function () {
    tbody.querySelectorAll('tr.student-row').forEach(function (tr) {
      computeRowAverage(tr);
      var avgInput = tr.querySelector('.avg-input');
      var namedInputs = tr.querySelectorAll("input[name^='notes[']");
      if (!avgInput.value) {
        namedInputs.forEach(function (inp) { inp.disabled = true; });
      } else {
        namedInputs.forEach(function (inp) { inp.disabled = false; });
      }
    });
  });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
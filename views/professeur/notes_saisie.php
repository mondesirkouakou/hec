<?php
$pageTitle = 'Saisie des notes';
ob_start();
?>

<style>
@media print {
    /* Hide navigation and UI elements */
    header,
    .navbar,
    .animated-header,
    .animated-footer,
    .sidebar,
    nav,
    .btn-toolbar,
    .d-flex.justify-content-between,
    .alert,
    .btn,
    button,
    h1.h2,
    .container-fluid > .d-flex {
        display: none !important;
    }
    
    /* Remove card styling for print */
    .card {
        box-shadow: none !important;
        border: 1px solid #000 !important;
        margin: 0 !important;
    }
    
    .card-header {
        background: white !important;
        border-bottom: 2px solid #000 !important;
        padding: 10px !important;
    }
    
    .card-header h6 {
        color: #000 !important;
        font-size: 14px !important;
        font-weight: bold !important;
    }
    
    /* Ensure table is fully visible */
    .table-responsive {
        overflow: visible !important;
    }
    
    .table {
        page-break-inside: auto;
        font-size: 12px;
    }
    
    .table thead {
        background: #f0f0f0 !important;
    }
    
    .table tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }
    
    .table th,
    .table td {
        border: 1px solid #000 !important;
        padding: 5px !important;
    }
    
    /* Remove container padding */
    .container-fluid {
        padding: 0 !important;
    }
}
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="h2">Saisie des notes</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="<?= BASE_URL ?>professeur/notes" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour à la sélection
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Classe: <?= htmlspecialchars($classe['intitule'] ?? '') ?> — Matière: <?= htmlspecialchars($matiere['intitule'] ?? ($matiere['nom'] ?? '')) ?></h6>
        </div>
        <div class="card-body">
            <?php if (!empty($etudiants)): ?>
                <form action="<?= BASE_URL ?>professeur/notes/<?= (int)$classe['id'] ?>/<?= (int)$matiere['id'] ?>" method="POST" id="notes-form">
                    <div class="table-responsive">
                        <?php
                            $maxNoteCols = 3;
                            foreach ($etudiants as $tmpEtud) {
                                for ($i = 5; $i >= 1; $i--) {
                                    $key = 'note' . $i;
                                    if (array_key_exists($key, $tmpEtud) && $tmpEtud[$key] !== null) {
                                        if ($i > $maxNoteCols) {
                                            $maxNoteCols = $i;
                                        }
                                        break;
                                    }
                                }
                            }
                        ?>
                        <table class="table table-bordered" width="100%" cellspacing="0" id="notes-table">
                            <thead>
                                <tr id="notes-header-row">
                                    <th>Matricule</th>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <?php for ($i = 1; $i <= $maxNoteCols; $i++): ?>
                                        <th class="note-col">Note <?= $i ?></th>
                                    <?php endfor; ?>
                                    <th id="th-add"><button type="button" id="add-note-col" class="btn btn-sm btn-outline-primary">+</button></th>
                                    <th>Appréciation</th>
                                    <th>Moyenne</th>
                                </tr>
                            </thead>
                            <tbody id="notes-tbody">
                                <?php foreach ($etudiants as $e): ?>
                                    <tr class="student-row" data-student-id="<?= (int)$e['id'] ?>">
                                        <td><?= htmlspecialchars($e['matricule'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($e['nom'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($e['prenom'] ?? '') ?></td>
                                        <?php for ($i = 1; $i <= $maxNoteCols; $i++): ?>
                                            <?php
                                                $field = 'note' . $i;
                                                $val = $e[$field] ?? '';
                                                if ($i === 1 && $val === '' && isset($e['note'])) {
                                                    // Compatibilité ancienne donnée où seule la moyenne globale était stockée
                                                    $val = $e['note'];
                                                }
                                            ?>
                                            <td style="max-width:120px">
                                                <input type="number" name="note<?= $i ?>[<?= (int)$e['id'] ?>]" step="0.01" min="0" max="20" class="form-control form-control-sm note-input" value="<?= htmlspecialchars($val) ?>">
                                            </td>
                                        <?php endfor; ?>
                                        <td class="add-placeholder"></td>
                                        <td>
                                            <input type="text" name="appreciations[<?= (int)$e['id'] ?>]" class="form-control form-control-sm" value="<?= htmlspecialchars($e['appreciation'] ?? '') ?>">
                                        </td>
                                        <td>
                                            <span class="avg-display"></span>
                                            <input type="hidden" class="avg-input" name="notes[<?= (int)$e['id'] ?>]" value="<?= htmlspecialchars($e['note'] ?? '') ?>">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fas fa-file-pdf"></i> Imprimer PDF</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-info">Aucun étudiant trouvé dans cette classe.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

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
    if (nextIndex > 5) {
      alert('Vous ne pouvez pas ajouter plus de 5 notes.');
      return;
    }
    var th = document.createElement('th');
    th.className = 'note-col';
    th.textContent = 'Note ' + nextIndex;
    var addTh = document.getElementById('th-add');
    headerRow.insertBefore(th, addTh);

    tbody.querySelectorAll('tr.student-row').forEach(function (tr) {
      var td = document.createElement('td');
      td.style.maxWidth = '120px';
      var input = document.createElement('input');
      input.type = 'number';
      input.step = '0.01';
      input.min = '0';
      input.max = '20';
      input.className = 'form-control form-control-sm note-input';
      var studentId = tr.getAttribute('data-student-id');
      input.name = 'note' + nextIndex + '[' + studentId + ']';
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
      var namedInputs = tr.querySelectorAll("input[name^='notes['], input[name^='appreciations[']");
      if (!avgInput.value) {
        namedInputs.forEach(function (inp) { inp.disabled = true; });
      } else {
        namedInputs.forEach(function (inp) { inp.disabled = false; });
      }
    });
  });
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>

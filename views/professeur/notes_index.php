<?php
$pageTitle = 'Ajouter notes';
ob_start();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="h2">Ajouter notes</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="<?= BASE_URL ?>professeur/dashboard" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour au tableau de bord
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Sélection</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($matieres)): ?>
                <div class="row mb-3">
                    <div class="col-md-6 mb-2">
                        <input type="text" id="filter-matiere-classe" class="form-control" placeholder="Filtrer par matière ou classe">
                    </div>
                    <div class="col-md-3 mb-2">
                        <select id="sort-matiere" class="form-control">
                            <option value="">Tri par défaut</option>
                            <option value="matiere_asc">Matière A → Z</option>
                            <option value="matiere_desc">Matière Z → A</option>
                            <option value="classes_asc">Nombre de classes ↑</option>
                            <option value="classes_desc">Nombre de classes ↓</option>
                        </select>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (empty($matieres)): ?>
                <div class="alert alert-info">Aucune matière affectée.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0" id="notes-index-table">
                        <thead>
                            <tr>
                                <th>Matière</th>
                                <th>Classes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($matieres as $m): ?>
                                <tr>
                                    <td><?= htmlspecialchars($m['intitule'] ?? ($m['nom'] ?? '')) ?></td>
                                    <td>
                                        <?php $classes = $classesParMatiere[$m['id']] ?? []; ?>
                                        <?php if (!empty($classes)): ?>
                                            <?php foreach ($classes as $c): ?>
                                                <a class="btn btn-sm btn-primary mb-1" href="<?= BASE_URL ?>professeur/notes/<?= (int)$c['id'] ?>/<?= (int)$m['id'] ?>">
                                                    <?= htmlspecialchars($c['code'] ?? $c['intitule'] ?? 'Classe') ?>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted">Aucune classe associée</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
 </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var table = document.getElementById('notes-index-table');
  var filterInput = document.getElementById('filter-matiere-classe');
  var sortSelect = document.getElementById('sort-matiere');
  if (!table) return;

  var tbody = table.tBodies[0];

  function getRows() {
    return Array.prototype.slice.call(tbody.querySelectorAll('tr'));
  }

  function applyFilterAndSort() {
    var term = filterInput ? filterInput.value.toLowerCase().trim() : '';
    var sort = sortSelect ? sortSelect.value : '';

    var rows = getRows();

    // Filtre
    rows.forEach(function (row) {
      row.style.display = '';
      if (term) {
        var matiereText = row.cells[0] ? row.cells[0].textContent.toLowerCase() : '';
        var classesText = row.cells[1] ? row.cells[1].textContent.toLowerCase() : '';
        if (matiereText.indexOf(term) === -1 && classesText.indexOf(term) === -1) {
          row.style.display = 'none';
        }
      }
    });

    // Tri (uniquement sur les lignes visibles)
    var visibleRows = rows.filter(function (row) { return row.style.display !== 'none'; });

    if (sort === 'matiere_asc' || sort === 'matiere_desc') {
      visibleRows.sort(function (a, b) {
        var ta = a.cells[0] ? a.cells[0].textContent.trim().toLowerCase() : '';
        var tb = b.cells[0] ? b.cells[0].textContent.trim().toLowerCase() : '';
        if (ta < tb) return sort === 'matiere_asc' ? -1 : 1;
        if (ta > tb) return sort === 'matiere_asc' ? 1 : -1;
        return 0;
      });
    } else if (sort === 'classes_asc' || sort === 'classes_desc') {
      visibleRows.sort(function (a, b) {
        var ca = a.cells[1] ? a.cells[1].querySelectorAll('a.btn').length : 0;
        var cb = b.cells[1] ? b.cells[1].querySelectorAll('a.btn').length : 0;
        if (ca < cb) return sort === 'classes_asc' ? -1 : 1;
        if (ca > cb) return sort === 'classes_asc' ? 1 : -1;
        return 0;
      });
    } else {
      // Tri par défaut: on garde l'ordre d'origine (on ne réordonne pas)
      return;
    }

    visibleRows.forEach(function (row) {
      tbody.appendChild(row);
    });
  }

  if (filterInput) {
    filterInput.addEventListener('input', applyFilterAndSort);
  }

  if (sortSelect) {
    sortSelect.addEventListener('change', applyFilterAndSort);
  }
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>

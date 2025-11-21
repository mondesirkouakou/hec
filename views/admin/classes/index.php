<?php
$pageTitle = 'Gestion des classes';
ob_start();
?>

<div class="page-header">
    <h1><i class="fas fa-chalkboard"></i> Gestion des classes</h1>
    <div class="page-actions">
        <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-info">
            <i class="fas fa-tachometer-alt"></i> Retour au dashboard
        </a>
        <a href="<?= BASE_URL ?>admin/classes/nouvelle" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouvelle classe
        </a>
        <button class="btn btn-secondary" data-toggle="modal" data-target="#importModal">
            <i class="fas fa-file-import"></i> Importer
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($classes)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Aucune classe n'a été créée pour le moment.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Niveau</th>
                            <th>Intitulé</th>
                            <th>Année universitaire</th>
                            <th>Effectif</th>
                            <th>Chef de classe</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classes as $classe): ?>
                        <tr>
                            <td><?= htmlspecialchars($classe['niveau']) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($classe['intitule']) ?></strong>
                                <?php if (!empty($classe['code'])): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($classe['code']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    $anneeDebut = $classe['annee_debut'] ?? null; 
                                    $anneeFin = $classe['annee_fin'] ?? null; 
                                    echo htmlspecialchars($anneeDebut && $anneeFin ? ($anneeDebut . ' - ' . $anneeFin) : '—');
                                ?>
                            </td>
                            <td>
                                <?php if (isset($classe['statut_listes']) && ($classe['statut_listes'] === 'en_attente' || $classe['statut_listes'] === 'validee')): ?>
                                    <?= $classe['effectif'] ?? 0 ?> étudiants
                                <?php else: ?>
                                    Non soumises
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($classe['chef_classe_nom'])): ?>
                                    <span class="badge badge-primary">Assigné</span>
                                <?php else: ?>
                                    <span class="badge badge-info">Non assigné</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php $active = !empty($classe['est_active']); ?>
                                <span class="badge badge-<?= $active ? 'primary' : 'info' ?>">
                                    <?= $active ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="actions">
                                <div class="btn-group" role="group">
                                    <a href="/hec/admin/classes/<?= $classe['id'] ?>" 
                                       class="btn btn-sm btn-info" 
                                       title="Voir la fiche">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/hec/admin/classes/modifier/<?= $classe['id'] ?>" 
                                       class="btn btn-sm btn-warning" 
                                       title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-<?= $active ? 'danger' : 'success' ?>" 
                                            onclick="toggleClasseStatus(<?= $classe['id'] ?>, <?= $active ? 1 : 0 ?>)"
                                            title="<?= $active ? 'Désactiver' : 'Activer' ?>">
                                        <i class="fas <?= $active ? 'fa-lock' : 'fa-lock-open' ?>"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if (isset($totalPages, $currentPage) && $totalPages > 1): ?>
            <nav aria-label="Navigation des pages">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $currentPage - 1 ?>">
                            <i class="fas fa-chevron-left"></i> Précédent
                        </a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == 1 || $i == $totalPages || ($i >= $currentPage - 1 && $i <= $currentPage + 1)): ?>
                            <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php elseif ($i == $currentPage - 2 || $i == $currentPage + 2): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $currentPage + 1 ?>">
                            Suivant <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
            
        <?php endif; ?>
    </div>
</div>

<!-- Modal d'import -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Importer des classes</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="/hec/admin/classes/importer" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="fichier_import">Fichier Excel (.xlsx)</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="fichier_import" name="fichier_import" accept=".xlsx" required>
                            <label class="custom-file-label" for="fichier_import">Choisir un fichier</label>
                        </div>
                        <small class="form-text text-muted">
                            Téléchargez le <a href="/hec/assets/templates/import_classes.xlsx" download>modèle Excel</a> pour le format attendu.
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="ecraser_doublons" name="ecraser_doublons">
                            <label class="custom-control-label" for="ecraser_doublons">Écraser les classes existantes</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Importer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirmation de changement de statut -->
<div class="modal fade" id="confirmStatusModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmation</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="statusModalMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <a href="#" id="confirmStatusBtn" class="btn btn-danger">Confirmer</a>
            </div>
        </div>
    </div>
</div>

<script>
// Gestion de l'affichage du nom du fichier dans l'input file
document.querySelector('.custom-file-input').addEventListener('change', function(e) {
    const fileName = e.target.files[0] ? e.target.files[0].name : 'Choisir un fichier';
    const label = e.target.nextElementSibling;
    label.textContent = fileName;
});

// Gestion du changement de statut d'une classe
function toggleClasseStatus(classeId, isActive) {
    const modal = $('#confirmStatusModal');
    const action = isActive ? 'désactiver' : 'activer';
    const message = isActive 
        ? 'Êtes-vous sûr de vouloir désactiver cette classe ? Les étudiants ne pourront plus y être inscrits.'
        : 'Êtes-vous sûr de vouloir activer cette classe ?';
    
    modal.find('#statusModalMessage').text(message);
    modal.find('#confirmStatusBtn')
        .text(isActive ? 'Désactiver' : 'Activer')
        .attr('href', `/hec/admin/classes/toggle-status/${classeId}`)
        .toggleClass('btn-danger', isActive)
        .toggleClass('btn-success', !isActive);
    
    modal.modal('show');
}

// Initialisation des tooltips
$(function () {
    $('[data-toggle="tooltip"]').tooltip();
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../layouts/main.php';
?>

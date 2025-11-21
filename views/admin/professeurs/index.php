<?php
$pageTitle = 'Gestion des professeurs';
ob_start();
?>

<div class="page-header">
    <h1><i class="fas fa-chalkboard-teacher"></i> Gestion des professeurs</h1>
    <div class="page-actions">
        <a href="/hec/admin/professeurs/ajouter" class="btn btn-primary">
            <i class="fas fa-plus"></i> Ajouter un professeur
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Rechercher un professeur..." onkeyup="filterTable()">
        </div>
    </div>
    
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table" id="professorsTable">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Spécialité</th>
                        <th>Matières</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($professeurs as $professeur): ?>
                    <tr>
                        <td><?= htmlspecialchars($professeur['nom']) ?></td>
                        <td><?= htmlspecialchars($professeur['prenom']) ?></td>
                        <td><?= htmlspecialchars($professeur['email']) ?></td>
                        <td><?= htmlspecialchars($professeur['telephone'] ?? 'Non renseigné') ?></td>
                        <td><?= htmlspecialchars($professeur['specialite'] ?? 'Non spécifiée') ?></td>
                        <td>
                            <?php 
                            $matieres = explode(',', $professeur['matieres'] ?? '');
                            if (count($matieres) > 0 && !empty($matieres[0])): 
                            ?>
                                <span class="badge badge-info" data-toggle="tooltip" title="<?= htmlspecialchars($professeur['matieres']) ?>">
                                    <?= count($matieres) ?> matière(s)
                                </span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Aucune</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-<?= $professeur['is_active'] ? 'success' : 'danger' ?>">
                                <?= $professeur['is_active'] ? 'Actif' : 'Inactif' ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a href="/hec/admin/professeurs/voir/<?= $professeur['id'] ?>" class="btn btn-sm btn-info" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="/hec/admin/professeurs/modifier/<?= $professeur['id'] ?>" class="btn btn-sm btn-warning" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="btn btn-sm btn-<?= $professeur['is_active'] ? 'danger' : 'success' ?>" 
                                    onclick="toggleStatus(<?= $professeur['id'] ?>, <?= $professeur['is_active'] ?>)"
                                    title="<?= $professeur['is_active'] ? 'Désactiver' : 'Activer' ?>">
                                <i class="fas <?= $professeur['is_active'] ? 'fa-user-times' : 'fa-user-check' ?>"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($currentPage > 1): ?>
                <a href="?page=<?= $currentPage - 1 ?>" class="page-link">
                    <i class="fas fa-chevron-left"></i> Précédent
                </a>
            <?php endif; ?>
            
            <?php 
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalPages, $startPage + 4);
            $startPage = max(1, $endPage - 4);
            
            if ($startPage > 1) {
                echo '<a href="?page=1" class="page-link">1</a>';
                if ($startPage > 2) echo '<span class="page-link disabled">...</span>';
            }
            
            for ($i = $startPage; $i <= $endPage; $i++): ?>
                <a href="?page=<?= $i ?>" class="page-link <?= $i == $currentPage ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; 
            
            if ($endPage < $totalPages) {
                if ($endPage < $totalPages - 1) echo '<span class="page-link disabled">...</span>';
                echo '<a href="?page=' . $totalPages . '" class="page-link">' . $totalPages . '</a>';
            }
            ?>
            
            <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?= $currentPage + 1 ?>" class="page-link">
                    Suivant <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Confirmation de suppression -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmation</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Êtes-vous sûr de vouloir désactiver ce professeur ?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <a href="#" id="confirmBtn" class="btn btn-danger">Confirmer</a>
            </div>
        </div>
    </div>
</div>

<script>
// Filtrage du tableau
function filterTable() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toUpperCase();
    const table = document.getElementById('professorsTable');
    const tr = table.getElementsByTagName('tr');

    for (let i = 1; i < tr.length; i++) {
        const tdNom = tr[i].getElementsByTagName('td')[0];
        const tdPrenom = tr[i].getElementsByTagName('td')[1];
        const tdEmail = tr[i].getElementsByTagName('td')[2];
        
        if (tdNom && tdPrenom && tdEmail) {
            const txtValue = tdNom.textContent + ' ' + tdPrenom.textContent + ' ' + tdEmail.textContent;
            
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = '';
            } else {
                tr[i].style.display = 'none';
            }
        }
    }
}

// Basculer le statut d'un professeur
function toggleStatus(professeurId, isActive) {
    const modal = $('#confirmModal');
    const action = isActive ? 'désactiver' : 'activer';
    
    modal.find('.modal-body').text(`Êtes-vous sûr de vouloir ${action} ce professeur ?`);
    modal.find('#confirmBtn')
        .text(action.charAt(0).toUpperCase() + action.slice(1))
        .attr('href', `/hec/admin/professeurs/toggle-status/${professeurId}`)
        .toggleClass('btn-danger', isActive)
        .toggleClass('btn-success', !isActive);
    
    modal.modal('show');
}

// Initialisation des tooltips
$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../layouts/main.php';
?>

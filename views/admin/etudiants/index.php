<?php
$pageTitle = 'Gestion des étudiants';
ob_start();
?>

<div class="page-header">
    <h1><i class="fas fa-user-graduate"></i> Gestion des étudiants</h1>
    <div class="page-actions">
        <button class="btn btn-secondary" onclick="exportToExcel()">
            <i class="fas fa-file-export"></i> Exporter
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Rechercher un étudiant..." onkeyup="filterTable()">
        </div>
        <div class="filters">
            <select class="form-control" onchange="filterTable()" id="classFilter">
                <option value="">Toutes les classes</option>
                <?php foreach ($classes as $classe): ?>
                    <option value="<?= $classe['id'] ?>"><?= htmlspecialchars($classe['intitule']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table" id="studentsTable">
                <thead>
                    <tr>
                        <th>Matricule</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Classe</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($etudiants as $etudiant): ?>
                    <tr>
                        <td><?= htmlspecialchars($etudiant['matricule']) ?></td>
                        <td><?= htmlspecialchars($etudiant['nom']) ?></td>
                        <td><?= htmlspecialchars($etudiant['prenom']) ?></td>
                        <td><?= htmlspecialchars($etudiant['email']) ?></td>
                        <td><?= htmlspecialchars($etudiant['classe_nom'] ?? 'Non affecté') ?></td>
                        <td>
                            <span class="badge badge-<?= $etudiant['is_active'] ? 'success' : 'danger' ?>">
                                <?= $etudiant['is_active'] ? 'Actif' : 'Inactif' ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a href="/hec/admin/etudiants/voir/<?= $etudiant['id'] ?>" class="btn btn-sm btn-info" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="/hec/admin/etudiants/modifier/<?= $etudiant['id'] ?>" class="btn btn-sm btn-warning" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="btn btn-sm btn-<?= $etudiant['is_active'] ? 'danger' : 'success' ?>" 
                                    onclick="toggleStatus(<?= $etudiant['id'] ?>, <?= $etudiant['is_active'] ?>)"
                                    title="<?= $etudiant['is_active'] ? 'Désactiver' : 'Activer' ?>">
                                <i class="fas <?= $etudiant['is_active'] ? 'fa-user-times' : 'fa-user-check' ?>"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="pagination">
            <?php if ($currentPage > 1): ?>
                <a href="?page=<?= $currentPage - 1 ?>" class="page-link">
                    <i class="fas fa-chevron-left"></i> Précédent
                </a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>" class="page-link <?= $i == $currentPage ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?= $currentPage + 1 ?>" class="page-link">
                    Suivant <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
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
                Êtes-vous sûr de vouloir désactiver cet étudiant ?
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
    const classFilter = document.getElementById('classFilter').value;
    const table = document.getElementById('studentsTable');
    const tr = table.getElementsByTagName('tr');

    for (let i = 1; i < tr.length; i++) {
        const tdMatricule = tr[i].getElementsByTagName('td')[0];
        const tdNom = tr[i].getElementsByTagName('td')[1];
        const tdPrenom = tr[i].getElementsByTagName('td')[2];
        const tdClasse = tr[i].getElementsByTagName('td')[4];
        
        if (tdMatricule && tdNom && tdPrenom) {
            const txtValue = tdMatricule.textContent + ' ' + tdNom.textContent + ' ' + tdPrenom.textContent;
            const classMatch = classFilter === '' || (tdClasse && tdClasse.getAttribute('data-class-id') === classFilter);
            
            if ((txtValue.toUpperCase().indexOf(filter) > -1) && classMatch) {
                tr[i].style.display = '';
            } else {
                tr[i].style.display = 'none';
            }
        }
    }
}

// Basculer le statut d'un étudiant
function toggleStatus(etudiantId, isActive) {
    const modal = $('#confirmModal');
    const action = isActive ? 'désactiver' : 'activer';
    
    modal.find('.modal-body').text(`Êtes-vous sûr de vouloir ${action} cet étudiant ?`);
    modal.find('#confirmBtn')
        .text(action.charAt(0).toUpperCase() + action.slice(1))
        .attr('href', `/hec/admin/etudiants/toggle-status/${etudiantId}`)
        .toggleClass('btn-danger', isActive)
        .toggleClass('btn-success', !isActive);
    
    modal.modal('show');
}

// Exporter vers Excel
function exportToExcel() {
    // Implémentation de l'export Excel
    alert('Fonction d\'export à implémenter');
    // window.location.href = '/hec/admin/etudiants/export';
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

<?php
$pageTitle = 'Gérer les chefs de classe';
ob_start();
?>

<style>
/* Force table visibility on admin chefs-classe page - CRITICAL FIX */
body.admin-chefs-classe-page .card,
body.admin-chefs-classe-page .card *,
body.admin-chefs-classe-page .table,
body.admin-chefs-classe-page .table *,
body.admin-chefs-classe-page .table-responsive,
body.admin-chefs-classe-page .table-responsive * {
    opacity: 1 !important;
    visibility: visible !important;
    transform: none !important;
    animation: none !important;
}

body.admin-chefs-classe-page .card:hover,
body.admin-chefs-classe-page .card:hover *,
body.admin-chefs-classe-page .table:hover,
body.admin-chefs-classe-page .table:hover *,
body.admin-chefs-classe-page .table tbody tr:hover,
body.admin-chefs-classe-page .table tbody tr:hover * {
    opacity: 1 !important;
    visibility: visible !important;
    transform: none !important;
    animation: none !important;
}
</style>

<script>
// Add page-specific class to body for scoped CSS
if (document.body && !document.body.classList.contains('admin-chefs-classe-page')) {
    document.body.classList.add('admin-chefs-classe-page');
}
</script>

<div class="page-header">
    <h1>
        <i class="fas fa-user-tie"></i>
        Gérer les chefs de classe
    </h1>
    <div class="page-actions">
        <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-info">
            <i class="fas fa-tachometer-alt"></i> Retour au dashboard
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (empty($chefs)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                Aucun chef de classe n'a encore été désigné.
            </div>
        <?php else: ?>
            <form method="POST" action="<?= BASE_URL ?>admin/chefs-classe/actions" id="chefsForm">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAllChefs">
                                </th>
                                <th>Classe</th>
                                <th>Chef de classe</th>
                                <th>Email</th>
                                <th>Statut</th>
                                <th>Date de nomination</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($chefs as $chef): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="chefs[]" value="<?= (int)$chef['user_id'] ?>">
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($chef['classe_code'] ?? '') ?></strong>
                                        <?php if (!empty($chef['classe_intitule'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($chef['classe_intitule']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars(trim(($chef['nom'] ?? '') . ' ' . ($chef['prenom'] ?? ''))) ?></td>
                                    <td><?= htmlspecialchars($chef['email'] ?? '') ?></td>
                                    <td>
                                        <?php $actif = !empty($chef['is_active']); ?>
                                        <span class="badge <?= $actif ? 'text-bg-primary' : 'text-bg-danger' ?>">
                                            <?= $actif ? 'Actif' : 'Inactif' ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($chef['date_nomination'] ?? '—') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <button type="submit" name="action" value="activer" class="btn btn-success" onclick="return confirm('Activer les comptes sélectionnés ?');">
                        <i class="fas fa-toggle-on"></i> Activer la sélection
                    </button>
                    <button type="submit" name="action" value="desactiver" class="btn btn-warning" onclick="return confirm('Désactiver les comptes sélectionnés ?');">
                        <i class="fas fa-toggle-off"></i> Désactiver la sélection
                    </button>
                    <button type="submit" name="action" value="supprimer" class="btn btn-danger" onclick="return confirm('Supprimer les comptes chef de classe sélectionnés ? Ils seront désactivés et ne seront plus chefs de classe.');">
                        <i class="fas fa-trash"></i> Supprimer la sélection
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var selectAll = document.getElementById('selectAllChefs');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            var checkboxes = document.querySelectorAll('input[name="chefs[]"]');
            checkboxes.forEach(function(cb) {
                cb.checked = selectAll.checked;
            });
        });
    }
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../layouts/main.php';
?>

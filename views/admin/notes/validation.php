<?php
$pageTitle = 'Validation des notes';
ob_start();
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Validation des notes soumises</h1>
        <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Retour au tableau de bord
        </a>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Notes en attente de validation</h6>
        </div>
        <div class="card-body">
            <?php if (empty($notes)): ?>
                <p class="text-muted">Aucune note soumise par les professeurs n'est en attente.</p>
            <?php else: ?>
                <form action="<?= BASE_URL ?>admin/notes/validation" method="POST">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll"></th>
                                    <th>Classe</th>
                                    <th>Matière</th>
                                    <th>Semestre</th>
                                    <th>Matricule</th>
                                    <th>Étudiant</th>
                                    <th>Note</th>
                                    <th>Appréciation</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($notes as $n): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="notes[]" value="<?= (int)$n['note_id'] ?>">
                                        </td>
                                        <td><?= htmlspecialchars($n['classe_nom']) ?></td>
                                        <td><?= htmlspecialchars($n['matiere_nom']) ?></td>
                                        <td>S<?= (int)$n['semestre_numero'] ?></td>
                                        <td><?= htmlspecialchars($n['matricule']) ?></td>
                                        <td><?= htmlspecialchars($n['nom'] . ' ' . $n['prenom']) ?></td>
                                        <td><?= number_format((float)$n['note'], 2, ',', ' ') ?></td>
                                        <td><?= htmlspecialchars($n['appreciation'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Valider la sélection
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            document.querySelectorAll('input[name="notes[]"]').forEach(cb => {
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
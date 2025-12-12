<?php
$pageTitle = 'Détails du semestre';
ob_start();
?>

<div class="container-fluid admin-dashboard">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="dashboard-title" style="color:#000 !important;">Semestre <?= (int)$semestre['numero'] ?></h1>
        <div>
            <?php if (empty($semestre['est_cloture'])): ?>
                <a href="<?= BASE_URL ?>admin/semestres/<?= (int)$semestre['id'] ?>/activer" class="btn btn-success">
                    <i class="fas fa-check"></i> Activer
                </a>
                <a href="<?= BASE_URL ?>admin/semestres/<?= (int)$semestre['id'] ?>/cloturer" class="btn btn-danger" onclick="return confirm('Clôturer ce semestre ?')">
                    <i class="fas fa-lock"></i> Clôturer
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h6 class="m-0">Informations</h6>
        </div>
        <div class="card-body">
            <p><strong>Année:</strong> <?= htmlspecialchars($annee['annee_debut']) ?> - <?= htmlspecialchars($annee['annee_fin']) ?></p>
            <p><strong>Statut:</strong>
                <?php if (!empty($semestre['est_cloture'])): ?>
                    <span class="badge badge-secondary">Clôturé</span>
                <?php elseif (!empty($semestre['est_ouvert'])): ?>
                    <span class="badge badge-success">Ouvert</span>
                <?php else: ?>
                    <span class="badge badge-warning">Fermé</span>
                <?php endif; ?>
            </p>
        </div>
    </div>

    <div class="mt-3">
        <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-outline-primary">
            <i class="fas fa-tachometer-alt"></i> Retour au dashboard
        </a>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../layouts/main.php';
?>
<?php
$pageTitle = 'Gestion des semestres';
ob_start();
?>

<div class="container-fluid admin-dashboard">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="dashboard-title">Gestion des semestres <?= !empty($anneeLibelle) ? '(' . htmlspecialchars($anneeLibelle) . ')' : '' ?></h1>
        <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-outline-primary">
            <i class="fas fa-tachometer-alt"></i> Retour au dashboard
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h6 class="m-0">Liste des semestres</h6>
        </div>
        <div class="card-body">
            <?php if (empty($semestres)): ?>
                <div class="alert alert-info">Aucun semestre défini.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Semestre</th>
                                <th>Période</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($semestres as $s): ?>
                                <tr>
                                    <td>Semestre <?= (int)$s['numero'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($s['date_debut'])) ?> au <?= date('d/m/Y', strtotime($s['date_fin'])) ?></td>
                                    <td>
                                        <?php if (!empty($s['est_cloture'])): ?>
                                            <span class="badge badge-secondary">Clôturé</span>
                                        <?php elseif (!empty($s['est_ouvert'])): ?>
                                            <span class="badge badge-success">Ouvert</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Fermé</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= BASE_URL ?>admin/semestres/<?= (int)$s['id'] ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> Voir
                                        </a>
                                        <a href="<?= BASE_URL ?>admin/semestres/<?= (int)$s['id'] ?>/modifier" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Modifier
                                        </a>
                                        <?php if (empty($s['est_cloture'])): ?>
                                            <a href="<?= BASE_URL ?>admin/semestres/<?= (int)$s['id'] ?>/activer" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i> Activer
                                            </a>
                                            <a href="<?= BASE_URL ?>admin/semestres/<?= (int)$s['id'] ?>/cloturer" class="btn btn-sm btn-danger" onclick="return confirm('Clôturer ce semestre ?')">
                                                <i class="fas fa-lock"></i> Clôturer
                                            </a>
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

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../layouts/main.php';
?>
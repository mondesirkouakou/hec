<?php
$pageTitle = 'Liste des étudiants de la classe';
ob_start();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="h2">Classe: <?= htmlspecialchars($classe['intitule'] ?? '') ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="<?= BASE_URL ?>professeur/dashboard" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour au tableau de bord
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Étudiants</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($etudiants)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Matricule</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($etudiants as $e): ?>
                                <tr>
                                    <td><?= htmlspecialchars($e['nom'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($e['prenom'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($e['matricule'] ?? '') ?></td>
                                    <td><?= (isset($e['is_active']) && (int)$e['is_active'] === 1) ? 'Actif' : 'Inactif' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">Aucun étudiant inscrit dans cette classe.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>

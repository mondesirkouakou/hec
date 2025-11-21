<?php
$pageTitle = 'Liste des étudiants';
ob_start();
?>
<div class="container-fluid">
    <div class="dashboard-header animated-header d-flex justify-content-between align-items-center">
        <h1 class="dashboard-title">Liste des étudiants</h1>
        <div class="header-actions">
            <a href="<?= BASE_URL ?>chef-classe/dashboard" class="btn btn-secondary ripple-effect"><i class="fas fa-arrow-left"></i> Retour</a>
        </div>
    </div>

    <?php $listeSoumise = ($classe['statut_listes'] ?? '') === 'en_attente'; ?>
    <div class="card animated-card student-card">
        <div class="card-header card-header-secondary d-flex justify-content-between align-items-center">
            <h5 class="card-title">Étudiants (<?= count($etudiants ?? []) ?>)</h5>
            <?php if (!$listeSoumise): ?>
                <button class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#formAjouterEtudiant"><i class="fas fa-plus"></i> Ajouter</button>
            <?php else: ?>
                <span class="badge badge-warning">Listes en attente de validation</span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php if (!$listeSoumise): ?>
                <div id="formAjouterEtudiant" class="collapse mb-3">
                    <form action="<?= BASE_URL ?>chef-classe/etudiants" method="POST" class="row g-3">
                        <div class="col-md-3"><input type="text" name="matricule" class="form-control" required placeholder="Matricule"></div>
                        <div class="col-md-3"><input type="text" name="nom" class="form-control" required placeholder="Nom"></div>
                        <div class="col-md-3"><input type="text" name="prenom" class="form-control" required placeholder="Prénom(s)"></div>
                        <div class="col-md-3"><input type="date" name="date_naissance" class="form-control" required></div>
                        <div class="col-md-3"><input type="text" name="lieu_naissance" class="form-control" required placeholder="Lieu de naissance"></div>
                        <div class="col-md-12"><button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Ajouter</button></div>
                    </form>
                </div>
            <?php endif; ?>

            <?php if (empty($etudiants)): ?>
                <p class="text-muted">Aucun étudiant dans cette classe</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-header-primary">
                            <tr>
                                <th>Matricule</th>
                                <th>Nom & Prénom</th>
                                <th>Email</th>
                                <?php if (!$listeSoumise): ?><th>Actions</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($etudiants as $e): ?>
                                <tr>
                                    <td><?= htmlspecialchars($e['matricule']) ?></td>
                                    <td><?= htmlspecialchars(($e['nom'] ?? '') . ' ' . ($e['prenom'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars($e['email'] ?? '') ?></td>
                                    <?php if (!$listeSoumise): ?>
                                        <td>
                                            <form action="<?= BASE_URL ?>chef-classe/etudiants/supprimer" method="POST" onsubmit="return confirm('Supprimer cet étudiant ?')">
                                                <input type="hidden" name="etudiant_id" value="<?= $e['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    <?php endif; ?>
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
include __DIR__ . '/../layouts/main.php';
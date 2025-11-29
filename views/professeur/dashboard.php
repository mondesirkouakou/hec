<?php
$pageTitle = 'Tableau de bord Professeur';
ob_start();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="h2">Tableau de bord Professeur</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="<?= BASE_URL ?>logout" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Mes informations</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($professeur)): ?>
                <div class="row">
                    <div class="col-md-4">
                        <div><strong>Nom</strong></div>
                        <div><?= htmlspecialchars(($professeur['nom'] ?? '') . ' ' . ($professeur['prenom'] ?? '')) ?></div>
                    </div>
                    <div class="col-md-4">
                        <div><strong>Email</strong></div>
                        <div><?= htmlspecialchars($professeur['email'] ?? '') ?></div>
                    </div>
                    <div class="col-md-4">
                        <div><strong>Téléphone</strong></div>
                        <div><?= htmlspecialchars($professeur['telephone'] ?? '') ?></div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">Profil professeur non disponible.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Mes matières</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($matieres)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Intitulé</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($matieres as $m): ?>
                                <tr>
                                    <td><?= htmlspecialchars($m['intitule'] ?? ($m['nom'] ?? '')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">Aucune matière affectée.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Mes classes</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($classes)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Intitulé</th>
                                <th>Niveau</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($classes as $c): ?>
                                <tr>
                                    <td><?= htmlspecialchars($c['code'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($c['intitule'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($c['niveau'] ?? '') ?></td>
                                    <td>
                                        <a class="btn btn-sm btn-primary" href="<?= BASE_URL ?>professeur/classes/<?= (int)$c['id'] ?>/liste">
                                            <i class="fas fa-eye"></i> Voir liste
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">Aucune classe associée.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Ajouter notes</h6>
            <a href="<?= BASE_URL ?>professeur/notes" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Ouvrir</a>
        </div>
        <div class="card-body">
            <p>Saisir les notes des étudiants pour vos classes et matières affectées.</p>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>

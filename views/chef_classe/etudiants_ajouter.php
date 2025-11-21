<?php
$pageTitle = 'Ajouter un étudiant';
ob_start();
?>
<div class="container-fluid">
    <div class="dashboard-header animated-header d-flex justify-content-between align-items-center">
        <h1 class="dashboard-title">Ajouter un étudiant</h1>
        <div class="header-actions">
            <a href="<?= BASE_URL ?>chef-classe/etudiants" class="btn btn-secondary ripple-effect"><i class="fas fa-list"></i> Liste des étudiants</a>
            <a href="<?= BASE_URL ?>chef-classe/dashboard" class="btn btn-light ripple-effect"><i class="fas fa-home"></i> Tableau de bord</a>
        </div>
    </div>

    <?php $listeSoumise = ($classe['statut_listes'] ?? '') === 'en_attente'; ?>
    <?php if ($listeSoumise): ?>
        <div class="alert alert-warning">La liste est en attente de validation. L'ajout est désactivé.</div>
    <?php endif; ?>

    <div class="card animated-card">
        <div class="card-header card-header-secondary">
            <h5 class="card-title">Formulaire d'ajout</h5>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>chef-classe/etudiants" method="POST" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Matricule</label>
                    <input type="text" name="matricule" class="form-control" required placeholder="Matricule" <?= $listeSoumise ? 'disabled' : '' ?>>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nom</label>
                    <input type="text" name="nom" class="form-control" required <?= $listeSoumise ? 'disabled' : '' ?>>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Prénom(s)</label>
                    <input type="text" name="prenom" class="form-control" required <?= $listeSoumise ? 'disabled' : '' ?>>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date de naissance</label>
                    <input type="date" name="date_naissance" class="form-control" required <?= $listeSoumise ? 'disabled' : '' ?>>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Lieu de naissance</label>
                    <input type="text" name="lieu_naissance" class="form-control" required <?= $listeSoumise ? 'disabled' : '' ?>>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary" <?= $listeSoumise ? 'disabled' : '' ?>><i class="fas fa-plus"></i> Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
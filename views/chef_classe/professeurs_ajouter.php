<?php
$pageTitle = 'Ajouter un professeur';
ob_start();
?>
<div class="container-fluid">
    <div class="dashboard-header animated-header d-flex justify-content-between align-items-center">
        <h1 class="dashboard-title">Ajouter un professeur</h1>
        <div class="header-actions">
            <a href="<?= BASE_URL ?>chef-classe/professeurs" class="btn btn-secondary ripple-effect"><i class="fas fa-list"></i> Liste des professeurs</a>
            <a href="<?= BASE_URL ?>chef-classe/dashboard" class="btn btn-light ripple-effect"><i class="fas fa-home"></i> Tableau de bord</a>
        </div>
    </div>

    <?php $listeSoumise = ($classe['statut_listes'] ?? '') === 'en_attente'; ?>
    <?php if ($listeSoumise): ?>
        <div class="alert alert-warning">La liste est en attente de validation. L'ajout est désactivé.</div>
    <?php endif; ?>

    <div class="card animated-card">
        <div class="card-header card-header-accent">
            <h5 class="card-title">Formulaire d'ajout</h5>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>chef-classe/professeurs" method="POST" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Nom</label>
                    <input type="text" name="nom" class="form-control" required <?= $listeSoumise ? 'disabled' : '' ?>>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Prénom(s)</label>
                    <input type="text" name="prenom" class="form-control" required <?= $listeSoumise ? 'disabled' : '' ?>>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="prenom.nom@hec.ci" <?= $listeSoumise ? 'disabled' : '' ?>>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Téléphone</label>
                    <input type="tel" name="telephone" class="form-control" required pattern="[0-9]{10}" placeholder="0708123456" <?= $listeSoumise ? 'disabled' : '' ?>>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Matière(s)</label>
                    <select name="matiere_ids[]" class="form-control" multiple required <?= $listeSoumise ? 'disabled' : '' ?>>
                        <option value="" disabled>Sélectionner une ou plusieurs matières</option>
                        <?php
                        $db = Database::getInstance();
                        $matieresDisponibles = $db->fetchAll(
                            "SELECT * FROM matieres WHERE id NOT IN (SELECT DISTINCT matiere_id FROM affectation_professeur WHERE classe_id = :classe_id) ORDER BY intitule",
                            ['classe_id' => $classe['id']]
                        );
                        foreach ($matieresDisponibles as $m): ?>
                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['intitule']) ?></option>
                        <?php endforeach; ?>
                    </select>
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
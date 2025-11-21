<?php
$pageTitle = 'Modifier un semestre';
ob_start();
?>

<div class="container-fluid admin-dashboard">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="dashboard-title">Modifier le semestre <?= (int)$semestre['numero'] ?></h1>
    </div>

    <?php if (isset($_SESSION['errors'])): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; unset($_SESSION['errors']); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form action="<?= BASE_URL ?>admin/semestres/<?= (int)$semestre['id'] ?>" method="POST">
                <input type="hidden" name="_method" value="PUT">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Numéro</label>
                        <input type="number" name="numero" class="form-control" min="1" max="2" value="<?= (int)$semestre['numero'] ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Date de début</label>
                        <input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($semestre['date_debut']) ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Date de fin</label>
                        <input type="date" name="date_fin" class="form-control" value="<?= htmlspecialchars($semestre['date_fin']) ?>" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Année universitaire</label>
                        <select name="annee_universitaire_id" class="form-control" required>
                            <?php foreach ($annees as $a): ?>
                                <option value="<?= (int)$a['id'] ?>" <?= ($a['id'] == $semestre['annee_universitaire_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($a['annee_debut']) ?> - <?= htmlspecialchars($a['annee_fin']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="est_ouvert" name="est_ouvert" <?= !empty($semestre['est_ouvert']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="est_ouvert">Ouvert</label>
                    </div>
                    <div class="col-md-3 mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="est_cloture" name="est_cloture" <?= !empty($semestre['est_cloture']) ? 'checked' : '' ?> disabled>
                        <label class="form-check-label" for="est_cloture">Clôturé</label>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>admin/semestres/<?= (int)$semestre['id'] ?>" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../layouts/main.php';
?>
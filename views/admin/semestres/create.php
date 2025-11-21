<?php
$pageTitle = 'Nouveau semestre';
ob_start();
?>

<div class="container-fluid admin-dashboard">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="dashboard-title">Créer un semestre</h1>
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
            <form action="<?= BASE_URL ?>admin/semestres" method="POST">
                <input type="hidden" name="_method" value="POST">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Numéro (1 ou 2)</label>
                        <input type="number" name="numero" class="form-control" min="1" max="2" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Date de début</label>
                        <input type="date" name="date_debut" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Date de fin</label>
                        <input type="date" name="date_fin" class="form-control" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Année universitaire</label>
                        <select name="annee_universitaire_id" class="form-control" required>
                            <?php foreach ($annees as $a): ?>
                                <option value="<?= (int)$a['id'] ?>" <?= (isset($_GET['annee']) && $_GET['annee'] == $a['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($a['annee_debut']) ?> - <?= htmlspecialchars($a['annee_fin']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="est_ouvert" name="est_ouvert">
                        <label class="form-check-label" for="est_ouvert">Ouvrir ce semestre</label>
                    </div>
                    <div class="col-md-3 mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="est_cloture" name="est_cloture">
                        <label class="form-check-label" for="est_cloture">Clôturer (immédiatement)</label>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>admin/semestres" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary">Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../layouts/main.php';
?>
<?php
$pageTitle = 'Nouvelle classe';
ob_start();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="h2">Nouvelle classe</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="<?= BASE_URL ?>admin/classes" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </a>
            <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-sm btn-outline-primary ms-2">
                <i class="fas fa-tachometer-alt"></i> Retour au dashboard
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['errors'])): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <?php unset($_SESSION['errors']); ?>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Informations de la classe</h6>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>admin/classes/nouvelle" method="POST">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="code" class="form-label">Code</label>
                        <input type="text" class="form-control" id="code" name="code" value="<?= htmlspecialchars($_SESSION['old']['code'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="intitule" class="form-label">Intitulé <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="intitule" name="intitule" value="<?= htmlspecialchars($_SESSION['old']['intitule'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="niveau" class="form-label">Niveau <span class="text-danger">*</span></label>
                        <select class="form-control" id="niveau" name="niveau" required>
                            <option value="">Choisir...</option>
                            <option value="Licence" <?= (($_SESSION['old']['niveau'] ?? '') === 'Licence') ? 'selected' : '' ?>>Licence</option>
                            <option value="Master" <?= (($_SESSION['old']['niveau'] ?? '') === 'Master') ? 'selected' : '' ?>>Master</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="annee_universitaire_id" class="form-label">Année universitaire <span class="text-danger">*</span></label>
                        <?php if (!empty($activeYear) && !empty($activeYear['id'])): ?>
                            <input type="hidden" name="annee_universitaire_id" value="<?= (int)$activeYear['id'] ?>">
                            <select class="form-control" id="annee_universitaire_id" disabled>
                                <option value="<?= (int)$activeYear['id'] ?>" selected>
                                    <?= htmlspecialchars($activeYear['annee_debut']) ?> - <?= htmlspecialchars($activeYear['annee_fin']) ?>
                                </option>
                            </select>
                        <?php else: ?>
                            <div class="alert alert-warning mb-0">
                                Aucune année universitaire active n'est définie. Veuillez en activer une avant de créer une classe.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="reset" class="btn btn-secondary me-md-2">
                        <i class="fas fa-undo"></i> Réinitialiser
                    </button>
                    <button type="submit" class="btn btn-primary" <?= (empty($activeYear) || empty($activeYear['id'])) ? 'disabled' : '' ?>>
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
unset($_SESSION['old']);
$content = ob_get_clean();
require_once __DIR__ . '/../../layouts/main.php';
?>
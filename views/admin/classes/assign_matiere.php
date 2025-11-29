<?php
$pageTitle = 'Attribuer une matière';
ob_start();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="h2">Attribuer une matière: <?= htmlspecialchars($classe['intitule']) ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="<?= BASE_URL ?>admin/classes/modifier/<?= (int)$classe['id'] ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour à la classe
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
    <?php if (isset($_SESSION['errors']) && is_array($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
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
            <h6 class="m-0 font-weight-bold text-primary">Nouvelle attribution</h6>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>admin/classes/assign-matiere/<?= (int)$classe['id'] ?>" method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="matiere_intitule" class="form-label">Matière</label>
                        <input type="text" class="form-control" id="matiere_intitule" name="matiere_intitule" placeholder="Saisir le nom de la matière" required>
                    </div>
                    <div class="col-md-3">
                        <label for="coefficient" class="form-label">Coefficient</label>
                        <input type="number" class="form-control" id="coefficient" name="coefficient" min="1" value="1" required>
                    </div>
                    <div class="col-md-3">
                        <label for="credits" class="form-label">Crédits</label>
                        <input type="number" class="form-control" id="credits" name="credits" min="1" value="1" required>
                    </div>
                </div>
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Attribuer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Matières déjà attribuées</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($assignedMatieres)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Matière</th>
                                <th>Coefficient</th>
                                <th>Crédits</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignedMatieres as $am): ?>
                                <tr>
                                    <td><?= htmlspecialchars($am['intitule']) ?></td>
                                    <td><?= htmlspecialchars($am['coefficient']) ?></td>
                                    <td><?= htmlspecialchars($am['credits']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">Aucune matière attribuée pour le moment.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../layouts/main.php';
?>

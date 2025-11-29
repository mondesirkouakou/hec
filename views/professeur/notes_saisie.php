<?php
$pageTitle = 'Saisie des notes';
ob_start();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="h2">Saisie des notes</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="<?= BASE_URL ?>professeur/notes" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour à la sélection
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
            <h6 class="m-0 font-weight-bold text-primary">Classe: <?= htmlspecialchars($classe['intitule'] ?? '') ?> — Matière: <?= htmlspecialchars($matiere['intitule'] ?? ($matiere['nom'] ?? '')) ?></h6>
        </div>
        <div class="card-body">
            <?php if (!empty($etudiants)): ?>
                <form action="<?= BASE_URL ?>professeur/notes/<?= (int)$classe['id'] ?>/<?= (int)$matiere['id'] ?>" method="POST">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Matricule</th>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Note</th>
                                    <th>Appréciation</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($etudiants as $e): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($e['matricule'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($e['nom'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($e['prenom'] ?? '') ?></td>
                                        <td style="max-width:120px">
                                            <input type="number" step="0.01" min="0" max="20" name="notes[<?= (int)$e['id'] ?>]" class="form-control form-control-sm" value="<?= htmlspecialchars($e['note'] ?? '') ?>">
                                        </td>
                                        <td>
                                            <input type="text" name="appreciations[<?= (int)$e['id'] ?>]" class="form-control form-control-sm" value="<?= htmlspecialchars($e['appreciation'] ?? '') ?>">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                        <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>professeur/export/notes/<?= (int)$matiere['id'] ?>/<?= (int)$classe['id'] ?>"><i class="fas fa-file-csv"></i> Exporter CSV</a>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-info">Aucun étudiant trouvé dans cette classe.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>

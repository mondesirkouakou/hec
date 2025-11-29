<?php
$pageTitle = 'Ajouter notes';
ob_start();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="h2">Ajouter notes</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="<?= BASE_URL ?>professeur/dashboard" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour au tableau de bord
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Sélection</h6>
        </div>
        <div class="card-body">
            <?php if (empty($matieres)): ?>
                <div class="alert alert-info">Aucune matière affectée.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Matière</th>
                                <th>Classes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($matieres as $m): ?>
                                <tr>
                                    <td><?= htmlspecialchars($m['intitule'] ?? ($m['nom'] ?? '')) ?></td>
                                    <td>
                                        <?php $classes = $classesParMatiere[$m['id']] ?? []; ?>
                                        <?php if (!empty($classes)): ?>
                                            <?php foreach ($classes as $c): ?>
                                                <a class="btn btn-sm btn-primary mb-1" href="<?= BASE_URL ?>professeur/notes/<?= (int)$c['id'] ?>/<?= (int)$m['id'] ?>">
                                                    <?= htmlspecialchars($c['code'] ?? $c['intitule'] ?? 'Classe') ?>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted">Aucune classe associée</span>
                                        <?php endif; ?>
                                    </td>
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
require_once __DIR__ . '/../layouts/main.php';
?>

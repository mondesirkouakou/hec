<?php
$pageTitle = 'Saisie des notes par classe';
ob_start();
?>

<div class="container-fluid admin-dashboard">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="dashboard-title">Saisie des notes</h1>
        <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Tableau de bord</a>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header">Sélection</div>
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>admin/notes/saisie">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Classe</label>
                        <select name="classe_id" class="form-control" required>
                            <option value="">Choisir...</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?= (int)$c['id'] ?>" <?= ($classeId === (int)$c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['intitule']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Semestre (ouvert)</label>
                        <select name="semestre_id" class="form-control" required>
                            <option value="">Choisir...</option>
                            <?php foreach ($semestres as $s): ?>
                                <option value="<?= (int)$s['id'] ?>" <?= ($semestreId === (int)$s['id']) ? 'selected' : '' ?>>Semestre <?= (int)$s['numero'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Matière</label>
                        <select name="matiere_id" class="form-control" required>
                            <option value="">Choisir...</option>
                            <?php foreach ($matieres as $m): ?>
                                <option value="<?= (int)$m['id'] ?>" <?= ($matiereId === (int)$m['id']) ? 'selected' : '' ?>><?= htmlspecialchars($m['intitule']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mt-3 d-flex justify-content-end">
                    <button class="btn btn-primary">Afficher les étudiants</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($classeId && $semestreId && $matiereId && !empty($etudiants)): ?>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Étudiants</span>
                <div>
                    <?php $session = $session ?? 1; ?>
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                        <?php
                        $btnClass = 'btn btn-sm ' . ($session === $i ? 'btn-primary active' : 'btn-outline-primary');
                        $url = BASE_URL . 'admin/notes/saisie?classe_id=' . (int)$classeId . '&semestre_id=' . (int)$semestreId . '&matiere_id=' . (int)$matiereId . '&session=' . $i;
                        ?>
                        <a href="<?= $url ?>" class="<?= $btnClass ?> ml-1">Session <?= $i ?></a>
                    <?php endfor; ?>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>admin/notes/enregistrer">
                    <input type="hidden" name="classe_id" value="<?= (int)$classeId ?>">
                    <input type="hidden" name="semestre_id" value="<?= (int)$semestreId ?>">
                    <input type="hidden" name="matiere_id" value="<?= (int)$matiereId ?>">
                    <input type="hidden" name="session" value="<?= (int)$session ?>">

                    <div class="table-responsive">
                        <table class="table table-bordered">
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
                                        <td><?= htmlspecialchars($e['matricule']) ?></td>
                                        <td><?= htmlspecialchars($e['nom']) ?></td>
                                        <td><?= htmlspecialchars($e['prenom']) ?></td>
                                        <td style="width: 120px">
                                            <input type="number" step="0.01" min="0" max="20" class="form-control" name="notes[<?= (int)$e['id'] ?>][note]" value="<?= htmlspecialchars($e['note_examen'] ?? '') ?>">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="notes[<?= (int)$e['id'] ?>][appreciation]" placeholder="Optionnel" value="<?= htmlspecialchars($e['appreciation'] ?? '') ?>">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-success">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    <?php elseif ($classeId || $semestreId || $matiereId): ?>
        <div class="alert alert-warning">Veuillez sélectionner une classe, un semestre et une matière.</div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../layouts/main.php';
?>
<?php
$pageTitle = 'Liste des professeurs';
ob_start();
?>
<div class="container-fluid">
    <div class="dashboard-header animated-header d-flex justify-content-between align-items-center">
        <h1 class="dashboard-title">Liste des professeurs</h1>
        <div class="header-actions">
            <a href="<?= BASE_URL ?>chef-classe/dashboard" class="btn btn-secondary ripple-effect"><i class="fas fa-arrow-left"></i> Retour</a>
        </div>
    </div>

    <?php $listeSoumise = ($classe['statut_listes'] ?? '') === 'en_attente'; ?>
    <div class="card animated-card teacher-card">
        <div class="card-header card-header-accent d-flex justify-content-between align-items-center">
            <h5 class="card-title">Professeurs (<?= count($professeurs ?? []) ?>)</h5>
            <?php if (!$listeSoumise): ?>
                <button class="btn btn-accent btn-sm" data-bs-toggle="collapse" data-bs-target="#formAjouterProf"><i class="fas fa-plus"></i> Ajouter</button>
            <?php else: ?>
                <span class="badge badge-warning">Listes en attente de validation</span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php if (!$listeSoumise): ?>
                <div id="formAjouterProf" class="collapse mb-3">
                    <form action="<?= BASE_URL ?>chef-classe/professeurs" method="POST" class="row g-3">
                        <div class="col-md-3"><input type="text" name="nom" class="form-control" required placeholder="Nom"></div>
                        <div class="col-md-3"><input type="text" name="prenom" class="form-control" required placeholder="Prénom(s)"></div>
                        <div class="col-md-3"><input type="email" name="email" class="form-control" required placeholder="prenom.nom@hec.ci"></div>
                        <div class="col-md-3"><input type="tel" name="telephone" class="form-control" required pattern="[0-9]{10}" placeholder="0708123456"></div>
                        <div class="col-md-4">
                            <select name="matiere_ids[]" class="form-control" multiple required>
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
                        <div class="col-md-12"><button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Ajouter</button></div>
                    </form>
                </div>
            <?php endif; ?>

            <?php if (empty($professeurs)): ?>
                <p class="text-muted">Aucun professeur assigné à cette classe</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-header-accent">
                            <tr>
                                <th>Nom & Prénom</th>
                                <th>Email</th>
                                <th>Matière(s)</th>
                                <?php if (!$listeSoumise): ?><th>Actions</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($professeurs as $p): ?>
                                <tr>
                                    <td><?= htmlspecialchars(($p['nom'] ?? '') . ' ' . ($p['prenom'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars($p['email'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($p['matieres'] ?? '') ?></td>
                                    <?php if (!$listeSoumise): ?>
                                        <td>
                                            <?php if (isset($p['professeur_id']) && isset($p['matiere_id'])): ?>
                                                <form action="<?= BASE_URL ?>chef-classe/professeurs/supprimer" method="POST" onsubmit="return confirm('Retirer ce professeur ?')">
                                                    <input type="hidden" name="professeur_id" value="<?= $p['professeur_id'] ?>">
                                                    <input type="hidden" name="matiere_id" value="<?= $p['matiere_id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
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
include __DIR__ . '/../layouts/main.php';
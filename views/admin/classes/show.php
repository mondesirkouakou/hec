<?php
$pageTitle = 'Détails de la classe';
ob_start();
?>

<div class="page-header">
    <h1><i class="fas fa-chalkboard"></i> Détails de la classe</h1>
    <div class="page-actions">
        <a href="<?= BASE_URL ?>admin/classes" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
        <a href="<?= BASE_URL ?>admin/classes/modifier/<?= $classe['id'] ?>" class="btn btn-warning">
            <i class="fas fa-edit"></i> Modifier
        </a>
        <?php if (($classe['statut_listes'] ?? null) === 'en_attente'): ?>
            <form action="<?= BASE_URL ?>admin/classes/<?= (int)$classe['id'] ?>/valider-listes" method="POST" class="d-inline">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check"></i> Valider les listes
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Liste des étudiants</h3>
    </div>
    <div class="card-body">
        <?php if (isset($classe['statut_listes']) && ($classe['statut_listes'] === 'en_attente' || $classe['statut_listes'] === 'validee')): ?>
            <?php if (!empty($etudiants)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Matricule</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Date de naissance</th>
                                <th>Lieu de naissance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($etudiants as $etudiant): ?>
                                <tr>
                                    <td><?= htmlspecialchars($etudiant['matricule']) ?></td>
                                    <td><?= htmlspecialchars($etudiant['nom']) ?></td>
                                    <td><?= htmlspecialchars($etudiant['prenom']) ?></td>
                                    <td><?= htmlspecialchars($etudiant['date_naissance']) ?></td>
                                    <td><?= htmlspecialchars($etudiant['lieu_naissance']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Aucun étudiant n'est inscrit dans cette classe pour le moment.
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> Liste pas encore soumise.
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">Liste des professeurs</h3>
    </div>
    <div class="card-body">
        <?php if (isset($classe['statut_listes']) && ($classe['statut_listes'] === 'en_attente' || $classe['statut_listes'] === 'validee')): ?>
            <?php if (!empty($professeurs)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Email</th>
                                <th>Matières</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($professeurs as $prof): ?>
                                <tr>
                                    <td><?= htmlspecialchars($prof['nom']) ?></td>
                                    <td><?= htmlspecialchars($prof['prenom']) ?></td>
                                    <td><?= htmlspecialchars($prof['email']) ?></td>
                                    <td><?= htmlspecialchars($prof['matieres'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Aucun professeur n'est affecté à cette classe pour le moment.
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> Liste pas encore soumise.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../layouts/main.php';
?>
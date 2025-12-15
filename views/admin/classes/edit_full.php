<?php
$pageTitle = 'Modifier la classe';
ob_start();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="h2">Modifier la classe: <?= htmlspecialchars($classe['intitule']) ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="<?= BASE_URL ?>admin/classes" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour à la liste
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

    <!-- Section Informations de la classe -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Informations générales de la classe</h6>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>admin/classes/modifier/<?= (int)$classe['id'] ?>" method="POST">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="code" class="form-label">Code</label>
                        <input type="text" class="form-control" id="code" name="code" value="<?= htmlspecialchars($classe['code'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="intitule" class="form-label">Intitulé <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="intitule" name="intitule" value="<?= htmlspecialchars($classe['intitule'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="niveau" class="form-label">Niveau <span class="text-danger">*</span></label>
                        <select class="form-control" id="niveau" name="niveau" required>
                            <option value="">Choisir...</option>
                            <option value="Licence" <?= (($classe['niveau'] ?? '') === 'Licence') ? 'selected' : '' ?>>Licence</option>
                            <option value="Master" <?= (($classe['niveau'] ?? '') === 'Master') ? 'selected' : '' ?>>Master</option>
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
                                Aucune année universitaire active n'est définie. Veuillez en activer une avant de modifier une classe.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary" <?= (empty($activeYear) || empty($activeYear['id'])) ? 'disabled' : '' ?>>
                        <i class="fas fa-save"></i> Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Section Gestion des étudiants -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Gestion des étudiants</h6>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="m-0 font-weight-bold text-primary">Liste des étudiants</h6>
                <?php if (($classe['statut_listes'] ?? 'non_soumis') === 'soumis'): ?>
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                        <i class="fas fa-plus"></i> Ajouter un étudiant
                    </button>
                <?php else: ?>
                    <span class="text-warning">La liste des étudiants n'a pas encore été soumise par le chef de classe.</span>
                <?php endif; ?>
            </div>
            <?php if (($classe['statut_listes'] ?? 'non_soumis') === 'soumis'): ?>
                <?php if (!empty($etudiants)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Matricule</th>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($etudiants as $etudiant): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($etudiant['matricule']) ?></td>
                                        <td><?= htmlspecialchars($etudiant['nom']) ?></td>
                                        <td><?= htmlspecialchars($etudiant['prenom']) ?></td>
                                        <td>
                                            <button class="btn btn-info btn-sm"><i class="fas fa-edit"></i> Modifier</button>
                                            <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Supprimer</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">Aucun étudiant inscrit dans cette classe.</div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-warning">La liste des étudiants n'a pas encore été soumise par le chef de classe.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Chef de classe</h6>
        </div>
        <div class="card-body">
            <p class="mb-2">Vous pouvez créer le compte du chef de classe pour cette classe.</p>
            <a href="<?= BASE_URL ?>admin/classes/<?= (int)$classe['id'] ?>/designer-chef" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-user-plus"></i> Créer / gérer le chef de classe
            </a>
        </div>
    </div>

    <!-- Section Gestion des matières, coefficients et crédits -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Gestion des matières, coefficients et crédits</h6>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="m-0 font-weight-bold text-primary">Matières attribuées à la classe</h6>
                <a href="<?= BASE_URL ?>admin/classes/assign-matiere/<?= (int)$classe['id'] ?>" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i> Attribuer une matière
                </a>
            </div>
            <?php if (!empty($assignedMatieres)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Matière</th>
                                <th>Coefficient</th>
                                <th>Crédits</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignedMatieres as $matiere): ?>
                                <tr>
                                    <td><?= htmlspecialchars($matiere['intitule']) ?></td>
                                    <td><?= htmlspecialchars($matiere['coefficient']) ?></td>
                                    <td><?= htmlspecialchars($matiere['credits']) ?></td>
                                    <td>
                                        <button class="btn btn-info btn-sm"><i class="fas fa-edit"></i> Modifier</button>
                                        <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Supprimer</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">Aucune matière attribuée à cette classe pour le moment.</div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Modal Ajouter un étudiant -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStudentModalLabel">Ajouter un étudiant à la classe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addStudentForm">
                    <div class="mb-3">
                        <label for="studentMatricule" class="form-label">Matricule de l'étudiant</label>
                        <input type="text" class="form-control" id="studentMatricule" name="matricule" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </form>
            </div>
        </div>
    </div>
</div>

 



<?php 
unset($_SESSION['old']);
$content = ob_get_clean();
require_once __DIR__ . '/../../layouts/main.php';
?>

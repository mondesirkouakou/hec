<?php
$isEdit = isset($professeur);
$pageTitle = $isEdit ? 'Modifier un professeur' : 'Ajouter un professeur';
ob_start();
?>

<div class="page-header">
    <h1><i class="fas <?= $isEdit ? 'fa-edit' : 'fa-plus' ?>"></i> <?= $isEdit ? 'Modifier' : 'Ajouter' ?> un professeur</h1>
    <a href="/hec/admin/professeurs" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Retour à la liste
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form id="professorForm" action="/hec/admin/professeurs/ajouter" method="POST">
            <div class="row">
                <div class="col-md-6">
                    <h4>Informations personnelles</h4>
                    <div class="form-group">
                        <label for="nom">Nom *</label>
                        <input type="text" class="form-control" id="nom" name="nom"
                               value="<?= htmlspecialchars($professeur['nom'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="prenom">Prénom(s) *</label>
                        <input type="text" class="form-control" id="prenom" name="prenom"
                               value="<?= htmlspecialchars($professeur['prenom'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="telephone">Téléphone *</label>
                        <input type="tel" class="form-control" id="telephone" name="telephone"
                               value="<?= htmlspecialchars($professeur['telephone'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <h4>Informations de compte</h4>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= htmlspecialchars($professeur['email'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Laisser vide pour utiliser le téléphone comme mot de passe par défaut">
                        <small class="form-text text-muted">Si vide, le téléphone sera utilisé comme mot de passe initial.</small>
                    </div>

                    <div class="form-group">
                        <label for="specialite">Spécialité</label>
                        <input type="text" class="form-control" id="specialite" name="specialite"
                               value="<?= htmlspecialchars($professeur['specialite'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?= $isEdit ? 'Mettre à jour' : 'Enregistrer' ?>
                </button>
                <a href="/hec/admin/professeurs" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../layouts/main.php';
?>

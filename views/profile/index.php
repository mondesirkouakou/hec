<?php
$pageTitle = 'Profil';
ob_start();

$isFirstLogin = !empty($_SESSION['first_login']);
?>

<div class="container" style="max-width:720px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Mon profil</h2>
        <a href="<?= BASE_URL ?>" class="btn btn-sm btn-outline-secondary">Retour</a>
    </div>

    <div class="card mb-3">
        <div class="card-header">Informations</div>
        <div class="card-body">
            <form method="post" action="<?= BASE_URL ?>profile">
                <div class="mb-3">
                    <label class="form-label" for="display_name">Nom affiché</label>
                    <input type="text" class="form-control" id="display_name" name="display_name" value="<?= htmlspecialchars($displayName ?? '') ?>" required>
                    <div class="form-text">Ce nom sera affiché dans le message "Bienvenue".</div>
                </div>

                <hr>

                <h5>Changer le mot de passe</h5>

                <?php if ($isFirstLogin): ?>
                    <div class="alert alert-info">
                        <strong>Première connexion :</strong> vous devez changer votre mot de passe.
                    </div>
                <?php endif; ?>

                <?php if (!$isFirstLogin): ?>
                    <div class="mb-3">
                        <label class="form-label" for="current_password">Mot de passe actuel</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label" for="new_password">Nouveau mot de passe</label>
                    <input type="password" class="form-control" id="new_password" name="new_password">
                </div>

                <div class="mb-3">
                    <label class="form-label" for="confirm_password">Confirmer le nouveau mot de passe</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                </div>

                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>

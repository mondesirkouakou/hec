<?php
$pageTitle = 'Changer le mot de passe';
ob_start();

$isFirstLogin = $_SESSION['first_login'] ?? false;
?>

<div class="container" style="max-width:600px;">
    <h2>Changement de mot de passe</h2>
    <?php if ($isFirstLogin): ?>
        <div class="alert alert-info">
            <strong>Première connexion :</strong> Vous devez changer votre mot de passe pour continuer.
            <br>Votre mot de passe initial était : 
            <?php 
            if ($_SESSION['role_id'] == 4) { // Étudiant
                echo "SAMA2007";
            } else { // Professeur ou autre
                echo "votre numéro de téléphone";
            }
            ?>
        </div>
    <?php else: ?>
        <p>Pour des raisons de sécurité, veuillez changer votre mot de passe.</p>
    <?php endif; ?>
    
    <form action="<?= BASE_URL ?>change-password" method="POST">
        <?php if (!$isFirstLogin): ?>
        <div class="form-group">
            <label for="current_password">Mot de passe actuel</label>
            <input type="password" id="current_password" name="current_password" class="form-control" required>
        </div>
        <?php endif; ?>
        <div class="form-group">
            <label for="new_password">Nouveau mot de passe</label>
            <input type="password" id="new_password" name="new_password" class="form-control" required 
                   pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}"
                   title="Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial">
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirmer le nouveau mot de passe</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>
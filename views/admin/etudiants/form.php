<?php
$isEdit = isset($etudiant);
$pageTitle = $isEdit ? 'Modifier un étudiant' : 'Ajouter un étudiant';
ob_start();
?>

<div class="page-header">
    <h1><i class="fas <?= $isEdit ? 'fa-edit' : 'fa-plus' ?>"></i> <?= $isEdit ? 'Modifier' : 'Ajouter' ?> un étudiant</h1>
    <a href="/hec/admin/etudiants" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Retour à la liste
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form id="studentForm" action="/hec/admin/etudiants/<?= $isEdit ? 'modifier/' . $etudiant['id'] : 'ajouter' ?>" method="POST">
            <div class="row">
                <div class="col-md-6">
                    <h4>Informations personnelles</h4>
                    <div class="form-group">
                        <label for="matricule">Matricule *</label>
                        <input type="text" class="form-control" id="matricule" name="matricule" 
                               value="<?= $etudiant['matricule'] ?? '' ?>" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nom">Nom *</label>
                                <input type="text" class="form-control" id="nom" name="nom" 
                                       value="<?= $etudiant['nom'] ?? '' ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="prenom">Prénom(s) *</label>
                                <input type="text" class="form-control" id="prenom" name="prenom" 
                                       value="<?= $etudiant['prenom'] ?? '' ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date_naissance">Date de naissance</label>
                                <input type="date" class="form-control" id="date_naissance" name="date_naissance" 
                                       value="<?= $etudiant['date_naissance'] ?? '' ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lieu_naissance">Lieu de naissance</label>
                                <input type="text" class="form-control" id="lieu_naissance" name="lieu_naissance" 
                                       value="<?= $etudiant['lieu_naissance'] ?? '' ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" class="form-control" id="telephone" name="telephone" 
                               value="<?= $etudiant['telephone'] ?? '' ?>">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h4>Informations de compte</h4>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= $etudiant['email'] ?? '' ?>" required>
                    </div>
                    
                    <?php if (!$isEdit): ?>
                    <div class="form-group">
                        <label for="password">Mot de passe *</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" 
                                   value="<?= $etudiant['matricule'] ?? '' ?>" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="generatePassword">
                                    <i class="fas fa-sync-alt"></i> Générer
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted">Le mot de passe doit contenir au moins 8 caractères.</small>
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="classe_id">Classe</label>
                        <select class="form-control" id="classe_id" name="classe_id">
                            <option value="">Sélectionner une classe</option>
                            <?php foreach ($classes as $classe): ?>
                                <option value="<?= $classe['id'] ?>" 
                                    <?= (isset($etudiant['classe_id']) && $etudiant['classe_id'] == $classe['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($classe['intitule']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <?php if ($isEdit): ?>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" 
                                   <?= ($etudiant['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="is_active">Compte actif</label>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?= $isEdit ? 'Mettre à jour' : 'Enregistrer' ?>
                </button>
                <a href="/hec/admin/etudiants" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
// Générer un mot de passe aléatoire
document.getElementById('generatePassword').addEventListener('click', function() {
    const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    let password = '';
    
    // Au moins une majuscule, une minuscule, un chiffre et un caractère spécial
    password += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[Math.floor(Math.random() * 26)];
    password += 'abcdefghijklmnopqrstuvwxyz'[Math.floor(Math.random() * 26)];
    password += '0123456789'[Math.floor(Math.random() * 10)];
    password += '!@#$%^&*()'[Math.floor(Math.random() * 10)];
    
    // Compléter avec des caractères aléatoires
    for (let i = 0; i < 4; i++) {
        password += charset[Math.floor(Math.random() * charset.length)];
    }
    
    // Mélanger le mot de passe
    password = password.split('').sort(() => 0.5 - Math.random()).join('');
    
    document.getElementById('password').value = password;
});

// Validation du formulaire
document.getElementById('studentForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password');
    if (password && password.value.length < 8) {
        e.preventDefault();
        alert('Le mot de passe doit contenir au moins 8 caractères');
        return false;
    }
    return true;
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../layouts/main.php';
?>

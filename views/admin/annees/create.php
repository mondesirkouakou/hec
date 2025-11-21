<?php 
$pageTitle = 'Nouvelle année universitaire';
ob_start();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="h2">Nouvelle année universitaire</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="<?= BASE_URL ?>admin/annees-universitaires" class="btn btn-sm btn-outline-secondary">
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

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Informations de l'année universitaire</h6>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>admin/annees-universitaires/nouvelle" method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="annee_debut" class="form-label">Année de début <span class="text-danger">*</span></label>
                        <input type="number" 
                               class="form-control <?= isset($_SESSION['old']['annee_debut']) ? 'is-valid' : '' ?>" 
                               id="annee_debut" 
                               name="annee_debut" 
                               value="<?= htmlspecialchars($_SESSION['old']['annee_debut'] ?? ''); unset($_SESSION['old']['annee_debut']) ?>"
                               min="2000" 
                               max="2100" 
                               required>
                        <div class="form-text">Exemple: 2023 pour l'année universitaire 2023-2024</div>
                    </div>
                    <div class="col-md-6">
                        <label for="annee_fin" class="form-label">Année de fin <span class="text-danger">*</span></label>
                        <input type="number" 
                               class="form-control" 
                               id="annee_fin" 
                               name="annee_fin" 
                               value="<?= htmlspecialchars($_SESSION['old']['annee_fin'] ?? ''); unset($_SESSION['old']['annee_fin']) ?>"
                               min="2001" 
                               max="2101" 
                               readonly 
                               required>
                        <div class="form-text">Calculée automatiquement (année de début + 1)</div>
                    </div>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" 
                           class="form-check-input" 
                           id="est_active" 
                           name="est_active"
                           <?= ($_SESSION['old']['est_active'] ?? '') ? 'checked' : '' ?>>
                    <label class="form-check-label" for="est_active">Définir comme année universitaire active</label>
                    <div class="form-text">L'année active est celle qui sera utilisée par défaut dans le système.</div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="reset" class="btn btn-secondary me-md-2">
                        <i class="fas fa-undo"></i> Réinitialiser
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Calcul automatique de l'année de fin
const anneeDebut = document.getElementById('annee_debut');
const anneeFin = document.getElementById('annee_fin');

anneeDebut.addEventListener('change', function() {
    if (this.value) {
        anneeFin.value = parseInt(this.value) + 1;
    } else {
        anneeFin.value = '';
    }
});
</script>

<?php 
// Nettoyer les anciennes données du formulaire
unset($_SESSION['old']); 

$content = ob_get_clean();
require_once __DIR__ . '/../../layouts/main.php';
?>

<?php 
if (!isset($annee)) {
    header('Location: ' . BASE_URL . 'admin/annees-universitaires');
    exit();
}

$anneeLibelle = htmlspecialchars($annee['annee_debut'] . ' - ' . $annee['annee_fin']);
include __DIR__ . '/../../includes/header.php'; 
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="h2">Modifier l'année universitaire <?= $anneeLibelle ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="<?= BASE_URL ?>admin/annees-universitaires/<?= $annee['id'] ?>" class="btn btn-sm btn-outline-secondary me-2">
                <i class="fas fa-arrow-left"></i> Retour aux détails
            </a>
            <a href="<?= BASE_URL ?>admin/annees-universitaires" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-list"></i> Liste des années
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
            <h6 class="m-0 font-weight-bold text-primary">Modifier les informations</h6>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>admin/annees-universitaires/<?= $annee['id'] ?>" method="POST">
                <input type="hidden" name="_method" value="PUT">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="annee_debut" class="form-label">Année de début <span class="text-danger">*</span></label>
                        <input type="number" 
                               class="form-control <?= isset($_SESSION['old']['annee_debut']) ? 'is-invalid' : '' ?>" 
                               id="annee_debut" 
                               name="annee_debut" 
                               value="<?= htmlspecialchars($_SESSION['old']['annee_debut'] ?? $annee['annee_debut']) ?>"
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
                               value="<?= htmlspecialchars($_SESSION['old']['annee_fin'] ?? $annee['annee_fin']) ?>"
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
                           <?= (isset($_SESSION['old']['est_active']) ? $_SESSION['old']['est_active'] : $annee['est_active']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="est_active">Définir comme année universitaire active</label>
                    <div class="form-text">L'année active est celle qui sera utilisée par défaut dans le système.</div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="<?= BASE_URL ?>admin/annees-universitaires/<?= $annee['id'] ?>" class="btn btn-secondary me-md-2">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card shadow mb-4 border-danger">
        <div class="card-header py-3 bg-danger text-white">
            <h6 class="m-0 font-weight-bold">Zone dangereuse</h6>
        </div>
        <div class="card-body">
            <h5 class="card-title text-danger">Supprimer cette année universitaire</h5>
            <p class="card-text">
                La suppression d'une année universitaire est une action irréversible. 
                Toutes les données associées (classes, inscriptions, notes, etc.) seront également supprimées.
            </p>
            <p class="card-text">
                <strong>Attention :</strong> Cette action ne peut pas être annulée.
            </p>
            
            <form action="<?= BASE_URL ?>admin/annees-universitaires/<?= $annee['id'] ?>" method="POST" onsubmit="return confirm('Êtes-vous absolument sûr de vouloir supprimer cette année universitaire ? Cette action est irréversible et supprimera toutes les données associées.');">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-exclamation-triangle"></i> Supprimer définitivement
                </button>
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
?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

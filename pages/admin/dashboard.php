<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

requireRole(ROLE_ADMIN);

$pageTitle = "Tableau de bord Admin";
include __DIR__ . '/../../includes/header.php';
?>

<h1 class="mt-4">Tableau de bord Administrateur</h1>
<p>Bienvenue, <?php echo $_SESSION['user_name']; ?> (Admin).</p>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Gestion des classes</h5>
                <p class="card-text">Ajouter, modifier ou supprimer des classes.</p>
                <a href="gestion_classes.php" class="btn btn-primary">Gérer les classes</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Gestion des années universitaires</h5>
                <p class="card-text">Gérer les années et semestres universitaires.</p>
                <a href="gestion_annees.php" class="btn btn-primary">Gérer les années</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Gestion des notes</h5>
                <p class="card-text">Superviser et gérer les notes des étudiants.</p>
                <a href="gestion_notes.php" class="btn btn-primary">Gérer les notes</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Gestion des chefs de classe</h5>
                <p class="card-text">Assigner ou modifier les chefs de classe.</p>
                <a href="gestion_chefs.php" class="btn btn-primary">Gérer les chefs</a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
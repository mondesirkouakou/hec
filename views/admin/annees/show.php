<?php 
if (!isset($annee)) {
    header('Location: ' . BASE_URL . 'admin/annees-universitaires');
    exit();
}

$anneeLibelle = htmlspecialchars($annee['annee_debut'] . ' - ' . $annee['annee_fin']);
include __DIR__ . '/../../includes/header.php'; 
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Année universitaire <?= $anneeLibelle ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="<?= BASE_URL ?>admin/annees-universitaires" class="btn btn-sm btn-outline-secondary me-2">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </a>
            <a href="<?= BASE_URL ?>admin/annees-universitaires/<?= $annee['id'] ?>/modifier" class="btn btn-sm btn-primary me-2">
                <i class="fas fa-edit"></i> Modifier
            </a>
            <form action="<?= BASE_URL ?>admin/annees-universitaires/<?= $annee['id'] ?>/supprimer" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette année universitaire ? Cette action est irréversible.');">
                <button type="submit" class="btn btn-sm btn-danger">
                    <i class="fas fa-trash"></i> Supprimer
                </button>
            </form>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informations générales</h6>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>Année universitaire</th>
                            <td><?= $anneeLibelle ?></td>
                        </tr>
                        <tr>
                            <th>Statut</th>
                            <td>
                                <?php if ($annee['est_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                    <a href="<?= BASE_URL ?>admin/annees-universitaires/<?= $annee['id'] ?>/activer" 
                                       class="btn btn-sm btn-success ms-2"
                                       onclick="return confirm('Êtes-vous sûr de vouloir activer cette année universitaire ?')">
                                        <i class="fas fa-check"></i> Activer
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Date de création</th>
                            <td><?= date('d/m/Y H:i', strtotime($annee['created_at'])) ?></td>
                        </tr>
                        <tr>
                            <th>Dernière mise à jour</th>
                            <td><?= $annee['updated_at'] ? date('d/m/Y H:i', strtotime($annee['updated_at'])) : 'Jamais' ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistiques</h6>
                </div>
                <div class="card-body">
                    <?php
                    // Récupérer les statistiques de l'année universitaire
                    $statsQuery = "
                        SELECT 
                            (SELECT COUNT(*) FROM classes WHERE annee_universitaire_id = :annee_id) as nb_classes,
                            (SELECT COUNT(*) FROM inscriptions i WHERE i.annee_universitaire_id = :annee_id) as nb_etudiants,
                            (SELECT COUNT(DISTINCT ap.professeur_id) 
                             FROM affectation_professeur ap 
                             JOIN classes c ON ap.classe_id = c.id 
                             WHERE c.annee_universitaire_id = :annee_id) as nb_professeurs
                    ";
                    
                    $stats = $this->db->fetch($statsQuery, ['annee_id' => $annee['id']]);
                    ?>
                    
                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title"><?= $stats['nb_classes'] ?? 0 ?></h5>
                                    <p class="card-text">Classes</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title"><?= $stats['nb_etudiants'] ?? 0 ?></h5>
                                    <p class="card-text">Étudiants</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title"><?= $stats['nb_professeurs'] ?? 0 ?></h5>
                                    <p class="card-text">Professeurs</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="<?= BASE_URL ?>admin/classes?annee=<?= $annee['id'] ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-chalkboard"></i> Voir les classes
                        </a>
                        <a href="<?= BASE_URL ?>admin/etudiants?annee=<?= $annee['id'] ?>" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-user-graduate"></i> Voir les étudiants
                        </a>
                        <a href="<?= BASE_URL ?>admin/professeurs?annee=<?= $annee['id'] ?>" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-chalkboard-teacher"></i> Voir les professeurs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Semestres</h6>
            <a href="<?= BASE_URL ?>admin/semestres/nouveau?annee=<?= $annee['id'] ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Ajouter un semestre
                    </a>
                </div>
                <div class="card-body">
                    <?php
                    // Récupérer les semestres de l'année universitaire
                    $semestresQuery = "
                        SELECT * FROM semestres 
                        WHERE annee_universitaire_id = :annee_id 
                        ORDER BY numero
                    ";
                    $semestres = $this->db->fetchAll($semestresQuery, ['annee_id' => $annee['id']]);
                    ?>
                    
                    <?php if (empty($semestres)): ?>
                        <div class="alert alert-info">Aucun semestre n'a été défini pour cette année universitaire.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Semestre</th>
                                        <th>Période</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($semestres as $semestre): ?>
                                        <tr>
                                            <td>Semestre <?= $semestre['numero'] ?></td>
                                            <td>
                                                <?= date('d/m/Y', strtotime($semestre['date_debut'])) ?> 
                                                au 
                                                <?= date('d/m/Y', strtotime($semestre['date_fin'])) ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($semestre['est_cloture'])): ?>
                                                    <span class="badge bg-secondary">Clôturé</span>
                                                <?php elseif (!empty($semestre['est_ouvert'])): ?>
                                                    <span class="badge bg-success">Ouvert</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Non ouvert</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?= BASE_URL ?>admin/semestres/<?= $semestre['id'] ?>" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> Voir
                                                </a>
                                                <a href="<?= BASE_URL ?>admin/semestres/<?= $semestre['id'] ?>/modifier" 
                                                   class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i> Modifier
                                                </a>
                                                <?php if (empty($semestre['est_cloture'])): ?>
                                                    <a href="<?= BASE_URL ?>admin/semestres/<?= $semestre['id'] ?>/cloturer" 
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('Êtes-vous sûr de vouloir clôturer ce semestre ? Cette action est irréversible.')">
                                                        <i class="fas fa-lock"></i> Clôturer
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

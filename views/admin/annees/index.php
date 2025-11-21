<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Gestion des années universitaires</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="<?= BASE_URL ?>admin/annees-universitaires/nouvelle" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Nouvelle année universitaire
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Liste des années universitaires</h6>
        </div>
        <div class="card-body">
            <?php if (empty($annees)): ?>
                <div class="alert alert-info">Aucune année universitaire n'a été créée pour le moment.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Année universitaire</th>
                                <th>Statut</th>
                                <th>Date de création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($annees as $annee): ?>
                                <tr>
                                    <td><?= htmlspecialchars($annee['annee_debut']) ?> - <?= htmlspecialchars($annee['annee_fin']) ?></td>
                                    <td>
                                        <?php if ($annee['est_active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($annee['created_at'])) ?></td>
                                    <td>
                                        <?php if (!$annee['est_active']): ?>
                                            <a href="<?= BASE_URL ?>admin/annees-universitaires/<?= $annee['id'] ?>/activer" 
                                               class="btn btn-sm btn-success"
                                               onclick="return confirm('Êtes-vous sûr de vouloir activer cette année universitaire ?')">
                                                <i class="fas fa-check"></i> Activer
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="<?= BASE_URL ?>admin/annees-universitaires/<?= $annee['id'] ?>" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> Voir
                                        </a>
                                        
                                        <a href="<?= BASE_URL ?>admin/annees-universitaires/<?= $annee['id'] ?>/modifier" 
                                           class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Modifier
                                        </a>
                                        
                                        <form action="<?= BASE_URL ?>admin/annees-universitaires/<?= $annee['id'] ?>/supprimer" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette année universitaire ?')">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (!empty($anneeActive)): ?>
        <div class="alert alert-info">
            <strong>Année universitaire active :</strong> 
            <?= htmlspecialchars($anneeActive['annee_debut']) ?> - <?= htmlspecialchars($anneeActive['annee_fin']) ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

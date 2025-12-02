<?php
$pageTitle = 'Tableau de bord Administrateur';
ob_start();
?>

<div class="container-fluid admin-dashboard">
    <div class="dashboard-header animated-header">
        <h1 class="dashboard-title">Tableau de bord Administrateur</h1>
        <div class="header-actions">
            <select id="anneeUniversitaire" class="form-control form-control-animated mr-2">
                <option value="2025-2026" selected>2025-2026</option>
                <option value="2024-2025">2024-2025</option>
            </select>
            <button class="btn btn-primary ripple-effect explosive-zoom">
                <i class="fas fa-plus"></i> Nouvelle année
            </button>
        </div>
    </div>

    <!-- Cartes de statistiques -->
    <div class="row dashboard-row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card animated-card info-card rotate-3d magnetic-effect">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Classes actives</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">12</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-school fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card animated-card success-card rotate-3d magnetic-effect">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Étudiants inscrits</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">1,245</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card animated-card info-card rotate-3d magnetic-effect">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Enseignants
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">58</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card animated-card warning-card rotate-3d magnetic-effect">
                <div class="card-body">
                    <a href="<?= BASE_URL ?>admin/classes?statut_listes=en_attente" class="text-reset text-decoration-none d-block">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Listes de classes en attente</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= isset($nbListesEnAttente) ? (int)$nbListesEnAttente : 0 ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="row dashboard-row">
        <!-- Liste des actions rapides -->
        <div class="col-lg-6 mb-4">
            <div class="card animated-card actions-card rotate-3d">
                <div class="card-header card-header-primary">
                    <h6 class="card-title">Actions rapides</h6>
                </div>
                <div class="card-body card-body-animated">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <a href="<?= BASE_URL ?>admin/annees-universitaires/nouvelle" class="btn btn-light btn-block text-left p-3 border ripple-effect magnetic-effect">
                                <i class="fas fa-calendar-plus text-primary mr-2"></i>
                                Créer une année universitaire
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="<?= BASE_URL ?>admin/classes/nouvelle" class="btn btn-light btn-block text-left p-3 border ripple-effect magnetic-effect">
                                <i class="fas fa-plus-circle text-success mr-2"></i>
                                Ajouter une classe
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="<?= BASE_URL ?>admin/classes" class="btn btn-light btn-block text-left p-3 border ripple-effect magnetic-effect">
                                <i class="fas fa-user-plus text-info mr-2"></i>
                                Gérer les classes
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="<?= BASE_URL ?>admin/chefs-classe" class="btn btn-light btn-block text-left p-3 border ripple-effect magnetic-effect">
                                <i class="fas fa-user-tie text-warning mr-2"></i>
                                Gérer chef de classe
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="<?= BASE_URL ?>admin/notes/validation" class="btn btn-light btn-block text-left p-3 border">
                                <i class="fas fa-check text-success mr-2"></i>
                                Valider des notes
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="<?= BASE_URL ?>admin/notes/saisie" class="btn btn-light btn-block text-left p-3 border">
                                <i class="fas fa-pen text-primary mr-2"></i>
                                Saisir des notes
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="<?= BASE_URL ?>admin/semestres" class="btn btn-light btn-block text-left p-3 border">
                                <i class="fas fa-lock-open text-warning mr-2"></i>
                                Gestion des semestres
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dernières activités -->
        <div class="col-lg-6 mb-4">
            <div class="card animated-card activities-card rotate-3d">
                <div class="card-header card-header-accent d-flex justify-content-between align-items-center">
                    <h6 class="card-title">Activités récentes</h6>
                    <a href="<?= BASE_URL ?>admin/activites" class="btn btn-sm btn-link ripple-effect">Voir tout</a>
                </div>
                <div class="card-body card-body-animated">
                    <div class="activity-feed">
                        <div class="feed-item d-flex mb-3 animated-item">
                            <div class="feed-icon bg-primary text-white rounded-circle p-2 mr-3 ripple-effect">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="feed-content">
                                <p class="mb-1"><strong>Nouveau chef de classe</strong> ajouté pour IDA1</p>
                                <small class="text-muted">Il y a 2 heures</small>
                            </div>
                        </div>
                        <div class="feed-item d-flex mb-3 animated-item">
                            <div class="feed-icon bg-success text-white rounded-circle p-2 mr-3 ripple-effect">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="feed-content">
                                <p class="mb-1">Liste des étudiants de <strong>IDA2</strong> validée</p>
                                <small class="text-muted">Aujourd'hui, 10:45</small>
                            </div>
                        </div>
                        <div class="feed-item d-flex animated-item">
                            <div class="feed-icon bg-warning text-white rounded-circle p-2 mr-3 ripple-effect">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="feed-content">
                                <p class="mb-1">3 listes en attente de validation</p>
                                <small class="text-muted">Hier, 16:30</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des classes -->
    <div class="card animated-card classes-card">
        <div class="card-header card-header-secondary d-flex justify-content-between align-items-center">
            <h6 class="card-title">Liste des classes (2025-2026)</h6>
            <div>
                <button class="btn btn-sm btn-primary ripple-effect explosive-zoom">
                    <i class="fas fa-download fa-sm"></i> Exporter
                </button>
            </div>
        </div>
        <div class="card-body card-body-animated">
            <div class="table-responsive">
                <table class="table table-bordered animated-table" id="dataTable" width="100%" cellspacing="0">
                    <thead class="table-header-primary">
                        <tr>
                            <th>Classe</th>
                            <th>Chef de classe</th>
                            <th>Étudiants</th>
                            <th>Statut liste</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>IDA1</td>
                            <td>N'Guessan Auguste</td>
                            <td>45</td>
                            <td><span class="badge badge-success">Validée</span></td>
                            <td>
                                <a href="<?= BASE_URL ?>admin/classes/IDA1" class="btn btn-sm btn-info ripple-effect">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= BASE_URL ?>admin/notes/validation" class="btn btn-sm btn-success ripple-effect">
                                    <i class="fas fa-check"></i> Valider
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>IDA2</td>
                            <td>Kouassi Amani</td>
                            <td>42</td>
                            <td><span class="badge badge-success">Validée</span></td>
                            <td>
                                <a href="<?= BASE_URL ?>admin/classes/IDA2" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= BASE_URL ?>admin/notes/validation" class="btn btn-sm btn-success">
                                    <i class="fas fa-check"></i> Valider
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>IDA3</td>
                            <td>Yao Kouamé</td>
                            <td>38</td>
                            <td><span class="badge badge-warning">En attente</span></td>
                            <td>
                                <a href="<?= BASE_URL ?>admin/classes/IDA3" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button class="btn btn-sm btn-secondary" disabled>
                                    <i class="fas fa-check"></i> Valider
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nouvelle année universitaire -->
<div class="modal fade" id="newYearModal" tabindex="-1" role="dialog" aria-labelledby="newYearModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newYearModalLabel">Nouvelle année universitaire</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="newYearForm">
                    <div class="form-group">
                        <label for="anneeDebut">Année de début</label>
                        <select class="form-control" id="anneeDebut" required>
                            <option value="">Sélectionner...</option>
                            <option value="2025">2025</option>
                            <option value="2026">2026</option>
                            <option value="2027">2027</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="anneeFin">Année de fin</label>
                        <input type="text" class="form-control" id="anneeFin" readonly>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="copyPreviousYear">
                        <label class="form-check-label" for="copyPreviousYear">
                            Copier la structure de l'année précédente
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Annuler</button>
                <a class="btn btn-primary" href="login.html">Créer l'année</a>
            </div>
        </div>
    </div>
</div>

<script>
// Script pour gérer le formulaire de nouvelle année
document.addEventListener('DOMContentLoaded', function() {
    // Mise à jour automatique de l'année de fin
    const anneeDebut = document.getElementById('anneeDebut');
    const anneeFin = document.getElementById('anneeFin');
    
    if (anneeDebut && anneeFin) {
        anneeDebut.addEventListener('change', function() {
            if (this.value) {
                anneeFin.value = parseInt(this.value) + 1;
            } else {
                anneeFin.value = '';
            }
        });
    }

    // Initialisation de DataTables
    if ($.fn.DataTable.isDataTable('#dataTable')) {
        $('#dataTable').DataTable().destroy();
    }
    $('#dataTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.20/i18n/French.json'
        },
        responsive: true
    });
});
</script>

<style>
.activity-feed .feed-item {
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.activity-feed .feed-item:last-child {
    border-bottom: none;
}

.feed-icon {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-light {
    background-color: #f8f9fc;
    border: 1px solid #e3e6f0;
    transition: all 0.3s;
}

.btn-light:hover {
    transform: translateY(-3px) scale(1.05);
    background: linear-gradient(45deg, var(--accent-color), var(--primary-color));
    border: 2px solid var(--white);
    color: var(--white);
}
.btn-light:hover::before {
    animation: none !important;
    opacity: 0 !important;
}
.btn-light.magnetic-effect:hover {
    filter: none !important;
}
.btn-light:hover i,
.btn-light:hover .text-primary,
.btn-light:hover .text-success,
.btn-light:hover .text-info,
.btn-light:hover span {
    color: var(--white) !important;
}

.card {
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    margin-bottom: 1.5rem;
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.text-xs {
    font-size: 0.7rem;
}

.text-gray-300 {
    color: #dddfeb !important;
}

.h5 {
    font-size: 1.25rem;
}

.h3 {
    font-size: 1.75rem;
}
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>
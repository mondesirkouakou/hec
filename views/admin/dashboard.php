<?php
$pageTitle = 'Tableau de bord Administrateur';
ob_start();
?>

<div class="container-fluid admin-dashboard">
    <div class="dashboard-header animated-header mb-3" style="background: #ffffff !important;">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h1 class="dashboard-title mb-0" style="color: #0752dd !important;">Tableau de bord Administrateur</h1>
        </div>

        <!-- Années universitaires en boutons (timeline continue) -->
        <div class="d-flex flex-nowrap align-items-center mb-2 year-strip overflow-auto">
            <?php
            $currentYear = (int)date('Y');
            $yearsSource = !empty($anneeTimeline) ? $anneeTimeline : (array)$annees;
            if (!empty($yearsSource)):
                foreach ($yearsSource as $item):
                    // Si on vient directement de $annees (fallback), on normalise
                    if (isset($item['annee_debut']) && isset($item['annee_fin']) && !isset($item['exists'])) {
                        $item = [
                            'annee_debut' => $item['annee_debut'],
                            'annee_fin'   => $item['annee_fin'],
                            'exists'      => true,
                            'record'      => $item,
                        ];
                    }

                    $debut = (int)$item['annee_debut'];
                    $fin   = (int)$item['annee_fin'];
                    $label = $debut . '-' . $fin;
                    $exists = !empty($item['exists']);
                    $record = $exists ? $item['record'] : null;

                    $activeDebut = isset($anneeActive['annee_debut']) ? (int)$anneeActive['annee_debut'] : null;
                    $isActive = $exists && isset($anneeActive['id']) && (int)$anneeActive['id'] === (int)$record['id'];
                    $isSelected = $exists && isset($selectedAnneeId) && (int)$selectedAnneeId === (int)$record['id'];

                    // Couleur selon la position par rapport à l'année active
                    if ($isActive) {
                        $btnClass = 'btn-success'; // année en cours (active)
                    } elseif ($activeDebut !== null) {
                        if ($fin <= $activeDebut) {
                            $btnClass = 'btn-danger'; // années déjà passées/fermées
                        } elseif ($debut > $activeDebut) {
                            $btnClass = 'btn-info';   // années futures (non encore ouvertes)
                        } else {
                            $btnClass = 'btn-secondary';
                        }
                    } else {
                        // Fallback si aucune année active définie : basé sur l'année courante
                        if ($fin < $currentYear) {
                            $btnClass = 'btn-danger';
                        } elseif ($debut > $currentYear) {
                            $btnClass = 'btn-info';
                        } else {
                            $btnClass = 'btn-secondary';
                        }
                    }

                    if ($isSelected) {
                        $btnClass .= ' active';
                    }

                    if ($exists && isset($record['id'])) {
                        // Année réellement présente en base : sélectionnable
                        $href = BASE_URL . 'admin/dashboard?annee_id=' . (int)$record['id'];
                    } else {
                        // Année future non encore créée : affichée mais non cliquable depuis le dashboard
                        $href = '#';
                        $btnClass .= ' disabled-year';
                    }
            ?>
                    <a href="<?= htmlspecialchars($href) ?>" class="btn btn-sm <?= $btnClass ?> mr-2 year-pill">
                        <?= htmlspecialchars($label) ?>
                    </a>
            <?php
                endforeach;
            endif;
            ?>
        </div>

        <!-- Semestres en boutons pour l'année sélectionnée -->
        <div class="d-flex flex-wrap align-items-center">
            <?php if (!empty($semestresAnnee)): ?>
                <?php foreach ($semestresAnnee as $sem):
                    $isSemActive = ($sem['est_ouvert'] ?? 0) == 1;
                    $isSemClosed = ($sem['est_cloture'] ?? 0) == 1;
                    $isSelectedSem = isset($selectedSemestreId) && (int)$selectedSemestreId === (int)$sem['id'];

                    $btnClass = 'btn-sm mr-2 mb-2 ';
                    $icon = '';

                    if ($isSemActive) {
                        $btnClass .= 'btn-primary'; // semestre en cours
                    } elseif ($isSemClosed) {
                        $btnClass .= 'btn-danger';
                        $icon = '<i class="fas fa-lock"></i> ';
                    } else {
                        // Semestre fermé / non ouvert
                        $btnClass .= 'btn-danger';
                        $icon = '<i class="fas fa-lock-open"></i> ';
                    }

                    if ($isSelectedSem) {
                        $btnClass .= ' active';
                    }
                ?>
                    <a href="<?= BASE_URL ?>admin/dashboard?annee_id=<?= (int)$sem['annee_universitaire_id'] ?>&semestre_id=<?= (int)$sem['id'] ?>" class="btn <?= $btnClass ?> sem-pill">
                        <?= $icon ?>Semestre <?= (int)$sem['numero'] ?>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <span class="text-muted">Aucun semestre défini pour cette année.</span>
            <?php endif; ?>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($statsYear['classes_actives']) ? (int)$statsYear['classes_actives'] : 0 ?>
                            </div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($statsYear['etudiants_inscrits'])
                                    ? number_format((int)$statsYear['etudiants_inscrits'], 0, ',', ' ')
                                    : '0' ?>
                            </div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($statsYear['enseignants']) ? (int)$statsYear['enseignants'] : 0 ?>
                            </div>
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
                    <?php
                    $isYearClosed = !empty($isSelectedAnneeCloturee);
                    $pendingHref = BASE_URL . 'admin/classes?statut_listes=en_attente';
                    ?>
                    <a href="<?= $isYearClosed ? '#' : $pendingHref ?>"
                       class="text-reset text-decoration-none d-block<?= $isYearClosed ? ' disabled' : '' ?>"
                       <?= $isYearClosed ? 'aria-disabled="true" tabindex="-1" onclick="return false;"' : '' ?>>
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
        <div class="col-lg-12 mb-4">

            <div class="card animated-card actions-card rotate-3d">
                <div class="card-header card-header-primary">
                    <h6 class="card-title">Actions rapides</h6>
                </div>
                <div class="card-body card-body-animated">
                    <div class="row">
                        <div class="col-sm-6 col-md-4 col-lg-3 mb-3">
                            <a href="<?= $isYearClosed ? '#' : (BASE_URL . 'admin/classes/nouvelle') ?>" class="btn btn-light btn-block text-left p-3 border ripple-effect magnetic-effect<?= $isYearClosed ? ' disabled' : '' ?>" <?= $isYearClosed ? 'aria-disabled="true" tabindex="-1" onclick="return false;"' : '' ?>>
                                <i class="fas fa-plus-circle text-success mr-2"></i>
                                Ajouter une classe
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-3 mb-3">
                            <a href="<?= $isYearClosed ? '#' : (BASE_URL . 'admin/classes') ?>" class="btn btn-light btn-block text-left p-3 border ripple-effect magnetic-effect<?= $isYearClosed ? ' disabled' : '' ?>" <?= $isYearClosed ? 'aria-disabled="true" tabindex="-1" onclick="return false;"' : '' ?>>
                                <i class="fas fa-user-plus text-info mr-2"></i>
                                Gérer les classes
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-3 mb-3">
                            <a href="<?= $isYearClosed ? '#' : (BASE_URL . 'admin/chefs-classe') ?>" class="btn btn-light btn-block text-left p-3 border ripple-effect magnetic-effect<?= $isYearClosed ? ' disabled' : '' ?>" <?= $isYearClosed ? 'aria-disabled="true" tabindex="-1" onclick="return false;"' : '' ?>>
                                <i class="fas fa-user-tie text-warning mr-2"></i>
                                Gérer chef de classe
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-3 mb-3">
                            <a href="<?= $isYearClosed ? '#' : (BASE_URL . 'admin/notes/saisie') ?>" class="btn btn-light btn-block text-left p-3 border<?= $isYearClosed ? ' disabled' : '' ?>" <?= $isYearClosed ? 'aria-disabled="true" tabindex="-1" onclick="return false;"' : '' ?>>
                                <i class="fas fa-pen text-primary mr-2"></i>
                                Saisir des notes
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-3 mb-3">
                            <a href="<?= $isYearClosed ? '#' : (BASE_URL . 'admin/semestres') ?>" class="btn btn-light btn-block text-left p-3 border<?= $isYearClosed ? ' disabled' : '' ?>" <?= $isYearClosed ? 'aria-disabled="true" tabindex="-1" onclick="return false;"' : '' ?>>
                                <i class="fas fa-lock-open text-warning mr-2"></i>
                                Gestion des semestres
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des classes -->
    <div class="card animated-card classes-card">
        <div class="card-header card-header-secondary">
            <?php
                $labelAnneeClasses = '—';
                if (!empty($selectedAnnee)) {
                    $labelAnneeClasses = ($selectedAnnee['annee_debut'] ?? '') . '-' . ($selectedAnnee['annee_fin'] ?? '');
                } elseif (!empty($anneeActive)) {
                    $labelAnneeClasses = ($anneeActive['annee_debut'] ?? '') . '-' . ($anneeActive['annee_fin'] ?? '');
                }
            ?>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                <h6 class="card-title mb-2 mb-md-0">Liste des classes (<?= htmlspecialchars($labelAnneeClasses) ?>)</h6>
                <form class="w-100 w-md-auto" method="get" action="<?= BASE_URL ?>admin/dashboard">
                    <?php if (!empty($selectedAnneeId)): ?>
                        <input type="hidden" name="annee_id" value="<?= (int)$selectedAnneeId ?>">
                    <?php endif; ?>
                    <?php if (!empty($selectedSemestreId)): ?>
                        <input type="hidden" name="semestre_id" value="<?= (int)$selectedSemestreId ?>">
                    <?php endif; ?>
                    <div class="input-group input-group-sm mx-auto" style="max-width:360px;">
                        <input type="text" name="search_matricule" class="form-control" placeholder="Matricule étudiant..." value="<?= htmlspecialchars($searchMatricule ?? '') ?>">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-outline-secondary"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-body card-body-animated">
            <?php if (!empty($searchMatricule)): ?>
                <div class="mb-3">
                    <?php if (!empty($etudiantRecherche)): ?>
                        <div class="alert alert-success small mb-0 d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Étudiant trouvé :</strong>
                                <?= htmlspecialchars($etudiantRecherche['matricule'] ?? '') ?> —
                                <?= htmlspecialchars(($etudiantRecherche['prenom'] ?? '') . ' ' . ($etudiantRecherche['nom'] ?? '')) ?>
                                <?php if (!empty($etudiantRecherche['classe_intitule'])): ?>
                                    (<span class="text-muted">Classe <?= htmlspecialchars($etudiantRecherche['classe_intitule']) ?></span>)
                                <?php endif; ?>
                            </div>
                            <div>
                                <?php
                                $bulletinHref = BASE_URL . 'admin/etudiants/' . (int)($etudiantRecherche['id'] ?? 0) . '/bulletin';
                                $bulletinParams = [];
                                if (!empty($selectedAnneeId)) { $bulletinParams[] = 'annee_id=' . (int)$selectedAnneeId; }
                                if (!empty($selectedSemestreId)) { $bulletinParams[] = 'semestre_id=' . (int)$selectedSemestreId; }
                                if (!empty($bulletinParams)) { $bulletinHref .= '?' . implode('&', $bulletinParams); }
                                ?>
                                <a href="<?= htmlspecialchars($bulletinHref) ?>" class="btn btn-sm btn-outline-primary">Voir le bulletin</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning small mb-0">Aucun étudiant trouvé pour le matricule "<?= htmlspecialchars($searchMatricule) ?>".</div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-bordered animated-table" id="dataTable" width="100%" cellspacing="0">
                    <thead class="table-header-primary">
                        <tr>
                            <th>Classe</th>
                            <th>Étudiants</th>
                            <th>Statut liste</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($classesDashboard)): ?>
                        <?php foreach ($classesDashboard as $classe): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($classe['intitule'] ?? '') ?></strong>
                                    <?php if (!empty($classe['code'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($classe['code']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($classe['statut_listes']) && in_array($classe['statut_listes'], ['en_attente', 'validee'], true)): ?>
                                        <?= (int)($classe['effectif'] ?? 0) ?> étudiants
                                    <?php else: ?>
                                        <span class="text-muted">Non soumises</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php $statutListes = $classe['statut_listes'] ?? null; ?>
                                    <?php if ($statutListes === 'validee'): ?>
                                        <span class="badge badge-success">Validée</span>
                                    <?php elseif ($statutListes === 'en_attente'): ?>
                                        <span class="badge badge-warning">En attente</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Non soumise</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>admin/classes/<?= (int)$classe['id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">Aucune classe pour l'année active.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Gestion des semestres (ouverture / fermeture directement depuis le dashboard) -->
    <div class="card animated-card mt-4">
        <div class="card-header card-header-secondary d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0">Gestion des semestres de l'année sélectionnée</h6>
            <?php if (!empty($semestreActif)): ?>
                <span class="badge badge-primary">Semestre actif : Semestre <?= (int)$semestreActif['numero'] ?></span>
            <?php else: ?>
                <span class="badge badge-secondary">Aucun semestre actif</span>
            <?php endif; ?>
        </div>
        <div class="card-body card-body-animated">
            <?php if (!empty($semestresAnnee)): ?>
                <div class="row">
                    <?php foreach ($semestresAnnee as $sem): ?>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex justify-content-between align-items-center border rounded p-2">
                                <div>
                                    <div><strong>Semestre <?= (int)$sem['numero'] ?></strong></div>
                                    <div class="small text-muted">
                                        <?php if (($sem['est_ouvert'] ?? 0) == 1): ?>
                                            Actuellement <span class="text-primary">ouvert</span>
                                        <?php elseif (($sem['est_cloture'] ?? 0) == 1): ?>
                                            <span class="text-danger">Clôturé</span>
                                        <?php else: ?>
                                            <span class="text-muted">Non ouvert</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <?php if (($sem['est_ouvert'] ?? 0) == 1): ?>
                                        <!-- Bouton pour fermer le semestre ouvert -->
                                        <a href="<?= BASE_URL ?>admin/semestres/<?= (int)$sem['id'] ?>/cloturer" class="btn btn-sm btn-danger" onclick="return confirm('Clôturer ce semestre ?');">
                                            <i class="fas fa-lock"></i> Clôturer
                                        </a>
                                    <?php elseif (($sem['est_cloture'] ?? 0) == 1): ?>
                                        <!-- Semestre déjà clôturé : pas de réouverture depuis le dashboard -->
                                        <button class="btn btn-sm btn-outline-secondary" disabled>
                                            <i class="fas fa-check"></i> Clôturé
                                        </button>
                                    <?php else: ?>
                                        <!-- Bouton pour ouvrir un semestre non encore ouvert -->
                                        <a href="<?= BASE_URL ?>admin/semestres/<?= (int)$sem['id'] ?>/activer" class="btn btn-sm btn-success" onclick="return confirm('Ouvrir ce semestre ?');">
                                            <i class="fas fa-unlock"></i> Ouvrir
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0">Aucun semestre n'est défini pour l'année sélectionnée. Utilisez la gestion des semestres pour les créer.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($tousSemestresClotures) && !empty($selectedAnneeId)): ?>
    <!-- Modal clôture d'année universitaire et création de la suivante -->
    <div class="modal fade" id="closeYearModal" tabindex="-1" role="dialog" aria-labelledby="closeYearModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="closeYearModalLabel">Clôturer l'année universitaire</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Fermer">
                        <span aria-hidden="true">×</span>
                <h5 class="modal-title" id="closeYearModalLabel">Clôturer l'année universitaire</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Fermer">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Les deux semestres de l'année sélectionnée sont <strong>clôturés</strong>.<br>
                    En confirmant, vous allez :
                </p>
                <ul>
                    <li>Clôturer l'année universitaire actuelle ;</li>
                    <li>Ouvrir automatiquement la nouvelle année (avec les classes et leurs matières reconduites, sans étudiants) ;</li>
                    <li>Réinitialiser les chefs de classe pour la nouvelle année.</li>
                </ul>
                <p class="mb-0">Souhaitez-vous continuer&nbsp;?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Annuler</button>
                <form method="POST" action="<?= BASE_URL ?>admin/annees-universitaires/<?= (int)$selectedAnneeId ?>/cloturer-et-creer" class="d-inline">
                    <button type="submit" class="btn btn-primary">
                        Confirmer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Tentative affichage modal clôture année...');
    // Essayer avec jQuery/Bootstrap
    if (typeof $ !== 'undefined' && typeof $.fn.modal !== 'undefined') {
        console.log('jQuery + Bootstrap modal disponibles');
        $('#closeYearModal').modal('show');
    } else {
        console.log('jQuery/Bootstrap non disponibles, affichage natif');
        // Fallback : afficher le modal manuellement
        var modal = document.getElementById('closeYearModal');
        if (modal) {
            modal.classList.add('show');
            modal.style.display = 'block';
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open');
            // Ajouter le backdrop
            var backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
        }
    }
});
</script>
<?php endif; ?>

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
.year-pill,
.sem-pill {
    border-radius: 999px;
    font-weight: 600;
    letter-spacing: 0.02em;
    box-shadow: 0 2px 6px rgba(0,0,0,0.12);
    padding-left: 16px;
    padding-right: 16px;
    white-space: nowrap;
}

.disabled-year {
    opacity: 0.6;
    cursor: default;
    pointer-events: none;
}

.year-pill.active,
.sem-pill.active {
    box-shadow: 0 0 0 2px rgba(255,255,255,0.9), 0 4px 10px rgba(0,0,0,0.2);
    transform: translateY(-1px);
}

/* Semestre sélectionné - border bleu */
.sem-pill.active {
    border: 3px solid #0752dd !important;
    box-shadow: 0 0 0 3px rgba(7, 82, 221, 0.25), 0 4px 10px rgba(0,0,0,0.2) !important;
    transform: translateY(-1px);
}

/* Année clôturée sélectionnée - border rouge */
.year-pill.btn-danger.active {
    border: 3px solid #ef4444 !important;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.25), 0 4px 10px rgba(0,0,0,0.2) !important;
}

.year-pill:hover,
.sem-pill:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.18);
}

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
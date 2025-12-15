<?php
$pageTitle = 'Tableau de bord étudiant';
ob_start();
?>

<div class="dashboard-header">
    <div class="welcome-message">
        <p class="text-muted">
            <?php
            $dateFr = null;
            if (class_exists('IntlDateFormatter')) {
                $fmt = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, date_default_timezone_get(), IntlDateFormatter::GREGORIAN, 'EEEE d MMMM y');
                $dateFr = $fmt->format(new DateTime());
            }
            if (!$dateFr) {
                $oldLocale = setlocale(LC_TIME, '0');
                setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'French_France.1252');
                $dateFr = utf8_encode(strftime('%A %e %B %Y'));
                if ($oldLocale) {
                    setlocale(LC_TIME, $oldLocale);
                }
            }
            ?>
            <i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($dateFr) ?>
            <span class="mx-2">•</span>
            <i class="fas fa-user-graduate"></i> <?= htmlspecialchars($classe['intitule'] ?? 'Non affecté') ?>
        </p>
    </div>
    <div class="quick-actions">
        <a href="<?= BASE_URL ?>etudiant/notes" class="btn btn-primary">
            <i class="fas fa-chart-line"></i> Mes notes
        </a>
    </div>
</div>

<!-- Sélecteur Année / Semestre / Session -->
<div class="student-period-selector mb-3">
    <!-- Années universitaires (timeline continue) -->
    <div class="d-flex flex-nowrap align-items-center mb-2 year-strip overflow-auto">
        <?php
        $currentYear = (int)date('Y');
        $yearsSource = !empty($anneeTimeline) ? $anneeTimeline : (array)$annees;
        if (!empty($yearsSource)):
            foreach ($yearsSource as $item):
                // Normaliser si nécessaire
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

                // Couleur par rapport à l'année active (même logique que l'admin)
                if ($isActive) {
                    $btnClass = 'btn-success';
                } elseif ($activeDebut !== null) {
                    if ($fin <= $activeDebut) {
                        $btnClass = 'btn-danger';
                    } elseif ($debut > $activeDebut) {
                        $btnClass = 'btn-info';
                    } else {
                        $btnClass = 'btn-secondary';
                    }
                } else {
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
                    $url = BASE_URL . 'etudiant/dashboard?annee_id=' . (int)$record['id'];
                } else {
                    // Année future non créée : visible mais non cliquable
                    $url = '#';
                    $btnClass .= ' disabled-year';
                }
        ?>
                <a href="<?= htmlspecialchars($url) ?>" class="btn btn-sm <?= $btnClass ?> mr-2 year-pill">
                    <?= htmlspecialchars($label) ?>
                </a>
        <?php
            endforeach;
        endif;
        ?>
    </div>

    <!-- Semestres -->
    <div class="d-flex flex-wrap align-items-center mb-2">
        <?php if (!empty($semestresAnnee)): ?>
            <?php foreach ($semestresAnnee as $sem):
                $isSemActive = ($sem['est_ouvert'] ?? 0) == 1;
                $isSemClosed = ($sem['est_cloture'] ?? 0) == 1;
                $isSelectedSem = isset($selectedSemestreId) && (int)$selectedSemestreId === (int)$sem['id'];

                $btnClass = 'btn-sm mr-2 mb-2 ';
                $icon = '';

                if ($isSemActive) {
                    $btnClass .= 'btn-primary';
                } elseif ($isSemClosed) {
                    $btnClass .= 'btn-danger';
                    $icon = '<i class="fas fa-lock"></i> ';
                } else {
                    $btnClass .= 'btn-danger';
                    $icon = '<i class="fas fa-lock-open"></i> ';
                }

                if ($isSelectedSem) {
                    $btnClass .= ' active';
                }

                $url = BASE_URL . 'etudiant/dashboard?annee_id=' . (int)$sem['annee_universitaire_id'] . '&semestre_id=' . (int)$sem['id'];
            ?>
                <a href="<?= $url ?>" class="btn <?= $btnClass ?> sem-pill">
                    <?= $icon ?>Semestre <?= (int)$sem['numero'] ?>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <span class="text-muted">Aucun semestre défini pour cette année.</span>
        <?php endif; ?>
    </div>

    <!-- Sessions -->
    <div class="d-flex flex-wrap align-items-center">
        <?php $selectedSession = $selectedSession ?? 1; ?>
        <?php for ($i = 1; $i <= 4; $i++): ?>
            <?php
            $btnClass = 'btn btn-sm ' . ($selectedSession === $i ? 'btn-outline-dark active session-pill' : 'btn-outline-dark session-pill');
            $url = BASE_URL . 'etudiant/dashboard';
            $params = [];
            if (!empty($selectedAnneeId)) { $params[] = 'annee_id=' . (int)$selectedAnneeId; }
            if (!empty($selectedSemestreId)) { $params[] = 'semestre_id=' . (int)$selectedSemestreId; }
            $params[] = 'session=' . $i;
            if (!empty($params)) { $url .= '?' . implode('&', $params); }
            ?>
            <a href="<?= $url ?>" class="<?= $btnClass ?> mr-2 mb-2">Session <?= $i ?></a>
        <?php endfor; ?>
    </div>
</div>

<div class="dashboard-cards">
    <!-- Carte Mes notes de classe -->
    <div class="dashboard-card" data-bs-toggle="modal" data-bs-target="#notesClasseModal" style="cursor: pointer;">
        <div class="card-icon bg-primary">
            <i class="fas fa-book-open"></i>
        </div>
        <div class="card-content">
            <h3>Mes notes de classe</h3>
            <?php if (!empty($notes_par_matiere)): ?>
                <div class="table-responsive mt-3">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Matière</th>
                                <th>Note 1</th>
                                <th>Note 2</th>
                                <th>Note 3</th>
                                <th>Note 4</th>
                                <th>Note 5</th>
                                <th>Moyenne</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($notes_par_matiere as $m): ?>
                                <tr>
                                    <td><?= htmlspecialchars($m['nom']) ?></td>
                                    <td>
                                        <?php if ($m['note1'] !== null): ?>
                                            <?= number_format($m['note1'], 2, ',', ' ') ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($m['note2'] !== null): ?>
                                            <?= number_format($m['note2'], 2, ',', ' ') ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($m['note3'] !== null): ?>
                                            <?= number_format($m['note3'], 2, ',', ' ') ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($m['note4'] !== null): ?>
                                            <?= number_format($m['note4'], 2, ',', ' ') ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($m['note5'] !== null): ?>
                                            <?= number_format($m['note5'], 2, ',', ' ') ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= number_format($m['moyenne'] ?? 0, 2, ',', ' ') ?></strong> / 20
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted mt-3">Aucune note enregistrée pour le moment.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Carte Bulletin -->
    <div class="dashboard-card">
        <div class="card-icon bg-info">
            <i class="fas fa-file-alt"></i>
        </div>
        <div class="card-content">
            <h3>Bulletin</h3>
            <p class="text-muted mb-2">
                Consultez et imprimez votre relevé de notes officiel.
            </p>
            <?php if (!empty($notes)): ?>
                <?php
                $bulletinUrl = BASE_URL . 'etudiant/bulletin';
                $params = [];
                if (!empty($selectedSemestreId)) { $params[] = 'semestre_id=' . (int)$selectedSemestreId; }
                if (!empty($selectedSession)) { $params[] = 'session=' . (int)$selectedSession; }
                if (!empty($params)) { $bulletinUrl .= '?' . implode('&', $params); }
                ?>
                <a href="<?= $bulletinUrl ?>" class="btn btn-outline-primary btn-sm mt-1">
                    <i class="fas fa-file-alt"></i> Voir mon bulletin
                </a>
            <?php else: ?>
                <p class="text-muted small mb-0">
                    Aucune note enregistrée pour cette session.
                </p>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Modal grand tableau des notes de classe -->
<div class="modal fade" id="notesClasseModal" tabindex="-1" aria-labelledby="notesClasseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notesClasseModalLabel">Mes notes de classe - Détail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <?php if (!empty($notes_par_matiere)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Matière</th>
                                    <th>Note 1</th>
                                    <th>Note 2</th>
                                    <th>Note 3</th>
                                    <th>Note 4</th>
                                    <th>Note 5</th>
                                    <th>Moyenne / 20</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($notes_par_matiere as $m): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($m['nom']) ?></td>
                                        <td>
                                            <?php if ($m['note1'] !== null): ?>
                                                <?= number_format($m['note1'], 2, ',', ' ') ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($m['note2'] !== null): ?>
                                                <?= number_format($m['note2'], 2, ',', ' ') ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($m['note3'] !== null): ?>
                                                <?= number_format($m['note3'], 2, ',', ' ') ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($m['note4'] !== null): ?>
                                                <?= number_format($m['note4'], 2, ',', ' ') ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($m['note5'] !== null): ?>
                                                <?= number_format($m['note5'], 2, ',', ' ') ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?= number_format($m['moyenne'] ?? 0, 2, ',', ' ') ?></strong>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">Aucune note enregistrée pour le moment.</p>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <a href="<?= BASE_URL ?>etudiant/notes" class="btn btn-primary">
                    <i class="fas fa-chart-line"></i> Aller à la page Mes notes
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles spécifiques au tableau de bord */
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.welcome-message h1 {
    margin: 0;
    font-size: 1.8rem;
    color: var(--primary-color);
}

.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.dashboard-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.card-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    color: white;
    font-size: 1.5rem;
}

.average-grade {
    font-size: 2.5rem;
    font-weight: bold;
    margin: 0.5rem 0;
    display: flex;
    align-items: baseline;
    gap: 0.5rem;
}

.grade-max {
    font-size: 1.2rem;
    color: #6c757d;
    font-weight: normal;
}

.evolution {
    font-size: 1rem;
    margin-left: 0.5rem;
}

.next-class {
    margin-top: 1rem;
}

.next-class-time {
    font-size: 1.2rem;
    font-weight: bold;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.next-class-details h4 {
    margin: 0.5rem 0;
    font-size: 1.1rem;
}

.recent-notes, .recent-documents {
    list-style: none;
    padding: 0;
    margin: 0;
}

.note-item, .document-item {
    padding: 0.75rem 0;
    border-bottom: 1px solid #eee;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.note-item:last-child, .document-item:last-child {
    border-bottom: none;
}

.note-matiere {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.note-details {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
}

.note-appreciation {
    font-style: italic;
    color: #6c757d;
    font-size: 0.9rem;
    margin-top: 0.25rem;
}

.document-item {
    flex-direction: row;
    align-items: center;
    gap: 1rem;
}

.document-icon {
    font-size: 1.5rem;
    color: #6c757d;
    width: 40px;
    text-align: center;
}

.document-details {
    flex: 1;
    min-width: 0;
}

.document-title {
    font-weight: 500;
    color: var(--primary-color);
    text-decoration: none;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.document-meta {
    font-size: 0.8rem;
    color: #6c757d;
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.document-actions {
    opacity: 0;
    transition: opacity 0.2s;
}

.document-item:hover .document-actions {
    opacity: 1;
}

/* Empêcher le retour à la ligne dans les pills Année / Semestre / Session */
.year-pill,
.sem-pill,
.session-pill {
    white-space: nowrap;
}



/* Responsive */
@media (max-width: 992px) {
    .dashboard-cards {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 768px) {
    .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .dashboard-cards {
        grid-template-columns: 1fr;
    }
    
    .document-meta {
        flex-direction: column;
        gap: 0.25rem;
    }
}
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>

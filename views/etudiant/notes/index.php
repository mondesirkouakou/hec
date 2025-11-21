<?php
$pageTitle = 'Mes notes';
ob_start();
?>

<div class="page-header">
    <h1><i class="fas fa-chart-line"></i> Mes notes</h1>
    <div class="header-actions">
        <button class="btn btn-outline-primary" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimer
        </button>
        <button class="btn btn-outline-secondary" id="exportPdf">
            <i class="fas fa-file-pdf"></i> Exporter en PDF
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="filters-container">
            <div class="form-group">
                <label for="semestreFilter">Semestre</label>
                <select class="form-control" id="semestreFilter">
                    <option value="">Tous les semestres</option>
                    <?php foreach ($semestres as $semestre): ?>
                        <option value="<?= $semestre['id'] ?>" <?= ($filters['semestre_id'] ?? '') == $semestre['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($semestre['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="matiereFilter">Matière</label>
                <select class="form-control" id="matiereFilter">
                    <option value="">Toutes les matières</option>
                    <?php foreach ($matieres as $matiere): ?>
                        <option value="<?= $matiere['id'] ?>" <?= ($filters['matiere_id'] ?? '') == $matiere['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($matiere['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button class="btn btn-primary" id="applyFilters">
                <i class="fas fa-filter"></i> Appliquer
            </button>
        </div>
    </div>
    
    <div class="card-body">
        <?php if (empty($notes_par_matiere)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Aucune note n'a été enregistrée pour le moment.
            </div>
        <?php else: ?>
            <!-- Résumé des moyennes -->
            <div class="summary-cards">
                <div class="summary-card">
                    <div class="summary-icon bg-primary">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div class="summary-content">
                        <h3>Moyenne Générale</h3>
                        <div class="summary-value"><?= number_format($stats['moyenne_generale'] ?? 0, 2, ',', ' ') ?>/20</div>
                        <div class="summary-comparison <?= ($stats['evolution_moyenne'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">
                            <i class="fas fa-arrow-<?= ($stats['evolution_moyenne'] ?? 0) >= 0 ? 'up' : 'down' ?>"></i>
                            <?= abs($stats['evolution_moyenne'] ?? 0) ?>% vs semestre dernier
                        </div>
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="summary-icon bg-success">
                        <i class="fas fa-medal"></i>
                    </div>
                    <div class="summary-content">
                        <h3>Meilleure matière</h3>
                        <div class="summary-value"><?= number_format($stats['meilleure_note']['moyenne'] ?? 0, 2, ',', ' ') ?>/20</div>
                        <div class="summary-detail">
                            <?= htmlspecialchars($stats['meilleure_note']['matiere_nom'] ?? 'N/A') ?>
                        </div>
                    </div>
                </div>
                
                <div class="summary-card">
                    <div class="summary-icon bg-info">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="summary-content">
                        <h3>Rang</h3>
                        <div class="summary-value"><?= $stats['rang'] ?? 'N/A' ?><small>e</small></div>
                        <div class="summary-detail">
                            sur <?= $stats['effectif_classe'] ?? 0 ?> étudiants
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Graphique d'évolution -->
            <div class="chart-container">
                <canvas id="evolutionChart"></canvas>
            </div>
            
            <!-- Détail par matière -->
            <div class="matieres-container">
                <?php foreach ($notes_par_matiere as $matiere): ?>
                    <div class="matiere-card">
                        <div class="matiere-header">
                            <h3><?= htmlspecialchars($matiere['nom']) ?></h3>
                            <div class="matiere-moyenne">
                                <span class="badge <?= $matiere['moyenne'] >= 10 ? 'badge-success' : 'badge-danger' ?>">
                                    <?= number_format($matiere['moyenne'], 2, ',', ' ') ?>/20
                                </span>
                                <small>Moyenne</small>
                            </div>
                        </div>
                        
                        <div class="matiere-body">
                            <div class="notes-list">
                                <?php foreach ($matiere['evaluations'] as $evaluation): ?>
                                    <div class="note-item">
                                        <div class="note-type">
                                            <span class="badge badge-light"><?= htmlspecialchars($evaluation['type']) ?></span>
                                            <span class="text-muted"><?= date('d/m/Y', strtotime($evaluation['date_evaluation'])) ?></span>
                                        </div>
                                        <div class="note-value <?= $evaluation['valeur'] >= 10 ? 'text-success' : 'text-danger' ?>">
                                            <?= number_format($evaluation['valeur'], 2, ',', ' ') ?>/20
                                            <small class="text-muted">(Coef. <?= $evaluation['coefficient'] ?>)</small>
                                        </div>
                                        <?php if (!empty($evaluation['appreciation'])): ?>
                                            <div class="note-appreciation">
                                                <i class="fas fa-comment text-muted"></i>
                                                <?= htmlspecialchars($evaluation['appreciation']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="matiere-stats">
                                <div class="stat-item">
                                    <div class="stat-label">Moyenne de classe</div>
                                    <div class="stat-value"><?= number_format($matiere['moyenne_classe'] ?? 0, 2, ',', ' ') ?>/20</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-label">Rang</div>
                                    <div class="stat-value"><?= $matiere['rang'] ?? 'N/A' ?>/<?= $matiere['effectif'] ?? 0 ?></div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-label">Évolution</div>
                                    <div class="stat-value <?= ($matiere['evolution'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">
                                        <?= ($matiere['evolution'] ?? 0) >= 0 ? '+' : '' ?><?= number_format($matiere['evolution'] ?? 0, 1, ',', ' ') ?>%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Scripts pour les graphiques -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation du graphique d'évolution
    const ctx = document.getElementById('evolutionChart').getContext('2d');
    const evolutionChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($graphique_evolution, 'periode')) ?>,
            datasets: [{
                label: 'Votre moyenne',
                data: <?= json_encode(array_column($graphique_evolution, 'moyenne_etudiant')) ?>,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1,
                fill: false
            }, {
                label: 'Moyenne de la classe',
                data: <?= json_encode(array_column($graphique_evolution, 'moyenne_classe')) ?>,
                borderColor: 'rgb(255, 99, 132)',
                tension: 0.1,
                borderDash: [5, 5],
                fill: false
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Évolution de vos moyennes',
                    font: {
                        size: 16
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    min: 0,
                    max: 20
                }
            }
        }
    });

    // Gestion des filtres
    document.getElementById('applyFilters').addEventListener('click', function() {
        const semestreId = document.getElementById('semestreFilter').value;
        const matiereId = document.getElementById('matiereFilter').value;
        
        let url = new URL(window.location.href);
        url.searchParams.set('semestre_id', semestreId);
        url.searchParams.set('matiere_id', matiereId);
        
        window.location.href = url.toString();
    });

    // Export PDF
    document.getElementById('exportPdf').addEventListener('click', function() {
        // Implémentation de l'export PDF
        alert('Fonctionnalité d\'export PDF à implémenter');
        // window.location.href = '/etudiant/notes/export-pdf?' + new URLSearchParams(<?= json_encode($filters) ?>).toString();
    });
});
</script>

<style>
/* Styles spécifiques à la page des notes */
.filters-container {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: flex-end;
}

.filters-container .form-group {
    margin-bottom: 0;
    min-width: 200px;
}

.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.summary-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.summary-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.summary-content h3 {
    margin: 0 0 0.5rem;
    font-size: 1rem;
    color: #6c757d;
}

.summary-value {
    font-size: 1.5rem;
    font-weight: bold;
    color: #333;
}

.summary-comparison, .summary-detail {
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

.chart-container {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.matieres-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.matiere-card {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.matiere-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #f8f9fa;
}

.matiere-header h3 {
    margin: 0;
    font-size: 1.25rem;
}

.matiere-moyenne {
    text-align: right;
}

.matiere-moyenne .badge {
    font-size: 1.1rem;
    padding: 0.5rem 0.75rem;
}

.matiere-moyenne small {
    display: block;
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

.matiere-body {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 1.5rem;
}

.notes-list {
    padding: 1.5rem;
}

.note-item {
    padding: 1rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.note-item:last-child {
    border-bottom: none;
}

.note-type {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
}

.note-value {
    font-size: 1.1rem;
    font-weight: 500;
    margin: 0.25rem 0;
}

.note-appreciation {
    font-size: 0.9rem;
    color: #6c757d;
    font-style: italic;
    margin-top: 0.5rem;
    padding-left: 1.5rem;
    position: relative;
}

.note-appreciation i {
    position: absolute;
    left: 0;
    top: 0.2rem;
}

.matiere-stats {
    background-color: #f8f9fa;
    padding: 1.5rem;
    border-left: 1px solid #eee;
}

.stat-item {
    margin-bottom: 1.25rem;
}

.stat-item:last-child {
    margin-bottom: 0;
}

.stat-label {
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.stat-value {
    font-weight: 500;
    font-size: 1.1rem;
}

/* Responsive */
@media (max-width: 992px) {
    .matiere-body {
        grid-template-columns: 1fr;
    }
    
    .matiere-stats {
        border-left: none;
        border-top: 1px solid #eee;
    }
}

@media (max-width: 768px) {
    .filters-container {
        flex-direction: column;
        gap: 1rem;
    }
    
    .filters-container .form-group {
        width: 100%;
    }
    
    .summary-cards {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../layouts/main.php';
?>

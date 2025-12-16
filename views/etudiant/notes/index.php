<?php
$pageTitle = 'Mes notes';
ob_start();
?>

<div class="page-header">
    <h1><i class="fas fa-chart-line"></i> Mes notes</h1>
</div>

<div class="mb-3">
    <a href="<?= htmlspecialchars(isset($backUrlStudent) && !empty($backUrlStudent) ? $backUrlStudent : (BASE_URL . 'etudiant/dashboard')) ?>" class="btn btn-sm btn-secondary">
        <i class="fas fa-arrow-left"></i> Retour au dashboard
    </a>
</div>

<div class="card">
    <div class="card-header">
        Statistiques et diagramme de mes moyennes 
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
                        <?php if (!empty($stats['evolution_moyenne'])): ?>
                            <div class="summary-comparison <?= ($stats['evolution_moyenne'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">
                                <i class="fas fa-arrow-<?= ($stats['evolution_moyenne'] ?? 0) >= 0 ? 'up' : 'down' ?>"></i>
                                <?= abs($stats['evolution_moyenne'] ?? 0) ?>% vs semestre dernier
                            </div>
                        <?php endif; ?>
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
            </div>
            
            <!-- Graphique d'évolution -->
            <div class="chart-container">
                <div class="chart-scroll">
                    <canvas id="evolutionChart"></canvas>
                </div>
            </div>
            
            <!-- Détail par matière -->
            <div class="matieres-container" style="display:none;"></div>
        <?php endif; ?>
    </div>
</div>

<!-- Scripts pour les graphiques -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation du graphique d'évolution
    const canvas = document.getElementById('evolutionChart');
    const ctx = canvas.getContext('2d');
    const labels = <?= json_encode(array_column($graphique_evolution, 'periode')) ?>;

    const isMobile = window.matchMedia && window.matchMedia('(max-width: 576px)').matches;
    const pxPerLabel = isMobile ? 80 : 50;
    const desiredWidth = Math.max(canvas.parentElement ? canvas.parentElement.clientWidth : 0, labels.length * pxPerLabel);
    if (desiredWidth > 0) {
        if (canvas.parentElement) {
            canvas.parentElement.style.width = desiredWidth + 'px';
        }
    }

    const evolutionChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Moyenne de classe (CC)',
                data: <?= json_encode(array_column($graphique_evolution, 'moyenne_classe_etudiant')) ?>,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1,
                fill: false
            }, {
                label: 'Moyenne d\'examen',
                data: <?= json_encode(array_column($graphique_evolution, 'moyenne_examen_etudiant')) ?>,
                borderColor: 'rgb(255, 99, 132)',
                tension: 0.1,
                borderDash: [5, 5],
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    left: isMobile ? 18 : 10,
                    right: isMobile ? 8 : 10,
                    top: 0,
                    bottom: 0
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Évolution de vos moyennes',
                    font: {
                        size: 16
                    }
                },
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                x: {
                    ticks: {
                        maxRotation: isMobile ? 60 : 0,
                        minRotation: isMobile ? 60 : 0,
                        autoSkip: !isMobile,
                        maxTicksLimit: isMobile ? undefined : 6
                    }
                },
                y: {
                    beginAtZero: false,
                    min: 0,
                    max: 20,
                    ticks: {
                        padding: isMobile ? 14 : 6,
                        stepSize: isMobile ? 5 : undefined,
                        autoSkip: isMobile ? false : true,
                        maxTicksLimit: isMobile ? 5 : undefined,
                        font: {
                            size: isMobile ? 12 : 11
                        }
                    }
                }
            }
        }
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
    height: 360px;
    overflow-x: auto;
}

.chart-scroll {
    height: 100%;
    min-width: 100%;
    padding-left: 8px;
}

.chart-container canvas {
    width: 100% !important;
    height: 100% !important;
}

@media (max-width: 576px) {
    .chart-container {
        padding: 0.75rem;
        height: 360px;
    }
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

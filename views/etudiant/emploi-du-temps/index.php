<?php
$pageTitle = 'Mon emploi du temps';
ob_start();
?>

<div class="page-header">
    <h1><i class="fas fa-calendar-alt"></i> Mon emploi du temps</h1>
    <div class="header-actions">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-secondary" id="prevWeek">
                <i class="fas fa-chevron-left"></i> Semaine précédente
            </button>
            <button type="button" class="btn btn-outline-secondary" id="currentWeek">
                Cette semaine
            </button>
            <button type="button" class="btn btn-outline-secondary" id="nextWeek">
                Semaine suivante <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        <button class="btn btn-outline-primary" id="exportIcal">
            <i class="fas fa-calendar-plus"></i> Exporter (iCal)
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 id="currentWeekDisplay" class="mb-0">Semaine du <?= date('d/m/Y', strtotime('monday this week')) ?> au <?= date('d/m/Y', strtotime('sunday this week')) ?></h3>
            <div class="view-options">
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary active" data-view="week">
                        <i class="fas fa-calendar-week"></i> Vue semaine
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-view="day">
                        <i class="far fa-calendar-day"></i> Vue jour
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card-body p-0">
        <div class="timetable-container">
            <!-- En-tête des jours -->
            <div class="timetable-header">
                <div class="timetable-time-col"></div>
                <?php
                $startOfWeek = new DateTime('monday this week');
                $days = [];
                for ($i = 0; $i < 5; $i++) {
                    $currentDay = clone $startOfWeek;
                    $currentDay->add(new DateInterval("P{$i}D"));
                    $isToday = $currentDay->format('Y-m-d') === date('Y-m-d');
                    $days[] = $currentDay;
                    ?>
                    <div class="timetable-day-col <?= $isToday ? 'today' : '' ?>">
                        <div class="day-header">
                            <div class="day-name"><?= $currentDay->format('l') ?></div>
                            <div class="day-date"><?= $currentDay->format('d/m/Y') ?></div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            
            <!-- Corps de l'emploi du temps -->
            <div class="timetable-body">
                <!-- Colonne des heures -->
                <div class="timetable-hours-col">
                    <?php
                    for ($h = 8; $h < 20; $h++) {
                        echo '<div class="timetable-hour">' . sprintf('%02d:00', $h) . '</div>';
                        if ($h < 19) {
                            echo '<div class="timetable-half-hour">' . sprintf('%02d:30', $h) . '</div>';
                        }
                    }
                    ?>
                </div>
                
                <!-- Cases des cours -->
                <?php foreach ($days as $day): ?>
                    <div class="timetable-day-slots">
                        <?php
                        // Créer un tableau pour regrouper les cours par heure de début
                        $coursParHeure = [];
                        foreach ($cours as $coursItem) {
                            $dateDebut = new DateTime($coursItem['date_heure_debut']);
                            $dateFin = new DateTime($coursItem['date_heure_fin']);
                            
                            if ($dateDebut->format('Y-m-d') === $day->format('Y-m-d')) {
                                $heureDebut = (int)$dateDebut->format('H');
                                $minuteDebut = (int)$dateDebut->format('i');
                                $heureFin = (int)$dateFin->format('H');
                                $minuteFin = (int)$dateFin->format('i');
                                
                                // Calculer la position et la hauteur en fonction de l'heure
                                $positionTop = (($heureDebut - 8) * 2 + ($minuteDebut >= 30 ? 1 : 0)) * 30;
                                $duree = (($heureFin - $heureDebut) * 2) + ($minuteFin >= 30 ? 1 : 0) - ($minuteDebut >= 30 ? 1 : 0);
                                $height = $duree * 30;
                                
                                $coursParHeure[] = [
                                    'top' => $positionTop,
                                    'height' => $height,
                                    'data' => $coursItem
                                ];
                            }
                        }
                        
                        // Trier les cours par heure de début
                        usort($coursParHeure, function($a, $b) {
                            return $a['top'] - $b['top'];
                        });
                        
                        // Afficher les créneaux avec les cours
                        for ($h = 8; $h < 20; $h++) {
                            // Créneau de 8h à 20h
                            for ($m = 0; $m < 60; $m += 30) {
                                $heureActuelle = $h + ($m / 60);
                                $estOccupe = false;
                                
                                foreach ($coursParHeure as $coursItem) {
                                    $heureDebut = (int)date('H', strtotime($coursItem['data']['date_heure_debut']));
                                    $minuteDebut = (int)date('i', strtotime($coursItem['data']['date_heure_debut']));
                                    $heureFin = (int)date('H', strtotime($coursItem['data']['date_heure_fin']));
                                    $minuteFin = (int)date('i', strtotime($coursItem['data']['date_heure_fin']));
                                    
                                    $debutEnHeures = $heureDebut + ($minuteDebut / 60);
                                    $finEnHeures = $heureFin + ($minuteFin / 60);
                                    
                                    if ($heureActuelle >= $debutEnHeures && $heureActuelle < $finEnHeures) {
                                        $estOccupe = true;
                                        $coursCourant = $coursItem;
                                        break;
                                    }
                                }
                                
                                if ($estOccupe && $m % 60 === 0) {
                                    // Afficher le cours
                                    $duree = $coursCourant['data']['duree'] ?? 1;
                                    $rowSpan = $duree * 2; // Chaque heure = 2 créneaux de 30min
                                    ?>
                                    <div class="timetable-slot cours-slot" 
                                         style="grid-row: <?= floor(($coursCourant['top'] / 30) + 1) ?> / span <?= $rowSpan ?>;"
                                         data-toggle="popover" 
                                         data-html="true"
                                         data-placement="top"
                                         data-trigger="hover"
                                         data-content='
                                            <div class="popover-cours">
                                                <h6><?= htmlspecialchars($coursCourant['data']['matiere_nom'] ?? '') ?></h6>
                                                <p class="mb-1"><i class="fas fa-user-tie"></i> <?= htmlspecialchars($coursCourant['data']['professeur_nom'] ?? '') ?></p>
                                                <p class="mb-1"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($coursCourant['data']['salle_nom'] ?? '') ?></p>
                                                <p class="mb-0"><i class="far fa-clock"></i> <?= date('H:i', strtotime($coursCourant['data']['date_heure_debut'] ?? '')) ?> - <?= date('H:i', strtotime($coursCourant['data']['date_heure_fin'] ?? '')) ?></p>
                                                <?php if (!empty($coursCourant['data']['commentaire'])): ?>
                                                    <hr class="my-2">
                                                    <p class="mb-0"><i class="far fa-comment"></i> <?= htmlspecialchars($coursCourant['data']['commentaire']) ?></p>
                                                <?php endif; ?>
                                            </div>
                                         '>
                                        <div class="cours-content">
                                            <div class="cours-title"><?= htmlspecialchars($coursCourant['data']['matiere_nom'] ?? '') ?></div>
                                            <div class="cours-details">
                                                <span class="cours-salle"><?= htmlspecialchars($coursCourant['data']['salle_nom'] ?? '') ?></span>
                                                <span class="cours-prof"><?= htmlspecialchars($coursCourant['data']['professeur_nom'] ?? '') ?></span>
                                            </div>
                                            <div class="cours-horaire">
                                                <?= date('H:i', strtotime($coursCourant['data']['date_heure_debut'] ?? '')) ?> - <?= date('H:i', strtotime($coursCourant['data']['date_heure_fin'] ?? '')) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                    // Sauter les créneaux suivants occupés par le même cours
                                    $m += ($duree * 60) - 30;
                                    if ($m >= 60) {
                                        $h += floor($m / 60);
                                        $m = $m % 60;
                                    }
                                } else {
                                    // Créneau vide
                                    ?>
                                    <div class="timetable-slot empty-slot"></div>
                                    <?php
                                }
                                
                                // Gestion du passage à l'heure suivante
                                if ($m >= 30 && $h < 19) {
                                    $h++;
                                    $m = -30; // Sera incrémenté à 0 dans la prochaine itération
                                }
                            }
                        }
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal de détail du cours -->
<div class="modal fade" id="coursModal" tabindex="-1" aria-labelledby="coursModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="coursModalLabel">Détails du cours</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="coursModalBody">
                <!-- Le contenu sera chargé dynamiquement -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" id="addToCalendar">
                    <i class="far fa-calendar-plus"></i> Ajouter à mon agenda
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des popovers
    $('[data-toggle="popover"]').popover({
        container: 'body',
        trigger: 'hover',
        html: true
    });
    
    // Gestion de la navigation entre les semaines
    let currentDate = new Date('<?= date('Y-m-d') ?>');
    
    function updateWeekDisplay() {
        const startOfWeek = new Date(currentDate);
        startOfWeek.setDate(currentDate.getDate() - currentDate.getDay() + (currentDate.getDay() === 0 ? -6 : 1));
        
        const endOfWeek = new Date(startOfWeek);
        endOfWeek.setDate(startOfWeek.getDate() + 4); // Du lundi au vendredi
        
        const options = { day: '2-digit', month: '2-digit', year: 'numeric' };
        document.getElementById('currentWeekDisplay').textContent = 
            `Semaine du ${startOfWeek.toLocaleDateString('fr-FR', options)} au ${endOfWeek.toLocaleDateString('fr-FR', options)}`;
    }
    
    document.getElementById('prevWeek').addEventListener('click', function() {
        currentDate.setDate(currentDate.getDate() - 7);
        updateWeekDisplay();
        // Ici, vous devriez recharger les données de la semaine précédente
        // loadWeekData(currentDate);
    });
    
    document.getElementById('nextWeek').addEventListener('click', function() {
        currentDate.setDate(currentDate.getDate() + 7);
        updateWeekDisplay();
        // Ici, vous devriez recharger les données de la semaine suivante
        // loadWeekData(currentDate);
    });
    
    document.getElementById('currentWeek').addEventListener('click', function() {
        currentDate = new Date();
        updateWeekDisplay();
        // Ici, vous devriez recharger les données de la semaine courante
        // loadWeekData(currentDate);
    });
    
    // Gestion du changement de vue (jour/semaine)
    document.querySelectorAll('[data-view]').forEach(button => {
        button.addEventListener('click', function() {
            const view = this.dataset.view;
            document.querySelectorAll('[data-view]').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Ici, vous pouvez ajouter la logique pour changer la vue
            if (view === 'day') {
                // Afficher la vue jour
                // loadDayView(currentDate);
            } else {
                // Afficher la vue semaine
                // loadWeekView();
            }
        });
    });
    
    // Export iCal
    document.getElementById('exportIcal').addEventListener('click', function() {
        // Ici, vous pouvez ajouter la logique pour générer et télécharger le fichier iCal
        alert('Fonctionnalité d\'export iCal à implémenter');
        // window.location.href = '/etudiant/emploi-du-temps/export-ical';
    });
    
    // Gestion du clic sur un cours
    document.querySelectorAll('.cours-slot').forEach(slot => {
        slot.addEventListener('click', function() {
            // Récupérer les données du cours depuis les attributs data-*
            // ou via une requête AJAX pour plus de détails
            const coursId = this.dataset.coursId;
            
            // Ici, vous pouvez charger les détails complets du cours via AJAX
            // puis afficher la modale avec les détails
            $('#coursModal').modal('show');
        });
    });
    
    // Initialisation
    updateWeekDisplay();
});
</script>

<style>
/* Styles spécifiques à l'emploi du temps */
.timetable-container {
    display: flex;
    flex-direction: column;
    width: 100%;
    overflow-x: auto;
}

.timetable-header {
    display: grid;
    grid-template-columns: 80px repeat(5, 1fr);
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    position: sticky;
    top: 0;
    z-index: 10;
}

.timetable-time-col {
    padding: 10px;
    font-weight: bold;
    text-align: center;
    background-color: #f8f9fa;
    border-right: 1px solid #dee2e6;
}

.timetable-day-col {
    padding: 10px;
    text-align: center;
    border-right: 1px solid #dee2e6;
    min-width: 180px;
}

.timetable-day-col.today {
    background-color: #e7f5ff;
}

.day-header {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.day-name {
    font-weight: bold;
    text-transform: capitalize;
    margin-bottom: 4px;
}

.day-date {
    font-size: 0.9em;
    color: #6c757d;
}

.timetable-body {
    display: grid;
    grid-template-columns: 80px repeat(5, 1fr);
    position: relative;
}

.timetable-hours-col {
    grid-column: 1;
    position: sticky;
    left: 0;
    background-color: #fff;
    z-index: 5;
}

.timetable-hour, .timetable-half-hour {
    height: 60px;
    padding: 5px;
    text-align: right;
    font-size: 0.8em;
    color: #6c757d;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: flex-start;
    justify-content: flex-end;
    background-color: #fff;
}

.timetable-half-hour {
    height: 30px;
    font-size: 0.7em;
    color: #adb5bd;
}

.timetable-day-slots {
    display: grid;
    grid-template-rows: repeat(24, 60px);
    grid-auto-flow: column;
    min-width: 180px;
}

.timetable-slot {
    border-bottom: 1px solid #f0f0f0;
    border-right: 1px solid #f0f0f0;
    position: relative;
}

.empty-slot {
    background-color: #fff;
}

.cours-slot {
    background-color: #e3f2fd;
    border-left: 3px solid #1976d2;
    margin: 1px;
    border-radius: 4px;
    padding: 4px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.2s;
}

.cours-slot:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    transform: translateY(-1px);
}

.cours-content {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.cours-title {
    font-weight: 500;
    font-size: 0.85em;
    line-height: 1.2;
    margin-bottom: 2px;
    word-break: break-word;
}

.cours-details {
    font-size: 0.75em;
    color: #495057;
    margin-bottom: 2px;
    display: flex;
    justify-content: space-between;
}

.cours-horaire {
    font-size: 0.7em;
    color: #6c757d;
    margin-top: auto;
}

/* Styles pour le popover */
.popover {
    max-width: 300px;
}

.popover-cours {
    padding: 8px;
}

.popover-cours h6 {
    color: #1976d2;
    margin-bottom: 8px;
    font-weight: 600;
}

.popover-cours p {
    margin-bottom: 4px;
    font-size: 0.9em;
}

.popover-cours i {
    width: 16px;
    text-align: center;
    margin-right: 5px;
    color: #6c757d;
}

/* Styles pour la vue jour (masquée par défaut) */
.timetable-day-view {
    display: none;
}

/* Styles pour les écrans plus petits */
@media (max-width: 992px) {
    .timetable-container {
        font-size: 0.9em;
    }
    
    .timetable-day-col, .timetable-slot {
        min-width: 150px;
    }
    
    .cours-title {
        font-size: 0.8em;
    }
    
    .cours-details {
        flex-direction: column;
    }
    
    .cours-prof {
        display: block;
    }
}

@media (max-width: 768px) {
    .timetable-header {
        grid-template-columns: 60px repeat(5, 1fr);
    }
    
    .timetable-time-col {
        width: 60px;
        padding: 5px;
        font-size: 0.8em;
    }
    
    .timetable-day-col {
        min-width: 120px;
        font-size: 0.9em;
    }
    
    .timetable-slot {
        min-width: 120px;
    }
    
    .header-actions {
        flex-direction: column;
        gap: 10px;
        margin-top: 10px;
    }
    
    .header-actions .btn-group {
        width: 100%;
        justify-content: center;
    }
}
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../layouts/main.php';
?>

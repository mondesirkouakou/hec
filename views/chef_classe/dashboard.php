<?php
$pageTitle = 'Tableau de bord Chef de Classe';
ob_start();

// Récupérer les données
$classe = $classe ?? null;
$etudiants = $etudiants ?? [];
$professeurs = $professeurs ?? [];
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

// Vérifier si les listes ont été soumises
$listeSoumise = ($classe['statut_listes'] ?? '') === 'en_attente';
?>

<div class="container-fluid dashboard-container">
    <div class="dashboard-header animated-header">
        <h1 class="dashboard-title neon-effect">Tableau de bord Chef de Classe</h1>
        <div class="header-actions">
            <?php if (!$listeSoumise): ?>
                <button type="button" class="btn btn-primary btn-lg ripple-effect explosive-zoom" data-bs-toggle="modal" data-bs-target="#soumettreModal">
                    <i class="fas fa-paper-plane"></i> Soumettre les listes
                </button>
            <?php else: ?>
                <span class="badge status-badge pulse heartbeat">Listes soumises - En attente de validation</span>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success animated-alert"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger animated-alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>


    <!-- Informations de la classe -->
    <div class="card animated-card info-card rotate-3d">
        <div class="card-header card-header-primary">
            <h5 class="card-title">Ma Classe</h5>
        </div>
        <div class="card-body card-body-animated">
            <?php if ($classe): ?>
                <p><strong>Classe:</strong> <?= htmlspecialchars($classe['intitule']) ?> (<?= htmlspecialchars($classe['code']) ?>)</p>
                <p><strong>Niveau:</strong> <?= htmlspecialchars($classe['niveau']) ?></p>
                <?php $statut = $classe['statut_listes'] ?? null; ?>
                <p><strong>Statut des listes:</strong>
                    <?php if ($statut === 'en_attente'): ?>
                        <span class="badge badge-warning">En attente de validation</span>
                    <?php elseif ($statut === 'validee'): ?>
                        <span class="badge badge-success">Validées</span>
                    <?php else: ?>
                        <span class="badge badge-secondary">Non soumises</span>
                    <?php endif; ?>
                </p>
            <?php else: ?>
                <p class="text-muted">Aucune classe assignée</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="row dashboard-row">
        <!-- Liste des étudiants -->
        <div class="col-lg-6">
            <div class="card animated-card student-card">
                <div class="card-header card-header-secondary d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Étudiants (<?= count($etudiants) ?>)</h5>
                    <div class="d-flex gap-2">
                    <?php if (!$listeSoumise): ?>
                        <a href="<?= BASE_URL ?>chef-classe/etudiants/ajouter" class="btn btn-primary btn-sm ripple-effect magnetic-effect">
                            <i class="fas fa-plus"></i> Ajouter
                        </a>
                    <?php endif; ?>
                    </div>
                </div>
                <div class="card-body card-body-animated">
                    <?php if (empty($etudiants)): ?>
                        <p class="text-muted">Aucun étudiant dans cette classe</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm animated-table">
                                <thead class="table-header-primary">
                                    <tr>
                                        <th>Matricule</th>
                                        <th>Nom & Prénom</th>
                                        <th>Email</th>
                                        <?php if (!$listeSoumise): ?>
                                            <th>Actions</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($etudiants as $index => $e): ?>
                                        <tr class="table-row-animated" style="animation-delay: <?= $index * 0.1 ?>s;">
                                            <td><?= htmlspecialchars($e['matricule']) ?></td>
                                            <td><?= htmlspecialchars($e['nom'] . ' ' . $e['prenom']) ?></td>
                                            <td><?= htmlspecialchars($e['email']) ?></td>
                                            <?php if (!$listeSoumise): ?>
                                                <td>
                                                    <button class="btn btn-sm btn-danger ripple-effect" onclick="supprimerEtudiant(<?= $e['id'] ?>)" title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Liste des professeurs -->
        <div class="col-lg-6">
            <div class="card animated-card teacher-card">
                <div class="card-header card-header-accent d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Professeurs (<?= count($professeurs) ?>)</h5>
                    <div class="d-flex gap-2">
                    <?php if (!$listeSoumise): ?>
                        <a href="<?= BASE_URL ?>chef-classe/professeurs/ajouter" class="btn btn-primary btn-sm ripple-effect magnetic-effect">
                            <i class="fas fa-plus"></i> Ajouter
                        </a>
                    <?php endif; ?>
                    </div>
                </div>
                <div class="card-body card-body-animated">
                    <?php if (empty($professeurs)): ?>
                        <p class="text-muted">Aucun professeur assigné à cette classe</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm animated-table">
                                <thead class="table-header-accent">
                                    <tr>
                                        <th>Nom & Prénom</th>
                                        <th>Email</th>
                                        <th>Matière(s)</th>
                                        <?php if (!$listeSoumise): ?>
                                            <th>Actions</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($professeurs as $index => $p): ?>
                                        <tr class="table-row-animated" style="animation-delay: <?= $index * 0.1 ?>s;">
                                            <td><?= htmlspecialchars($p['nom'] . ' ' . $p['prenom']) ?></td>
                                            <td><?= htmlspecialchars($p['email']) ?></td>
                                            <td><?= htmlspecialchars($p['matieres']) ?></td>
                                            <?php if (!$listeSoumise): ?>
                                                <td>
                                                    <?php if (isset($p['professeur_id']) && isset($p['matiere_id'])): ?>
                                                        <button class="btn btn-sm btn-danger ripple-effect" onclick="supprimerProfesseur(<?= (int)$p['professeur_id'] ?>, <?= (int)$p['matiere_id'] ?>)" title="Supprimer">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endif; ?>
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

<!-- Modal Soumettre -->
<div class="modal fade animated-modal" id="soumettreModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-content-animated">
            <div class="modal-header modal-header-primary">
                <h5 class="modal-title">Soumettre les listes</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body modal-body-animated">
                <div class="submission-icon">
                    <i class="fas fa-paper-plane fa-3x text-primary"></i>
                </div>
                <p class="text-center">Êtes-vous sûr de vouloir soumettre les listes à l'administrateur ?</p>
                <p class="text-warning text-center"><strong>Attention :</strong> Après soumission, vous ne pourrez plus modifier les listes.</p>
            </div>
            <div class="modal-footer modal-footer-animated">
                <button type="button" class="btn btn-secondary ripple-effect" data-bs-dismiss="modal">Annuler</button>
                <form action="<?= BASE_URL ?>chef-classe/soumettre" method="POST">
                    <button type="submit" class="btn btn-success ripple-effect">
                        <i class="fas fa-paper-plane"></i> Soumettre
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajouter Étudiant -->
<div class="modal fade animated-modal" id="ajouterEtudiantModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-animated">
            <div class="modal-header modal-header-secondary">
                <h5 class="modal-title">Ajouter un étudiant</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="<?= BASE_URL ?>chef-classe/etudiants" method="POST">
                <div class="modal-body modal-body-animated">
                    <div class="form-group form-group-animated">
                        <label>Matricule</label>
                        <input type="text" name="matricule" class="form-control form-control-animated" required placeholder="Matricule">
                    </div>
                    <div class="form-group form-group-animated">
                        <label>Nom</label>
                        <input type="text" name="nom" class="form-control form-control-animated" required>
                    </div>
                    <div class="form-group form-group-animated">
                        <label>Prénom(s)</label>
                        <input type="text" name="prenom" class="form-control form-control-animated" required>
                    </div>
                    <div class="form-group form-group-animated">
                        <label>Date de naissance</label>
                        <input type="date" name="date_naissance" class="form-control form-control-animated" required>
                    </div>
                    <div class="form-group form-group-animated">
                        <label>Lieu de naissance</label>
                        <input type="text" name="lieu_naissance" class="form-control form-control-animated" required>
                    </div>
                </div>
                <div class="modal-footer modal-footer-animated">
                    <button type="button" class="btn btn-secondary ripple-effect" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary ripple-effect">
                        <i class="fas fa-plus"></i> Ajouter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ajouter Professeur -->
<div class="modal fade animated-modal" id="ajouterProfesseurModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-animated">
            <div class="modal-header modal-header-accent">
                <h5 class="modal-title">Ajouter un professeur</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="<?= BASE_URL ?>chef-classe/professeurs" method="POST">
                <div class="modal-body modal-body-animated">
                    <div class="form-group form-group-animated">
                        <label>Nom</label>
                        <input type="text" name="nom" class="form-control form-control-animated" required>
                    </div>
                    <div class="form-group form-group-animated">
                        <label>Prénom(s)</label>
                        <input type="text" name="prenom" class="form-control form-control-animated" required>
                    </div>
                    <div class="form-group form-group-animated">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control form-control-animated" required placeholder="prenom.nom@hec.ci">
                    </div>
                    <div class="form-group form-group-animated">
                        <label>Téléphone (sera le mot de passe initial)</label>
                        <input type="tel" name="telephone" class="form-control form-control-animated" required pattern="[0-9]{10}" placeholder="0708123456">
                    </div>
                    <div class="form-group form-group-animated">
                        <label>Matière</label>
                        <select name="matiere_id" class="form-control form-control-animated" required>
                            <option value="">Sélectionner une matière</option>
                            <?php
                            // Récupérer les matières disponibles (non encore assignées)
                            $db = Database::getInstance();
                            $matieresDisponibles = $db->fetchAll("
                                SELECT * FROM matieres 
                                WHERE id NOT IN (
                                    SELECT DISTINCT matiere_id 
                                    FROM affectation_professeur 
                                    WHERE classe_id = :classe_id
                                )
                                ORDER BY intitule
                            ", ['classe_id' => $classe['id']]);
                            foreach ($matieresDisponibles as $m): ?>
                                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['intitule']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer modal-footer-animated">
                    <button type="button" class="btn btn-secondary ripple-effect" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary ripple-effect">
                        <i class="fas fa-plus"></i> Ajouter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Fonctions avec animations spectaculaires
function supprimerEtudiant(etudiantId) {
    // Animation de confirmation spectaculaire
    Swal.fire({
        title: 'Êtes-vous sûr?',
        text: 'Cet étudiant sera supprimé définitivement!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e02524',
        cancelButtonColor: '#0752dd',
        confirmButtonText: 'Oui, supprimer!',
        cancelButtonText: 'Annuler',
        showClass: {
            popup: 'animate__animated animate__fadeInDown'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutUp'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Animation de chargement
            Swal.fire({
                title: 'Suppression en cours...',
                text: 'Veuillez patienter',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= BASE_URL ?>chef-classe/etudiants/supprimer';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'etudiant_id';
            input.value = etudiantId;
            form.appendChild(input);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function supprimerProfesseur(professeurId, matiereId) {
    // Animation de confirmation spectaculaire
    Swal.fire({
        title: 'Êtes-vous sûr?',
        text: 'Ce professeur sera retiré de cette matière!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e02524',
        cancelButtonColor: '#0752dd',
        confirmButtonText: 'Oui, retirer!',
        cancelButtonText: 'Annuler',
        showClass: {
            popup: 'animate__animated animate__fadeInDown'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutUp'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Animation de chargement
            Swal.fire({
                title: 'Retrait en cours...',
                text: 'Veuillez patienter',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= BASE_URL ?>chef-classe/professeurs/supprimer';
            
            const inputProf = document.createElement('input');
            inputProf.type = 'hidden';
            inputProf.name = 'professeur_id';
            inputProf.value = professeurId;
            form.appendChild(inputProf);
            
            const inputMat = document.createElement('input');
            inputMat.type = 'hidden';
            inputMat.name = 'matiere_id';
        inputMat.value = matiereId;
        form.appendChild(inputMat);
        
        document.body.appendChild(form);
        form.submit();
        }
    });
}

// Ajouter des effets spectaculaires au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Animation d'entrée pour le dashboard
    const dashboard = document.querySelector('.dashboard-container');
    if (dashboard) {
        dashboard.style.opacity = '0';
        dashboard.style.transform = 'translateY(50px)';
        setTimeout(() => {
            dashboard.style.transition = 'all 1s ease-out';
            dashboard.style.opacity = '1';
            dashboard.style.transform = 'translateY(0)';
        }, 100);
    }
    
    // Animation pour les cartes
    const cards = document.querySelectorAll('.animated-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'scale(0.9)';
        setTimeout(() => {
            card.style.transition = 'all 0.8s ease-out';
            card.style.opacity = '1';
            card.style.transform = 'scale(1)';
        }, 300 + (index * 200));
    });
    
    // Effet de lueur sur les boutons
    const buttons = document.querySelectorAll('.ripple-effect');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.boxShadow = '0 0 20px rgba(7, 82, 221, 0.6)';
        });
        button.addEventListener('mouseleave', function() {
            this.style.boxShadow = 'none';
        });
    });
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>
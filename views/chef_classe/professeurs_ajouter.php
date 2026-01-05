<?php
$pageTitle = 'Ajouter un professeur';
ob_start();
?>

<style>
/* Multiselect Dropdown Styles */
.multiselect-dropdown {
    position: relative;
    width: 100%;
}

.multiselect-button {
    display: flex;
    justify-content: space-between;
    align-items: center;
    text-align: left;
    cursor: pointer;
    background: white;
    border: 1px solid #ced4da;
    padding: 0.375rem 0.75rem;
    border-radius: 0.25rem;
    transition: all 0.2s;
}

.multiselect-button:hover:not(:disabled) {
    border-color: #0752dd;
    box-shadow: 0 0 0 0.2rem rgba(7, 82, 221, 0.1);
}

.multiselect-button:disabled {
    background-color: #e9ecef;
    cursor: not-allowed;
}

.multiselect-text {
    flex: 1;
    color: #6c757d;
}

.multiselect-text.has-selection {
    color: #212529;
    font-weight: 500;
}

.multiselect-icon {
    transition: transform 0.3s;
    color: #6c757d;
}

.multiselect-button.active .multiselect-icon {
    transform: rotate(180deg);
}

.multiselect-options {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ced4da;
    border-top: none;
    border-radius: 0 0 0.25rem 0.25rem;
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.multiselect-options.show {
    display: block;
}

.multiselect-option {
    display: flex;
    align-items: center;
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    transition: background-color 0.2s;
    margin: 0;
}

.multiselect-option:hover {
    background-color: #f8f9fa;
}

.multiselect-option input[type="checkbox"] {
    margin-right: 0.5rem;
    cursor: pointer;
    width: 18px;
    height: 18px;
}

.multiselect-label {
    flex: 1;
    cursor: pointer;
    user-select: none;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .multiselect-options {
        max-height: 250px;
    }
    
    .multiselect-option {
        padding: 0.75rem;
    }
    
    .multiselect-option input[type="checkbox"] {
        width: 20px;
        height: 20px;
    }
}
</style>

<div class="container-fluid">
    <div class="dashboard-header animated-header d-flex justify-content-between align-items-center">
        <h1 class="dashboard-title">Ajouter un professeur</h1>
        <div class="header-actions">
            <a href="<?= BASE_URL ?>chef-classe/professeurs" class="btn btn-secondary ripple-effect"><i class="fas fa-list"></i> Liste des professeurs</a>
            <a href="<?= BASE_URL ?>chef-classe/dashboard" class="btn btn-light ripple-effect"><i class="fas fa-home"></i> Tableau de bord</a>
        </div>
    </div>

    <?php $listeSoumise = ($classe['statut_listes'] ?? '') === 'en_attente'; ?>
    <?php if ($listeSoumise): ?>
        <div class="alert alert-warning">La liste est en attente de validation. L'ajout est désactivé.</div>
    <?php endif; ?>

    <div class="card animated-card">
        <div class="card-header card-header-accent">
            <h5 class="card-title">Formulaire d'ajout</h5>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>chef-classe/professeurs" method="POST" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Nom</label>
                    <input type="text" name="nom" class="form-control" required <?= $listeSoumise ? 'disabled' : '' ?>>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Prénom(s)</label>
                    <input type="text" name="prenom" class="form-control" required <?= $listeSoumise ? 'disabled' : '' ?>>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="prenom.nom@hec.ci" <?= $listeSoumise ? 'disabled' : '' ?>>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Téléphone</label>
                    <input type="tel" name="telephone" class="form-control" required pattern="[0-9]{10}" placeholder="0708123456" <?= $listeSoumise ? 'disabled' : '' ?>>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Matière(s)</label>
                    <div class="multiselect-dropdown" id="matiereDropdown">
                        <button type="button" class="form-control multiselect-button" id="matiereButton" <?= $listeSoumise ? 'disabled' : '' ?>>
                            <span class="multiselect-text">Sélectionner une ou plusieurs matières</span>
                            <i class="fas fa-chevron-down multiselect-icon"></i>
                        </button>
                        <div class="multiselect-options" id="matiereOptions">
                            <?php
                            $db = Database::getInstance();
                            try {
                                $matieresDisponibles = $db->fetchAll(
                                    "SELECT m.*
                                     FROM matieres m
                                     JOIN classe_matiere cm ON cm.matiere_id = m.id
                                     WHERE cm.classe_id = :classe_id
                                     ORDER BY m.intitule",
                                    ['classe_id' => $classe['id']]
                                );
                            } catch (Exception $e) {
                                $matieresDisponibles = [];
                            }

                            foreach ($matieresDisponibles as $m): ?>
                                <label class="multiselect-option">
                                    <input type="checkbox" name="matiere_ids[]" value="<?= $m['id'] ?>" class="matiere-checkbox" <?= $listeSoumise ? 'disabled' : '' ?>>
                                    <span class="multiselect-label"><?= htmlspecialchars($m['intitule']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <input type="hidden" id="matiereValidation" required>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary" <?= $listeSoumise ? 'disabled' : '' ?>><i class="fas fa-plus"></i> Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const button = document.getElementById('matiereButton');
    const options = document.getElementById('matiereOptions');
    const textSpan = button.querySelector('.multiselect-text');
    const checkboxes = document.querySelectorAll('.matiere-checkbox');
    const validation = document.getElementById('matiereValidation');
    
    // Toggle dropdown
    button.addEventListener('click', function(e) {
        e.preventDefault();
        if (!button.disabled) {
            options.classList.toggle('show');
            button.classList.toggle('active');
        }
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!button.contains(e.target) && !options.contains(e.target)) {
            options.classList.remove('show');
            button.classList.remove('active');
        }
    });
    
    // Update button text when checkboxes change
    function updateButtonText() {
        const checked = Array.from(checkboxes).filter(cb => cb.checked);
        
        if (checked.length === 0) {
            textSpan.textContent = 'Sélectionner une ou plusieurs matières';
            textSpan.classList.remove('has-selection');
            validation.value = '';
        } else if (checked.length === 1) {
            textSpan.textContent = checked[0].nextElementSibling.textContent;
            textSpan.classList.add('has-selection');
            validation.value = 'valid';
        } else {
            textSpan.textContent = checked.length + ' matières sélectionnées';
            textSpan.classList.add('has-selection');
            validation.value = 'valid';
        }
    }
    
    // Listen to checkbox changes
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateButtonText);
    });
    
    // Prevent dropdown from closing when clicking on options
    options.addEventListener('click', function(e) {
        e.stopPropagation();
    });
    
    // Initialize
    updateButtonText();
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
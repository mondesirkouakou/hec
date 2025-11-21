<?php
$pageTitle = 'Saisie des notes';
ob_start();
?>

<div class="page-header">
    <h1><i class="fas fa-edit"></i> Saisie des notes</h1>
    <div class="header-actions">
        <a href="/hec/professeur/notes" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <?= htmlspecialchars($classe['intitule']) ?> - 
            <?= htmlspecialchars($matiere['nom']) ?> (<?= htmlspecialchars($evaluation['type']) ?>)
        </h3>
        <div class="card-options">
            <span class="badge badge-info">
                <i class="fas fa-calendar-alt"></i> 
                <?= date('d/m/Y', strtotime($evaluation['date_evaluation'])) ?>
            </span>
            <span class="badge badge-primary">
                <i class="fas fa-percentage"></i> 
                Coefficient: <?= $evaluation['coefficient'] ?>
            </span>
        </div>
    </div>
    
    <form id="notesForm" action="/hec/professeur/notes/enregistrer" method="POST">
        <input type="hidden" name="evaluation_id" value="<?= $evaluation['id'] ?>">
        <input type="hidden" name="classe_id" value="<?= $classe['id'] ?>">
        <input type="hidden" name="matiere_id" value="<?= $matiere['id'] ?>">
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="thead-light">
                        <tr>
                            <th>Matricule</th>
                            <th>Nom et prénom</th>
                            <th>Note / <?= $evaluation['note_maximale'] ?></th>
                            <th>Appréciation</th>
                            <th>Absent</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($etudiants as $etudiant): 
                            $note = $notes[$etudiant['id']] ?? null;
                            $isAbsent = $note && $note['is_absent'];
                        ?>
                        <tr class="<?= $isAbsent ? 'table-warning' : '' ?>" id="row-<?= $etudiant['id'] ?>">
                            <td><?= htmlspecialchars($etudiant['matricule']) ?></td>
                            <td><?= htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom']) ?></td>
                            <td style="width: 150px;">
                                <input type="hidden" name="etudiant_id[]" value="<?= $etudiant['id'] ?>">
                                <input type="number" 
                                       class="form-control note-input" 
                                       name="note[<?= $etudiant['id'] ?>]" 
                                       value="<?= $note ? number_format($note['valeur'], 2, '.', '') : '' ?>"
                                       min="0" 
                                       max="<?= $evaluation['note_maximale'] ?>" 
                                       step="0.01"
                                       <?= $isAbsent ? 'disabled' : 'required' ?>>
                            </td>
                            <td>
                                <input type="text" 
                                       class="form-control" 
                                       name="appreciation[<?= $etudiant['id'] ?>]" 
                                       value="<?= $note ? htmlspecialchars($note['appreciation']) : '' ?>"
                                       placeholder="Appréciation"
                                       maxlength="100"
                                       <?= $isAbsent ? 'disabled' : '' ?>>
                            </td>
                            <td class="text-center" style="width: 80px;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" 
                                           class="custom-control-input absent-checkbox" 
                                           id="absent-<?= $etudiant['id'] ?>"
                                           name="absent[<?= $etudiant['id'] ?>]" 
                                           value="1"
                                           data-etudiant-id="<?= $etudiant['id'] ?>"
                                           <?= $isAbsent ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="absent-<?= $etudiant['id'] ?>"></label>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer text-right">
            <button type="button" class="btn btn-outline-secondary mr-2" onclick="history.back()">
                <i class="fas fa-times"></i> Annuler
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Enregistrer les notes
            </button>
        </div>
    </form>
</div>

<!-- Modal de confirmation -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmation</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Voulez-vous vraiment marquer cet étudiant comme absent ? La note saisie sera supprimée.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmAbsence">Confirmer l'absence</button>
            </div>
        </div>
    </div>
</div>

<script>
// Gestion des cases à cocher "Absent"
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.absent-checkbox');
    let currentCheckbox = null;
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                currentCheckbox = this;
                $('#confirmModal').modal('show');
            } else {
                toggleAbsence(this, false);
            }
        });
    });
    
    // Confirmation de l'absence
    document.getElementById('confirmAbsence').addEventListener('click', function() {
        toggleAbsence(currentCheckbox, true);
        $('#confirmModal').modal('hide');
    });
    
    function toggleAbsence(checkbox, isAbsent) {
        const etudiantId = checkbox.dataset.etudiantId;
        const row = document.getElementById(`row-${etudiantId}`);
        const noteInput = row.querySelector('.note-input');
        const appreciationInput = row.querySelector('input[name^="appreciation"]');
        
        if (isAbsent) {
            row.classList.add('table-warning');
            noteInput.disabled = true;
            noteInput.value = '';
            appreciationInput.disabled = true;
            appreciationInput.value = 'Absent(e)';
        } else {
            row.classList.remove('table-warning');
            noteInput.disabled = false;
            noteInput.required = true;
            appreciationInput.disabled = false;
            if (appreciationInput.value === 'Absent(e)') {
                appreciationInput.value = '';
            }
        }
    }
    
    // Validation du formulaire
    document.getElementById('notesForm').addEventListener('submit', function(e) {
        let hasError = false;
        const noteInputs = document.querySelectorAll('.note-input:not([disabled])');
        
        noteInputs.forEach(input => {
            if (!input.value) {
                hasError = true;
                input.classList.add('is-invalid');
            } else {
                const note = parseFloat(input.value);
                const maxNote = parseFloat(input.max);
                
                if (isNaN(note) || note < 0 || note > maxNote) {
                    hasError = true;
                    input.classList.add('is-invalid');
                } else {
                    input.classList.remove('is-invalid');
                }
            }
        });
        
        if (hasError) {
            e.preventDefault();
            alert('Veuillez corriger les erreurs dans le formulaire.');
        }
    });
    
    // Validation en temps réel
    document.querySelectorAll('.note-input').forEach(input => {
        input.addEventListener('input', function() {
            const value = parseFloat(this.value);
            const maxNote = parseFloat(this.max);
            
            if (this.value && !isNaN(value) && value >= 0 && value <= maxNote) {
                this.classList.remove('is-invalid');
            }
        });
    });
});
</script>

<style>
.note-input {
    text-align: center;
}
.table-warning {
    background-color: #fff3cd !important;
}
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../layouts/main.php';
?>

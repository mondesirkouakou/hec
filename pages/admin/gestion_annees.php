<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../classes/AnneeUniversitaire.php';
require_once __DIR__ . '/../../classes/Semestre.php';

requireRole(ROLE_ADMIN);

$pageTitle = "Gestion des Années Universitaires";
include __DIR__ . '/../../includes/header.php';

$anneeManager = new AnneeUniversitaire(getDbConnection());
$semestreManager = new Semestre(getDbConnection());

$annees = $anneeManager->getAllAnnees();
$semestres = $semestreManager->getAllSemestres();

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_annee':
                $annee = $_POST['annee'];
                $active = isset($_POST['active']) ? 1 : 0;
                if ($anneeManager->addAnnee($annee, $active)) {
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Année universitaire ajoutée avec succès.'];
                } else {
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'Erreur lors de l\'ajout de l\'année universitaire.'];
                }
                break;
            case 'edit_annee':
                $id = $_POST['id_annee'];
                $annee = $_POST['annee'];
                $active = isset($_POST['active']) ? 1 : 0;
                if ($anneeManager->updateAnnee($id, $annee, $active)) {
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Année universitaire modifiée avec succès.'];
                } else {
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'Erreur lors de la modification de l\'année universitaire.'];
                }
                break;
            case 'delete_annee':
                $id = $_POST['id_annee'];
                if ($anneeManager->deleteAnnee($id)) {
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Année universitaire supprimée avec succès.'];
                } else {
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'Erreur lors de la suppression de l\'année universitaire.'];
                }
                break;
            case 'add_semestre':
                $nom = $_POST['nom_semestre'];
                $id_annee = $_POST['id_annee_semestre'];
                if ($semestreManager->addSemestre($nom, $id_annee)) {
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Semestre ajouté avec succès.'];
                } else {
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'Erreur lors de l\'ajout du semestre.'];
                }
                break;
            case 'edit_semestre':
                $id = $_POST['id_semestre'];
                $nom = $_POST['nom_semestre'];
                $id_annee = $_POST['id_annee_semestre'];
                if ($semestreManager->updateSemestre($id, $nom, $id_annee)) {
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Semestre modifié avec succès.'];
                } else {
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'Erreur lors de la modification du semestre.'];
                }
                break;
            case 'delete_semestre':
                $id = $_POST['id_semestre'];
                if ($semestreManager->deleteSemestre($id)) {
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Semestre supprimé avec succès.'];
                } else {
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'Erreur lors de la suppression du semestre.'];
                }
                break;
        }
    }
    redirect(BASE_URL . 'pages/admin/gestion_annees.php');
}

?>

<h1 class="mt-4">Gestion des Années Universitaires et Semestres</h1>

<?php if ($message): ?>
    <div class="message <?php echo $message['type']; ?>">
        <?php echo $message['text']; ?>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">Ajouter une nouvelle Année Universitaire</div>
            <div class="card-body">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="add_annee">
                    <div class="form-group">
                        <label for="annee">Année (ex: 2023-2024):</label>
                        <input type="text" class="form-control" id="annee" name="annee" required>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="active_annee" name="active" value="1">
                        <label class="form-check-label" for="active_annee">Active</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Ajouter Année</button>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Liste des Années Universitaires</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Année</th>
                            <th>Active</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($annees)): ?>
                            <?php foreach ($annees as $annee): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($annee['id_annee']); ?></td>
                                    <td><?php echo htmlspecialchars($annee['annee']); ?></td>
                                    <td><?php echo $annee['active'] ? 'Oui' : 'Non'; ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editAnneeModal<?php echo $annee['id_annee']; ?>">Modifier</button>
                                        <form action="" method="POST" style="display:inline-block;">
                                            <input type="hidden" name="action" value="delete_annee">
                                            <input type="hidden" name="id_annee" value="<?php echo $annee['id_annee']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette année universitaire ?');">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Edit Annee Modal -->
                                <div class="modal fade" id="editAnneeModal<?php echo $annee['id_annee']; ?>" tabindex="-1" role="dialog" aria-labelledby="editAnneeModalLabel<?php echo $annee['id_annee']; ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form action="" method="POST">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editAnneeModalLabel<?php echo $annee['id_annee']; ?>">Modifier l'Année Universitaire</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="action" value="edit_annee">
                                                    <input type="hidden" name="id_annee" value="<?php echo $annee['id_annee']; ?>">
                                                    <div class="form-group">
                                                        <label for="annee_edit<?php echo $annee['id_annee']; ?>">Année:</label>
                                                        <input type="text" class="form-control" id="annee_edit<?php echo $annee['id_annee']; ?>" name="annee" value="<?php echo htmlspecialchars($annee['annee']); ?>" required>
                                                    </div>
                                                    <div class="form-group form-check">
                                                        <input type="checkbox" class="form-check-input" id="active_annee_edit<?php echo $annee['id_annee']; ?>" name="active" value="1" <?php echo $annee['active'] ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="active_annee_edit<?php echo $annee['id_annee']; ?>">Active</label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">Aucune année universitaire trouvée.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">Ajouter un nouveau Semestre</div>
            <div class="card-body">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="add_semestre">
                    <div class="form-group">
                        <label for="nom_semestre">Nom du semestre:</label>
                        <input type="text" class="form-control" id="nom_semestre" name="nom_semestre" required>
                    </div>
                    <div class="form-group">
                        <label for="id_annee_semestre">Année Universitaire:</label>
                        <select class="form-control" id="id_annee_semestre" name="id_annee_semestre" required>
                            <?php foreach ($annees as $annee): ?>
                                <option value="<?php echo $annee['id_annee']; ?>"><?php echo htmlspecialchars($annee['annee']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Ajouter Semestre</button>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Liste des Semestres</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom du semestre</th>
                            <th>Année</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($semestres)): ?>
                            <?php foreach ($semestres as $semestre): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($semestre['id_semestre']); ?></td>
                                    <td><?php echo htmlspecialchars($semestre['nom_semestre']); ?></td>
                                    <td><?php echo htmlspecialchars($semestre['annee']); ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editSemestreModal<?php echo $semestre['id_semestre']; ?>">Modifier</button>
                                        <form action="" method="POST" style="display:inline-block;">
                                            <input type="hidden" name="action" value="delete_semestre">
                                            <input type="hidden" name="id_semestre" value="<?php echo $semestre['id_semestre']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce semestre ?');">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Edit Semestre Modal -->
                                <div class="modal fade" id="editSemestreModal<?php echo $semestre['id_semestre']; ?>" tabindex="-1" role="dialog" aria-labelledby="editSemestreModalLabel<?php echo $semestre['id_semestre']; ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form action="" method="POST">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editSemestreModalLabel<?php echo $semestre['id_semestre']; ?>">Modifier le Semestre</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="action" value="edit_semestre">
                                                    <input type="hidden" name="id_semestre" value="<?php echo $semestre['id_semestre']; ?>">
                                                    <div class="form-group">
                                                        <label for="nom_semestre_edit<?php echo $semestre['id_semestre']; ?>">Nom du semestre:</label>
                                                        <input type="text" class="form-control" id="nom_semestre_edit<?php echo $semestre['id_semestre']; ?>" name="nom_semestre" value="<?php echo htmlspecialchars($semestre['nom_semestre']); ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="id_annee_semestre_edit<?php echo $semestre['id_semestre']; ?>">Année Universitaire:</label>
                                                        <select class="form-control" id="id_annee_semestre_edit<?php echo $semestre['id_semestre']; ?>" name="id_annee_semestre" required>
                                                            <?php foreach ($annees as $annee): ?>
                                                                <option value="<?php echo $annee['id_annee']; ?>" <?php echo ($annee['id_annee'] == $semestre['id_annee']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($annee['annee']); ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">Aucun semestre trouvé.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
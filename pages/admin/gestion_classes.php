<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../classes/Classe.php';

requireRole(ROLE_ADMIN);

$pageTitle = "Gestion des Classes";
include __DIR__ . '/../../includes/header.php';

$classeManager = new Classe(getDbConnection());
$classes = $classeManager->getAllClasses();

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $nom = $_POST['nom_classe'];
                if ($classeManager->addClass($nom)) {
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Classe ajoutée avec succès.'];
                } else {
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'Erreur lors de l\'ajout de la classe.'];
                }
                break;
            case 'edit':
                $id = $_POST['id_classe'];
                $nom = $_POST['nom_classe'];
                if ($classeManager->updateClass($id, $nom)) {
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Classe modifiée avec succès.'];
                } else {
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'Erreur lors de la modification de la classe.'];
                }
                break;
            case 'delete':
                $id = $_POST['id_classe'];
                if ($classeManager->deleteClass($id)) {
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Classe supprimée avec succès.'];
                } else {
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'Erreur lors de la suppression de la classe.'];
                }
                break;
        }
    }
    redirect(BASE_URL . 'pages/admin/gestion_classes.php');
}

?>

<h1 class="mt-4">Gestion des Classes</h1>

<?php if ($message): ?>
    <div class="message <?php echo $message['type']; ?>">
        <?php echo $message['text']; ?>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">Ajouter une nouvelle classe</div>
    <div class="card-body">
        <form action="" method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="nom_classe">Nom de la classe:</label>
                <input type="text" class="form-control" id="nom_classe" name="nom_classe" required>
            </div>
            <button type="submit" class="btn btn-primary">Ajouter</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Liste des classes</div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom de la classe</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($classes)): ?>
                    <?php foreach ($classes as $classe): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($classe['id_classe']); ?></td>
                            <td><?php echo htmlspecialchars($classe['nom_classe']); ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editClassModal<?php echo $classe['id_classe']; ?>">Modifier</button>
                                <form action="" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id_classe" value="<?php echo $classe['id_classe']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette classe ?');">Supprimer</button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Class Modal -->
                        <div class="modal fade" id="editClassModal<?php echo $classe['id_classe']; ?>" tabindex="-1" role="dialog" aria-labelledby="editClassModalLabel<?php echo $classe['id_classe']; ?>" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <form action="" method="POST">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editClassModalLabel<?php echo $classe['id_classe']; ?>">Modifier la classe</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="action" value="edit">
                                            <input type="hidden" name="id_classe" value="<?php echo $classe['id_classe']; ?>">
                                            <div class="form-group">
                                                <label for="nom_classe_edit<?php echo $classe['id_classe']; ?>">Nom de la classe:</label>
                                                <input type="text" class="form-control" id="nom_classe_edit<?php echo $classe['id_classe']; ?>" name="nom_classe" value="<?php echo htmlspecialchars($classe['nom_classe']); ?>" required>
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
                        <td colspan="3">Aucune classe trouvée.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
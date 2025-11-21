<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../classes/ChefClasse.php';
require_once __DIR__ . '/../../classes/Classe.php';
require_once __DIR__ . '/../../classes/User.php';

requireRole(ROLE_ADMIN);

$pageTitle = "Gestion des Chefs de Classe";
include __DIR__ . '/../../includes/header.php';

$chefClasseManager = new ChefClasse(getDbConnection());
$classeManager = new Classe(getDbConnection());
$userManager = new User(getDbConnection());

$chefsDeClasse = $chefClasseManager->getAllChefsDeClasse();
$classes = $classeManager->getAllClasses();
$users = $userManager->getAllUsersByRole(ROLE_CHEF_CLASSE);

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'assign':
                $id_utilisateur = $_POST['id_utilisateur'];
                $id_classe = $_POST['id_classe'];
                if ($chefClasseManager->assignChefToClasse($id_utilisateur, $id_classe)) {
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Chef de classe assigné avec succès.'];
                } else {
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'Erreur lors de l\'assignation du chef de classe.'];
                }
                break;
            case 'unassign':
                $id_chef_classe = $_POST['id_chef_classe'];
                if ($chefClasseManager->unassignChefFromClasse($id_chef_classe)) {
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Chef de classe désassigné avec succès.'];
                } else {
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'Erreur lors de la désassignation du chef de classe.'];
                }
                break;
        }
    }
    redirect(BASE_URL . 'pages/admin/gestion_chefs.php');
}

?>

<h1 class="mt-4">Gestion des Chefs de Classe</h1>

<?php if ($message): ?>
    <div class="message <?php echo $message['type']; ?>">
        <?php echo $message['text']; ?>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">Assigner un Chef de Classe</div>
    <div class="card-body">
        <form action="" method="POST">
            <input type="hidden" name="action" value="assign">
            <div class="form-group">
                <label for="id_utilisateur">Sélectionner un utilisateur (Chef de Classe):</label>
                <select class="form-control" id="id_utilisateur" name="id_utilisateur" required>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id_utilisateur']; ?>"><?php echo htmlspecialchars($user['nom'] . ' ' . $user['prenom']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="id_classe">Sélectionner une classe:</label>
                <select class="form-control" id="id_classe" name="id_classe" required>
                    <?php foreach ($classes as $classe): ?>
                        <option value="<?php echo $classe['id_classe']; ?>"><?php echo htmlspecialchars($classe['nom_classe']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Assigner</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Chefs de Classe Actuels</div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Chef de Classe</th>
                    <th>Classe Assignée</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($chefsDeClasse)): ?>
                    <?php foreach ($chefsDeClasse as $chef): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($chef['id_chef_classe']); ?></td>
                            <td><?php echo htmlspecialchars($chef['nom_chef'] . ' ' . $chef['prenom_chef']); ?></td>
                            <td><?php echo htmlspecialchars($chef['nom_classe']); ?></td>
                            <td>
                                <form action="" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="action" value="unassign">
                                    <input type="hidden" name="id_chef_classe" value="<?php echo $chef['id_chef_classe']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir désassigner ce chef de classe ?');">Désassigner</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">Aucun chef de classe assigné.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
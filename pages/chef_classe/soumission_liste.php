<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../classes/ChefClasse.php';
require_once __DIR__ . '/../../classes/Etudiant.php';

requireRole(ROLE_CHEF_CLASSE);

$pageTitle = "Soumission de Liste";
include __DIR__ . '/../../includes/header.php';

$chefClasseManager = new ChefClasse(getDbConnection());
$etudiantManager = new Etudiant(getDbConnection());

$id_chef_classe_utilisateur = $_SESSION['user_id'];
$classe_chef = $chefClasseManager->getClasseByChefId($id_chef_classe_utilisateur);

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

$etudiants_classe = [];
if ($classe_chef) {
    $etudiants_classe = $etudiantManager->getEtudiantsByClasse($classe_chef['id_classe']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'submit_list') {
        // Ici, vous pouvez implémenter la logique de soumission de liste.
        // Par exemple, marquer la liste comme soumise, ou enregistrer des commentaires.
        // Pour cet exemple, nous allons juste simuler une soumission réussie.
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Liste soumise avec succès (simulation).'];
        redirect(BASE_URL . 'pages/chef_classe/soumission_liste.php');
    }
}

?>

<h1 class="mt-4">Soumission de Liste de Classe</h1>

<?php if ($message): ?>
    <div class="message <?php echo $message['type']; ?>">
        <?php echo $message['text']; ?>
    </div>
<?php endif; ?>

<?php if ($classe_chef): ?>
    <p>Classe assignée: <strong><?php echo htmlspecialchars($classe_chef['nom_classe']); ?></strong></p>

    <div class="card mb-4">
        <div class="card-header">Étudiants de la classe</div>
        <div class="card-body">
            <?php if (!empty($etudiants_classe)): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($etudiants_classe as $etudiant): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($etudiant['id_utilisateur']); ?></td>
                                <td><?php echo htmlspecialchars($etudiant['nom']); ?></td>
                                <td><?php echo htmlspecialchars($etudiant['prenom']); ?></td>
                                <td><?php echo htmlspecialchars($etudiant['email']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <form action="" method="POST">
                    <input type="hidden" name="action" value="submit_list">
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Êtes-vous sûr de vouloir soumettre cette liste ?');">Soumettre la liste</button>
                </form>
            <?php else: ?>
                <p>Aucun étudiant trouvé dans votre classe.</p>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div class="message error">
        Vous n'êtes pas assigné à une classe. Veuillez contacter l'administrateur.
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
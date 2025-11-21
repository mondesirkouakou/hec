<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../classes/Professeur.php';
require_once __DIR__ . '/../../classes/ChefClasse.php';

requireRole(ROLE_CHEF_CLASSE);

$pageTitle = "Liste des Professeurs";
include __DIR__ . '/../../includes/header.php';

$professeurManager = new Professeur(getDbConnection());
$chefClasseManager = new ChefClasse(getDbConnection());

$id_chef_classe_utilisateur = $_SESSION['user_id'];
$classe_chef = $chefClasseManager->getClasseByChefId($id_chef_classe_utilisateur);

$professeurs = [];
if ($classe_chef) {
    // Dans un système réel, les professeurs seraient liés aux classes ou aux matières enseignées dans la classe.
    // Pour cet exemple, nous listons tous les professeurs.
    $professeurs = $professeurManager->getAllProfesseurs();
}

?>

<h1 class="mt-4">Liste des Professeurs</h1>

<?php if ($classe_chef): ?>
    <p>Classe assignée: <strong><?php echo htmlspecialchars($classe_chef['nom_classe']); ?></strong></p>

    <div class="card">
        <div class="card-header">Professeurs</div>
        <div class="card-body">
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
                    <?php if (!empty($professeurs)): ?>
                        <?php foreach ($professeurs as $professeur): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($professeur['id_utilisateur']); ?></td>
                                <td><?php echo htmlspecialchars($professeur['nom']); ?></td>
                                <td><?php echo htmlspecialchars($professeur['prenom']); ?></td>
                                <td><?php echo htmlspecialchars($professeur['email']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">Aucun professeur trouvé.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="message error">
        Vous n'êtes pas assigné à une classe. Veuillez contacter l'administrateur.
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
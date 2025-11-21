<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../classes/Etudiant.php';
require_once __DIR__ . '/../../classes/ChefClasse.php';

requireRole(ROLE_CHEF_CLASSE);

$pageTitle = "Liste des Étudiants";
include __DIR__ . '/../../includes/header.php';

$etudiantManager = new Etudiant(getDbConnection());
$chefClasseManager = new ChefClasse(getDbConnection());

$id_chef_classe_utilisateur = $_SESSION['user_id'];
$classe_chef = $chefClasseManager->getClasseByChefId($id_chef_classe_utilisateur);

$etudiants = [];
if ($classe_chef) {
    $etudiants = $etudiantManager->getEtudiantsByClasse($classe_chef['id_classe']);
}

?>

<h1 class="mt-4">Liste des Étudiants de ma Classe</h1>

<?php if ($classe_chef): ?>
    <p>Classe assignée: <strong><?php echo htmlspecialchars($classe_chef['nom_classe']); ?></strong></p>

    <div class="card">
        <div class="card-header">Étudiants</div>
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
                    <?php if (!empty($etudiants)): ?>
                        <?php foreach ($etudiants as $etudiant): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($etudiant['id_utilisateur']); ?></td>
                                <td><?php echo htmlspecialchars($etudiant['nom']); ?></td>
                                <td><?php echo htmlspecialchars($etudiant['prenom']); ?></td>
                                <td><?php echo htmlspecialchars($etudiant['email']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">Aucun étudiant trouvé dans votre classe.</td>
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
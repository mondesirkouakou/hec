<?php
require_once '../../config/config.php'; // For BASE_URL and other global configs
require_once '../../config/database.php'; // For $pdo object

// Get classe_id from URL
$classe_id = filter_input(INPUT_GET, 'classe_id', FILTER_VALIDATE_INT);
var_dump($classe_id); exit();

if (!$classe_id) {
    // Handle error: no classe_id provided or invalid
    // Redirect or display an error message
    header('Location: ' . BASE_URL . 'admin/classes'); // Redirect to classes list
    exit();
}

// Fetch all available matieres
$stmt = $pdo->query("SELECT id, intitule FROM matieres ORDER BY intitule");
$allMatieres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch assigned matieres for this class
$stmt = $pdo->prepare("
    SELECT m.id, m.intitule, cm.coefficient, cm.credits
    FROM classes_matieres cm
    JOIN matieres m ON cm.matiere_id = m.id
    WHERE cm.classe_id = :classe_id
    ORDER BY m.intitule
");
$stmt->execute([':classe_id' => $classe_id]);
$assignedMatieres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matiere_id = filter_input(INPUT_POST, 'matiere_id', FILTER_VALIDATE_INT);
    $coefficient = filter_input(INPUT_POST, 'coefficient', FILTER_VALIDATE_INT);
    $credits = filter_input(INPUT_POST, 'credits', FILTER_VALIDATE_INT);

    if ($matiere_id && $coefficient && $credits) {
        // Check if already assigned
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM classes_matieres WHERE classe_id = :classe_id AND matiere_id = :matiere_id");
        $checkStmt->execute([':classe_id' => $classe_id, ':matiere_id' => $matiere_id]);
        if ($checkStmt->fetchColumn() > 0) {
            $_SESSION['error'] = "Cette matière est déjà attribuée à cette classe.";
        } else {
            $insertStmt = $pdo->prepare("INSERT INTO classes_matieres (classe_id, matiere_id, coefficient, credits) VALUES (:classe_id, :matiere_id, :coefficient, :credits)");
            if ($insertStmt->execute([
                ':classe_id' => $classe_id,
                ':matiere_id' => $matiere_id,
                ':coefficient' => $coefficient,
                ':credits' => $credits
            ])) {
                $_SESSION['success'] = "Matière attribuée avec succès.";
                // Redirect to refresh the page and show updated list
                header('Location: ' . BASE_URL . 'admin/classes/assign_matiere.php?classe_id=' . $classe_id);
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de l'attribution de la matière.";
            }
        }
    } else {
        $_SESSION['error'] = "Veuillez remplir tous les champs correctement.";
    }
    // Redirect to prevent form resubmission on refresh
    header('Location: ' . BASE_URL . 'admin/classes/assign_matiere.php?classe_id=' . $classe_id);
    exit();
}

// Display messages if any
$success_message = $_SESSION['success'] ?? null;
$error_message = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attribuer une matière à la classe</title>
    <!-- Inclure les CSS de Bootstrap si nécessaire -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Attribuer une matière à la classe</h1>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success mt-3" role="alert">
                <?= $success_message ?>
            </div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger mt-3" role="alert">
                <?= $error_message ?>
            </div>
        <?php endif; ?>
        
        <!-- Formulaire d'ajout de matière -->
        <form action="?classe_id=<?= $classe_id ?>" method="POST">
            <div class="mb-3">
                <label for="matiereSelect" class="form-label">Matière</label>
                <select class="form-control" id="matiereSelect" name="matiere_id" required>
                    <option value="">Sélectionner une matière</option>
                    <?php foreach ($allMatieres as $matiere): ?>
                        <option value="<?= htmlspecialchars($matiere['id']) ?>"><?= htmlspecialchars($matiere['intitule']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="coefficient" class="form-label">Coefficient</label>
                <input type="number" class="form-control" id="coefficient" name="coefficient" min="1" value="1" required>
            </div>
            <div class="mb-3">
                <label for="credits" class="form-label">Crédits</label>
                <input type="number" class="form-control" id="credits" name="credits" min="1" value="1" required>
            </div>
            <button type="submit" class="btn btn-primary">Attribuer</button>
        </form>

        <hr>

        <h2>Matières attribuées à cette classe</h2>
        <!-- Liste des matières attribuées -->
        <ul class="list-group">
            <?php if (!empty($assignedMatieres)): ?>
                <?php foreach ($assignedMatieres as $matiere): ?>
                    <li class="list-groupa_item">
                        <?= htmlspecialchars($matiere['intitule']) ?> (Coefficient: <?= htmlspecialchars($matiere['coefficient']) ?>, Crédits: <?= htmlspecialchars($matiere['credits']) ?>)
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="list-groupa_item">Aucune matière attribuée pour le moment.</li>
            <?php endif; ?>
        </ul>

        <a href="javascript:history.back()" class="btn btn-secondary mt-3">Retour</a>
    </div>

    <!-- Inclure les JS de Bootstrap si nécessaire -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
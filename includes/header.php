<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="container">
                <a href="<?php echo BASE_URL; ?>" class="logo"><?php echo APP_NAME; ?></a>
                <ul>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="<?php echo BASE_URL; ?>dashboard.php">Tableau de bord</a></li>
                        <li><a href="<?php echo BASE_URL; ?>controllers/logout.php">Déconnexion</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo BASE_URL; ?>login.php">Connexion</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>
    <main class="container">
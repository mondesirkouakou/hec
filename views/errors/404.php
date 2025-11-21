<?php
$pageTitle = 'Page non trouvée';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo APP_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 2rem 0;
        }
        .error-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            padding: 3rem 2rem;
            text-align: center;
        }
        .error-code {
            font-size: 8rem;
            font-weight: 700;
            color: #dc3545;
            margin-bottom: 1rem;
            line-height: 1;
        }
        .error-title {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #343a40;
        }
        .error-message {
            font-size: 1.25rem;
            color: #6c757d;
            margin-bottom: 2rem;
        }
        .btn-error {
            padding: 0.75rem 1.5rem;
            font-size: 1.1rem;
            border-radius: 50px;
            margin: 0.5rem;
        }
        footer {
            margin-top: 2rem;
            padding: 1.5rem 0;
            color: #6c757d;
            font-size: 0.9rem;
            border-top: 1px solid #e9ecef;
        }
    </style>
</head>
<body>
    <div class="container error-container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="error-card">
                    <div class="error-code">404</div>
                    <h1 class="error-title">Page non trouvée</h1>
                    <p class="error-message">
                        Désolé, la page que vous recherchez est introuvable ou n'existe plus.
                    </p>
                    <div class="d-flex justify-content-center flex-wrap">
                        <a href="<?= BASE_URL ?>" class="btn btn-primary btn-error">
                            <i class="fas fa-home me-2"></i> Page d'accueil
                        </a>
                        <button onclick="history.back()" class="btn btn-outline-secondary btn-error">
                            <i class="fas fa-arrow-left me-2"></i> Retour
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center">
        <div class="container">
            <p class="mb-0">
                &copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. Tous droits réservés.
            </p>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

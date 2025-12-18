<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Portail HEC Abidjan' ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/theme-custom.css?v=3">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/premium-ux.css?v=1">
</head>
<body>
    <header class="animated-header">
        <div class="container">
            <div class="header-content">
                <h1 class="logo-title"><a href="<?= BASE_URL ?>">Portail HEC Abidjan</a></h1>
                <nav class="animated-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="user-dropdown">
                            <button type="button" class="d-flex align-items-center p-0 border-0 bg-transparent text-decoration-none" id="userDropdown" style="color: inherit; cursor: pointer;">
                                <span class="welcome-text me-2">Bienvenue, <?= htmlspecialchars($_SESSION['display_name'] ?? $_SESSION['username']) ?></span>
                                <div class="user-avatar">
                                    <i class="fas fa-user-circle fa-2x"></i>
                                </div>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow animated--grow-in" id="userMenu" aria-labelledby="userDropdown" style="z-index: 2000;">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="#" id="themeToggle">
                                        <i class="fas fa-moon me-2 w-20"></i>
                                        <span id="themeLabel">Mode Sombre</span>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="<?= BASE_URL ?>profile">
                                        <i class="fas fa-user me-2 w-20"></i>
                                        Profil
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center text-danger" href="<?= BASE_URL ?>logout">
                                        <i class="fas fa-sign-out-alt me-2 w-20"></i>
                                        Déconnexion
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>login" class="nav-logo-link">
                            <img src="<?= BASE_URL ?>assets/images/logo.png" alt="HEC" class="nav-logo" />
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <main class="<?= !empty($isFullWidth) ? 'animated-container' : 'container animated-container' ?>">
        <div class="particles-container">
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success animated-alert">
                <?= $_SESSION['success'] ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger animated-alert">
                <?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="content-wrapper fade-in">
            <?= $content ?>
        </div>
    </main>

    <footer class="animated-footer reflection-effect">
        <div class="container">
            <p class="scrolling-text">&copy; <?= date('Y') ?> HEC Abidjan - Tous droits réservés</p>
            <div class="footer-links">
                <a href="#" class="footer-link ripple-effect">À propos</a>
                <a href="#" class="footer-link ripple-effect">Contact</a>
                <a href="#" class="footer-link ripple-effect">Aide</a>
            </div>
        </div>
    </footer>

    <div id="loading-overlay" class="loading-overlay">
        <div class="loading-spinner">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
        </div>
    </div>

    <button id="back-to-top" class="btn btn-primary back-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= BASE_URL ?>assets/js/main.js?v=2"></script>
    <script src="<?= BASE_URL ?>assets/js/theme-effects.js"></script>
    <script src="<?= BASE_URL ?>assets/js/premium-ux.js?v=1"></script>
    <style>
        .welcome-text{max-width:55vw;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:inline-block;vertical-align:middle}
        @media (min-width:768px){.welcome-text{max-width:280px}}
    </style>
</body>
</html>

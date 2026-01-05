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
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/theme-custom.css?v=15">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/premium-ux.css?v=15">
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
        
        /* FORCE FORM INPUTS TO BE VISIBLE IN DARK MODE */
        body.dark-mode .form-control,
        body.dark-mode input.form-control,
        body.dark-mode textarea.form-control,
        body.dark-mode select.form-control {
            background: #ffffff !important;
            color: #000000 !important;
            border-color: #3b82f6 !important;
            -webkit-text-fill-color: #000000 !important;
            opacity: 1 !important;
        }
        
        body.dark-mode .form-control::placeholder {
            color: #6b7280 !important;
            opacity: 0.7 !important;
            -webkit-text-fill-color: #6b7280 !important;
        }
        
        body.dark-mode select.form-control option,
        body.dark-mode select option,
        body.dark-mode select.form-control optgroup,
        body.dark-mode select optgroup,
        body.dark-mode select.form-control option:hover,
        body.dark-mode select option:hover,
        body.dark-mode select.form-control option:checked,
        body.dark-mode select option:checked {
            background: #ffffff !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
        }
        
        /* Force listbox items visibility */
        body.dark-mode select,
        body.dark-mode select.form-control,
        body.dark-mode select[size],
        body.dark-mode select[multiple] {
            background: #ffffff !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
        }
        
        body.dark-mode select option,
        body.dark-mode select.form-control option {
            background: #ffffff !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            padding: 4px 8px !important;
        }
        
        body.dark-mode select option:hover,
        body.dark-mode select.form-control option:hover {
            background: #f0f0f0 !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
        }
        
        /* Fix dropdown listbox background */
        body.dark-mode select,
        body.dark-mode select.form-control {
            background-color: #ffffff !important;
            background-image: none !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
        }
        
        /* Force dropdown menu background */
        body.dark-mode select::-webkit-calendar-picker-indicator,
        body.dark-mode select::-webkit-inner-spin-button,
        body.dark-mode select::-webkit-outer-spin-button {
            background: #ffffff !important;
            color: #000000 !important;
        }
        
        /* Ensure dropdown list is visible */
        body.dark-mode select:focus,
        body.dark-mode select.form-control:focus {
            background-color: #ffffff !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
        }
        
        /* Override any dark mode select styling */
        body.dark-mode * select,
        body.dark-mode * select.form-control {
            background: #ffffff !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
        }
        
        /* AGGRESSIVE FIX FOR COMBOBOX VISIBILITY */
        body.dark-mode select,
        body.dark-mode select.form-control,
        body.dark-mode .form-select,
        body.dark-mode .custom-select,
        body.dark-mode select[multiple],
        body.dark-mode select[size] {
            background-color: #ffffff !important;
            background: #ffffff !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            appearance: none !important;
            border-color: #3b82f6 !important;
        }
        
        body.dark-mode select option,
        body.dark-mode select.form-control option,
        body.dark-mode .form-select option,
        body.dark-mode .custom-select option {
            background-color: #ffffff !important;
            background: #ffffff !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
        }
        
        body.dark-mode select optgroup,
        body.dark-mode select.form-control optgroup,
        body.dark-mode .form-select optgroup,
        body.dark-mode .custom-select optgroup {
            background-color: #ffffff !important;
            background: #ffffff !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
            font-weight: bold !important;
        }
        
        /* Force black text in ALL scenarios */
        body.dark-mode select *,
        body.dark-mode select.form-control *,
        body.dark-mode .form-select *,
        body.dark-mode .custom-select * {
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
        }
        
        /* Override browser defaults */
        body.dark-mode select::-webkit-scrollbar,
        body.dark-mode select::-webkit-scrollbar-track,
        body.dark-mode select::-webkit-scrollbar-thumb {
            background: #ffffff !important;
        }
        
        /* Force dropdown arrow to be visible */
        body.dark-mode select::-webkit-select-arrow,
        body.dark-mode select::-ms-expand {
            background: #000000 !important;
            color: #000000 !important;
        }
        
        /* FORCE ALL OPTIONS TO HAVE WHITE BACKGROUND */
        body.dark-mode select option,
        body.dark-mode select.form-control option,
        body.dark-mode .form-select option,
        body.dark-mode .custom-select option,
        body.dark-mode select option:hover,
        body.dark-mode select.form-control option:hover,
        body.dark-mode .form-select option:hover,
        body.dark-mode .custom-select option:hover,
        body.dark-mode select option:checked,
        body.dark-mode select.form-control option:checked,
        body.dark-mode .form-select option:checked,
        body.dark-mode .custom-select option:checked,
        body.dark-mode select option:active,
        body.dark-mode select.form-control option:active,
        body.dark-mode .form-select option:active,
        body.dark-mode .custom-select option:active,
        body.dark-mode select option:focus,
        body.dark-mode select.form-control option:focus,
        body.dark-mode .form-select option:focus,
        body.dark-mode .custom-select option:focus {
            background-color: #ffffff !important;
            background: #ffffff !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
        }
        
        /* Override any gray backgrounds in options */
        body.dark-mode select option:nth-child(even),
        body.dark-mode select.form-control option:nth-child(even),
        body.dark-mode .form-select option:nth-child(even),
        body.dark-mode .custom-select option:nth-child(even),
        body.dark-mode select option:nth-child(odd),
        body.dark-mode select.form-control option:nth-child(odd),
        body.dark-mode .form-select option:nth-child(odd),
        body.dark-mode .custom-select option:nth-child(odd) {
            background-color: #ffffff !important;
            background: #ffffff !important;
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
        }
        
        /* FORCE CHECKBOXES VISIBILITY IN DARK MODE */
        body.dark-mode input[type="checkbox"],
        body.dark-mode input[type="radio"] {
            background-color: #ffffff !important;
            background: #ffffff !important;
            border: 2px solid #3b82f6 !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            appearance: none !important;
            width: 18px !important;
            height: 18px !important;
            border-radius: 3px !important;
            cursor: pointer !important;
            position: relative !important;
        }
        
        body.dark-mode input[type="radio"] {
            border-radius: 50% !important;
        }
        
        body.dark-mode input[type="checkbox"]:checked,
        body.dark-mode input[type="radio"]:checked {
            background-color: #3b82f6 !important;
            background: #3b82f6 !important;
        }
        
        body.dark-mode input[type="checkbox"]:checked::after,
        body.dark-mode input[type="radio"]:checked::after {
            content: '✓' !important;
            position: absolute !important;
            top: -2px !important;
            left: 2px !important;
            color: #ffffff !important;
            font-size: 14px !important;
            font-weight: bold !important;
        }
        
        body.dark-mode input[type="radio"]:checked::after {
            content: '•' !important;
            top: -3px !important;
            left: 4px !important;
        }
        
        /* Checkbox labels and containers */
        body.dark-mode .form-check,
        body.dark-mode .form-check-label,
        body.dark-mode .checkbox-label,
        body.dark-mode label[for*="matiere"],
        body.dark-mode .matiere-checkbox,
        body.dark-mode .matiere-container {
            color: #000000 !important;
            background: #ffffff !important;
            -webkit-text-fill-color: #000000 !important;
        }
        
        /* Force checkbox containers to be white */
        body.dark-mode .form-check-inline,
        body.dark-mode .checkbox-group,
        body.dark-mode .matieres-container,
        body.dark-mode .matieres-list {
            background: #ffffff !important;
        }
        
        body.dark-mode .form-check-inline .form-check-label,
        body.dark-mode .checkbox-group label,
        body.dark-mode .matieres-container label,
        body.dark-mode .matieres-list label {
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
        }
        
        /* Override any dark mode checkbox styling */
        body.dark-mode * input[type="checkbox"],
        body.dark-mode * input[type="radio"] {
            background-color: #ffffff !important;
            border: 2px solid #3b82f6 !important;
        }
        
        body.dark-mode * .form-check-label,
        body.dark-mode * label {
            color: #000000 !important;
            -webkit-text-fill-color: #000000 !important;
        }
        
        /* FORCE BUTTONS VISIBILITY IN DARK MODE */
        body.dark-mode .btn,
        body.dark-mode .btn-primary,
        body.dark-mode .btn-secondary,
        body.dark-mode .btn-success,
        body.dark-mode .btn-warning,
        body.dark-mode .btn-danger,
        body.dark-mode .btn-info,
        body.dark-mode .btn-light,
        body.dark-mode .btn-dark,
        body.dark-mode .btn-outline-primary,
        body.dark-mode .btn-outline-secondary,
        body.dark-mode .btn-outline-success,
        body.dark-mode .btn-outline-warning,
        body.dark-mode .btn-outline-danger,
        body.dark-mode .btn-outline-info,
        body.dark-mode .btn-outline-light,
        body.dark-mode .btn-outline-dark {
            background-color: #ffffff !important;
            background: #ffffff !important;
            color: #000000 !important;
            border-color: #3b82f6 !important;
            -webkit-text-fill-color: #000000 !important;
        }
        
        body.dark-mode .btn:hover,
        body.dark-mode .btn-primary:hover,
        body.dark-mode .btn-secondary:hover,
        body.dark-mode .btn-success:hover,
        body.dark-mode .btn-warning:hover,
        body.dark-mode .btn-danger:hover,
        body.dark-mode .btn-info:hover,
        body.dark-mode .btn-light:hover,
        body.dark-mode .btn-dark:hover,
        body.dark-mode .btn-outline-primary:hover,
        body.dark-mode .btn-outline-secondary:hover,
        body.dark-mode .btn-outline-success:hover,
        body.dark-mode .btn-outline-warning:hover,
        body.dark-mode .btn-outline-danger:hover,
        body.dark-mode .btn-outline-info:hover,
        body.dark-mode .btn-outline-light:hover,
        body.dark-mode .btn-outline-dark:hover {
            background-color: #f0f0f0 !important;
            background: #f0f0f0 !important;
            color: #000000 !important;
            border-color: #0752dd !important;
            -webkit-text-fill-color: #000000 !important;
        }
        
        /* Specific button for "Tableau de bord" */
        body.dark-mode a[href*="dashboard"],
        body.dark-mode a[href*="tableau"],
        body.dark-mode a[href*="board"],
        body.dark-mode .btn-dashboard,
        body.dark-mode .btn-tableau,
        body.dark-mode .btn-board,
        body.dark-mode button[title*="Tableau"],
        body.dark-mode button[title*="tableau"],
        body.dark-mode button[title*="board"] {
            background-color: #ffffff !important;
            background: #ffffff !important;
            color: #000000 !important;
            border-color: #3b82f6 !important;
            -webkit-text-fill-color: #000000 !important;
        }
        
        body.dark-mode a[href*="dashboard"]:hover,
        body.dark-mode a[href*="tableau"]:hover,
        body.dark-mode a[href*="board"]:hover,
        body.dark-mode .btn-dashboard:hover,
        body.dark-mode .btn-tableau:hover,
        body.dark-mode .btn-board:hover,
        body.dark-mode button[title*="Tableau"]:hover,
        body.dark-mode button[title*="tableau"]:hover,
        body.dark-mode button[title*="board"]:hover {
            background-color: #f0f0f0 !important;
            background: #f0f0f0 !important;
            color: #000000 !important;
            border-color: #0752dd !important;
            -webkit-text-fill-color: #000000 !important;
        }
        
        /* Force all links styled as buttons */
        body.dark-mode a.btn,
        body.dark-mode a[role="button"],
        body.dark-mode a.button {
            background-color: #ffffff !important;
            background: #ffffff !important;
            color: #000000 !important;
            border-color: #3b82f6 !important;
            -webkit-text-fill-color: #000000 !important;
        }
        
        body.dark-mode a.btn:hover,
        body.dark-mode a[role="button"]:hover,
        body.dark-mode a.button:hover {
            background-color: #f0f0f0 !important;
            background: #f0f0f0 !important;
            color: #000000 !important;
            border-color: #0752dd !important;
            -webkit-text-fill-color: #000000 !important;
        }
    </style>
</body>
</html>

<?php
// Constantes pour les rôles utilisateurs
define('ROLE_ADMIN', 'admin');
define('ROLE_CHEF_CLASSE', 'chef_classe');
define('ROLE_PROFESSEUR', 'professeur');
define('ROLE_ETUDIANT', 'etudiant');

// Autres constantes du projet
define('APP_NAME', 'HEC Gestion Scolaire');
define('DEFAULT_LANG', 'fr');

// Chemins (à ajuster si nécessaire)
define('ROOT_PATH', dirname(__DIR__));
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('VIEWS_PATH', ROOT_PATH . '/pages');

// Statuts des notes (exemple)
define('NOTE_STATUS_PUBLISHED', 'publiee');
define('NOTE_STATUS_DRAFT', 'brouillon');
?>
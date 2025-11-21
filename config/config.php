<?php
// Paramètres de connexion à la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hec_db');

// Paramètres de messagerie (exemple)
define('MAIL_HOST', 'smtp.example.com');
define('MAIL_USER', 'your_email@example.com');
define('MAIL_PASS', 'your_email_password');
define('MAIL_PORT', 587);
define('MAIL_ENCRYPTION', 'tls');

// Autres paramètres globaux
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
define('BASE_URL', $scheme . '://' . $host . ($basePath ? $basePath . '/' : '/'));
define('APP_NAME', 'HEC Abidjan');

// Démarrer la session (si ce n'est pas déjà fait)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
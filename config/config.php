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
$forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null;
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($forwardedProto === 'https');
$scheme = $isHttps ? 'https' : 'http';
$host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? ($_SERVER['HTTP_HOST'] ?? 'localhost');
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
define('BASE_URL', $scheme . '://' . $host . ($basePath ? $basePath . '/' : '/'));
define('APP_NAME', 'HEC Abidjan');

// Démarrer la session (si ce n'est pas déjà fait)
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => $basePath ? ($basePath . '/') : '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
?>
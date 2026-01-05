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

// Headers de sécurité
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

// Content Security Policy
$csp = "default-src 'self'; ";
$csp .= "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; ";
$csp .= "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com; ";
$csp .= "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; ";
$csp .= "img-src 'self' data: https:; ";
$csp .= "connect-src 'self'; ";
$csp .= "frame-ancestors 'none';";
header("Content-Security-Policy: " . $csp);

// HSTS (HTTP Strict Transport Security) - uniquement en HTTPS
if ($isHttps) {
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
}
?>
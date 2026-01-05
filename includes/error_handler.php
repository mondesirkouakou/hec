<?php
/**
 * Gestionnaire d'erreurs sécurisé
 * Désactive l'affichage des erreurs en production et les log de manière sécurisée
 */

// Déterminer l'environnement (production ou développement)
define('IS_PRODUCTION', !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1', 'localhost']));

// Configuration selon l'environnement
if (IS_PRODUCTION) {
    // PRODUCTION : Masquer toutes les erreurs
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0);
} else {
    // DÉVELOPPEMENT : Afficher les erreurs
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

// Toujours logger les erreurs
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Créer le dossier logs s'il n'existe pas
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

/**
 * Gestionnaire d'erreurs personnalisé
 */
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    // Ne pas traiter les erreurs supprimées avec @
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $errorTypes = [
        E_ERROR => 'ERROR',
        E_WARNING => 'WARNING',
        E_PARSE => 'PARSE',
        E_NOTICE => 'NOTICE',
        E_CORE_ERROR => 'CORE_ERROR',
        E_CORE_WARNING => 'CORE_WARNING',
        E_COMPILE_ERROR => 'COMPILE_ERROR',
        E_COMPILE_WARNING => 'COMPILE_WARNING',
        E_USER_ERROR => 'USER_ERROR',
        E_USER_WARNING => 'USER_WARNING',
        E_USER_NOTICE => 'USER_NOTICE',
        E_STRICT => 'STRICT',
        E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
        E_DEPRECATED => 'DEPRECATED',
        E_USER_DEPRECATED => 'USER_DEPRECATED',
    ];
    
    $errorType = $errorTypes[$errno] ?? 'UNKNOWN';
    
    // Logger l'erreur
    $logMessage = sprintf(
        "[%s] %s: %s in %s on line %d\n",
        date('Y-m-d H:i:s'),
        $errorType,
        $errstr,
        $errfile,
        $errline
    );
    
    error_log($logMessage, 3, __DIR__ . '/../logs/php_errors.log');
    
    // En production, afficher un message générique
    if (IS_PRODUCTION) {
        if ($errno === E_ERROR || $errno === E_USER_ERROR || $errno === E_RECOVERABLE_ERROR) {
            showErrorPage();
            exit();
        }
    }
    
    return true;
}

/**
 * Gestionnaire d'exceptions personnalisé
 */
function customExceptionHandler($exception) {
    // Logger l'exception
    $logMessage = sprintf(
        "[%s] EXCEPTION: %s in %s on line %d\nStack trace:\n%s\n",
        date('Y-m-d H:i:s'),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );
    
    error_log($logMessage, 3, __DIR__ . '/../logs/php_errors.log');
    
    // En production, afficher un message générique
    if (IS_PRODUCTION) {
        showErrorPage();
    } else {
        // En développement, afficher les détails
        echo '<h1>Exception non gérée</h1>';
        echo '<p><strong>Message:</strong> ' . htmlspecialchars($exception->getMessage()) . '</p>';
        echo '<p><strong>Fichier:</strong> ' . htmlspecialchars($exception->getFile()) . '</p>';
        echo '<p><strong>Ligne:</strong> ' . $exception->getLine() . '</p>';
        echo '<pre>' . htmlspecialchars($exception->getTraceAsString()) . '</pre>';
    }
    
    exit();
}

/**
 * Gestionnaire d'erreurs fatales
 */
function customShutdownHandler() {
    $error = error_get_last();
    
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Logger l'erreur fatale
        $logMessage = sprintf(
            "[%s] FATAL ERROR: %s in %s on line %d\n",
            date('Y-m-d H:i:s'),
            $error['message'],
            $error['file'],
            $error['line']
        );
        
        error_log($logMessage, 3, __DIR__ . '/../logs/php_errors.log');
        
        // En production, afficher un message générique
        if (IS_PRODUCTION) {
            showErrorPage();
        }
    }
}

/**
 * Affiche une page d'erreur générique (production)
 */
function showErrorPage() {
    // Nettoyer le buffer de sortie
    if (ob_get_length()) {
        ob_clean();
    }
    
    http_response_code(500);
    
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Erreur - <?= APP_NAME ?? 'HEC Abidjan' ?></title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                margin: 0;
                padding: 20px;
            }
            .error-container {
                background: white;
                border-radius: 10px;
                padding: 40px;
                max-width: 500px;
                text-align: center;
                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            }
            .error-icon {
                font-size: 64px;
                margin-bottom: 20px;
            }
            h1 {
                color: #333;
                margin-bottom: 10px;
            }
            p {
                color: #666;
                line-height: 1.6;
                margin-bottom: 30px;
            }
            .btn {
                display: inline-block;
                background: #667eea;
                color: white;
                padding: 12px 30px;
                text-decoration: none;
                border-radius: 5px;
                transition: background 0.3s;
            }
            .btn:hover {
                background: #5568d3;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">⚠️</div>
            <h1>Une erreur est survenue</h1>
            <p>Nous sommes désolés, une erreur technique s'est produite. Nos équipes ont été notifiées et travaillent à résoudre le problème.</p>
            <p>Veuillez réessayer dans quelques instants.</p>
            <a href="<?= BASE_URL ?? '/' ?>" class="btn">Retour à l'accueil</a>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Enregistrer les gestionnaires
set_error_handler('customErrorHandler');
set_exception_handler('customExceptionHandler');
register_shutdown_function('customShutdownHandler');
?>

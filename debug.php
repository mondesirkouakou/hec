<?php
echo "<h1>Informations de débogage</h1>";

// Afficher les informations du serveur
if (function_exists('apache_get_modules')) {
    echo "<h2>Modules Apache chargés :</h2>";
    echo "<pre>";
    print_r(apache_get_modules());
    echo "</pre>";
}

// Afficher les variables d'environnement
if (isset($_SERVER['HTTP_MOD_REWRITE']) && $_SERVER['HTTP_MOD_REWRITE'] === 'On') {
    echo "<p style='color:green;'>Le module de réécriture est activé.</p>";
} else {
    echo "<p style='color:red;'>Le module de réécriture n'est PAS activé ou n'est pas détecté correctement.</p>";
}

// Afficher les informations de réécriture
echo "<h2>Variables de réécriture :</h2>";
$rewrite_vars = [
    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'Non défini',
    'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? 'Non défini',
    'PHP_SELF' => $_SERVER['PHP_SELF'] ?? 'Non défini',
    'ORIG_SCRIPT_NAME' => $_SERVER['ORIG_SCRIPT_NAME'] ?? 'Non défini',
    'SCRIPT_FILENAME' => $_SERVER['SCRIPT_FILENAME'] ?? 'Non défini',
    'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? 'Non défini',
    'DOCUMENT_URI' => $_SERVER['DOCUMENT_URI'] ?? 'Non défini',
    'HTTPS' => $_SERVER['HTTPS'] ?? 'Non défini',
    'HTTP_MOD_REWRITE' => $_SERVER['HTTP_MOD_REWRITE'] ?? 'Non défini',
    'REDIRECT_STATUS' => $_SERVER['REDIRECT_STATUS'] ?? 'Non défini'
];

echo "<table border='1' cellpadding='5'>";
foreach ($rewrite_vars as $key => $value) {
    echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
}
echo "</table>";

// Tester la réécriture d'URL
echo "<h2>Test de réécriture d'URL :</h2>";
$test_urls = [
    '/',
    '/login',
    '/admin',
    '/admin/dashboard',
    '/api/test'
];

echo "<ul>";
foreach ($test_urls as $url) {
    $full_url = 'http://' . $_SERVER['HTTP_HOST'] . '/hec' . $url;
    echo "<li><a href='$full_url' target='_blank'>$url</a>";
    
    // Tester avec file_exists
    $file_path = $_SERVER['DOCUMENT_ROOT'] . '/hec' . $url;
    if (file_exists($file_path) && !is_dir($file_path)) {
        echo " (existe physiquement)";
    } else {
        echo " (n'existe pas physiquement)";
    }
    
    echo "</li>";
}
echo "</ul>";

// Vérifier si le fichier .htaccess est lu
echo "<h2>Vérification du fichier .htaccess :</h2>";
$htaccess_path = __DIR__ . '/.htaccess';
if (file_exists($htaccess_path)) {
    echo "<p>Fichier .htaccess trouvé à : " . realpath($htaccess_path) . "</p>";
    echo "<p>Taille du fichier : " . filesize($htaccess_path) . " octets</p>";
    
    // Lire le contenu du fichier .htaccess
    $htaccess_content = file_get_contents($htaccess_path);
    echo "<h3>Contenu du fichier .htaccess :</h3>";
    echo "<pre>" . htmlspecialchars($htaccess_content) . "</pre>";
    
    // Vérifier si le module de réécriture est activé dans httpd.conf
    echo "<h3>Vérification de la configuration d'Apache :</h3>";
    $httpd_conf = 'C:/xampp/apache/conf/httpd.conf';
    if (file_exists($httpd_conf)) {
        $httpd_content = file_get_contents($httpd_conf);
        if (strpos($httpd_content, 'LoadModule rewrite_module') !== false) {
            echo "<p style='color:green;'>Le module de réécriture est chargé dans httpd.conf</p>";
            
            // Vérifier la configuration AllowOverride
            if (preg_match('/<Directory\s+"C:\\/xampp\\/htdocs">.*?AllowOverride\s+(\w+).*?<\/Directory>/is', $httpd_content, $matches)) {
                $allow_override = $matches[1];
                if (strtoupper($allow_override) === 'ALL') {
                    echo "<p style='color:green;'>AllowOverride est défini sur 'All' pour le répertoire htdocs</p>";
                } else {
                    echo "<p style='color:red;'>AllowOverride est défini sur '$allow_override' au lieu de 'All' pour le répertoire htdocs</p>";
                    echo "<p>Pour corriger cela, modifiez le fichier $httpd_conf et assurez-vous que la section Directory pour htdocs contient :</p>";
                    echo "<pre>&lt;Directory \"C:/xampp/htdocs\"&gt;
    Options Indexes FollowSymLinks Includes ExecCGI
    AllowOverride All
    Require all granted
&lt;/Directory&gt;</pre>";
                }
            } else {
                echo "<p style='color:red;'>Impossible de trouver la configuration AllowOverride pour le répertoire htdocs dans httpd.conf</p>";
            }
        } else {
            echo "<p style='color:red;'>Le module de réécriture n'est pas chargé dans httpd.conf</p>";
            echo "<p>Pour activer le module de réécriture, décommentez (supprimez le #) la ligne suivante dans $httpd_conf :</p>";
            echo "<pre>LoadModule rewrite_module modules/mod_rewrite.so</pre>";
        }
    } else {
        echo "<p>Impossible de trouver le fichier de configuration d'Apache à : $httpd_conf</p>";
    }
} else {
    echo "<p style='color:red;'>Le fichier .htaccess n'a pas été trouvé à l'emplacement attendu : $htaccess_path</p>";
}

// Vérifier si le module mod_rewrite est chargé
echo "<h2>Vérification du module mod_rewrite :</h2>";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    if (in_array('mod_rewrite', $modules)) {
        echo "<p style='color:green;'>Le module mod_rewrite est chargé.</p>";
    } else {
        echo "<p style='color:red;'>Le module mod_rewrite n'est PAS chargé.</p>";
        echo "<p>Pour activer le module mod_rewrite, suivez ces étapes :</p>";
        echo "<ol>";
        echo "<li>Ouvrez le fichier de configuration d'Apache (httpd.conf)</li>";
        echo "<li>Recherchez la ligne : <code>#LoadModule rewrite_module modules/mod_rewrite.so</code></li>";
        echo "<li>Supprimez le # au début de la ligne pour la décommenter</li>";
        echo "<li>Redémarrez Apache</li>";
        echo "</ol>";
    }
} else {
    echo "<p>Impossible de vérifier les modules Apache chargés (la fonction apache_get_modules n'est pas disponible).</p>";
}

// Vérifier si le fichier index.php est accessible
echo "<h2>Vérification de l'accessibilité de index.php :</h2>";
$index_path = __DIR__ . '/index.php';
if (file_exists($index_path)) {
    echo "<p>Le fichier index.php a été trouvé à : " . realpath($index_path) . "</p>";
    
    // Vérifier les permissions
    $permissions = [
        'readable' => is_readable($index_path) ? 'Oui' : 'Non',
        'writable' => is_writable($index_path) ? 'Oui' : 'Non',
        'executable' => is_executable($index_path) ? 'Oui' : 'Non'
    ];
    
    echo "<ul>";
    foreach ($permissions as $perm => $value) {
        echo "<li>Permission $perm : $value</li>";
    }
    echo "</ul>";
    
    // Afficher les premières lignes du fichier index.php
    echo "<h3>Contenu de index.php (premières 50 lignes) :</h3>";
    $index_content = file($index_path);
    echo "<pre>" . htmlspecialchars(implode('', array_slice($index_content, 0, 50)));
    if (count($index_content) > 50) {
        echo "\n[...] (fichier tronqué, " . (count($index_content) - 50) . " lignes supplémentaires)";
    }
    echo "</pre>";
} else {
    echo "<p style='color:red;'>Le fichier index.php n'a pas été trouvé à l'emplacement attendu : $index_path</p>";
}

// Vérifier les erreurs de réécriture dans les logs
echo "<h2>Vérification des erreurs de réécriture :</h2>";
$log_files = [
    'C:/xampp/apache/logs/error.log',
    'C:/xampp/php/logs/php_error_log',
    'C:/xampp/apache/logs/access.log'
];

echo "<ul>";
foreach ($log_files as $log_file) {
    if (file_exists($log_file)) {
        $log_size = filesize($log_file);
        $log_size_mb = round($log_size / (1024 * 1024), 2);
        echo "<li>Fichier de log trouvé : $log_file ($log_size_mb Mo)";
        
        // Lire les dernières lignes du fichier de log
        $log_lines = [];
        if ($handle = fopen($log_file, 'r')) {
            $buffer = 4096; // Taille du buffer pour la lecture à l'envers
            fseek($handle, -$buffer, SEEK_END);
            $data = fread($handle, $buffer);
            $log_lines = array_filter(explode("\n", $data));
            fclose($handle);
            
            // Filtrer les lignes pertinentes
            $relevant_lines = [];
            foreach ($log_lines as $line) {
                if (stripos($line, 'rewrite') !== false || 
                    stripos($line, '.htaccess') !== false || 
                    stripos($line, 'File does not exist') !== false) {
                    $relevant_lines[] = $line;
                }
            }
            
            if (!empty($relevant_lines)) {
                echo "<pre>" . htmlspecialchars(implode("\n", array_slice($relevant_lines, -10))) . "</pre>";
            } else {
                echo "<p>Aucune erreur de réécriture récente trouvée dans ce fichier de log.</p>";
            }
        }
        
        echo "</li>";
    } else {
        echo "<li>Fichier de log non trouvé : $log_file</li>";
    }
}
echo "</ul>";

// Vérifier les variables d'environnement
echo "<h2>Variables d'environnement :</h2>";
echo "<pre>";
$env_vars = [
    'SERVER_SOFTWARE' => $_SERVER['SERVER_SOFTWARE'] ?? 'Non défini',
    'SERVER_ADMIN' => $_SERVER['SERVER_ADMIN'] ?? 'Non défini',
    'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? 'Non défini',
    'SERVER_ADDR' => $_SERVER['SERVER_ADDR'] ?? 'Non défini',
    'SERVER_PORT' => $_SERVER['SERVER_PORT'] ?? 'Non défini',
    'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? 'Non défini',
    'REQUEST_SCHEME' => $_SERVER['REQUEST_SCHEME'] ?? 'Non défini',
    'CONTEXT_PREFIX' => $_SERVER['CONTEXT_PREFIX'] ?? 'Non défini',
    'CONTEXT_DOCUMENT_ROOT' => $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?? 'Non défini'
];

foreach ($env_vars as $key => $value) {
    echo "$key: $value\n";
}
echo "</pre>";

// Vérifier les en-têtes de réponse
echo "<h2>En-têtes de réponse :</h2>";
echo "<pre>";
$headers = headers_list();
foreach ($headers as $header) {
    echo "$header\n";
}
echo "</pre>";

// Vérifier les permissions du répertoire
echo "<h2>Permissions du répertoire :</h2>";
$dir_perms = [
    'readable' => is_readable(__DIR__) ? 'Oui' : 'Non',
    'writable' => is_writable(__DIR__) ? 'Oui' : 'Non',
    'executable' => is_executable(__DIR__) ? 'Oui' : 'Non'
];

echo "<ul>";
foreach ($dir_perms as $perm => $value) {
    echo "<li>Permission $perm : $value</li>";
}
echo "</ul>";

// Vérifier les fichiers .htaccess dans les sous-répertoires
echo "<h2>Fichiers .htaccess dans les sous-répertoires :</h2>";
$htaccess_files = [];
$iterator = new RecursiveDirectoryIterator(__DIR__, RecursiveDirectoryIterator::SKIP_DOTS);
$recursive = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);

foreach ($recursive as $file) {
    if ($file->isFile() && $file->getFilename() === '.htaccess') {
        $htaccess_files[] = $file->getPathname();
    }
}

if (!empty($htaccess_files)) {
    echo "<ul>";
    foreach ($htaccess_files as $htaccess) {
        $relative_path = str_replace(__DIR__, '.', $htaccess);
        echo "<li>$relative_path";
        
        // Afficher un aperçu du contenu
        $content = file_get_contents($htaccess);
        echo "<pre>" . htmlspecialchars(substr($content, 0, 200)) . (strlen($content) > 200 ? '...' : '') . "</pre>";
        
        echo "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Aucun autre fichier .htaccess trouvé dans les sous-répertoires.</p>";
}

// Vérifier la configuration de PHP
echo "<h2>Configuration PHP (php.ini) :</h2>";
$php_ini_settings = [
    'allow_url_fopen',
    'allow_url_include',
    'display_errors',
    'display_startup_errors',
    'error_log',
    'error_reporting',
    'log_errors',
    'max_execution_time',
    'max_input_time',
    'memory_limit',
    'post_max_size',
    'upload_max_filesize'
];

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Paramètre</th><th>Valeur</th></tr>";
foreach ($php_ini_settings as $setting) {
    $value = ini_get($setting);
    echo "<tr><td>$setting</td><td>$value</td></tr>";
}
echo "</table>";

// Vérifier les erreurs de configuration courantes
echo "<h2>Vérification des erreurs courantes :</h2>";
$issues = [];

// Vérifier si le module mod_rewrite est chargé
if (function_exists('apache_get_modules') && !in_array('mod_rewrite', apache_get_modules())) {
    $issues[] = "Le module Apache 'mod_rewrite' n'est pas chargé.";
}

// Vérifier si AllowOverride est défini sur All
$httpd_conf = @file_get_contents('C:/xampp/apache/conf/httpd.conf');
if ($httpd_conf && !preg_match('/<Directory\s+"C:\\/xampp\\/htdocs">.*?AllowOverride\s+All/is', $httpd_conf)) {
    $issues[] = "La directive 'AllowOverride' n'est pas définie sur 'All' pour le répertoire htdocs dans httpd.conf";
}

// Vérifier si le fichier .htaccess est présent
if (!file_exists(__DIR__ . '/.htaccess')) {
    $issues[] = "Le fichier .htaccess est manquant dans le répertoire racine.";
}

// Vérifier les permissions du répertoire
if (!is_writable(__DIR__)) {
    $issues[] = "Le répertoire racine n'est pas accessible en écriture.";
}

// Afficher les problèmes détectés
if (empty($issues)) {
    echo "<p style='color:green;'>Aucun problème critique détecté dans la configuration.</p>";
} else {
    echo "<div style='color:red;'>";
    echo "<h3>Problèmes détectés :</h3>";
    echo "<ul>";
    foreach ($issues as $issue) {
        echo "<li>$issue</li>";
    }
    echo "</ul>";
    echo "</div>";
}

// Suggestions de résolution
echo "<h2>Suggestions pour résoudre les problèmes :</h2>";
echo "<ol>";
echo "<li><strong>Vérifiez que le module mod_rewrite est activé :</strong>";
echo "<ul>";
echo "<li>Ouvrez le fichier C:\\xampp\\apache\\conf\\httpd.conf</li>";
echo "<li>Recherchez la ligne : <code>#LoadModule rewrite_module modules/mod_rewrite.so</code></li>";
echo "<li>Supprimez le # au début de la ligne pour la décommenter</li>";
echo "<li>Redémarrez Apache</li>";
echo "</ul></li>";

echo "<li><strong>Vérifiez la configuration AllowOverride :</strong>";
echo "<ul>";
echo "<li>Ouvrez le fichier C:\\xampp\\apache\\conf\\httpd.conf</li>";
echo "<li>Recherchez la section &lt;Directory \"C:/xampp/htdocs\"&gt;</li>";
echo "<li>Assurez-vous qu'elle contient : <code>AllowOverride All</code></li>";
echo "<li>Redémarrez Apache</li>";
echo "</ul></li>";

echo "<li><strong>Vérifiez les permissions des fichiers :</strong>";
echo "<ul>";
echo "<li>Assurez-vous que le serveur web a les permissions de lecture sur tous les fichiers et répertoires</li>";
echo "<li>Assurez-vous que le fichier .htaccess est lisible</li>";
echo "</ul></li>";

echo "<li><strong>Vérifiez les logs d'erreur :</strong>";
echo "<ul>";
echo "<li>Consultez le fichier C:\\xampp\\apache\\logs\\error.log pour les erreurs récentes</li>";
echo "<li>Vérifiez également C:\\xampp\\php\\logs\\php_error_log pour les erreurs PHP</li>";
echo "</ul></li>";
echo "</ol>";

// Afficher les informations sur le système
echo "<h2>Informations système :</h2>";
echo "<ul>";
echo "<li>Système d'exploitation : " . php_uname('s') . ' ' . php_uname('r') . "</li>";
echo "<li>Version de PHP : " . phpversion() . "</li>";
echo "<li>Serveur web : " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Inconnu') . "</li>";
echo "<li>Répertoire racine du document : " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Inconnu') . "</li>";
echo "<li>Répertoire du script : " . __DIR__ . "</li>";
echo "</ul>";

// Lien vers la documentation
echo "<h2>Documentation utile :</h2>";
echo "<ul>";
echo "<li><a href='https://httpd.apache.org/docs/2.4/mod/mod_rewrite.html' target='_blank'>Documentation officielle de mod_rewrite</a></li>";
echo "<li><a href='https://httpd.apache.org/docs/2.4/fr/howto/htaccess.html' target='_blank'>Guide d'utilisation des fichiers .htaccess</a></li>";
echo "<li><a href='https://www.digitalocean.com/community/tutorials/how-to-set-up-mod_rewrite' target='_blank'>Comment configurer mod_rewrite</a></li>";
echo "</ul>";

// Fin du script
echo "<hr>";
echo "<p>Ce script de débogage a été généré le " . date('Y-m-d H:i:s') . "</p>";
?>

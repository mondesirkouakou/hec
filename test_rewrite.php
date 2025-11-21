<?php
// Vérifier si le module de réécriture est activé
$rewrite_enabled = in_array('mod_rewrite', apache_get_modules()) ? 'activé' : 'désactivé';

// Afficher les informations de débogage
echo "<h1>Test de réécriture d'URL</h1>";
echo "<p>Module de réécriture : <strong>$rewrite_enabled</strong></p>";

// Afficher les variables d'environnement
echo "<h2>Variables d'environnement :</h2>";
echo "<pre>";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'Non défini') . "\n";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'Non défini') . "\n";
echo "PHP_SELF: " . ($_SERVER['PHP_SELF'] ?? 'Non défini') . "\n";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Non défini') . "\n";

// Vérifier si le fichier .htaccess est lu
$htaccess_path = __DIR__ . '/.htaccess';
$htaccess_exists = file_exists($htaccess_path) ? 'Oui' : 'Non';
$htaccess_readable = is_readable($htaccess_path) ? 'Oui' : 'Non';

echo "\nFichier .htaccess :\n";
echo "- Existe : $htaccess_exists\n";
echo "- Lisible : $htaccess_readable\n";

// Vérifier les permissions du répertoire
$dir_writable = is_writable(__DIR__) ? 'Oui' : 'Non';
$dir_readable = is_readable(__DIR__) ? 'Oui' : 'Non';

echo "\nPermissions du répertoire :\n";
echo "- Écriture : $dir_writable\n";
echo "- Lecture : $dir_readable\n";

// Vérifier la configuration d'Apache
$httpd_conf = 'C:/xampp/apache/conf/httpd.conf';
$httpd_conf_content = @file_get_contents($httpd_conf);

if ($httpd_conf_content !== false) {
    $allow_override_all = strpos($httpd_conf_content, 'AllowOverride All') !== false ? 'Oui' : 'Non';
    $rewrite_module = strpos($httpd_conf_content, 'LoadModule rewrite_module') !== false ? 'Oui' : 'Non';
    
    echo "\nConfiguration d'Apache :\n";
    echo "- AllowOverride All trouvé : $allow_override_all\n";
    echo "- Module rewrite chargé : $rewrite_module\n";
} else {
    echo "\nImpossible de lire le fichier de configuration d'Apache.\n";
}

echo "</pre>";

// Tester la réécriture d'URL
echo "<h2>Test de réécriture :</h2>";
echo "<p>Essayez d'accéder à <a href="/hec/test_rewrite/123">/hec/test_rewrite/123</a> pour tester la réécriture.</p>";

// Si un paramètre est passé dans l'URL
if (isset($_GET['id'])) {
    echo "<p style='color:green;'>La réécriture d'URL fonctionne ! ID reçu : " . htmlspecialchars($_GET['id']) . "</p>";
}
?>

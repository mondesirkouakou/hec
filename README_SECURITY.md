# Guide de Sécurité - HEC Abidjan

## 🔒 Fonctionnalités de Sécurité Implémentées

### 1. Protection CSRF (Cross-Site Request Forgery)

#### Utilisation dans les formulaires

```php
<?php require_once __DIR__ . '/includes/functions.php'; ?>

<form method="POST" action="...">
    <?= csrfField() ?>
    <!-- Vos champs de formulaire -->
</form>
```

#### Validation côté serveur

```php
// Au début de votre traitement POST
verifyCsrfToken();

// Ou manuellement
if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    die('Token CSRF invalide');
}
```

### 2. Headers de Sécurité

Les headers suivants sont automatiquement ajoutés dans `config/config.php`:

- **X-Frame-Options**: Prévient le clickjacking
- **X-Content-Type-Options**: Empêche le MIME sniffing
- **X-XSS-Protection**: Protection XSS du navigateur
- **Content-Security-Policy**: Contrôle les ressources chargées
- **Strict-Transport-Security**: Force HTTPS (en production)
- **Referrer-Policy**: Contrôle les informations de référence
- **Permissions-Policy**: Limite l'accès aux APIs du navigateur

### 3. Gestion des Sessions Sécurisée

Configuration automatique:
- `session.use_strict_mode = 1`
- `session.cookie_httponly = 1`
- `session.use_only_cookies = 1`
- `secure = true` (en HTTPS)
- `samesite = Lax`
- Régénération d'ID après login

### 4. Protection contre les Injections SQL

- Utilisation de **PDO avec requêtes préparées**
- `PDO::ATTR_EMULATE_PREPARES = false`
- Tous les paramètres sont bindés

### 5. Gestion des Mots de Passe

- Hashage avec `password_hash()` et `PASSWORD_BCRYPT`
- Vérification avec `password_verify()`
- Génération de mots de passe sécurisés disponible

### 6. Limitation de Taux (Rate Limiting)

```php
// Vérifier avant une action sensible
if (!rateLimitCheck($_SERVER['REMOTE_ADDR'], 5, 300)) {
    die('Trop de tentatives. Réessayez dans 5 minutes.');
}

// Réinitialiser après succès
rateLimitReset($_SERVER['REMOTE_ADDR']);
```

### 7. Validation et Nettoyage des Entrées

```php
$email = sanitizeInput($_POST['email'], 'email');
$age = sanitizeInput($_POST['age'], 'int');
$url = sanitizeInput($_POST['website'], 'url');
$text = sanitizeInput($_POST['comment'], 'string');
```

### 8. Logging des Événements de Sécurité

```php
logSecurityEvent('login_failed', 'Username: ' . $username);
logSecurityEvent('csrf_failed', 'Form: registration');
```

Les logs sont stockés dans `logs/security.log`.

## 📋 Checklist de Sécurité pour les Développeurs

### Pour chaque formulaire:
- [ ] Ajouter `<?= csrfField() ?>` dans le formulaire
- [ ] Appeler `verifyCsrfToken()` au début du traitement POST
- [ ] Valider et nettoyer toutes les entrées utilisateur
- [ ] Utiliser `htmlspecialchars()` pour l'affichage

### Pour chaque requête SQL:
- [ ] Utiliser des requêtes préparées
- [ ] Binder tous les paramètres
- [ ] Ne jamais concaténer directement des variables

### Pour l'authentification:
- [ ] Implémenter la limitation de taux
- [ ] Logger les tentatives échouées
- [ ] Régénérer l'ID de session après login
- [ ] Vérifier les permissions sur chaque page protégée

### Pour les mots de passe:
- [ ] Utiliser `password_hash()` pour le hashage
- [ ] Utiliser `password_verify()` pour la vérification
- [ ] Forcer des mots de passe forts (min 8 caractères)
- [ ] Permettre le changement de mot de passe

## 🚨 Que Faire en Cas d'Incident

1. **Consulter les logs**: `logs/security.log`
2. **Identifier l'attaque**: Type, IP, timestamp
3. **Bloquer l'IP** si nécessaire (via firewall)
4. **Régénérer les tokens CSRF**: Appeler `regenerateCsrfToken()`
5. **Forcer la déconnexion**: Invalider toutes les sessions
6. **Analyser et corriger** la vulnérabilité

## 📚 Ressources Supplémentaires

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)
- [Content Security Policy](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP)

## 🔄 Mises à Jour de Sécurité

### Version 2.0 (Janvier 2026)
- ✅ Protection CSRF complète
- ✅ Headers de sécurité HTTP
- ✅ Limitation de taux
- ✅ Logging des événements de sécurité
- ✅ Validation et nettoyage des entrées
- ✅ Régénération d'ID de session

### Prochaines Améliorations
- [ ] Authentification à deux facteurs (2FA)
- [ ] Détection d'anomalies comportementales
- [ ] Chiffrement des données sensibles au repos
- [ ] Audit de sécurité automatisé

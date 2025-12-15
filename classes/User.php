<?php
/**
 * Classe de base pour la gestion des utilisateurs
 */
class User {

    // Propriétés correspondant aux champs de la table users
    protected $id;
    protected $username;
    protected $email;
    protected $password;
    protected $role_id;
    protected $first_login = true;
    protected $is_active = true;
    protected $last_login;
    protected $created_at;
    protected $updated_at;

    // Objet de base de données
    protected $db;

    private $columnCache = [];

    /**
     * Constructeur
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Getters et Setters
    public function getId() {
        return $this->id;
    }

    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        $this->username = $username;
        return $this;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        if (!empty($password)) {
            $this->password = password_hash($password, PASSWORD_BCRYPT);
        }
        return $this;
    }

    public function getRoleId() {
        return $this->role_id;
    }

    public function setRoleId($role_id) {
        $this->role_id = $role_id;
        return $this;
    }

    public function isFirstLogin() {
        return (bool)$this->first_login;
    }

    public function setFirstLogin($first_login) {
        $this->first_login = (bool)$first_login;
        return $this;
    }

    public function isActive() {
        return (bool)$this->is_active;
    }

    public function setActive($is_active) {
        $this->is_active = (bool)$is_active;
        return $this;
    }

    public function getLastLogin() {
        return $this->last_login;
    }

    public function getCreatedAt() {
        return $this->created_at;
    }

    public function getUpdatedAt() {
        return $this->updated_at;
    }

    /**
     * Vérifie si un email existe déjà dans la base de données
     * @param string $email L'email à vérifier
     * @return bool True si l'email existe, false sinon
     */
    public function emailExists($email) {
        $sql = "SELECT id FROM users WHERE email = :email";
        $result = $this->db->fetch($sql, ['email' => $email]);
        return !empty($result);
    }

    /**
     * Vérifie si un nom d'utilisateur existe déjà
     * @param string $username Le nom d'utilisateur à vérifier
     * @return bool True si le nom d'utilisateur existe, false sinon
     */
    public function usernameExists($username) {
        $sql = "SELECT id FROM users WHERE username = :username";
        $result = $this->db->fetch($sql, ['username' => $username]);
        return !empty($result);
    }

    /**
     * Authentifie un utilisateur
     * @param string $identifier Le nom d'utilisateur ou l'email
     * @param string $password Le mot de passe en clair
     * @return bool True si l'authentification réussit, false sinon
     */
    public function login($identifier, $password) {
        $sql = "SELECT * FROM users WHERE (username = :u OR email = :e) AND is_active = 1 LIMIT 1";
        $user = $this->db->fetch($sql, ['u' => $identifier, 'e' => $identifier]);

        if ($user && password_verify($password, $user['password'])) {
            // Mettre à jour la date de dernière connexion
            $this->updateLastLogin($user['id']);
            
            // Si c'est la première connexion, forcer le changement de mot de passe
            if ($user['first_login']) {
                $_SESSION['force_password_change'] = true;
            }
            
            // Stocker les informations de l'utilisateur en session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['first_login'] = $user['first_login'];
            $roleMap = [1 => 'admin', 2 => 'chef-classe', 3 => 'professeur', 4 => 'etudiant'];
            $_SESSION['user_role'] = $roleMap[$user['role_id']] ?? 'guest';
            $displayName = $user['username'];
            $hasCustomDisplayName = isset($user['display_name']) && trim((string)$user['display_name']) !== '';
            if ($hasCustomDisplayName) {
                $displayName = trim((string)$user['display_name']);
            }

            // Si l'utilisateur n'a pas défini de nom affiché personnalisé,
            // on essaie de construire un nom à partir de son profil.
            if (!$hasCustomDisplayName) {
                if ($user['role_id'] == 4 || $user['role_id'] == 2) {
                    $row = $this->db->fetch("SELECT nom, prenom FROM etudiants WHERE user_id = :uid", ['uid' => $user['id']]);
                    if ($row) {
                        $name = trim(($row['prenom'] ?? '') . ' ' . ($row['nom'] ?? ''));
                        if (!empty($name)) {
                            $displayName = $name;
                        }
                    }
                } elseif ($user['role_id'] == 3) {
                    $row = $this->db->fetch("SELECT nom, prenom FROM professeurs WHERE user_id = :uid", ['uid' => $user['id']]);
                    if ($row) {
                        $name = trim(($row['prenom'] ?? '') . ' ' . ($row['nom'] ?? ''));
                        if (!empty($name)) {
                            $displayName = $name;
                        }
                    }
                }
            }
            $_SESSION['display_name'] = $displayName;
            
            return true;
        }
        
        return false;
    }

    private function hasColumn($table, $column) {
        $key = $table . '.' . $column;
        if (array_key_exists($key, $this->columnCache)) {
            return (bool)$this->columnCache[$key];
        }

        try {
            $row = $this->db->fetch(
                "SELECT COUNT(*) AS cnt
                 FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = :t
                   AND COLUMN_NAME = :c",
                ['t' => $table, 'c' => $column]
            );
            $exists = !empty($row) && (int)($row['cnt'] ?? 0) > 0;
            $this->columnCache[$key] = $exists;
            return $exists;
        } catch (Exception $e) {
            $this->columnCache[$key] = false;
            return false;
        }
    }

    public function updateDisplayName($userId, $displayName) {
        $displayName = trim((string)$displayName);
        if ($displayName === '') {
            return false;
        }

        // Si la colonne n'existe pas (ancienne base), on ne casse pas :
        // la valeur restera en session.
        if (!$this->hasColumn('users', 'display_name')) {
            return true;
        }

        $sql = "UPDATE users SET display_name = :display_name WHERE id = :id";
        return $this->db->execute($sql, [
            'display_name' => $displayName,
            'id' => (int)$userId
        ]) >= 0;
    }

    /**
     * Met à jour la date de dernière connexion
     * @param int $userId L'ID de l'utilisateur
     */
    private function updateLastLogin($userId) {
        $sql = "UPDATE users SET last_login = NOW() WHERE id = :id";
        $this->db->execute($sql, ['id' => $userId]);
    }

    /**
     * Change le mot de passe de l'utilisateur
     * @param int $userId L'ID de l'utilisateur
     * @param string $newPassword Le nouveau mot de passe en clair
     * @return bool True si la mise à jour a réussi, false sinon
     */
    public function changePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $sql = "UPDATE users SET password = :password, first_login = 0 WHERE id = :id";
        
        return $this->db->execute($sql, [
            'password' => $hashedPassword,
            'id' => $userId
        ]) > 0;
    }


    /**
     * Crée un nouvel utilisateur
     * @param array $userData Les données de l'utilisateur
     * @return int|false L'ID du nouvel utilisateur ou false en cas d'échec
     */
    public function create($userData) {
        // Vérifier que l'email n'existe pas déjà
        if ($this->emailExists($userData['email'])) {
            throw new Exception("Un compte avec cet email existe déjà.");
        }

        // Vérifier que le nom d'utilisateur n'existe pas déjà
        if ($this->usernameExists($userData['username'])) {
            throw new Exception("Ce nom d'utilisateur est déjà pris.");
        }

        // Hasher le mot de passe
        $hashedPassword = password_hash($userData['password'], PASSWORD_BCRYPT);

        // Préparer la requête d'insertion
        $sql = "INSERT INTO users (username, email, password, role_id, first_login, is_active) 
                VALUES (:username, :email, :password, :role_id, :first_login, :is_active)";
        
        $params = [
            'username' => $userData['username'],
            'email' => $userData['email'],
            'password' => $hashedPassword,
            'role_id' => $userData['role_id'] ?? 4, // Par défaut, rôle étudiant
            'first_login' => 1,
            'is_active' => 1
        ];

        // Exécuter la requête
        $userId = $this->db->insert($sql, $params);
        
        return $userId ? (int)$userId : false;
    }

    /**
     * Récupère un utilisateur par son ID
     * @param int $userId L'ID de l'utilisateur
     * @return array|false Les données de l'utilisateur ou false si non trouvé
     */
    public function getById($userId) {
        $sql = "SELECT u.*, r.nom as role_nom 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                WHERE u.id = :id";
        return $this->db->fetch($sql, ['id' => $userId]);
    }

    public function updatePassword($userId, $newPassword) {
        return $this->changePassword($userId, $newPassword);
    }

    /**
     * Récupère un utilisateur par son email
     * @param string $email L'email de l'utilisateur
     * @return array|false Les données de l'utilisateur ou false si non trouvé
     */
    public function getByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email";
        return $this->db->fetch($sql, ['email' => $email]);
    }

    /**
     * Met à jour les informations d'un utilisateur
     * @param int $userId L'ID de l'utilisateur
     * @param array $userData Les nouvelles données
     * @return bool True si la mise à jour a réussi, false sinon
     */
    public function update($userId, $userData) {
        $updates = [];
        $params = ['id' => $userId];

        if (!empty($userData['email'])) {
            // Vérifier que l'email n'est pas déjà utilisé par un autre utilisateur
            $existingUser = $this->getByEmail($userData['email']);
            if ($existingUser && $existingUser['id'] != $userId) {
                throw new Exception("Cet email est déjà utilisé par un autre compte.");
            }
            $updates[] = "email = :email";
            $params['email'] = $userData['email'];
        }

        if (!empty($userData['username'])) {
            // Vérifier que le nom d'utilisateur n'est pas déjà utilisé
            $existingUser = $this->getByUsername($userData['username']);
            if ($existingUser && $existingUser['id'] != $userId) {
                throw new Exception("Ce nom d'utilisateur est déjà pris.");
            }
            $updates[] = "username = :username";
            $params['username'] = $userData['username'];
        }

        if (!empty($userData['password'])) {
            $updates[] = "password = :password";
            $params['password'] = password_hash($userData['password'], PASSWORD_BCRYPT);
        }

        if (isset($userData['is_active'])) {
            $updates[] = "is_active = :is_active";
            $params['is_active'] = (int)(bool)$userData['is_active'];
        }

        if (empty($updates)) {
            return false; // Rien à mettre à jour
        }

        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = :id";
        return $this->db->execute($sql, $params) > 0;
    }

    /**
     * Désactive un compte utilisateur
     * @param int $userId L'ID de l'utilisateur
     * @return bool True si la désactivation a réussi, false sinon
     */
    public function deactivate($userId) {
        $sql = "UPDATE users SET is_active = 0 WHERE id = :id";
        return $this->db->execute($sql, ['id' => $userId]) > 0;
    }

    /**
     * Récupère un utilisateur par son nom d'utilisateur
     * @param string $username Le nom d'utilisateur
     * @return array|false Les données de l'utilisateur ou false si non trouvé
     */
    public function getByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = :username";
        return $this->db->fetch($sql, ['username' => $username]);
    }

    /**
     * Vérifie si l'utilisateur a un rôle spécifique
     * @param int $userId L'ID de l'utilisateur
     * @param string $role Le nom du rôle à vérifier
     * @return bool True si l'utilisateur a le rôle, false sinon
     */
    public function hasRole($userId, $role) {
        $sql = "SELECT COUNT(*) as count 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                WHERE u.id = :user_id AND r.nom = :role";
        
        $result = $this->db->fetch($sql, [
            'user_id' => $userId,
            'role' => $role
        ]);
        
        return !empty($result) && $result['count'] > 0;
    }

    /**
     * Récupère tous les utilisateurs avec pagination
     * @param int $page Le numéro de la page
     * @param int $perPage Nombre d'utilisateurs par page
     * @return array Les utilisateurs et le nombre total
     */
    public function getAllPaginated($page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        // Récupérer les utilisateurs
        $sql = "SELECT u.*, r.nom as role_nom 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                ORDER BY u.id DESC 
                LIMIT :offset, :perPage";
        
        $users = $this->db->fetchAll($sql, [
            'offset' => $offset,
            'perPage' => $perPage
        ]);
        
        // Compter le nombre total d'utilisateurs
        $count = $this->db->fetchColumn("SELECT COUNT(*) FROM users");
        
        return [
            'users' => $users,
            'total' => (int)$count,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($count / $perPage)
        ];
    }

    /**
     * Supprime un utilisateur par son ID
     * @param int $userId L'ID de l'utilisateur à supprimer
     * @return bool True si la suppression a réussi, false sinon
     */
    public function delete($userId) {
        $sql = "DELETE FROM users WHERE id = :id";
        return $this->db->execute($sql, ['id' => $userId]) > 0;
    }
}
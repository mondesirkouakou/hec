<?php
/**
 * Classe de gestion de la base de données
 */
class Database {
    private static $instance = null;
    private $connection;
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $charset = 'utf8mb4';

    /**
     * Constructeur privé pour implémenter le pattern Singleton
     */
    private function __construct() {
        // Récupération des paramètres de configuration
        $this->host = DB_HOST;
        $this->dbname = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        
        $this->connect();
    }

    /**
     * Obtient l'instance unique de la classe Database
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Établit la connexion à la base de données
     * @throws PDOException Si la connexion échoue
     */
    private function connect() {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => true
        ];

        try {
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            // En production, vous pourriez vouloir logger cette erreur
            throw new PDOException("Erreur de connexion à la base de données: " . $e->getMessage());
        }
    }

    /**
     * Exécute une requête préparée et retourne le résultat
     * @param string $sql La requête SQL avec des paramètres nommés
     * @param array $params Les paramètres à lier à la requête
     * @return PDOStatement Le résultat de la requête
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            // En production, vous pourriez vouloir logger cette erreur
            throw new PDOException("Erreur lors de l'exécution de la requête: " . $e->getMessage());
        }
    }

    /**
     * Récupère une seule ligne
     * @param string $sql La requête SQL
     * @param array $params Les paramètres à lier
     * @return array|false La ligne résultante ou false si aucune ligne n'est trouvée
     */
    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    /**
     * Récupère toutes les lignes
     * @param string $sql La requête SQL
     * @param array $params Les paramètres à lier
     * @return array Un tableau de lignes
     */
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Récupère une seule valeur (première colonne de la première ligne)
     * @param string $sql La requête SQL
     * @param array $params Les paramètres à lier
     * @return mixed La valeur ou false si aucune ligne n'est trouvée
     */
    public function fetchColumn($sql, $params = []) {
        return $this->query($sql, $params)->fetchColumn();
    }

    /**
     * Exécute une requête d'insertion et retourne l'ID du dernier enregistrement inséré
     * @param string $sql La requête SQL d'insertion
     * @param array $params Les paramètres à lier
     * @return string L'ID du dernier enregistrement inséré
     */
    public function insert($sql, $params = []) {
        $this->query($sql, $params);
        return $this->connection->lastInsertId();
    }

    /**
     * Exécute une requête de mise à jour ou de suppression
     * @param string $sql La requête SQL
     * @param array $params Les paramètres à lier
     * @return int Le nombre de lignes affectées
     */
    public function execute($sql, $params = []) {
        return $this->query($sql, $params)->rowCount();
    }

    /**
     * Commence une transaction
     * @return bool
     */
    public function beginTransaction() {
        if ($this->connection->inTransaction()) {
            return false;
        }
        return $this->connection->beginTransaction();
    }

    /**
     * Valide une transaction
     * @return bool
     */
    public function commit() {
        if (!$this->connection->inTransaction()) {
            return false;
        }
        return $this->connection->commit();
    }

    /**
     * Annule une transaction
     * @return bool
     */
    public function rollBack() {
        if (!$this->connection->inTransaction()) {
            return false;
        }
        return $this->connection->rollBack();
    }

    public function inTransaction() {
        return $this->connection->inTransaction();
    }

    /**
     * Échappe une valeur pour une utilisation dans une requête SQL
     * @param string $value La valeur à échapper
     * @return string La valeur échappée
     */
    public function quote($value) {
        return $this->connection->quote($value);
    }

    /**
     * Empêche le clonage de l'instance
     */
    private function __clone() {}

    /**
     * Empêche la désérialisation de l'instance
     */
    public function __wakeup() {}
}

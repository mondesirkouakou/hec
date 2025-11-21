<?php
/**
 * Classe pour la gestion des étudiants
 * Étend la classe User pour ajouter des fonctionnalités spécifiques aux étudiants
 */
class Etudiant extends User {
    // Propriétés spécifiques aux étudiants
    protected $matricule;
    protected $date_naissance;
    protected $lieu_naissance;
    protected $telephone;

    // Table dans la base de données
    protected $table_etudiants = 'etudiants';

    /**
     * Crée un nouvel étudiant
     * @param array $etudiantData Les données de l'étudiant
     * @return int|false L'ID du nouvel étudiant ou false en cas d'échec
     */
    public function creerEtudiant($etudiantData) {
        try {
            $startedTransaction = !$this->db->inTransaction();
            if ($startedTransaction) {
                $this->db->beginTransaction();
            }

            // 1. Créer d'abord l'utilisateur de base
            $emailLocal = preg_replace('/[^A-Za-z0-9._-]/', '-', $etudiantData['matricule']);
            $emailGenerated = strtolower($emailLocal) . '@etu.hec.ci';
            $userData = [
                'username' => $etudiantData['matricule'],
                'email' => $etudiantData['email'] ?? $emailGenerated,
                'password' => $etudiantData['password'] ?? 'SAMA2007',
                'role_id' => 4
            ];

            $userId = parent::create($userData);

            if (!$userId) {
                throw new Exception("Échec de la création de l'utilisateur étudiant");
            }

            // 2. Créer l'entrée dans la table des étudiants
            $sql = "INSERT INTO {$this->table_etudiants} 
                    (user_id, matricule, nom, prenom, date_naissance, lieu_naissance, telephone)
                    VALUES (:user_id, :matricule, :nom, :prenom, :date_naissance, :lieu_naissance, :telephone)";

            $params = [
                'user_id' => $userId,
                'matricule' => $etudiantData['matricule'],
                'nom' => $etudiantData['nom'],
                'prenom' => $etudiantData['prenom'],
                'date_naissance' => $etudiantData['date_naissance'] ?? null,
                'lieu_naissance' => $etudiantData['lieu_naissance'] ?? null,
                'telephone' => $etudiantData['telephone'] ?? null
            ];

            $etudiantId = $this->db->insert($sql, $params);

            if (!$etudiantId) {
                throw new Exception("Échec de la création du profil étudiant");
            }

            if ($startedTransaction) {
                $this->db->commit();
            }

            return $etudiantId;

        } catch (Exception $e) {
            if (isset($startedTransaction) && $startedTransaction) {
                $this->db->rollBack();
            }
            error_log("Erreur création étudiant: " . $e->getMessage());
            return false;
        }
    }

    public function count() {
        return (int)$this->db->fetchColumn("SELECT COUNT(*) FROM {$this->table_etudiants}");
    }

    /**
     * Récupère un étudiant par son ID utilisateur
     * @param int $userId L'ID de l'utilisateur
     * @return array|false Les données de l'étudiant ou false si non trouvé
     */
    public function getByUserId($userId) {
        $sql = "SELECT e.*, u.email, u.is_active, u.first_login 
                FROM {$this->table_etudiants} e 
                JOIN users u ON e.user_id = u.id 
                WHERE e.user_id = :user_id";
        
        return $this->db->fetch($sql, ['user_id' => $userId]);
    }

    /**
     * Récupère un étudiant par son matricule
     * @param string $matricule Le matricule de l'étudiant
     * @return array|false Les données de l'étudiant ou false si non trouvé
     */
    public function getByMatricule($matricule) {
        $sql = "SELECT e.*, u.email, u.is_active, u.first_login, r.nom as role_nom
                FROM {$this->table_etudiants} e 
                JOIN users u ON e.user_id = u.id 
                JOIN roles r ON u.role_id = r.id
                WHERE e.matricule = :matricule";
        
        return $this->db->fetch($sql, ['matricule' => $matricule]);
    }

    /**
     * Met à jour les informations d'un étudiant
     * @param int $etudiantId L'ID de l'étudiant
     * @param array $etudiantData Les nouvelles données
     * @return bool True si la mise à jour a réussi, false sinon
     */
    public function updateEtudiant($etudiantId, $etudiantData) {
        try {
            // Démarrer une transaction
            $this->db->beginTransaction();

            // 1. Mettre à jour les informations de base de l'utilisateur
            if (!empty($etudiantData['email'])) {
                $userUpdate = [
                    'email' => $etudiantData['email']
                ];
                
                // Si un nouveau mot de passe est fourni
                if (!empty($etudiantData['password'])) {
                    $userUpdate['password'] = $etudiantData['password'];
                    $userUpdate['first_login'] = 0; // Réinitialiser le flag de première connexion
                }
                
                parent::update($etudiantData['user_id'], $userUpdate);
            }

            // 2. Mettre à jour les informations spécifiques à l'étudiant
            $updates = [];
            $params = ['id' => $etudiantId];

            $fields = ['nom', 'prenom', 'date_naissance', 'lieu_naissance', 'telephone'];
            
            foreach ($fields as $field) {
                if (isset($etudiantData[$field])) {
                    $updates[] = "$field = :$field";
                    $params[$field] = $etudiantData[$field];
                }
            }

            if (!empty($updates)) {
                $sql = "UPDATE {$this->table_etudiants} SET " . implode(', ', $updates) . " WHERE id = :id";
                $this->db->execute($sql, $params);
            }

            // Valider la transaction
            $this->db->commit();
            return true;

        } catch (Exception $e) {
            // En cas d'erreur, annuler la transaction
            $this->db->rollBack();
            error_log("Erreur mise à jour étudiant: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Désactive un compte étudiant
     * @param int $etudiantId L'ID de l'étudiant
     * @return bool True si la désactivation a réussi, false sinon
     */
    public function desactiver($etudiantId) {
        try {
            // Récupérer l'ID utilisateur
            $sql = "SELECT user_id FROM {$this->table_etudiants} WHERE id = :id";
            $etudiant = $this->db->fetch($sql, ['id' => $etudiantId]);

            if (!$etudiant) {
                throw new Exception("Étudiant non trouvé");
            }

            // Désactiver le compte utilisateur
            return parent::deactivate($etudiant['user_id']);

        } catch (Exception $e) {
            error_log("Erreur désactivation étudiant: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère la liste des étudiants avec pagination
     * @param int $page Numéro de la page
     * @param int $perPage Nombre d'étudiants par page
     * @return array Tableau contenant les étudiants et les informations de pagination
     */
    public function getEtudiantsPagines($page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        
        // Récupérer les étudiants
        $sql = "SELECT e.*, u.email, u.is_active, u.first_login, 
                       CONCAT(e.nom, ' ', e.prenom) as nom_complet
                FROM {$this->table_etudiants} e 
                JOIN users u ON e.user_id = u.id 
                ORDER BY e.nom, e.prenom
                LIMIT :offset, :perPage";
        
        $etudiants = $this->db->fetchAll($sql, [
            'offset' => $offset,
            'perPage' => $perPage
        ]);
        
        // Compter le nombre total d'étudiants
        $count = $this->db->fetchColumn("SELECT COUNT(*) FROM {$this->table_etudiants}");
        
        return [
            'etudiants' => $etudiants,
            'total' => (int)$count,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($count / $perPage)
        ];
    }

    /**
     * Recherche des étudiants selon des critères
     * @param array $criteria Critères de recherche (matricule, nom, prenom, etc.)
     * @return array Liste des étudiants correspondant aux critères
     */
    public function rechercher($criteria) {
        $where = [];
        $params = [];
        
        if (!empty($criteria['matricule'])) {
            $where[] = "e.matricule LIKE :matricule";
            $params['matricule'] = '%' . $criteria['matricule'] . '%';
        }
        
        if (!empty($criteria['nom'])) {
            $where[] = "e.nom LIKE :nom";
            $params['nom'] = '%' . $criteria['nom'] . '%';
        }
        
        if (!empty($criteria['prenom'])) {
            $where[] = "e.prenom LIKE :prenom";
            $params['prenom'] = '%' . $criteria['prenom'] . '%';
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT e.*, u.email, u.is_active, CONCAT(e.nom, ' ', e.prenom) as nom_complet
                FROM {$this->table_etudiants} e 
                JOIN users u ON e.user_id = u.id 
                {$whereClause}
                ORDER BY e.nom, e.prenom";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Vérifie si un matricule existe déjà
     * @param string $matricule Le matricule à vérifier
     * @param int $excludeId ID de l'étudiant à exclure (pour les mises à jour)
     * @return bool True si le matricule existe, false sinon
     */
    public function matriculeExiste($matricule, $excludeId = null) {
        $sql = "SELECT id FROM {$this->table_etudiants} WHERE matricule = :matricule";
        $params = ['matricule' => $matricule];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return !empty($result);
    }

    // Getters et Setters pour les propriétés spécifiques
    public function getMatricule() {
        return $this->matricule;
    }

    public function setMatricule($matricule) {
        $this->matricule = $matricule;
        return $this;
    }

    public function getDateNaissance() {
        return $this->date_naissance;
    }

    public function setDateNaissance($date_naissance) {
        $this->date_naissance = $date_naissance;
        return $this;
    }

    public function getLieuNaissance() {
        return $this->lieu_naissance;
    }

    public function setLieuNaissance($lieu_naissance) {
        $this->lieu_naissance = $lieu_naissance;
        return $this;
    }

    public function getTelephone() {
        return $this->telephone;
    }

    public function setTelephone($telephone) {
        $this->telephone = $telephone;
        return $this;
    }
}

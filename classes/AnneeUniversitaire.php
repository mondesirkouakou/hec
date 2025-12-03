<?php

class AnneeUniversitaire {
    private $db;
    private $table_name = "annees_universitaires";

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Récupère toutes les années universitaires
     */
    public function getAll() {
        $query = "SELECT * FROM {$this->table_name} ORDER BY annee_debut DESC, annee_fin DESC";
        return $this->db->fetchAll($query);
    }

    public function count() {
        return (int)$this->db->fetchColumn("SELECT COUNT(*) FROM {$this->table_name}");
    }
    
    /**
     * Récupère une année universitaire par son ID
     */
    public function getById($id) {
        $query = "SELECT * FROM {$this->table_name} WHERE id = :id";
        return $this->db->fetch($query, ['id' => $id]);
    }
    
    /**
     * Récupère l'année universitaire active
     */
    public function getActiveYear() {
        $query = "SELECT * FROM {$this->table_name} WHERE est_active = 1 LIMIT 1";
        return $this->db->fetch($query);
    }
    
    /**
     * Crée une nouvelle année universitaire
     */
    public function create($data) {
        $query = "INSERT INTO {$this->table_name} (annee_debut, annee_fin, est_active, created_at) 
                 VALUES (:annee_debut, :annee_fin, :est_active, NOW())";
        
        $params = [
            'annee_debut' => $data['annee_debut'],
            'annee_fin' => $data['annee_fin'],
            'est_active' => $data['est_active'] ?? 0
        ];
        
        // La méthode Database::insert() exécute la requête et retourne l'ID inséré
        return $this->db->insert($query, $params);
    }
    
    /**
     * Met à jour une année universitaire existante
     */
    public function update($data) {
        $query = "UPDATE {$this->table_name} 
                 SET annee_debut = :annee_debut, 
                     annee_fin = :annee_fin,
                     est_active = :est_active
                 WHERE id = :id";
        
        $params = [
            'id' => $data['id'],
            'annee_debut' => $data['annee_debut'],
            'annee_fin' => $data['annee_fin'],
            'est_active' => $data['est_active'] ?? 0
        ];
        
        return $this->db->query($query, $params);
    }
    
    /**
     * Supprime une année universitaire
     */
    public function delete($id) {
        // Vérifier s'il y a des classes liées à cette année
        $query = "SELECT COUNT(*) as count FROM classes WHERE annee_universitaire_id = :id";
        $result = $this->db->fetch($query, ['id' => $id]);
        
        if ($result && $result['count'] > 0) {
            return false; // Ne pas supprimer s'il y a des classes liées
        }
        
        $query = "DELETE FROM {$this->table_name} WHERE id = :id";
        return $this->db->query($query, ['id' => $id]);
    }
    
    /**
     * Active une année universitaire et désactive les autres
     */
    public function setActiveYear($id) {
        // Désactiver toutes les années
        $this->db->query("UPDATE {$this->table_name} SET est_active = 0");
        
        // Activer l'année sélectionnée
        return $this->db->query(
            "UPDATE {$this->table_name} SET est_active = 1 WHERE id = :id",
            ['id' => $id]
        );
    }
    
    /**
     * Vérifie si une année universitaire existe déjà
     */
    public function yearExists($anneeDebut, $anneeFin, $excludeId = null) {
        $query = "SELECT id FROM {$this->table_name} 
                 WHERE (annee_debut = :annee_debut AND annee_fin = :annee_fin)";
        
        $params = [
            'annee_debut' => $anneeDebut,
            'annee_fin' => $anneeFin
        ];
        
        if ($excludeId) {
            $query .= " AND id != :id";
            $params['id'] = $excludeId;
        }
        
        $result = $this->db->fetch($query, $params);
        return !empty($result);
    }
}
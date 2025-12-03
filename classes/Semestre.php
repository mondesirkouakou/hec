<?php

class Semestre {
    private $db;
    private $table_name = "semestres";

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Récupère tous les semestres
     */
    public function getAll() {
        $query = "SELECT s.*, a.annee_debut, a.annee_fin 
                 FROM {$this->table_name} s
                 JOIN annees_universitaires a ON s.annee_universitaire_id = a.id
                 ORDER BY a.annee_debut DESC, s.numero ASC";
        return $this->db->fetchAll($query);
    }
    
    /**
     * Récupère un semestre par son ID
     */
    public function getById($id) {
        $query = "SELECT s.*, a.annee_debut, a.annee_fin 
                 FROM {$this->table_name} s
                 JOIN annees_universitaires a ON s.annee_universitaire_id = a.id
                 WHERE s.id = :id";
        return $this->db->fetch($query, ['id' => $id]);
    }
    
    /**
     * Récupère les semestres d'une année universitaire
     */
    public function getByAnneeUniversitaire($anneeId) {
        $query = "SELECT s.*, a.annee_debut, a.annee_fin 
                 FROM {$this->table_name} s
                 JOIN annees_universitaires a ON s.annee_universitaire_id = a.id
                 WHERE s.annee_universitaire_id = :annee_id
                 ORDER BY s.numero ASC";
        return $this->db->fetchAll($query, ['annee_id' => $anneeId]);
    }
    
    /**
     * Récupère le semestre actuellement actif
     */
    public function getActiveSemestre() {
        $query = "SELECT s.*, a.annee_debut, a.annee_fin 
                FROM {$this->table_name} s
                JOIN annees_universitaires a ON s.annee_universitaire_id = a.id
                WHERE s.est_ouvert = 1 
                LIMIT 1";
        return $this->db->fetch($query);
    }
    
    /**
     * Crée un nouveau semestre
     */
    public function create($data) {
        $query = "INSERT INTO {$this->table_name} 
                 (numero, date_debut, date_fin, annee_universitaire_id, est_ouvert, est_cloture) 
                 VALUES (:numero, :date_debut, :date_fin, :annee_universitaire_id, :est_ouvert, :est_cloture)";
        
        $params = [
            'numero' => $data['numero'],
            'date_debut' => $data['date_debut'],
            'date_fin' => $data['date_fin'],
            'annee_universitaire_id' => $data['annee_universitaire_id'],
            'est_ouvert' => $data['est_ouvert'] ?? 0,
            'est_cloture' => $data['est_cloture'] ?? 0
        ];
        
        // Utilise Database::insert() qui exécute la requête et retourne l'ID inséré
        return $this->db->insert($query, $params);
    }
    
    /**
     * Met à jour un semestre existant
     */
    public function update($data) {
        $query = "UPDATE {$this->table_name} 
                 SET numero = :numero, 
                     date_debut = :date_debut, 
                     date_fin = :date_fin,
                     annee_universitaire_id = :annee_universitaire_id,
                     est_ouvert = :est_ouvert,
                     est_cloture = :est_cloture
                 WHERE id = :id";
        
        $params = [
            'id' => $data['id'],
            'numero' => $data['numero'],
            'date_debut' => $data['date_debut'],
            'date_fin' => $data['date_fin'],
            'annee_universitaire_id' => $data['annee_universitaire_id'],
            'est_ouvert' => $data['est_ouvert'] ?? 0,
            'est_cloture' => $data['est_cloture'] ?? 0
        ];
        
        return $this->db->query($query, $params);
    }
    
    /**
     * Supprime un semestre
     */
    public function delete($id) {
        // Vérifier s'il y a des notes liées à ce semestre
        $query = "SELECT COUNT(*) as count FROM notes WHERE semestre_id = :id";
        $result = $this->db->fetch($query, ['id' => $id]);
        
        if ($result && $result['count'] > 0) {
            return false; // Ne pas supprimer s'il y a des notes
        }
        
        $query = "DELETE FROM {$this->table_name} WHERE id = :id";
        return $this->db->query($query, ['id' => $id]);
    }
    
    /**
     * Active un semestre et désactive les autres
     */
    public function setActiveSemestre($id) {
        // Désactiver tous les semestres
        $this->db->query("UPDATE {$this->table_name} SET est_ouvert = 0");
        
        // Activer le semestre sélectionné
        return $this->db->query(
            "UPDATE {$this->table_name} SET est_ouvert = 1, est_cloture = 0 WHERE id = :id",
            ['id' => $id]
        );
    }
    
    /**
     * Clôture un semestre
     */
    public function closeSemestre($id) {
        return $this->db->query(
            "UPDATE {$this->table_name} SET est_cloture = 1, est_ouvert = 0 WHERE id = :id",
            ['id' => $id]
        );
    }
    
    /**
     * Vérifie si un semestre existe déjà pour la même période
     */
    public function semestreExists($numero, $anneeId, $excludeId = null) {
        $query = "SELECT id FROM {$this->table_name} 
                 WHERE numero = :numero AND annee_universitaire_id = :annee_id";
        
        $params = [
            'numero' => $numero,
            'annee_id' => $anneeId
        ];
        
        if ($excludeId) {
            $query .= " AND id != :id";
            $params['id'] = $excludeId;
        }
        
        $result = $this->db->fetch($query, $params);
        return !empty($result);
    }
    
    /**
     * Vérifie si une date est dans la période d'un semestre
     */
    public function isDateInSemestre($date, $semestreId) {
        $query = "SELECT 1 FROM {$this->table_name} 
                 WHERE id = :id 
                 AND :date BETWEEN date_debut AND date_fin";
        
        $result = $this->db->fetch($query, [
            'id' => $semestreId,
            'date' => $date
        ]);
        
        return !empty($result);
    }
}
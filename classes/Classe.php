<?php

class Classe {
    private $db;
    private $table_name = "classes";

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Récupère toutes les classes
     */
    public function getAllClasses() {
        $query = "SELECT c.*, a.annee_debut, a.annee_fin,
                         COUNT(DISTINCT i.etudiant_id) AS effectif,
                         CONCAT(e_chef.nom, ' ', e_chef.prenom) AS chef_classe_nom,
                         c.statut_listes
                  FROM {$this->table_name} c
                  LEFT JOIN annees_universitaires a ON c.annee_universitaire_id = a.id
                  LEFT JOIN inscriptions i ON c.id = i.classe_id
                  LEFT JOIN chef_classe cc ON c.id = cc.classe_id
                  LEFT JOIN etudiants e_chef ON cc.etudiant_id = e_chef.id
                  GROUP BY c.id
                  ORDER BY c.code, a.annee_debut DESC";
        return $this->db->fetchAll($query);
    }

    public function count() {
        return (int)$this->db->fetchColumn("SELECT COUNT(*) FROM {$this->table_name}");
    }
    
    public function getById($id) {
        $query = "SELECT c.*, a.annee_debut, a.annee_fin, c.statut_listes 
                 FROM {$this->table_name} c
                 LEFT JOIN annees_universitaires a ON c.annee_universitaire_id = a.id
                 WHERE c.id = :id";
        return $this->db->fetch($query, ['id' => $id]);
    }

    public function getLatest($limit = 5) {
        $sql = "SELECT c.*, a.annee_debut, a.annee_fin 
                FROM {$this->table_name} c
                LEFT JOIN annees_universitaires a ON c.annee_universitaire_id = a.id
                ORDER BY c.created_at DESC
                LIMIT :limit";
        return $this->db->fetchAll($sql, ['limit' => (int)$limit]);
    }
    
    /**
     * Crée une nouvelle classe
     */
    public function create($data) {
        $query = "INSERT INTO {$this->table_name} (code, intitule, niveau, annee_universitaire_id, created_at) 
                 VALUES (:code, :intitule, :niveau, :annee_universitaire_id, NOW())";
        
        $params = [
            'code' => $data['code'],
            'intitule' => $data['intitule'],
            'niveau' => $data['niveau'],
            'annee_universitaire_id' => $data['annee_universitaire_id']
        ];
        
        $id = $this->db->insert($query, $params);
        return $id ? (int)$id : false;
    }
    
    /**
     * Met à jour une classe existante
     */
    public function update($data) {
        $query = "UPDATE {$this->table_name} 
                 SET code = :code, 
                     intitule = :intitule, 
                     niveau = :niveau,
                     annee_universitaire_id = :annee_universitaire_id,
                     updated_at = NOW()
                 WHERE id = :id";
        
        $params = [
            'id' => $data['id'],
            'code' => $data['code'],
            'intitule' => $data['intitule'],
            'niveau' => $data['niveau'],
            'annee_universitaire_id' => $data['annee_universitaire_id']
        ];
        
        return $this->db->execute($query, $params) > 0;
    }
    
    /**
     * Supprime une classe
     */
    public function delete($id) {
        // Vérifier s'il y a des étudiants dans cette classe
        $etudiants = $this->getEtudiants($id);
        if (!empty($etudiants)) {
            return false; // Ne pas supprimer s'il y a des étudiants
        }
        
        $query = "DELETE FROM {$this->table_name} WHERE id = :id";
        return $this->db->execute($query, ['id' => $id]) > 0;
    }
    
    /**
     * Récupère les étudiants d'une classe
     */
    public function getEtudiants($classeId) {
        $query = "SELECT e.*, u.email 
                 FROM etudiants e
                 JOIN users u ON e.user_id = u.id
                 JOIN inscriptions i ON e.id = i.etudiant_id
                 WHERE i.classe_id = :classe_id
                 ORDER BY e.nom, e.prenom";
        
        return $this->db->fetchAll($query, ['classe_id' => $classeId]);
    }
    
    /**
     * Récupère les professeurs d'une classe
     */
    public function getProfesseurs($classeId) {
        $query = "SELECT DISTINCT p.*, u.email, GROUP_CONCAT(m.intitule SEPARATOR ', ') as matieres
                 FROM professeurs p
                 JOIN users u ON p.user_id = u.id
                 JOIN affectation_professeur ap ON p.id = ap.professeur_id
                 JOIN matieres m ON ap.matiere_id = m.id
                 WHERE ap.classe_id = :classe_id
                 GROUP BY p.id
                 ORDER BY p.nom, p.prenom";
        
        return $this->db->fetchAll($query, ['classe_id' => $classeId]);
    }
    
    /**
     * Vérifie si un code de classe existe déjà
     */
    public function codeExists($code, $excludeId = null) {
        $query = "SELECT id FROM {$this->table_name} WHERE code = :code";
        $params = ['code' => $code];
        
        if ($excludeId) {
            $query .= " AND id != :id";
            $params['id'] = $excludeId;
        }
        
        $result = $this->db->fetch($query, $params);
        return !empty($result);
    }

    /**
     * Récupère les matières attribuées à une classe avec leurs détails (coefficient, crédits)
     */
    public function getAssignedMatieresWithDetails($classeId) {
        $query = "SELECT cm.matiere_id, m.intitule, cm.coefficient, cm.credits
                  FROM classe_matiere cm
                  JOIN matieres m ON cm.matiere_id = m.id
                  WHERE cm.classe_id = :classe_id
                  ORDER BY m.intitule";
        return $this->db->fetchAll($query, ['classe_id' => $classeId]);
    }

    /**
     * Attribue une matière à une classe
     */
    public function assignMatiereToClass($data) {
        // Vérifier si l'attribution existe déjà
        $checkQuery = "SELECT COUNT(*) FROM classe_matiere WHERE classe_id = :classe_id AND matiere_id = :matiere_id";
        $exists = $this->db->fetchColumn($checkQuery, [
            'classe_id' => $data['classe_id'],
            'matiere_id' => $data['matiere_id']
        ]);

        if ($exists > 0) {
            // Mettre à jour si elle existe déjà
            $query = "UPDATE classe_matiere SET coefficient = :coefficient, credits = :credits, updated_at = NOW() WHERE classe_id = :classe_id AND matiere_id = :matiere_id";
        } else {
            // Insérer si elle n'existe pas
            $query = "INSERT INTO classe_matiere (classe_id, matiere_id, coefficient, credits, created_at) VALUES (:classe_id, :matiere_id, :coefficient, :credits, NOW())";
        }

        $params = [
            'classe_id' => $data['classe_id'],
            'matiere_id' => $data['matiere_id'],
            'coefficient' => $data['coefficient'],
            'credits' => $data['credits']
        ];

        return $this->db->execute($query, $params);
    }
}
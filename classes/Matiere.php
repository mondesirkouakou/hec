<?php

class Matiere {
    private $db;
    private $table_name = "matieres";

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll() {
        $sql = "SELECT * FROM {$this->table_name} ORDER BY intitule ASC";
        return $this->db->fetchAll($sql);
    }

    public function getById($id) {
        $sql = "SELECT * FROM {$this->table_name} WHERE id = :id";
        return $this->db->fetch($sql, ['id' => $id]);
    }

    public function count() {
        return (int)$this->db->fetchColumn("SELECT COUNT(*) FROM {$this->table_name}");
    }

    public function getLatest($limit = 5) {
        $sql = "SELECT * FROM {$this->table_name} ORDER BY created_at DESC LIMIT :limit";
        return $this->db->fetchAll($sql, ['limit' => (int)$limit]);
    }
}
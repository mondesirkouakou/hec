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

    public function getByIntitule($intitule) {
        $sql = "SELECT * FROM {$this->table_name} WHERE LOWER(intitule) = LOWER(:intitule) LIMIT 1";
        return $this->db->fetch($sql, ['intitule' => $intitule]);
    }

    public function create($intitule) {
        $base = preg_replace('/[^A-Za-z0-9]/', '', $intitule);
        if ($base === '' || $base === null) {
            $base = 'MAT';
        }
        $base = strtoupper($base);
        $code = substr($base, 0, 10);

        $counter = 1;
        while ($this->db->fetch("SELECT id FROM {$this->table_name} WHERE code = :code", ['code' => $code])) {
            $suffix = (string)$counter;
            $code = substr($base, 0, max(1, 10 - strlen($suffix))) . $suffix;
            $counter++;
        }

        $sql = "INSERT INTO {$this->table_name} (code, intitule, credits) VALUES (:code, :intitule, :credits)";
        $params = [
            'code' => $code,
            'intitule' => $intitule,
            'credits' => null,
        ];
        $id = $this->db->insert($sql, $params);
        return $id ? (int)$id : null;
    }

    public function count() {
        return (int)$this->db->fetchColumn("SELECT COUNT(*) FROM {$this->table_name}");
    }

    public function getLatest($limit = 5) {
        $sql = "SELECT * FROM {$this->table_name} ORDER BY id DESC LIMIT :limit";
        return $this->db->fetchAll($sql, ['limit' => (int)$limit]);
    }
}

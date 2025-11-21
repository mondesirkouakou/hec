<?php

class User {
    private $conn;
    private $table_name = "users";

    public $id_user;
    public $nom;
    public $prenom;
    public $email;
    public $password;
    public $role;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET nom=:nom, prenom=:prenom, email=:email, password=:password, role=:role";

        $stmt = $this->conn->prepare($query);

        $this->nom = htmlspecialchars(strip_tags($this->nom));
        $this->prenom = htmlspecialchars(strip_tags($this->prenom));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        $this->role = htmlspecialchars(strip_tags($this->role));

        $stmt->bindParam(":nom", $this->nom);
        $stmt->bindParam(":prenom", $this->prenom);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":role", $this->role);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function login() {
        $query = "SELECT id_user, nom, prenom, email, password, role FROM " . $this->table_name . " WHERE email = :email LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id_user = $row['id_user'];
            $this->nom = $row['nom'];
            $this->prenom = $row['prenom'];
            $this->email = $row['email'];
            $this->role = $row['role'];

            if (password_verify($this->password, $row['password'])) {
                return true;
            }
        }
        return false;
    }

    public function emailExists() {
        $query = "SELECT id_user FROM " . $this->table_name . " WHERE email = :email LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function getUserById($id_user) {
        $query = "SELECT id_user, nom, prenom, email, role FROM " . $this->table_name . " WHERE id_user = :id_user LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_user', $id_user);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update() {
        $password_set = !empty($this->password) ? ", password = :password" : "";

        $query = "UPDATE " . $this->table_name . " SET nom = :nom, prenom = :prenom, email = :email, role = :role {$password_set} WHERE id_user = :id_user";

        $stmt = $this->conn->prepare($query);

        $this->nom = htmlspecialchars(strip_tags($this->nom));
        $this->prenom = htmlspecialchars(strip_tags($this->prenom));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->id_user = htmlspecialchars(strip_tags($this->id_user));

        $stmt->bindParam(':nom', $this->nom);
        $stmt->bindParam(':prenom', $this->prenom);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':role', $this->role);

        if ($password_set) {
            $this->password = password_hash($this->password, PASSWORD_BCRYPT);
            $stmt->bindParam(':password', $this->password);
        }

        $stmt->bindParam(':id_user', $this->id_user);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_user = :id_user";
        $stmt = $this->conn->prepare($query);
        $this->id_user = htmlspecialchars(strip_tags($this->id_user));
        $stmt->bindParam(':id_user', $this->id_user);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getAllUsers() {
        $query = "SELECT id_user, nom, prenom, email, role, created_at FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
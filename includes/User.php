<?php
require_once 'database.php';
require_once 'functions.php';

class User {
    private $db;
    private $table = 'users';

    public function __construct() {
        $this->db = new Database();
    }

    public function create($data) {
        $conn = $this->db->getConnection();
        
        $sql = "INSERT INTO " . $this->table . " (name, email, password, role, status, created_at) 
                VALUES (?, ?, ?, 'user', 'active', NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $data['name'], $data['email'], hashPassword($data['password']));
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    public function findByEmail($email) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT * FROM " . $this->table . " WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    public function findById($id) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    public function updatePassword($email, $newPassword) {
        $conn = $this->db->getConnection();
        
        $sql = "UPDATE " . $this->table . " SET password = ? WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", hashPassword($newPassword), $email);
        
        return $stmt->execute();
    }

    public function update($id, $data) {
        $conn = $this->db->getConnection();
        
        $sql = "UPDATE " . $this->table . " SET name = ?, email = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $data['name'], $data['email'], $id);
        
        return $stmt->execute();
    }

    public function delete($id) {
        $conn = $this->db->getConnection();
        
        $sql = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        return $stmt->execute();
    }

    public function getAllUsers() {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT * FROM " . $this->table . " ORDER BY created_at DESC";
        $result = $conn->query($sql);
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function count() {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT COUNT(*) as count FROM " . $this->table;
        $result = $conn->query($sql);
        
        return $result->fetch_assoc()['count'];
    }
}
?>
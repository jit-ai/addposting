<?php
require_once 'database.php';
require_once 'functions.php';

class Posting {
    private $db;
    private $table = 'postings';

    public function __construct() {
        $this->db = new Database();
    }

public function create($data) {
        $conn = $this->db->getConnection();
        
        // Determine status and scheduled time
        $status = isset($data['status']) ? $data['status'] : 'active';
        $scheduledAt = null;
        
        $sql = "INSERT INTO " . $this->table . " (user_id, title, description, category, price, state, city, contact, images, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        // Handle optional price
        $price = isset($data['price']) && $data['price'] !== '' ? (float)$data['price'] : 0;
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssdsssss", $data['user_id'], $data['title'], $data['description'], 
                        $data['category'], $price, $data['state'], $data['city'], $data['contact'], 
                        $data['images'], $status);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        
        return false;
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

    public function findByTitle($title) {
        $conn = $this->db->getConnection();
        
        // First, publish any scheduled posts that are due
        $this->publishScheduledPosts();
        // Check if title ends with -ID (numeric)
        if (preg_match('/^(.+)-(\d+)$/', $title, $matches)) {
            $slug = $matches[1];
            $id = $matches[2];
            // Find by ID first for efficiency
            $posting = $this->findById($id);
            if ($posting && $posting['status'] === 'active') {
                $urlTitle = strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9 ]/', '', $posting['title'])));
                if ($urlTitle === $slug) {
                    return $posting;
                }
            }
        }
        
        // Get all active postings and find matching title
        $sql = "SELECT * FROM " . $this->table . " WHERE status = 'active' ORDER BY created_at DESC";
        $result = $conn->query($sql);
        
        while ($row = $result->fetch_assoc()) {
            $urlTitle = strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9 ]/', '', $row['title'])));
            if ($urlTitle === $title) {
                return $row;
            }
        }
        
        return null;
    }

    public function findByUserId($userId, $limit = null, $offset = 0) {
        $conn = $this->db->getConnection();

        $sql = "SELECT * FROM " . $this->table . " WHERE user_id = ? ORDER BY created_at DESC";

        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $userId, $limit, $offset);
        } else {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $userId);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function countByUserId($userId) {
        $conn = $this->db->getConnection();

        $sql = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'];
    }

    public function getAll($category = null, $search = null, $state = null, $city = null, $limit = null, $offset = 0) {
        $conn = $this->db->getConnection();

        // First, publish any scheduled posts that are due
        $this->publishScheduledPosts();

        $sql = "SELECT p.*, u.name as author_name FROM " . $this->table . " p
                JOIN users u ON p.user_id = u.id
                WHERE p.status = 'active'";

        $params = [];
        $types = '';

        if ($category) {
            $sql .= " AND p.category = ?";
            $params[] = $category;
            $types .= 's';
        }

        if ($search) {
            $sql .= " AND (p.title LIKE ? OR p.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $types .= 'ss';
        }

        if ($state) {
            $sql .= " AND p.state = ?";
            $params[] = $state;
            $types .= 's';
        }

        if ($city) {
            $sql .= " AND p.city = ?";
            $params[] = $city;
            $types .= 's';
        }

        $sql .= " ORDER BY p.created_at DESC";

        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= 'ii';
        }

        if ($types) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($sql);
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getTotalCount($category = null, $search = null, $state = null, $city = null) {
        $conn = $this->db->getConnection();

        $sql = "SELECT COUNT(*) as total FROM " . $this->table . " p
                WHERE p.status = 'active'";

        $params = [];
        $types = '';

        if ($category) {
            $sql .= " AND p.category = ?";
            $params[] = $category;
            $types .= 's';
        }

        if ($search) {
            $sql .= " AND (p.title LIKE ? OR p.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $types .= 'ss';
        }

        if ($state) {
            $sql .= " AND p.state = ?";
            $params[] = $state;
            $types .= 's';
        }

        if ($city) {
            $sql .= " AND p.city = ?";
            $params[] = $city;
            $types .= 's';
        }

        if ($types) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($sql);
        }

        $row = $result->fetch_assoc();
        return $row['total'];
    }

    public function publishScheduledPosts() {
        $conn = $this->db->getConnection();
        
        $sql = "UPDATE " . $this->table . " SET status = 'active', updated_at = NOW() 
                WHERE status = 'pending' AND scheduled_at <= NOW()";
        
        return $conn->query($sql);
    }

    public function update($id, $data) {
        $conn = $this->db->getConnection();
        
        // Handle optional price
        $price = isset($data['price']) && $data['price'] !== '' ? (float)$data['price'] : 0;
        
        $sql = "UPDATE " . $this->table . " SET title = ?, description = ?, category = ?, price = ?, 
                state = ?, city = ?, contact = ?, images = ?, status = ?, updated_at = NOW() 
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdsssssi", $data['title'], $data['description'],
                        $data['category'], $price, $data['state'], $data['city'], $data['contact'],
                        $data['images'], $data['status'], $id);
        
        return $stmt->execute();
    }

    public function delete($id) {
        $conn = $this->db->getConnection();
        
        $sql = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        return $stmt->execute();
    }

    public function count() {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT COUNT(*) as count FROM " . $this->table;
        $result = $conn->query($sql);
        
        return $result->fetch_assoc()['count'];
    }

    public function countByCategory() {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT category, COUNT(*) as count FROM " . $this->table . " 
                WHERE status = 'active' GROUP BY category";
        $result = $conn->query($sql);
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getRecent($limit = 5) {
        $conn = $this->db->getConnection();
        
        // First, publish any scheduled posts that are due
        $this->publishScheduledPosts();
        
        $sql = "SELECT p.*, u.name as author_name FROM " . $this->table . " p 
                JOIN users u ON p.user_id = u.id 
                WHERE p.status = 'active' 
                ORDER BY p.created_at DESC LIMIT ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getSimilar($category, $excludeId, $limit = 4) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT p.*, u.name as author_name FROM " . $this->table . " p 
                JOIN users u ON p.user_id = u.id 
                WHERE p.status = 'active' 
                AND p.category = ? 
                AND p.id != ? 
                ORDER BY p.created_at DESC LIMIT ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $category, $excludeId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>
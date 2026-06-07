<?php
// ========================================
// AUTH API - Authentification et Autorisation
// ========================================

header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';

// Classe d'authentification
class Auth {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    // Inscription d'un nouvel utilisateur
    public function register($data) {
        if (!isset($data['email'], $data['password'], $data['first_name'], $data['last_name'])) {
            return ['success' => false, 'message' => 'Données manquantes'];
        }
        
        // Vérifier si l'email existe déjà
        $this->db->query('SELECT id FROM users WHERE email = ?');
        $this->db->bind('s', $data['email']);
        if ($this->db->resultSet()->num_rows > 0) {
            return ['success' => false, 'message' => 'Email déjà utilisé'];
        }
        
        // Créer le nouvel utilisateur
        $hashed_password = password_hash($data['password'], HASH_ALGO, HASH_OPTIONS);
        $this->db->query('INSERT INTO users (first_name, last_name, email, password, phone, user_type) 
                         VALUES (?, ?, ?, ?, ?, ?)');
        $this->db->bind('s', $data['first_name']);
        $this->db->bind('s', $data['last_name']);
        $this->db->bind('s', $data['email']);
        $this->db->bind('s', $hashed_password);
        $this->db->bind('s', $data['phone'] ?? '');
        $this->db->bind('s', 'client');
        
        if ($this->db->execute()) {
            return ['success' => true, 'message' => 'Inscription réussie'];
        }
        return ['success' => false, 'message' => 'Erreur lors de l\'inscription'];
    }
    
    // Connexion utilisateur
    public function login($email, $password) {
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email ou mot de passe manquant'];
        }
        
        $this->db->query('SELECT id, first_name, last_name, email, password, user_type, status 
                         FROM users WHERE email = ? AND status = ?');
        $this->db->bind('s', $email);
        $this->db->bind('s', 'active');
        
        $result = $this->db->resultSet();
        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Identifiants invalides'];
        }
        
        $user = $result->fetch_assoc();
        
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Identifiants invalides'];
        }
        
        // Générer le token JWT
        $token = $this->generateToken($user);
        
        return [
            'success' => true,
            'message' => 'Connexion réussie',
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
                'user_type' => $user['user_type']
            ]
        ];
    }
    
    // Générer un token JWT
    private function generateToken($user) {
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload = base64_encode(json_encode([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'user_type' => $user['user_type'],
            'iat' => time(),
            'exp' => time() + JWT_EXPIRY
        ]));
        $signature = hash_hmac('sha256', "$header.$payload", JWT_SECRET, true);
        $signature = base64_encode($signature);
        
        return "$header.$payload.$signature";
    }
    
    // Vérifier le token JWT
    public function verifyToken($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }
        
        [$header, $payload, $signature] = $parts;
        $expected_signature = hash_hmac('sha256', "$header.$payload", JWT_SECRET, true);
        $expected_signature = base64_encode($expected_signature);
        
        if ($signature !== $expected_signature) {
            return false;
        }
        
        $decoded = json_decode(base64_decode($payload), true);
        if ($decoded['exp'] < time()) {
            return false;
        }
        
        return $decoded;
    }
}

// Traiter la requête
$method = $_SERVER['REQUEST_METHOD'];
$auth = new Auth($db);

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $_GET['action'] ?? '';
    
    if ($action === 'register') {
        echo json_encode($auth->register($input));
    } elseif ($action === 'login') {
        echo json_encode($auth->login($input['email'] ?? '', $input['password'] ?? ''));
    } else {
        echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
    }
}
?>

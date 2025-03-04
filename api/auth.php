<?php
class Auth {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($email, $password) {
        try {
            // For debugging
            error_log("Login attempt - Email: " . $email);
            
            // Check if email exists
            $query = "SELECT id, username, password, email, first_name, last_name, role 
                     FROM " . $this->table_name . " 
                     WHERE email = :email";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                error_log("User found - Stored password hash: " . $row['password']);
                
                // For testing purposes, accept 'password123' directly
                // In production, you should use proper password hashing
                if($password === 'password123' || password_verify($password, $row['password'])) {
                    // Generate JWT token
                    $token = $this->generateToken($row);
                    
                    return [
                        "success" => true,
                        "token" => $token,
                        "user" => [
                            "id" => $row['id'],
                            "username" => $row['username'],
                            "email" => $row['email'],
                            "first_name" => $row['first_name'],
                            "last_name" => $row['last_name'],
                            "role" => $row['role']
                        ]
                    ];
                }
            }
            
            return [
                "success" => false,
                "message" => "Invalid email or password"
            ];
            
        } catch(PDOException $e) {
            return [
                "success" => false,
                "message" => "Database error: " . $e->getMessage()
            ];
        }
    }

    private function generateToken($user) {
        $secret_key = "your_secret_key"; // In production, use a secure secret key from environment variables
        $issuer_claim = "salesapp_api"; // this can be the servername
        $audience_claim = "salesapp_client";
        $issuedat_claim = time(); // issued at
        $notbefore_claim = $issuedat_claim; //not before
        $expire_claim = $issuedat_claim + 3600; // expire time in seconds
        
        $token = array(
            "iss" => $issuer_claim,
            "aud" => $audience_claim,
            "iat" => $issuedat_claim,
            "nbf" => $notbefore_claim,
            "exp" => $expire_claim,
            "data" => array(
                "id" => $user['id'],
                "username" => $user['username'],
                "email" => $user['email'],
                "role" => $user['role']
            )
        );
        
        // Generate JWT using a library like Firebase JWT
        // For this example, we'll return a simple base64 encoded string
        return base64_encode(json_encode($token));
    }

    public function validateToken($token) {
        try {
            // Decode token
            $decoded = json_decode(base64_decode($token), true);
            
            if ($decoded && isset($decoded['exp'])) {
                // Check if token has expired
                if ($decoded['exp'] >= time()) {
                    return [
                        "success" => true,
                        "data" => $decoded['data']
                    ];
                }
            }
            
            return [
                "success" => false,
                "message" => "Invalid or expired token"
            ];
            
        } catch(Exception $e) {
            return [
                "success" => false,
                "message" => "Error decoding token"
            ];
        }
    }
}
?>

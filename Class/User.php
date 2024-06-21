<?php
class User
{
    private $conn;
    private $userID;
    private $userData;

    public function __construct($conn, $userID)
    {
        $this->conn = $conn;
        $this->userID = $userID;
        $this->loadUserData();
    }

    private function loadUserData()
    {
        $stmt = $this->conn->prepare("
            SELECT * 
            FROM users_db 
            WHERE userID = :userID
        ");
        $stmt->bindParam(':userID', $this->userID, PDO::PARAM_INT);
        $stmt->execute();
        $this->userData = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserData()
    {
        return $this->userData;
    }

    public function checkUserApproval()
    {
        if ($this->userData && $this->userData['status'] == 'not_approved') {
            unset($_SESSION['user_login']);
            header('Location: auth/sign_in');
            exit();
        }
    }

    public static function getUserIDFromSession($sessionKey)
    {
        return isset($_SESSION[$sessionKey]) ? $_SESSION[$sessionKey] : null;
    }
}

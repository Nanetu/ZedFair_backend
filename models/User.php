<?php

class User{
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function addUser($username, $email, $password, $role){
        $this->db->query("INSERT INTO user(username, email, password, role, created_at) VALUES (:username, :email, :password, :role, NOW())");
        $this->db->bind(':username', $username);
        $this->db->bind(':email', $email);
        $this->db->bind(':password', $password);
        $this->db->bind(':role', $role);
        $this->db->execute();
    }

    public function getUser($email){
        $this->db->query("SELECT user_id, username, email, password, role FROM user WHERE email = :email");
        $this->db->bind(':email', $email);
        $this->db->execute();
        return $this->db->result();
    }

    public function getUserById($id){
        $this->db->query("SELECT role FROM user WHERE user_id = :id");
        $this->db->bind(':id', $id);
        $this->db->execute();
        return $this->db->result();
    }

    public function getAllExhibitors(){
        $this->db->query("SELECT DISTINCT COUNT(*) AS total FROM user WHERE role = 'exhibitor' OR role = 'both'");
        $this->db->execute();
        return $this->db->result();
    }

    public function getAllVendors(){
        $this->db->query("SELECT DISTINCT COUNT(*) AS total FROM user WHERE role = 'vendor' OR role = 'both'");
        $this->db->execute();
        return $this->db->result();
    }

    public function getAllUsers(){
        $this->db->query("SELECT * FROM user");
        $this->db->execute();
        return $this->db->results();
    }
}

?>

<?php

class Vendor_Setup{
    private $db;

    public function __construct(){
        $this->db = new Database;
    }    
    
    public function initVendorSetup($userId, $categoryId) {
    $this->db->query("INSERT INTO vendor_setup (user_id, category_id) VALUES (:user_id, :category)");
    $this->db->bind(":user_id", $userId);
    $this->db->bind(":category", $categoryId);
    $this->db->execute();
    }

    public function getSetupCategory($userId) {
        $this->db->query("SELECT category_id FROM vendor_setup WHERE user_id = :uid");
        $this->db->bind(":uid", $userId);
        return $this->db->result();
    }

}

?>
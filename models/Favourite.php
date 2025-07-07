<?php

class Favourite{
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function addFavourite($user, $vendor){
        $this->db->query("INSERT INTO favourite(user_id, vendor_id, created_at) VALUES (:user, :vendor, NOW())");
        $this->db->bind(':user', $user);
        $this->db->bind(':vendor', $vendor);
        $this->db->execute();
    }

    public function getFavouriteVendors($user){
        $this->db->query("SELECT * FROM favourite WHERE user_id = :user");
        $this->db->bind(':user', $user);
        $this->db->execute();
        return $this->db->results();
    }
}

?>

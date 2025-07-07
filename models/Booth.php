<?php

class Booth{
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function addBooth($booth_id, $user, $location){
        $this->db->query("INSERT INTO booth(booth_id, user_id, location_id) VALUES (:booth_id, :user, :location)");
        $this->db->bind(':booth_id', $booth_id);
        $this->db->bind(':user', $user);
        $this->db->bind(':location', $location);
        $this->db->execute();
    }

    public function getBooth($user){
        $this->db->query("SELECT * FROM booth WHERE user_id = :user");
        $this->db->bind(':user', $user);
        $this->db->execute();
        return $this->db->result();
    }

    public function getBoothById($id){
        $this->db->query("SELECT * FROM booth WHERE booth_id = :id");
        $this->db->bind(':id', $id);
        $this->db->execute();
        return $this->db->result();
    }

    public function getAllBooths(){
        $this->db->query("SELECT * FROM booth");
        $this->db->execute();
        return $this->db->results();
    }
}

?>

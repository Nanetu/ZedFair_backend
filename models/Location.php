<?php

class Location{
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function addLocation($location, $assigned){
        $this->db->query("INSERT INTO location(location_type, is_assigned) VALUES (:location, :assigned)");
        $this->db->bind(':location', $location);
        $this->db->bind(':assigned', $assigned);
        $this->db->execute();
    }

    public function countLocations(){
        $this->db->query("SELECT COUNT(*) AS total FROM location");
        $this->db->execute();
        return $this->db->result();
    }

    public function setAssigned($location){
        $this->db->query("UPDATE location SET is_assigned = 1 WHERE location_id = :location");
        $this->db->bind(':location', $location);
        $this->db->execute();
        return $this->db->result();
    }

    public function getLocation($location){
        $this->db->query("SELECT * FROM location WHERE location_id = :location");
        $this->db->bind(':location', $location);
        $this->db->execute();
        return $this->db->result();
    }

    public function getAllFreeLocations(){
        $this->db->query("SELECT * FROM location WHERE is_assigned = 0");
        $this->db->execute();
        return $this->db->results();
    }
}

?>

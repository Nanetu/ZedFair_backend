<?php

class Event{
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function addEvent($vendor, $title, $time_start, $time_end){
        $this->db->query("INSERT INTO event(vendor_id, title, time_start, time_end) VALUES (:vendor, :title, :start, :end)");
        $this->db->bind(':vendor', $vendor);
        $this->db->bind(':title', $title);
        $this->db->bind(':start', $time_start);
        $this->db->bind(':end', $time_end);
        $this->db->execute();
    }

    public function getEventByVendor($vendor){
        $this->db->query("SELECT * FROM event WHERE vendor_id = :vendor ORDER BY time_start ASC LIMIT 1");
        $this->db->bind(':vendor', $vendor);
        $this->db->execute();
        return $this->db->result();
    }

    public function getAllEventsByVendor($vendor){
        $this->db->query("SELECT * FROM event WHERE vendor_id = :vendor AND time_start >= NOW()");
        $this->db->bind(':vendor', $vendor);
        $this->db->execute();
        return $this->db->results();
    }

    public function upComing(){
        $this->db->query("SELECT time_start, title FROM event WHERE time_start >= NOW() ORDER BY time_start ASC LIMIT 5");
        $this->db->execute();
        return $this->db->results();
    }


    public function getAllEvents(){
        $this->db->query("SELECT * FROM event");
        $this->db->execute();
        return $this->db->results();
    }

    public function deleteEvent($title){
        $this->db->query("DELETE FROM event WHERE title = :title");
        $this->db->bind(':title', $title);
        $this->db->execute();
    }
}

?>

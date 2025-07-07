<?php

class Schedule{
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function addSchedule($user, $event){
        $this->db->query("INSERT INTO schedule(user_id, event_id) VALUES (:user, :event)");
        $this->db->bind(':user', $user);
        $this->db->bind(':event', $event);
        $this->db->execute();
    }

    public function getScheduleByUser($user){
        $this->db->query("SELECT e.* FROM schedule s 
                            JOIN event e on s.event_id = e.event_id
                            WHERE s.user_id = :user
                            AND e.time_start >= NOW() 
                            ORDER BY e.time_start ASC");
        $this->db->bind(':user', $user);
        $this->db->execute();
        return $this->db->results();
    }

    public function getAllUsersForEvent($event){
        $this->db->query("SELECT * FROM schedule WHERE event_id = :event");
        $this->db->bind(':event', $event);
        $this->db->execute();
        return $this->db->results();
    }

    public function deleteSchedule($event){
        $this->db->query("DELETE FROM schedule WHERE event_id = :event");
        $this->db->bind(':event', $event);
        $this->db->execute();
    }
}

?>

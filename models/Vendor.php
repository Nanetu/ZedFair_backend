<?php

class Vendor{
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function addVendor($user, $business, $type, $category, $booth, $description, $logo){
        $this->db->query("INSERT INTO vendor(user_id, category_id, business_name, business_type, booth_number, description, logo, created_at)
                        VALUES (:user, :category, :business, :type, :booth, :description, :logo, NOW())
                        ");
        $this->db->bind(':user', $user);
        $this->db->bind(':category', $category);
        $this->db->bind(':business', $business);
        $this->db->bind(':type', $type);
        $this->db->bind(':booth', $booth);
        $this->db->bind(':description', $description);
        $this->db->bind(':logo', $logo);
        $this->db->execute();
    }

    public function getVendor($user){
        $this->db->query("SELECT * FROM vendor WHERE user_id = :user");
        $this->db->bind(':user', $user);
        $this->db->execute();
        return $this->db->result();
    }

    public function getVendorById($id){
        $this->db->query("SELECT * FROM vendor WHERE vendor_id = :id");
        $this->db->bind(':id', $id);
        $this->db->execute();
        return $this->db->result();
    }

    public function getVendorByName($name){
        $this->db->query("SELECT * FROM vendor WHERE business_name LIKE :name");
        $this->db->bind(':name', '%'.$name.'%');
        $this->db->execute();
        return $this->db->result();
    }

    public function updateProfile($user, $name, $type, $desc, $logo){
        $this->db->query("UPDATE vendor SET business_name = :name, business_type = :type, description = :desc, logo = :logo WHERE user_id = :user");
        $this->db->bind(':user', $user);
        $this->db->bind(':name', $name);
        $this->db->bind(':type', $type);
        $this->db->bind(':desc', $desc);
        $this->db->bind(':logo', $logo);
        $this->db->execute();
        return $this->db->result();
    }

    public function getVendorsByCategory($category, $sortby, $orderby){
        $this->db->query("SELECT * FROM vendor WHERE category_id = :category ORDER BY :sortby $orderby");
        $this->db->bind(':category', $category);
        $this->db->bind(':sortby', $sortby);
        $this->db->execute();
        return $this->db->results();

    }

    public function getAllVendorsBySortedEntry($entry, $sortby, $orderby){
        $this->db->query("SELECT * FROM vendor WHERE business_name LIKE :entry 
                        OR category_id LIKE :entry OR description LIKE :entry ORDER BY :sortby $orderby");
        $this->db->bind(':entry', '%'.$entry.'%');
        $this->db->bind(':sortby', $sortby);
        $this->db->execute();
        return $this->db->results();
    }

    public function getAllVendorsByEntry($entry){
        $this->db->query("SELECT * FROM vendor WHERE business_name LIKE :entry 
                        OR category_id LIKE :entry OR description LIKE :entry");
        $this->db->bind(':entry', '%'.$entry.'%');
        $this->db->execute();
        return $this->db->results();
    }

    public function getAllVendors(){
        $this->db->query("SELECT * FROM vendor");
        $this->db->execute();
        return $this->db->results();
    }

}

?>

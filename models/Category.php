<?php

class Category{
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function addCategory($name){
        $this->db->query("INSERT INTO category(category_name) VALUES (:name)");
        $this->db->bind(':name', $name);
        $this->db->execute();
    }

    public function getCategory($id){
        $this->db->query("SELECT category_name FROM category WHERE category_id = :id");
        $this->db->bind(':id', $id);
        $this->db->execute();
        return $this->db->result();
    }

    public function getCategoryByName($category){
        $this->db->query("SELECT category_id FROM category WHERE category_name = :category");
        $this->db->bind(':category', $category);
        $this->db->execute();
        return $this->db->result();
    }

    public function getAllCategories(){
        $this->db->query("SELECT * FROM category");
        $this->db->execute();
        return $this->db->results();
    }
}

?>

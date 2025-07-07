<?php

class Product{
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function addProduct($name, $category, $vendor, $amount){
        $this->db->query("INSERT INTO product(product_name, category_id, vendor_id, amount_left) VALUES (:name, :category, :vendor, :amount)");
        $this->db->bind(':name', $name);
        $this->db->bind(':category', $category);
        $this->db->bind(':vendor', $vendor);
        $this->db->bind(':amount', $amount);
        $this->db->execute();
    }

    public function getProductsForVendor($vendor){
        $this->db->query("SELECT * FROM product WHERE vendor = :vendor AND amount_left > 0");
        $this->db->bind(':vendor', $vendor);
        $this->db->execute();
        return $this->db->results();
    }

    public function getProductsByCategory($category){
        $this->db->query("SELECT * FROM product WHERE category_id = :category AND amount_left > 0");
        $this->db->bind(':category', $category);
        $this->db->execute();
        return $this->db->results();
    }

    public function getAllProducts($product){
        $this->db->query("SELECT * FROM product WHERE product_name LIKE :product");
        $this->db->bind(':product', '%'.$product.'%');
        $this->db->execute();
        return $this->db->results();
    }

    public function getRandomProducts() {
    $this->db->query("SELECT * FROM product WHERE amount_left > 0 ORDER BY RAND() LIMIT 5");
    $this->db->execute();
    return $this->db->results();
}

    public function countStock($product){
        $this->db->query("SELECT amount_left FROM product WHERE product_id = :product");
        $this->db->bind(':product', $product);
        $this->db->execute();
        return $this->db->result();
    }

    public function updateStock($product, $amount){
        $count = $this->countStock($product);
        $total = $count-$amount;
        $this->db->query("UPDATE product SET amount_left = :total");
        $this->db->bind(':total', $total);
        $this->db->execute();
        return $this->db->results();
    }
}

?>

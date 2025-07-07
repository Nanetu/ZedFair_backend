<?php

class Controller {
    protected function loadModel($model){
        require_once __DIR__ . '/../models/' . $model . '.php';
        return new $model;
    }

    protected function setJsonHeaders(){
        header("Content-Type: application/json");
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json');
    }
}

?>
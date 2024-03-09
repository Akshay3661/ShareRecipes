<?php

class Database {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $dbName = "ShareRecipeDB";
    public $conn;


    public function getConnection() {
        if (!$this->conn) {
            $this->conn = new mysqli($this->host, $this->username, $this->password);

            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }

            $this->createDatabase();
            $this->conn->select_db($this->dbName);
            $this->createUsersTable();
            $this->createCategoryTable();
            $this->addDefaultCategories();
            $this->createRecipeTable();
            $this->createIngredientsTable();
            $this->createImagesTable();

        }

        return $this->conn;
    }


    private function createDatabase() {
        $createDatabaseQuery = "CREATE DATABASE IF NOT EXISTS " . $this->dbName;
        if ($this->conn->query($createDatabaseQuery) === false) {
            throw new Exception("Database creation failed: " . $this->conn->error);
        }
    }

    private function createUsersTable() {
        $query = "CREATE TABLE IF NOT EXISTS usersTable (
            id INT AUTO_INCREMENT PRIMARY KEY,
            firstName VARCHAR(255),
            lastName VARCHAR(255),
            email VARCHAR(255),
            password VARCHAR(255)
        )";

        if ($this->conn->query($query) === false) {
            throw new Exception("Table creation failed: " . $this->conn->error);
        }
    }

    private function createCategoryTable() {
        $query = "CREATE TABLE IF NOT EXISTS categoryTable (
            id INT AUTO_INCREMENT PRIMARY KEY,
            categories VARCHAR(255),
            Status VARCHAR(50)
        )";

        if ($this->conn->query($query) === false) {
            throw new Exception("Table creation failed: " . $this->conn->error);
        }
    }

    private function addDefaultCategories() {
        $defaultCategories = ['Veg', 'Nonveg', 'Lunch', 'Dinner', 'Breakfast'];
    
        foreach ($defaultCategories as $category) {
            $category = $this->conn->real_escape_string($category);
    
            // Check if the category already exists
            $checkQuery = "SELECT id FROM categoryTable WHERE categories = '$category'";
            $checkResult = $this->conn->query($checkQuery);
    
            if ($checkResult->num_rows === 0) {
                // Category doesn't exist, insert it
                $insertQuery = "INSERT INTO categoryTable (categories, Status) VALUES ('$category', 'active')";
                
                if ($this->conn->query($insertQuery) === false) {
                    throw new Exception("Default category insertion failed: " . $this->conn->error);
                }
            }
        }
    }
      
    private function createRecipeTable() {
        $query = "CREATE TABLE IF NOT EXISTS recipeTable (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_Id INT,
            category_Id INT,
            name VARCHAR(255),
            description TEXT,
            method TEXT,
            status VARCHAR(50),
            FOREIGN KEY (user_Id) REFERENCES usersTable(id),
            FOREIGN KEY (category_Id) REFERENCES categoryTable(id)
        )";

        if ($this->conn->query($query) === false) {
            throw new Exception("Table creation failed: " . $this->conn->error);
        }
    }
    private function createIngredientsTable() {
        $query = "CREATE TABLE IF NOT EXISTS ingredientTable (
            id INT AUTO_INCREMENT PRIMARY KEY,
            recipe_Id INT,
            ingredients VARCHAR(255),
            FOREIGN KEY (recipe_Id) REFERENCES recipeTable(id)
        )";

        if ($this->conn->query($query) === false) {
            throw new Exception("Table creation failed: " . $this->conn->error);
        }
    }

    private function createImagesTable() {
        $query = "CREATE TABLE IF NOT EXISTS imagesTable (
            id INT AUTO_INCREMENT PRIMARY KEY,
            recipe_Id INT,
            images VARCHAR(255),
            FOREIGN KEY (recipe_Id) REFERENCES recipeTable(id)
        )";

        if ($this->conn->query($query) === false) {
            throw new Exception("Table creation failed: " . $this->conn->error);
        }
    }
    public function closeConnection() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
define("UPLOAD_SRC", $_SERVER['DOCUMENT_ROOT'] . "/shareRecipes/uploads/");

define("FETCH_SRC","http://127.0.0.1/shareRecipes/uploads/");




<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once '../config/Database.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newcategory'])) {
  
    // Function to sanitize input data
    function sanitizeInput($input)
    {
        global $conn;
        return $conn->real_escape_string(trim($input));
    }

    try {
        $newCategory = sanitizeInput($_POST['new_category']);

        $db = new Database();
        $conn = $db->getConnection();

        // Insert new category into categoryTable
        $insertCategoryQuery = "INSERT INTO categoryTable (categories, Status) VALUES ('$newCategory', 'active')";
        if ($conn->query($insertCategoryQuery) === false) {
            throw new Exception("Error adding new category: " . $conn->error);
        }

        $db->closeConnection();
        header("Location: ../view/MyRecipes.php?success=true");
        exit();
        
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());

        header("Location: ../view/MyRecipes.php?error=true");
        exit();
    }
} else {
    header("Location: ../view/MyRecipes.php?alert=`not so smart thing to do`");
    exit();
}
?>

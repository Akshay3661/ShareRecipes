<?php
session_start();
include("../config/Database.php");

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
    $recipeId = $_GET['id'];

    $conn->begin_transaction();

    try {
        // Delete from ingredientTable
        $deleteIngredientsQuery = "DELETE FROM ingredientTable WHERE recipe_id = ?";
        $stmtIngredients = $conn->prepare($deleteIngredientsQuery);
        $stmtIngredients->bind_param("i", $recipeId);
        if (!$stmtIngredients->execute()) {
            die('Error deleting ingredients: ' . $stmtIngredients->error);
        }

        // Delete from imagesTable
        $deleteImagesQuery = "DELETE FROM imagesTable WHERE recipe_id = ?";
        $stmtImages = $conn->prepare($deleteImagesQuery);
        $stmtImages->bind_param("i", $recipeId);
        if (!$stmtImages->execute()) {
            die('Error deleting images: ' . $stmtImages->error);
        }

        // Delete from recipeTable
        $deleteRecipeQuery = "DELETE FROM recipeTable WHERE id = ?";
        $stmtRecipe = $conn->prepare($deleteRecipeQuery);
        $stmtRecipe->bind_param("i", $recipeId);
        if (!$stmtRecipe->execute()) {
            die('Error deleting recipe: ' . $stmtRecipe->error);
        }

        // Commit the transaction
        $conn->commit();

        header("Location: ../view/myRecipes.php?success=Recipe_deleted");
        exit();
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollback();
        echo "Error: " . $e->getMessage('Failed');
    }
} else {
    // Redirect if not a GET request or recipe ID not set
    header("Location: ../view/myRecipes.php?alert=Failed_to_delete_Recipe");
    exit();
}
?>



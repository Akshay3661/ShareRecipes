<?php
session_start();
include("../config/Database.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['updateRecipe'])) {
    $db = new Database();
    $conn = $db->getConnection();

    $recipeId = filter_input(INPUT_POST, 'recipeId', FILTER_VALIDATE_INT);
    if ($recipeId === false || $recipeId === null) {
        die("Invalid recipe ID.");
    }

    $updateFields = [];
    $updateParams = [];


    function addField($postKey, $fieldName) {
        global $updateFields, $updateParams;
        if (isset($_POST[$postKey])) {
            $updateFields[] = "$fieldName=?";
            $updateParams[] = $_POST[$postKey];
        }
    }

    addField('name', 'name');
    addField('description', 'description');
    addField('category', 'category_Id');
    addField('Recipemethod', 'method');
    addField('status', 'status');


    if (isset($_FILES['images'])) {
        $imageUpdateResult = handleImageUpdate($conn, $recipeId);
        if (!$imageUpdateResult['success']) {
            die("Error updating images: " . $imageUpdateResult['error']);
        }
    }

    $updateFieldsString = implode(', ', $updateFields);
    $updateRecipeQuery = "UPDATE recipeTable SET $updateFieldsString WHERE id=?";
    $stmt = $conn->prepare($updateRecipeQuery);


    $paramsCount = count($updateParams);
    $types = str_repeat('s', $paramsCount) . 'i';
    $updateParams[] = &$recipeId; 
    $stmt->bind_param($types, ...$updateParams);

    

    if (!$stmt->execute()) {
        die("Error updating recipe: " . $stmt->error);
    }
    header("Location: ../view/myRecipes.php?Success=Updated");
    exit();
} else {
    echo "Unauthorized access or invalid request.";
}

function handleImageUpdate($conn, $recipeId) {
    $uploadDirectory = $_SERVER['DOCUMENT_ROOT'] . "/shareRecipes/uploads/";

    if (!file_exists($uploadDirectory)) {
        mkdir($uploadDirectory, 0777, true);
    }

    $imageUpdateResult = array('success' => true, 'error' => '');

    // Check if there's an existing record for the recipe
    $checkQuery = "SELECT * FROM imagesTable WHERE recipe_Id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("i", $recipeId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    $imageNamesJSON = ''; // Initialize the variable here

    if ($result->num_rows > 0) {
        // Existing record found, update it
        $updateImagesQuery = "UPDATE imagesTable SET images = ? WHERE recipe_Id = ?";
        $stmt = $conn->prepare($updateImagesQuery);
        $stmt->bind_param("si", $imageNamesJSON, $recipeId);
    } else {
        // No existing record found, insert a new record
        $insertImagesQuery = "INSERT INTO imagesTable (recipe_Id, images) VALUES (?, ?)";
        $stmt = $conn->prepare($insertImagesQuery);
        $stmt->bind_param("is", $recipeId, $imageNamesJSON);
    }

    // Handle file uploads
    if (isset($_FILES['images']) && $_FILES['images']['error'][0] != 4) {
        $uploadedImages = $_FILES['images'];
        $imageArray = array();

        foreach ($uploadedImages['tmp_name'] as $key => $tmp_name) {
            $fileName = $uploadedImages['name'][$key];
            $filePath = $uploadDirectory . $fileName;

            if (move_uploaded_file($tmp_name, $filePath)) {
                $imageArray[] = $fileName;
            } else {
                $imageUpdateResult['success'] = false;
                $imageUpdateResult['error'] = "Error uploading images.";
                break;
            }
        }

        if ($imageUpdateResult['success']) {
            $imageNamesJSON = json_encode($imageArray);
        }
    }

    // Execute the SQL query
    if (!$stmt->execute()) {
        $imageUpdateResult['success'] = false;
        $imageUpdateResult['error'] = "Error updating/inserting images in the database: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();

    return $imageUpdateResult;
}


?>





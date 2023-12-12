<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ./Login.php");
    exit();
}

include '../config/Database.php';

$db = new Database();
$con = $db->getConnection();

function image_upload($images)
{
    $uploaded_images = [];

    foreach ($images['tmp_name'] as $key => $temp_loc) {
        $new_name = random_int(11111, 99999) . $images['name'][$key];
        $new_loc = UPLOAD_SRC . $new_name;

        $allowed_types = ['image/jpeg', 'image/png'];
        $max_file_size = 5 * 1024 * 1024; // 5 MB

        if (in_array($images['type'][$key], $allowed_types) && $images['size'][$key] <= $max_file_size) {
            if (!move_uploaded_file($temp_loc, $new_loc)) {
                header("Location: ../view/myRecipes.php?alert=img_upload");
                die("Error uploading files.");
            } else {
                $uploaded_images[] = $new_name;
            }
        } else {
            die("Invalid file type or size.");
        }
    }

    return $uploaded_images;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addRecipe'])) {
    $user_id = $_SESSION['user_id'];
    $category = $_POST['category'];
    $name = htmlspecialchars($_POST['name']);
    $description = htmlspecialchars($_POST['description']);
    $method_text = htmlspecialchars($_POST['Recipemethod']);
    $status = $_POST['status'];

    $imgpaths = image_upload($_FILES["images"]);

    // Checking for JSON encode errors
    $imgpaths_json = json_encode($imgpaths);
    if (json_last_error() !== JSON_ERROR_NONE) {
        die("Error encoding image paths.");
    }

    try {
        $query = $con->prepare("INSERT INTO recipeTable (user_Id, category_Id, name, description, method, status) VALUES (?, ?, ?, ?, ?, ?)");

        if ($query) {
            $query->bind_param("iissss", $user_id, $category, $name, $description, $method_text, $status);

            if ($query->execute()) {
                $recipe_id = $con->insert_id;

                // Insert ingredients into the ingredientTable
                if (!empty($_POST['ingredients'])) {
                    $ingredient_query = $con->prepare("INSERT INTO ingredientTable (recipe_Id, ingredients) VALUES (?, ?)");

                    if ($ingredient_query) {
                        $ingredient_json = json_encode($_POST['ingredients']);
                        $ingredient_query->bind_param("is", $recipe_id, $ingredient_json);
                        $ingredient_query->execute();
                    } else {
                        die("Error preparing ingredient SQL query: " . $con->error);
                    }
                }

                // Insert images into the imagesTable
                if (!empty($imgpaths)) {
                    $image_query = $con->prepare("INSERT INTO imagesTable (recipe_Id, images) VALUES (?, ?)");

                    if ($image_query) {
                        // Combine image filenames into a single JSON array
                        $imgpaths_json = json_encode($imgpaths);

                        // Bind the JSON array to the prepared statement
                        $image_query->bind_param("is", $recipe_id, $imgpaths_json);
                        $image_query->execute();
                    } else {
                        die("Error preparing image SQL query: " . $con->error);
                    }
                }

                header("Location: ../view/myRecipes.php?success=added");
            } else {
                die("Error executing SQL query: " . $query->error);
            }
        } else {
            die("Error preparing SQL query: " . $con->error);
        }
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    } finally {
        // Close the database connection
        $db->closeConnection();
    }
} else {
    // Redirect if the form was not submitted
    header("Location: ../view/myRecipes.php");
    exit();
}

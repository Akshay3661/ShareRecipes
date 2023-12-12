<?php
// session_start();
// include("../config/Database.php");

// if ($_SERVER["REQUEST_METHOD"] === "GET") {
//     if (isset($_GET['id'])) {
//         $recipeId = $_GET['id'];

//         $db = new Database();
//         $conn = $db->getConnection();

//         $query = "SELECT id, images FROM imagesTable WHERE recipe_Id = ?";
//         $stmt = $conn->prepare($query);
//         $stmt->bind_param("i", $recipeId);
//         $stmt->execute();
//         $result = $stmt->get_result();

//         if ($result->num_rows > 0) {
//             while ($row = $result->fetch_assoc()) {
//                 $imageId = $row['id'];
//                 $imageUrl = $row['images'];

//                 $imagePath = UPLOAD_SRC . $imageUrl;

//                 if (file_exists($imagePath) && unlink($imagePath)) {
//                     $deleteQuery = "DELETE FROM imagesTable WHERE id = ?";
//                     $deleteStmt = $conn->prepare($deleteQuery);
//                     $deleteStmt->bind_param("i", $imageId);
                    
//                     if ($deleteStmt->execute()) {
//                         echo json_encode(['success' => true]);
//                     } else {
//                         echo json_encode(['success' => false, 'error' => 'Error deleting database record']);
//                     }
//                 } else {
//                     echo json_encode(['success' => false, 'error' => 'Error deleting file or file not found']);
//                 }
//             }
//         } else {
//             echo json_encode(['success' => true, 'message' => 'No images found for deletion']);
//         }
//     } else {
//         echo json_encode(['success' => false, 'error' => 'Recipe ID not provided']);
//     }
// } else {
//     echo json_encode(['success' => false, 'error' => 'Invalid request method']);
// }






if (isset($_GET['id'])) {
    $recipeId = $_GET['id'];

    include("../config/Database.php");

    $db = new Database();
    $conn = $db->getConnection();

    $deleteImagesQuery = "DELETE FROM imagesTable WHERE recipe_Id = ?";
    $stmt = $conn->prepare($deleteImagesQuery);
    $stmt->bind_param("i", $recipeId);

    if ($stmt->execute()) {
        header("Location: ../view/editRecipe.php?id=" . $recipeId);
        exit();
    } else {
        header("Location: ../view/editRecipe.php?id=" . $recipeId . "&error=delete_error");
        exit();
    }
} else {
    echo "Recipe ID not provided.";
    exit();
}
?>



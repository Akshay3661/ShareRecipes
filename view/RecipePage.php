<?php
session_start();
include("../config/Database.php");

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

$firstName = $_SESSION['firstName'];
$lastName = $_SESSION['lastName'];

$db = new Database();
$conn = $db->getConnection();

function logout()
{
    $_SESSION = array();
    session_destroy();
    header("Location: ./index.php");
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['logout'])) {
    logout();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Page</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/classic/ckeditor.js"></script>
</head>

<body>
    <header>
        <section class="wrapper nav-flex">
            <!-- Your navigation code here -->
        </section>
    </header>

    <div class="container mt-5">
        <div class="btn btn-success"><a href="../index.php" class="text-decoration-none text-white">Back</a></div>
        <h1>Recipe</h1>
        <?php

        $recipe_id = isset($_GET['recipeId']) ? (int)$_GET['recipeId'] : 0;

        $query = "SELECT recipeTable.*, imagesTable.images, ingredientTable.ingredients FROM recipeTable 
          LEFT JOIN imagesTable ON recipeTable.id = imagesTable.recipe_Id 
          LEFT JOIN ingredientTable ON recipeTable.id = ingredientTable.recipe_Id 
          WHERE recipeTable.`id` = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) :
        ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h2><?php echo $row['name']; ?></h2>
                </div>
                <div class="card-body">
                    <div id="recipeCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php
                            $images = json_decode($row['images']);
                            if (!empty($images)) {
                                foreach ($images as $index => $image) {
                                    $activeClass = ($index == 0) ? 'active' : '';
                                    echo '<div class="carousel-item ' . $activeClass . '">';
                                    echo '<img src="' . FETCH_SRC . $image . '" class="d-block w-50" alt="Recipe Image">';
                                    echo '</div>';
                                }
                            } else {
                                echo '<div class="carousel-item active">';
                                echo '<img src="https://via.placeholder.com/800x400" class="d-block w-100" alt="Placeholder Image">';
                                echo '</div>';
                            }
                            ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#recipeCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#recipeCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>

                    <h5 class="mt-3">Description:</h5>
                    <p><?php echo $row['description']; ?></p>

                    <div class="mb-3">
                        <label for="method" class="form-label">Method:</label>
                        <textarea class="form-control method" id="method" name="Recipemethod" rows="6" placeholder="write method to cook..." disabled>
                        <?php echo $row['method']; ?>
                        </textarea>
                    </div>

                    <h5>Ingredients:</h5>
                    <ul>
                        <?php
                        $ingredients = json_decode($row['ingredients']);
                        if (!empty($ingredients)) {
                            foreach ($ingredients as $ingredient) {
                                echo '<li>' . $ingredient . '</li>';
                            }
                        } else {
                            echo '<li>No ingredients available</li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    <script>
        ClassicEditor
            .create(document.querySelector('.method'))
            .then(editor => {
                console.log(editor);
            })
            .catch(error => {
                console.error(error);
            });
    </script>
</body>

</html>





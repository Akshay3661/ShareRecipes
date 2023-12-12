<?php
session_start();
include("../config/Database.php");

$recipeId = $_GET['id'];

$db = new Database();
$conn = $db->getConnection();

$query = "
    SELECT
        rt.id AS recipe_id,
        rt.name AS recipe_name,
        rt.description AS recipe_description,
        rt.method AS recipe_method,
        rt.status AS recipe_status,
        ut.id AS user_id,
        ut.firstName AS user_firstName,
        ut.lastName AS user_lastName,
        ct.id AS category_id,
        ct.categories AS category_name,
        it.ingredients,
        img.images
    FROM
        recipeTable rt
    JOIN
        usersTable ut ON rt.user_Id = ut.id
    JOIN
        categoryTable ct ON rt.category_Id = ct.id
    LEFT JOIN
        ingredientTable it ON rt.id = it.recipe_Id
    LEFT JOIN
        imagesTable img ON rt.id = img.recipe_Id
    WHERE
        rt.id = ? 
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $recipeId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Recipe not found.";
    exit();
}

$recipeData = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Recipe</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/classic/ckeditor.js"></script>

</head>

<body>

    <div class="container mt-5">
        <h2>Edit Recipe</h2>
        <form action="../Model/editRecipeHandler.php" id="editRecipe" method="post" enctype="multipart/form-data">
            <!-- Recipe ID -->
            <input type="hidden" name="recipeId" value="<?php echo $recipeId; ?>">

            <!-- Recipe Name and Description -->
            <div class="mb-3">
                <label for="name" class="form-label">Recipe Name:</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Enter Recipe Name" value="<?php echo $recipeData['recipe_name']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description:</label>
                <textarea class="form-control" id="description" name="description" rows="4" placeholder="Enter Description..." required><?php echo $recipeData['recipe_description']; ?></textarea>
            </div>

            <!-- Category -->
            <div class="mb-3">
                <label for="category" class="form-label">Category:</label>
                <select class="form-select" id="category" name="category" placeholder="" required>
                    <?php
                    $query = "SELECT id, categories FROM categoryTable";
                    $result = $conn->query($query);

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $selected = ($row['id'] == $recipeData['category_id']) ? 'selected' : '';
                            echo "<option value='{$row['id']}' {$selected}>{$row['categories']}</option>";
                        }
                    } else {
                        echo "<option value=''>No categories found</option>";
                    }
                    ?>
                </select>
                <!-- <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    Add New Category
                </button> -->
            </div>

            <!-- Recipe Ingredients and Method -->
            <div class="mb-3">
                <label for="ingredients" class="form-label">Recipe Ingredients:</label>
                <div id="ingredients-container">
                    <?php
                    if (isset($recipeData['ingredients'])) {
                        $ingredients = json_decode($recipeData['ingredients'], true);
                        if (!empty($ingredients)) {
                            foreach ($ingredients as $ingredient) {
                                echo '<div class="input-group mb-2">';
                                echo '<input type="text" class="form-control" name="ingredients[]" value="' . trim($ingredient) . '" required>';
                                echo '<button type="button" class="btn btn-danger" onclick="removeIngredient(this)">Delete</button>';
                                echo '</div>';
                            }
                        }
                    } else {
                        echo 'Ingredients field not set';
                    }
                    ?>
                </div>

                <button type="button" class="btn btn-success" onclick="addIngredient()">Add Ingredient</button>
            </div>
            <div class="mb-3">
                <label for="method" class="form-label">Method:</label>
                <textarea class="form-control method" id="method" name="Recipemethod" rows="6" placeholder="write method to cook..."><?php echo $recipeData['recipe_method']; ?></textarea>
            </div>

            <!-- Images -->
            <div class="mb-3">
                <label for="images" class="form-label">Images:</label>
                <input type="file" class="form-control" id="images" name="images[]" accept=".jpg,.png,.svg" multiple>
                <td class="d-flex">
                    <?php
                    if (isset($recipeData['images'])) {
                        $imageArray = json_decode($recipeData['images']);
                        if (!empty($imageArray)) {
                            foreach ($imageArray as $imageUrl) {
                                echo '<img class="m-2" src="' . FETCH_SRC . trim($imageUrl) . '" alt="Recipe Image" class="img-thumbnail" style="max-width: 100px; max-height: 75px;">';
                            }
                        } else {
                            echo 'No images available';
                        }
                    } else {
                        echo 'Images field not set';
                    }
                    ?>
                </td>
                <!-- <form method="post" action="../Model/deleteImages.php?id=<?php echo $recipeId; ?>" >
                    <button type="submit" name="deleteImages" class="btn btn-danger mt-3" onclick="return confirm('Are you sure you want to delete all images?')">Delete All Images</button>
                </form> -->
                <a href="../Model/deleteImages.php?id=<?php echo $recipeId; ?>" class="btn btn-danger mt-3" onclick="return confirm('Are you sure you want to delete all images?')">Delete All Images</a>

                <div id="uploaded-images" class="m-3"></div>
            </div>

            <!-- Status -->
            <div class="mb-3">
                <label for="status" class="form-label">Status:</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="Published" <?php echo ($recipeData['recipe_status'] == 'Published') ? 'selected' : ''; ?>>Published</option>
                    <option value="Un-Published" <?php echo ($recipeData['recipe_status'] == 'Un-Published') ? 'selected' : ''; ?>>Un-Published</option>
                </select>
            </div>
            <div class="btn btn-primary my-5 me-3"><a href="../view/MyRecipes.php" class="text-decoration-none text-white">Back</a></div>
            <button type="submit" class="btn btn-success my-5" name="updateRecipe">Update Recipe</button>
        </form>
    </div>

    <!-- Bootstrap Modal to add new category -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Form to add a new category -->
                    <form id="newCategoryForm" action="../Model/addCategory.php" name="addCategory" method="post">
                        <div class="mb-3">
                            <label for="new_category" class="form-label">New Category:</label>
                            <input type="text" class="form-control" id="new_category" name="new_category">
                        </div>
                        <button type="button" class="btn btn-primary" id="addNewCategoryBtn" name="addCategory">Add Category</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Modal for Delete Confirmation -->
    <!-- <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete all images for this recipe?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteAllButton">Delete All</button>
                </div>
            </div>
        </div>
    </div> -->

    <!-- deleteimage function -->
    <!-- <script>
        function deleteAllImages(recipeId) {
            document.getElementById('deleteModal').dataset.recipeId = recipeId;
            var myModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            myModal.show();
        }

        document.getElementById('confirmDeleteAllButton').addEventListener('click', function() {
            var recipeId = document.getElementById('deleteModal').dataset.$recipeId;
            window.location.href = '../Model/deleteImages.php?id=' + $recipeId;
        });
    </script> -->









    <!-- Bootstrap 5 JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>




    <!-- JavaScript for Ingredients -->
    <script>
        function addIngredient() {
            const container = document.getElementById('ingredients-container');
            const newInput = document.createElement('div');
            newInput.className = 'input-group mb-2';
            newInput.innerHTML = `
                <input type="text" class="form-control" name="ingredients[]" required>
                <button type="button" class="btn btn-danger" onclick="removeIngredient(this)">Delete</button>
            `;
            container.appendChild(newInput);
        }

        function removeIngredient(button) {
            button.parentElement.remove();
        }
    </script>

    <!-- JavaScript for Image Preview -->
    <script>
        document.getElementById('images').addEventListener('change', function(event) {
            const container = document.getElementById('uploaded-images');
            container.innerHTML = '';
            for (const file of event.target.files) {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.style.maxWidth = '100px';
                img.style.maxHeight = '100px';
                container.appendChild(img);
            }
        });
    </script>

    <!-- for Category form -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addNewCategoryBtn = document.getElementById('addNewCategoryBtn');
            const newCategoryInput = document.getElementById('new_category');
            const categorySelect = document.getElementById('category');

            addNewCategoryBtn.addEventListener('click', function() {
                const newCategory = newCategoryInput.value.trim();

                if (newCategory !== '') {
                    const newOption = document.createElement('option');
                    newOption.value = newCategory;
                    newOption.text = newCategory;
                    categorySelect.add(newOption);
                    categorySelect.value = newCategory;

                    $('#addCategoryModal').modal('hide');

                    newCategoryInput.value = '';
                } else {
                    console.log('Please enter a category.');
                }
            });
        });
    </script>

    <!-- for image Priview -->
    <script>
        document.getElementById('images').addEventListener('change', function(event) {
            const container = document.getElementById('uploaded-images');
            container.innerHTML = '';
            for (const file of event.target.files) {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.style.maxWidth = '100px';
                img.style.maxHeight = '100px';
                container.appendChild(img);
            }
        });
    </script>

    <!-- for CKEditor -->
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
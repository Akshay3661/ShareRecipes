<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ./Login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Recipe Form</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/classic/ckeditor.js"></script>

</head>

<body>

    <div class="container mt-5">
        <h2>Add Recipe</h2>
        <form action="../Model/addRecipe.php" id="addRecipe" method="post" enctype="multipart/form-data">
            <!-- Recipe Name and Description -->
            <div class="mb-3">
                <label for="name" class="form-label">Recipe Name:</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Enter Recipe Name" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description:</label>
                <textarea class="form-control" id="description" name="description" rows="4" placeholder="Enter Description..." required></textarea>
            </div>

            <!-- Category -->
            <div class="mb-3">
                <label for="category" class="form-label">Category:</label>
                <select class="form-select" id="category" name="category" placeholder="" required>
                    <option value='placeholder' name='placeholder' disabled selected>select Category</option>
                    <?php
                    // Fetch categories from the database
                    require_once '../config/Database.php';

                    $db = new Database();
                    $conn = $db->getConnection();

                    $query = "SELECT id, categories FROM categoryTable";
                    $result = $conn->query($query);

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['id']}' name='{$row['id']}'>{$row['categories']}</option>";
                        }
                    } else {
                        echo "<option value=''>No categories found</option>";
                    }

                    $db->closeConnection();
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
                    <div class="input-group mb-2">
                        <input type="text" class="form-control" name="ingredients[]" required>
                        <!-- <button type="button" class="btn btn-danger" onclick="removeIngredient(this)">Delete</button> -->
                    </div>
                    <div class="input-group mb-2">
                        <input type="text" class="form-control" name="ingredients[]" required>
                        <!-- <button type="button" class="btn btn-danger" onclick="removeIngredient(this)">Delete</button> -->
                    </div>

                </div>
                <button type="button" class="btn btn-success" onclick="addIngredient()">Add Ingredient</button>
            </div>
            <div class="mb-3">
                <label for="method" class="form-label">Method:</label>
                <textarea class="form-control method" id="method" name="Recipemethod" rows="6" placeholder="write method to cook...">
                </textarea>
            </div>

            <!-- Images -->
            <div class="mb-3">
                <label for="images" class="form-label">Images:</label>
                <input type="file" class="form-control" id="images" name="images[]" accept=".jpg,.png,.svg" multiple>
                <div id="uploaded-images" class="m-3"></div>
            </div>

            <!-- Status -->
            <div class="mb-3">
                <label for="status" class="form-label">Status:</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="Published">Published</option>
                    <option value="Un-Published">Un-Published</option>
                </select>
            </div>

            <!-- Submit Button -->
            <div class="d-inline">
                <div class="btn btn-primary my-3"><a href="../view/MyRecipes.php" class="text-decoration-none text-white">Back</a></div>
                <button type="submit" class="btn btn-success my-3" name="addRecipe">Add Recipe</button>
            </div>
        </form>
    </div>


    <!-- Bootstrap Modal to add  new category -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Form to add a new category -->
                    <form id="newCategoryForm" action="../Model/addCategory.php" method="post" name="addCategory">
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




    <!-- Bootstrap 5 JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>


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

    <!-- for Caregory form -->
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

    <!-- for ckeditor -->
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
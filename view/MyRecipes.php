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
    <link rel="shortcut icon" href="https://recipejunction.boxtasks.com/assets/media/logos//favicon7250.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">

    <!-- jquary -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <!-- datatables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link href="https://cdn.datatables.net/v/dt/dt-1.13.8/datatables.min.css" rel="stylesheet">

    <script>
        $(document).ready(function() {
            $('#myTable').DataTable();
        });
    </script>
    <title><?php echo htmlspecialchars($firstName) ?>'s Recipes</title>
</head>

<body>
    <header>
        <section class="wrapper nav-flex">
            <nav>
                <a href="./index.php"><img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ_l8CHB-dnWGliErDYPan3Xn89RCKnQuUroklA_WndhbiuiWhsl-tAomw0kI3revYg4-o&usqp=CAU" class="logo" style="width: 3rem;"></a>
                <?php if (isset($firstName)) : ?>
                    <h5 class="d-inline ps-1"> welcome, <?php echo $firstName . " " . $lastName; ?></h5>
                <?php endif; ?>
            </nav>
            <nav class="navigation">
                <ul>
                    <!-- <li><a href="#">RECIPES</a></li> -->
                </ul>
            </nav>
            <nav class="navigation">
                <div>
                    <!-- <li><a href="#">CATEGORIES</a></li> -->
                    <a href="../index.php" class="btn btn-primary"><i class="bi bi-plus-lg pe-2"></i>Home</a>
                    <a href="./AddRecipes.php" class="btn btn-dark d-inline"><i class="bi bi-plus-lg pe-2"></i>Add Recipes</a>
                </div>
            </nav>
        </section>
    </header>

    <div class="container mt-5">
        <div class="row">
            <div class="col">
                <div class="table-responsive">
                    <table id="myTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <!-- <th scope="col">Category</th> -->
                                <th scope="col">Images</th>
                                <th scope="col">Name</th>
                                <th scope="col">Description</th>
                                <th scope="col">Staus</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch recipes with multiple images
                            $query = "SELECT recipeTable.*, imagesTable.images FROM recipeTable LEFT JOIN imagesTable ON recipeTable.id = imagesTable.recipe_Id WHERE recipeTable.`user_Id` = ?";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("i", $_SESSION['user_id']);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            

                            // Display recipes in a table
                            while ($row = $result->fetch_assoc()) :
                            ?>


                                <tr>
                                    <!-- <td><?php echo $row['category_Id']; ?></td> -->
                                    <td>
                                        <?php
                                        $images = json_decode($row['images']);
                                        if (!empty($images)) {
                                            $firstImage = $images[0];
                                            echo '<img src="' . FETCH_SRC . $firstImage . '" alt="Recipe Image" class="img-thumbnail" style="max-width: 75px; max-height: 75px;">';
                                        } else {
                                            echo 'No image available';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo $row['name']; ?></td>
                                    <td><?php echo $row['description']; ?></td>
                                    <td><?php echo $row['status']; ?></td>
                                    <td>
                                        <a href="editRecipe.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $row['id']; ?>)">Delete</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap modal for confirmation -->
    <div class="modal" tabindex="-1" role="dialog" id="confirmDeleteModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this recipe?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.js"></script>

    <script>
        function confirmDelete(recipeId) {
            $('#confirmDeleteBtn').data('recipeId', recipeId);

            // Show the confirmation modal
            $('#confirmDeleteModal').modal('show');
        }

        // Handle click event of the "Delete" button in the modal
        $('#confirmDeleteBtn').click(function () {
            // Get the recipeId from the data attribute
            var recipeId = $(this).data('recipeId');

            // Redirect to deleteRecipe.php with the recipeId
            window.location.href = '../Model/deleteRecipe.php?id=' + recipeId;
        });
    </script>
</body>

</html>
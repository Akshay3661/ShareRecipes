<?php
session_start();
include("./config/Database.php");

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$firstName = $isLoggedIn ? $_SESSION['firstName'] : '';
$lastName = $isLoggedIn ? $_SESSION['lastName'] : '';

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

try {
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['search'])) {
        $searchTerm = $_GET['search'];

        $query = "
        SELECT
            recipe.id AS recipeId,
            recipe.name AS recipeName,
            recipe.description AS recipeDescription,
            recipe.category_Id AS categoryId,
            CONCAT(users.firstName, ' ', users.lastName) AS creatorFullName,
            category.categories AS foodCategory,
            images.images AS recipeImages
        FROM
            recipeTable AS recipe
        INNER JOIN
            usersTable AS users ON recipe.user_Id = users.id
        INNER JOIN
            categoryTable AS category ON recipe.category_Id = category.id
        LEFT JOIN
            imagesTable AS images ON recipe.id = images.recipe_Id
        WHERE
            recipe.status = 'Published' AND
            (recipe.name LIKE ? OR recipe.description LIKE ? OR category.categories LIKE ?)
        ";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            die("Query preparation failed: " . $conn->error);
        }

        $searchTerm = "%$searchTerm%";
        $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    } else {
        $query = "
        SELECT
            recipe.id AS recipeId,
            recipe.name AS recipeName,
            recipe.description AS recipeDescription,
            recipe.category_Id AS categoryId,
            CONCAT(users.firstName, ' ', users.lastName) AS creatorFullName,
            category.categories AS foodCategory,
            images.images AS recipeImages
        FROM
            recipeTable AS recipe
        INNER JOIN
            usersTable AS users ON recipe.user_Id = users.id
        INNER JOIN
            categoryTable AS category ON recipe.category_Id = category.id
        LEFT JOIN
            imagesTable AS images ON recipe.id = images.recipe_Id
        WHERE
            recipe.status = 'Published'
        ";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            die("Query preparation failed: " . $conn->error);
        }
    }

    $stmt->execute();
    if (!$stmt) {
        die("Query execution failed: " . $stmt->error);
    }

    $result = $stmt->get_result();

    if (!$result) {
        echo "Error in executing query: " . $stmt->error;
        exit;
    }

    $recipes = [];
    while ($row = $result->fetch_assoc()) {
        $row['recipeName'] = htmlspecialchars($row['recipeName']);
        $row['recipeDescription'] = htmlspecialchars($row['recipeDescription']);
        $row['foodCategory'] = htmlspecialchars($row['foodCategory']);
        $row['creatorFullName'] = htmlspecialchars($row['creatorFullName']);
        $recipes[] = $row;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="https://recipejunction.boxtasks.com/assets/media/logos//favicon7250.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/style.css">

    <title>RecipePoint</title>
</head>

<body>
    <header>
        <section class="wrapper nav-flex">
            <nav>
                <a href="./index.php"><img src="https://recipejunction.boxtasks.com/assets/media/logos//favicon7250.jpg" class="logo" style="width: 3rem; height:3rem; border-radius:50%"></a>
                <?php if ($isLoggedIn) : ?>
                    <h5 class="d-inline ps-1"> welcome, <?= $firstName . " " . $lastName ?></h5>
                <?php else : ?>
                    <h5 class="d-inline ps-1">RecipePoint</h5>
                <?php endif; ?>
            </nav>
            <nav class="navigation">
                <ul>
                    <!-- <li><a href="#">RECIPES</a></li>
                    <li><a href="#">CATEGORIES</a></li> -->
                </ul>
            </nav>
            <nav class="login-area">
                <div>
                    <?php if ($isLoggedIn) : ?>
                        <a href="./view/MyRecipes.php" class="btn btn-primary d-inline "><i class="bi bi-plus-lg pe-2"></i>MyRecipes</a>
                        <form method="post" class="d-inline">
                            <button type="submit" name="logout" class="btn btn-danger">Logout</button>
                        </form>
                    <?php else : ?>
                        <a href="./view/Login.php" class="btn btn-primary d-inline login">Login/SignUp</a>
                    <?php endif; ?>
                </div>
            </nav>
        </section>
    </header>

    <main>
        <section class="jumbo">
            <h1>what's cooking today?</h1>
            <div class="search">
                <form method="get" action="" class="d-flex w-100">
                    <input type="search" name="search" placeholder="find a recipe">
                    <button type="submit">FIND</button>
                </form>
            </div>
        </section>

        <section class="wrapper product">
            <h2 class="section-name">our delicious collections</h2>
            <!-- Recipes from database -->
            <?php foreach ($recipes as $recipe) : ?>
                <article class="card featured">
                    <?php if (isset($recipe['recipeImages']) && !empty($recipe['recipeImages'])) : ?>
                        <div id="carousel<?= $recipe['recipeId'] ?>" class="carousel slide" data-bs-ride="carousel" data-bs-interval="2000">
                            <div class="carousel-inner">
                                <?php foreach (json_decode($recipe['recipeImages'], true) as $index => $image) : ?>
                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                        <img src="<?= FETCH_SRC . htmlspecialchars($image) ?>" class="d-block w-100" alt="Recipe Image" style="height: 180px;">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <!-- Carousel controls -->
                            <button class="carousel-control-prev" type="button" data-bs-target="#carousel<?= $recipe['recipeId'] ?>" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carousel<?= $recipe['recipeId'] ?>" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    <header class="card-content">
                        <span class="card-category Nonveg"><?= $recipe['foodCategory'] ?></span>
                        <span class="card-header"><?= $recipe['recipeName'] ?></span>
                        <span class="card-desc"><?= $recipe['recipeDescription'] ?></span>
                    </header>
                    <footer class="card-content">
                        <div class="contributor">
                            <a href="./view/RecipePage.php?recipeId=<?= $recipe['recipeId'] ?>"><span class="contributor-name">by <?= $recipe['creatorFullName'] ?></span></a>

                        </div>

                    </footer>
                </article>
            <?php endforeach; ?>

        </section>
    </main>

    <footer>
        <section class="wrapper">
            <nav>
            </nav>
        </section>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
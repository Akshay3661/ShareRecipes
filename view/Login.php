<?php
session_start();
?>
<!doctype html>
<html lang="en">

<head>
    <title>Login-RecipePoint</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="../css/loginform.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-wEmeIV1mKuiNpC+IOBjI7aAzPcEZeedi5yW5f2yOq55WWLwNGmvvx4Um1vskeMj0" crossorigin="anonymous">


</head>

<body class="text-center">
    <div class="form-signin bg-light box">
        <form action="../Model/LoginHandler.php" method="post">
            <p class="h3 mb-5 fw-normal text-warning">Please Login</p>
            <div class="form-floating inputBox mt-3">
                <input type="email" class="form-control" name="email" id="email " placeholder="Email" required>
                <label for="email">Email</label>
            </div>

            <div class="form-floating inputBox">
                <input type="password" class="form-control" name="password" id="floatingPassword" placeholder="Password" required>
                <label for="floatingPassword">Password</label>
            </div>

            <?php
            if (isset($_SESSION['error_message'])) {
                echo '<p style="color: red;">' . $_SESSION['error_message'] . '</p>';
                unset($_SESSION['error_message']); // Clear error message to avoid displaying it again
            }
            ?>

            <button class="w-100 btn btn-lg btn-warning" type="submit">Login</button>
            <p class="mt-5 mb-3 text-muted">don't have account then <a href="../view/signup.php" class="text-decoration-none">Sign-Up</a></p>
            <p class="mt-5 mb-3 text-muted">&copy; 2017–2023</p>
        </form>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-p34f1UUtsS3wqzfto5wAAmdvj+osOnFyQFpp4Ua3gs/ZVWx6oOypYoCJhGGScy+8" crossorigin="anonymous">
    </script>
</body>

</html>
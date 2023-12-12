<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-wEmeIV1mKuiNpC+IOBjI7aAzPcEZeedi5yW5f2yOq55WWLwNGmvvx4Um1vskeMj0" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/loginform.css">
    <title>Signup-RecipePoint</title>
</head>

<body class="text-center">
    <div class="form-signin bg-light box">
        <form action="../Model/SignupHandler.php" method="post">
            <p class="h3 mb-5 fw-normal text-warning">Please SignUp</p>
            
            <div class="form-floating inputBox">
                <input type="text" class="form-control" name="firstname" id="firstname" placeholder="firstname" required oninput="validateFields()">
                <label for="name">First Name</label>
            </div>
            <div class="form-floating inputBox">
                <input type="text" class="form-control" name="lastname" id="lastname" placeholder="lastname" required oninput="validateFields()">
                <label for="name">Last Name</label>
            </div>
            <div class="form-floating inputBox">
                <input type="email" class="form-control" name="email" id="Email" placeholder="name@example.com" required oninput="validateFields()">
                <label for="Email">Email address</label>
            </div>
            <div class="form-floating inputBox">
                <input type="password" class="form-control" name="pwd" id="Password" placeholder="Password" required oninput="validateFields()">
                <label for="Password">Password</label>
            </div>
            <div class="form-floating inputBox">
                <input type="password" class="form-control" name="CFMpwd" id="cfmPassword" placeholder="Re-Enter Password" required oninput="validateFields()">
                <label for="cfmPassword">Confirm Password</label>
            </div>
            <div id="errorMessage" class="text-danger mb-3" style="font-size: 0.8rem;"></div>
            <button class="w-100 btn btn-lg btn-warning" id="Submit" type="submit" disabled>Submit</button>
            <p class="mt-5 mb-3 text-muted">&copy; 2017–2023</p>
        </form>
    </div>
    <script>
        function validateFields() {
            const firstName = document.getElementById('firstname').value;
            const lastName = document.getElementById('lastname').value;
            const email = document.getElementById('Email').value;
            const password = document.getElementById('Password').value;
            const confirmPassword = document.getElementById('cfmPassword').value;
            const submitButton = document.getElementById('Submit');
            const errorMessage = document.getElementById('errorMessage');

            if (firstName.trim() !== '' && lastName.trim() !== '' && email.trim() !== '' && password.trim() !== '' && confirmPassword.trim() !== '' && password === confirmPassword) {
                submitButton.disabled = false;
                errorMessage.textContent = '';
            } else {
                submitButton.disabled = true;
                errorMessage.textContent = 'Please fill in all fields and ensure passwords match.';
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-p34f1UUtsS3wqzfto5wAAmdvj+osOnFyQFpp4Ua3gs/ZVWx6oOypYoCJhGGScy+8" crossorigin="anonymous"></script>
</body>

</html>



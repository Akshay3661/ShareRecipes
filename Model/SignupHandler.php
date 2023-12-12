<?php
session_start();
include "../config/Database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate form data
    $firstName = htmlspecialchars($_POST['firstname'], ENT_QUOTES, 'UTF-8');
    $lastName = htmlspecialchars($_POST['lastname'], ENT_QUOTES, 'UTF-8');
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['pwd'];
    $confirmPassword = $_POST['CFMpwd'];

    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword)) {
        echo "All fields are required.";
        exit();
    }

    if (strlen($password) < 8) {
        echo "Password must be at least 8 characters long.";
        exit();
    }

    if ($password !== $confirmPassword) {
        echo "Passwords do not match!";
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("INSERT INTO usersTable (FirstName, LastName, Email, Password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $firstName, $lastName, $email, $hashedPassword);

    if ($stmt->execute()) {
        $_SESSION['user_id'] = $stmt->insert_id;
        echo "Registration successful!";
        header("Location: ../view/Login.php"); 
        exit();
    } else {
        error_log("Registration Error: " . $conn->error);
        echo "Registration failed. Please try again.";
    }

    $stmt->close();
    $conn->close();
}
?>

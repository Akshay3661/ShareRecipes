<?php
session_start();
include('../config/Database.php');

$db = new Database();
$conn = $db->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email'], $_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $sql = "SELECT id, firstName, lastName, email, password FROM usersTable WHERE email = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $hashedPassword = $row["password"];

            if (password_verify($password, $hashedPassword)) {
                // Regenerate session ID on login for security
                session_regenerate_id(true);
                
                // Set session variables
                $_SESSION['user_id'] = $row["id"];
                $_SESSION['firstName'] = $row["firstName"];
                $_SESSION['lastName'] = $row["lastName"];
                
                header("Location: ../index.php");
                exit();
            } else {
                $_SESSION['error_message'] = "Invalid email or password";
                header("Location: ../View/Login.php"); 
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Invalid email or password";    
            header("Location: ../View/Login.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Invalid input. Please try again.";
        header("Location: ../View/Login.php");
        exit();
    }
}
?>

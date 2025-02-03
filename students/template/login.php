<?php
session_start();
$servername = "localhost";
$username = "root"; // Change this to your MySQL username
$password = "pass"; // Change this to your MySQL password
$dbname = "srm_student_portal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $login = $_POST['login'];       // NetID (without '@srmist.edu.in')
    $passwd = $_POST['passwd'];     // Password entered by the user
    $userType = $_POST['userType']; // User type (student, fa, hod, warden)

    // Prepare the SQL query
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ? AND user_type = ?");
    $stmt->bind_param("ss", $login, $userType);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // If the user exists, fetch their details
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        // Check if the password matches the hashed password in the database
        if (password_verify($passwd, $hashed_password)) {
            // Successful login
            $_SESSION['user_id'] = $id;
            $_SESSION['user_type'] = $userType;
            header("Location: HRDSystem.html"); // Redirect to HRDSystem.html after successful login
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No user found with the provided credentials.";
    }
    $stmt->close();
}
$conn->close();
?>


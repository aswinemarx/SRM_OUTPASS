<?php
session_start();
include 'db.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $login = $_POST['login'] . '@srmist.edu.in'; // Append '@srmist.edu.in' to the NetID
    $passwd = $_POST['passwd'];     // Password entered by the user
    $userType = $_POST['userType']; // User type (student, fa, hod, hc)

    // Prepare the SQL query to check for username and userType
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
            $_SESSION['user_type'] = $userType;

            // Now fetch the user ID from the respective table (student, fa, hod, hc)
            if ($userType == 'student') {
                // Fetch the student ID based on the email (login)
                $stmt = $conn->prepare("SELECT id FROM student WHERE email = ?");
                $stmt->bind_param("s", $login); // Use the login value here
                $stmt->execute();
                $stmt->store_result();
                
                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($student_id);
                    $stmt->fetch();
                    $_SESSION['user_id'] = $student_id;
                    header("Location: HRDSystem.html"); // Redirect to HRDSystem.html for students
                    exit();
                } else {
                    $_SESSION['error'] = "No student found with the provided login.";
                }
            } else if ($userType == 'fa') {
                // Fetch the faculty ID based on the email (login)
                $stmt = $conn->prepare("SELECT id FROM fa WHERE email = ?");
                $stmt->bind_param("s", $login); // Use the login value here
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($faculty_id);
                    $stmt->fetch();
                    $_SESSION['user_id'] = $faculty_id;
                    header("Location: HRDSystemFA.html"); // Redirect to HRDSystemFA.html for faculty
                    exit();
                } else {
                    $_SESSION['error'] = "No faculty found with the provided login.";
                }
            } else if ($userType == 'hod') {
                // Fetch the HOD ID based on the email (login)
                $stmt = $conn->prepare("SELECT id FROM hod WHERE email = ?");
                $stmt->bind_param("s", $login); // Use the login value here
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($hod_id);
                    $stmt->fetch();
                    $_SESSION['user_id'] = $hod_id;
                    header("Location: HRDSystemHOD.html"); // Redirect to HRDSystemHOD.html for HOD
                    exit();
                } else {
                    $_SESSION['error'] = "No HOD found with the provided login.";
                }
            } else if ($userType == 'hc') {
                // Fetch the HC ID based on the email (login)
                $stmt = $conn->prepare("SELECT id FROM hc WHERE email = ?");
                $stmt->bind_param("s", $login); // Use the login value here
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($hc_id);
                    $stmt->fetch();
                    $_SESSION['user_id'] = $hc_id;
                    header("Location: HRDSystemHC.html"); // Redirect to HRDSystemHC.html for HC
                    exit();
                } else {
                    $_SESSION['error'] = "No HC found with the provided login.";
                }
            }
        } else {
            $_SESSION['error'] = "Invalid net id or password.";
        }
    } else {
        $_SESSION['error'] = "Invalid net id or password.";
    }
    $stmt->close();
    header("Location: login.html");
    exit();
}
$conn->close();
?>

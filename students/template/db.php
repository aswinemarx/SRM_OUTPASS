<?php
// db.php - Database connection file

$host = 'localhost';  // Your MySQL server (localhost if running locally)
$username = 'root';    // Your MySQL username
$password = 'samsung8.9';        // Your MySQL password
$dbname = 'srm_student_portal'; // Database name

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
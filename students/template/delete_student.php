<?php
include 'db.php';
$conn = new mysqli($host, $username, $password, $dbname);
header('Content-Type: application/json');

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'DB error']);
    exit;
}

if (isset($_POST['delete_all']) && $_POST['delete_all'] == 1 && isset($_POST['year'])) {
    $year = $_POST['year'];
    // Get all student IDs and emails for the year
    $stmt = $conn->prepare("SELECT id, email FROM student WHERE year = ?");
    $stmt->bind_param("s", $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $student_ids = [];
    $emails = [];
    while ($row = $result->fetch_assoc()) {
        $student_ids[] = $row['id'];
        $emails[] = $row['email'];
    }
    $stmt->close();

    if (!empty($student_ids)) {
        // Delete related outpass records
        $ids_str = implode(',', array_fill(0, count($student_ids), '?'));
        $types = str_repeat('i', count($student_ids));
        $stmt = $conn->prepare("DELETE FROM outpass WHERE s_id IN ($ids_str)");
        $stmt->bind_param($types, ...$student_ids);
        $stmt->execute();
        $stmt->close();

        // Delete students
        $stmt = $conn->prepare("DELETE FROM student WHERE id IN ($ids_str)");
        $stmt->bind_param($types, ...$student_ids);
        $success = $stmt->execute();
        $stmt->close();

        // Delete users
        if (!empty($emails)) {
            $emails_str = implode(',', array_fill(0, count($emails), '?'));
            $email_types = str_repeat('s', count($emails));
            $sql = "DELETE FROM users WHERE username IN ($emails_str) AND user_type = 'student'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($email_types, ...$emails);
            $stmt->execute();
            $stmt->close();
        }
        echo json_encode(['success' => $success]);
        $conn->close();
        exit;
    } else {
        echo json_encode(['success' => true]);
        $conn->close();
        exit;
    }
}

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    // Get the student's email
    $stmt = $conn->prepare("SELECT email FROM student WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($email);
    $stmt->fetch();
    $stmt->close();

    // Delete related outpass records first
    $stmt = $conn->prepare("DELETE FROM outpass WHERE s_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Now delete the student
    $stmt = $conn->prepare("DELETE FROM student WHERE id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();

    // Delete from users table
    if ($email) {
        $stmt = $conn->prepare("DELETE FROM users WHERE username = ? AND user_type = 'student'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->close();
    }

    echo json_encode(['success' => $success]);
    $conn->close();
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid request']);
$conn->close();
?>
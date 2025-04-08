<?php
include 'db.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

$userType = $_POST['userType'];
$name = $_POST['name'];
$email = $_POST['email'];
$role = $_POST['role'];
$department = $_POST['department'];

if ($userType === 'student') {
    $sql = "INSERT INTO student (name, email, department) VALUES (?, ?, ?)";
} else if ($userType === 'faculty') {
    $sql = "INSERT INTO faculty (name, email, position, department) VALUES (?, ?, ?, ?)";
} else {
    die(json_encode(['error' => 'Invalid user type']));
}

$stmt = $conn->prepare($sql);

if ($userType === 'student') {
    $stmt->bind_param("sss", $name, $email, $department);
} else {
    $stmt->bind_param("ssss", $name, $email, $role, $department);
}

if ($stmt->execute()) {
    echo json_encode(['message' => 'User added successfully']);
} else {
    echo json_encode(['error' => 'Failed to add user: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>

<script>
$('#saveNewUserButton').click(function () {
    const userData = {
        userType: $('#addUserType').val(),
        name: $('#addUserName').val(),
        email: $('#addUserEmail').val(),
        role: $('#addUserRole').val(),
        department: $('#addUserDepartment').val()
    };

    $.ajax({
        url: 'add_user.php',
        method: 'POST',
        data: userData,
        success: function (response) {
            const res = JSON.parse(response);
            if (res.error) {
                alert(res.error);
            } else {
                alert(res.message);
                $('#addUserModal').modal('hide');
                $('#applyFilterButton').click(); // Refresh the table
            }
        },
        error: function () {
            alert('Error adding user.');
        }
    });
});
</script>
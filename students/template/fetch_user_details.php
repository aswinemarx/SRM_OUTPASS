<?php
include 'db.php';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode([]));
}

$userType = $_POST['userType'] ?? '';

if ($userType === 'student') {
    $dept = $_POST['dept'] ?? '';
    $year = $_POST['year'] ?? '';
    $section = $_POST['section'] ?? '';
    $name = $_POST['name'] ?? '';

    $sql = "SELECT id, name, email, dept, year, section FROM student WHERE 1=1";
    $params = [];
    $types = '';

    if ($dept !== '') {
        $sql .= " AND dept = ?";
        $params[] = $dept;
        $types .= 's';
    }
    if ($year !== '') {
        $sql .= " AND year = ?";
        $params[] = $year;
        $types .= 's';
    }
    if ($section !== '') {
        $sql .= " AND section = ?";
        $params[] = $section;
        $types .= 's';
    }
    if ($name !== '') {
        $sql .= " AND name LIKE ?";
        $params[] = '%' . $name . '%';
        $types .= 's';
    }

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'name' => $row['name'],
            'email' => $row['email'],
            'department' => $row['dept'],
            'year' => $row['year'],
            'section' => $row['section']
        ];
    }
    echo json_encode($users);

    $stmt->close();

} else if ($userType === 'fa') {
    $name = $_POST['name'] ?? '';

    $sql = "SELECT id, name, email, dept FROM fa WHERE 1=1";
    $params = [];
    $types = '';

    if ($name !== '') {
        $sql .= " AND name LIKE ?";
        $params[] = '%' . $name . '%';
        $types .= 's';
    }

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'name' => $row['name'],
            'email' => $row['email'],
            'department' => $row['dept']
        ];
    }
    echo json_encode($users);

    $stmt->close();

} else if ($userType === 'hod') {
    $sql = "SELECT id, name, email, dept FROM hod";
    $result = $conn->query($sql);

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'name' => $row['name'],
            'email' => $row['email'],
            'department' => $row['dept']
        ];
    }
    echo json_encode($users);

} else if ($userType === 'hc') {
    $sql = "SELECT id, name, email, hostel FROM hc";
    $result = $conn->query($sql);

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'name' => $row['name'],
            'email' => $row['email'],
            'department' => $row['hostel']
        ];
    }
    echo json_encode($users);

} else {
    echo json_encode([]);
}

$conn->close();
?>
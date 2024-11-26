<?php
$host = 'localhost';
$dbname = 'lmf';
$username = 'root';
$password = '';

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

function executeQuery($sql, $params = []) {
    global $conn;
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt;
}

function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function fetchOne($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function insertData($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->insert_id;
}

function updateData($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->affected_rows;
}

function deleteData($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->affected_rows;
}
<?php

$config = json_decode(file_get_contents("db_config.json"), true);

$conn = new mysqli(
    $config['host'],
    $config['username'],
    $config['password'],
    $config['db_name']
);

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

$plant_name = $_GET['plant_name'];

$sql = "SELECT logger_id
        FROM plant_logger_mapping
        WHERE plant_name = '$plant_name'
        ORDER BY logger_id";

$result = $conn->query($sql);

$loggers = [];

while ($row = $result->fetch_assoc()) {
    $loggers[] = $row['logger_id'];
}

header('Content-Type: application/json');
echo json_encode($loggers, JSON_PRETTY_PRINT);

?>
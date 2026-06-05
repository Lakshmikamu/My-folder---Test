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

$sql = "SELECT DISTINCT epc_name
        FROM plant_logger_mapping
        ORDER BY epc_name";

$result = $conn->query($sql);

$epcs = [];

while ($row = $result->fetch_assoc()) {
    $epcs[] = $row['epc_name'];
}

header('Content-Type: application/json');
echo json_encode($epcs, JSON_PRETTY_PRINT);

?>
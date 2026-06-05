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

$epc_name = $_GET['epc_name'];

$sql = "SELECT DISTINCT plant_name
        FROM plant_logger_mapping
        WHERE epc_name = '$epc_name'
        ORDER BY plant_name";

$result = $conn->query($sql);

$plants = [];

while ($row = $result->fetch_assoc()) {
    $plants[] = $row['plant_name'];
}

header('Content-Type: application/json');
echo json_encode($plants, JSON_PRETTY_PRINT);

?>
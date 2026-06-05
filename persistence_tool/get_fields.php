<?php

header('Content-Type: application/json');

$config = json_decode(file_get_contents("db_config.json"), true);

$conn = new mysqli(
    $config['host'],
    $config['username'],
    $config['password'],
    $config['db_name']
);

if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed"]));
}

$epc_name = $_GET['epc_name'] ?? '';
$logger_id = $_GET['logger_id'] ?? '';
$device = $_GET['device'] ?? '';

if (empty($epc_name) || empty($logger_id) || empty($device)) {
    die(json_encode([
        "error" => "epc_name, logger_id and device are required"
    ]));
}

$stmt = $conn->prepare("
    SELECT influx_url, org_name, token
    FROM epc_master
    WHERE epc_name = ?
");

$stmt->bind_param("s", $epc_name);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die(json_encode(["error" => "EPC not found"]));
}

$epc = $result->fetch_assoc();

$url   = $epc['influx_url'];
$org   = $epc['org_name'];
$token = $epc['token'];

$query = 'SHOW TAG VALUES FROM "wattmon_std_mv" 
          WITH KEY IN (f) 
          WHERE "dlid"=\'' . $logger_id . '\' 
          AND "did"=\'' . $device . '\'';

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => $url . "/query?org=" . urlencode($org) . "&db=" . urlencode($org),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Token " . $token,
        "Content-Type: application/x-www-form-urlencoded"
    ],
    CURLOPT_POSTFIELDS => http_build_query([
        "q" => $query
    ])
]);

$response = curl_exec($curl);

if (curl_errno($curl)) {
    die(json_encode([
        "error" => curl_error($curl)
    ]));
}

curl_close($curl);

$data = json_decode($response, true);

$fields = [];

if (isset($data['results'][0]['series'][0]['values'])) {
    foreach ($data['results'][0]['series'][0]['values'] as $row) {
        $fields[] = $row[1];
    }
}

echo json_encode($fields, JSON_PRETTY_PRINT);

$conn->close();

?>
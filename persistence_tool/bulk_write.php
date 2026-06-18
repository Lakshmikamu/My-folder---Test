<?php

date_default_timezone_set('Asia/Kolkata');
header('Content-Type: application/json');
$config = json_decode(file_get_contents("db_config.json"), true);

$conn = new mysqli(
    $config['host'],
    $config['username'],
    $config['password'],
    $config['db_name']
);

if ($conn->connect_error) {
    die(json_encode([
        "error" => "Database connection failed"
    ]));
}

$epc_name  = $_POST['epc_name'] ?? '';
$logger_id = $_POST['logger_id'] ?? '';
$device    = $_POST['device'] ?? '';
$field     = $_POST['field'] ?? '';

$start_time = $_POST['start_time'] ?? '';
$end_time   = $_POST['end_time'] ?? '';

$value  = $_POST['value'] ?? '';
$reason = $_POST['reason'] ?? '';
$stmt = $conn->prepare("
    SELECT influx_url, org_name, token
    FROM epc_master
    WHERE epc_name = ?
");

$stmt->bind_param("s", $epc_name);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {

    die(json_encode([
        "error" => "EPC not found"
    ]));
}

$epc = $result->fetch_assoc();

$influx_url = $epc['influx_url'];
$org_name   = $epc['org_name'];
$token      = $epc['token'];
$startUnix = strtotime($start_time);
$endUnix   = strtotime($end_time);
$totalRecords =
    floor(($endUnix - $startUnix) / 60) + 1;

if ($totalRecords > 2000) {

    die(json_encode([
        "error" => "Maximum 2000 records allowed per bulk write"
    ]));
}
$timestamps = [];

for ($t = $startUnix; $t <= $endUnix; $t += 60) {

    $timestamps[] = [
        "datetime" => date('Y-m-d H:i:s', $t),
        "unix" => $t
    ];

}

$lineProtocols = [];

foreach ($timestamps as $row) {

    $lineProtocols[] =
        'wattmon_std_mv,' .
        'dlid=' . $logger_id .
        ',did=' . $device .
        ',f=' . $field .
        ' value=' . $value .
        ' ' . $row['unix'];

}
if (count($lineProtocols) == 0) {

    die(json_encode([
        "error" => "No records generated"
    ]));
}

$payload = implode("\n", $lineProtocols);

echo json_encode([
    "status" => "success",
    "total_records" => count($lineProtocols),
    "payload_lines" => substr_count($payload, "\n") + 1,
    "payload_size" => strlen($payload)
], JSON_PRETTY_PRINT);

?>
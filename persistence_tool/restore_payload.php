<?php

$config = json_decode(
    file_get_contents("db_config.json"),
    true
);

$conn = new mysqli(
    $config['host'],
    $config['username'],
    $config['password'],
    $config['db_name']
);

$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("
    SELECT *
    FROM correction_log
    WHERE id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

$row = $result->fetch_assoc();

$message = "";

if (isset($_POST['restore'])) {

    $payload = $row['write_payload'];

    $stmt2 = $conn->prepare("
        SELECT influx_url, org_name, token
        FROM epc_master
        WHERE epc_name = ?
    ");

    $stmt2->bind_param("s", $row['epc_name']);
    $stmt2->execute();

    $epcResult = $stmt2->get_result();
    $epc = $epcResult->fetch_assoc();

    $writeUrl =
        $epc['influx_url'] .
        "/api/v2/write?org=" .
        urlencode($epc['org_name']) .
        "&bucket=" .
        urlencode($epc['org_name']) .
        "&precision=s";

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $writeUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Token " . $epc['token'],
            "Content-Type: text/plain; charset=utf-8"
        ],
        CURLOPT_POSTFIELDS => $payload
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    if (empty($error) && $httpCode == 204) {

        $message =
            "<div class='alert alert-success mt-3'>
                Payload restored successfully to InfluxDB
            </div>";

    } else {

        $message =
            "<div class='alert alert-danger mt-3'>
                <b>HTTP Code:</b> {$httpCode}<br>
                <b>Response:</b> {$response}<br>
                <b>Error:</b> {$error}
            </div>";
    }
}

?>

<!DOCTYPE html>
<html>

<head>

<title>Restore Confirmation</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
      rel="stylesheet">

</head>

<body>

<div class="container mt-4">

<?php echo $message; ?>

<h2>Restore Confirmation</h2>

<p><b>EPC:</b> <?php echo $row['epc_name']; ?></p>

<p><b>Records:</b> <?php echo $row['total_records']; ?></p>

<p><b>Reason:</b> <?php echo $row['reason']; ?></p>

<form method="post">

<input type="hidden"
       name="id"
       value="<?php echo $row['id']; ?>">

<button type="submit"
        name="restore"
        class="btn btn-success">

Restore To InfluxDB

</button>

</form>

<hr>

</div>

</body>

</html>
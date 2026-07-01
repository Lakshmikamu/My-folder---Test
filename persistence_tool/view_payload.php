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

$id = $_GET['id'];

$stmt = $conn->prepare("
    SELECT *
    FROM correction_log
    WHERE id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

$row = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html>

<head>

<title>Payload Viewer</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
      rel="stylesheet">

</head>

<body>

<div class="container mt-4">

<h3>Payload Viewer</h3>

<hr>

<p><b>ID:</b> <?php echo $row['id']; ?></p>

<p><b>EPC:</b> <?php echo $row['epc_name']; ?></p>

<p><b>Reason:</b> <?php echo $row['reason']; ?></p>

<textarea
    class="form-control"
    rows="20"
    readonly><?php echo $row['write_payload']; ?></textarea>

<br>

<a href="restore_payload.php?id=<?php echo $row['id']; ?>"
   class="btn btn-success">
   Restore To InfluxDB
</a>

<a href="correction_history.php"
   class="btn btn-secondary">
   Back
</a>

</div>

</body>

</html>
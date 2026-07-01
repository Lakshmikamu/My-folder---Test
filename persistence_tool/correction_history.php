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

if ($conn->connect_error) {
    die("Database connection failed");
}

$result = $conn->query("
    SELECT *
    FROM correction_log
    ORDER BY id DESC
");

?>

<!DOCTYPE html>
<html>

<head>

    <title>Correction History</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
          rel="stylesheet">

</head>

<body>

<div class="container mt-4">

    <h2>Correction History</h2>

    <table class="table table-bordered table-striped">

        <thead>

        <tr>
            <th>ID</th>
            <th>Date</th>
            <th>EPC</th>
            <th>Logger</th>
            <th>Device</th>
            <th>Field</th>
            <th>Type</th>
            <th>Records</th>
            <th>Reason</th>
            <th>Action</th>
        </tr>

        </thead>

        <tbody>

        <?php while($row = $result->fetch_assoc()) { ?>

        <tr>

            <td><?php echo $row['id']; ?></td>

            <td><?php echo $row['created_at']; ?></td>

            <td><?php echo $row['epc_name']; ?></td>

            <td><?php echo $row['logger_id']; ?></td>

            <td><?php echo $row['device']; ?></td>

            <td><?php echo $row['field_name']; ?></td>

            <td><?php echo $row['correction_type']; ?></td>

            <td><?php echo $row['total_records']; ?></td>

            <td><?php echo $row['reason']; ?></td>

<td>
    <a href="view_payload.php?id=<?php echo $row['id']; ?>"
       class="btn btn-primary btn-sm">
        View Payload
    </a>
</td>

        </tr>

        <?php } ?>

        </tbody>

    </table>

</div>

</body>

</html>
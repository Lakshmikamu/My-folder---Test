<?php
require_once("db_actions.php");

$database = new Database('db_config.json');
$db = $database->connect();
$table = 'file_router';
$crud = new CRUD($db, $table);
/*$conditions = [
    'alert_type' => 'Datalogger_Comm_Loss',
    'active_status' => 1
];*/
$dataloggersConfig = $crud->read();


// Database connection
/*$conn = mysqli_connect("localhost", "root", "#Renewgrid1", "test_db"); 
if (!$conn) {
    die("❌ Connection failed: " . mysqli_connect_error());
}*/

// --- ADD new record ---
if (isset($_POST['add'])) {
    $logger_id = $_POST['logger_id'];
    $path = $_POST['path'];

    /*$sql = "INSERT INTO file_router (logger_id, path) VALUES ('$logger_id', '$path')";
    if (mysqli_query($conn, $sql)) {
        // Redirect to same page to refresh table
        header("Location: ".$_SERVER['PHP_SELF']."?msg=added");
        exit();
    } else {
        echo "<p style='color:red;'>❌ Error: " . mysqli_error($conn) . "</p>";
    }*/

    $data = ['logger_id' => $logger_id, 'path' => $path];
    $addResult = $crud->create($data);
    if ($addResult) {
        header("Location: logger_manager.php?msg=added");
        exit();
    } else {
        echo "<p style='color:red;'>❌ Error: " ."Add failed for ID:".$id. "</p>"."\n";
    }
}

// --- DELETE record ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    /*$sql = "DELETE FROM file_router WHERE ID=$id";
    if (mysqli_query($conn, $sql)) {
        header("Location: ".$_SERVER['PHP_SELF']."?msg=deleted");
        exit();
    } else {
        echo "<p style='color:red;'>❌ Error: " . mysqli_error($conn) . "</p>";
    }*/

    $deleteConditions = [
        'ID' => $id
    ];
    $deleteResult = $crud->delete($deleteConditions);

    if ($deleteResult) {
        header("Location: logger_manager.php?msg=deleted");        
        exit();
    } else {
        echo "<p style='color:red;'>❌ Error: " ."Delete failed for ID:".$id. "</p>"."\n";
    }

}

// --- UPDATE record ---
if (isset($_POST['update'])) {
    $id = $_POST['ID'];
    $logger_id = $_POST['logger_id'];
    $path = $_POST['path'];

    /*$sql = "UPDATE file_router SET logger_id='$logger_id', path='$path' WHERE ID=$id";
    if (mysqli_query($conn, $sql)) {
        header("Location: ".$_SERVER['PHP_SELF']."?msg=updated");
        exit();
    } else {
        echo "<p style='color:red;'>❌ Error: " . mysqli_error($conn) . "</p>";
    }*/

    $data = [
        'logger_id' => $logger_id,
        'path' => $path
    ];

    $updateConditions = [
        'ID' => $id
    ];

    $updateResult = $crud->update($data, $updateConditions);

    if ($updateResult) {
        header("Location: logger_manager.php?msg=updated");           
        exit();
    } else {
        echo "<p style='color:red;'>❌ Error: " ."Update failed for ID:".$id. "</p>"."\n";
    }


}



// --- Show messages after redirect ---
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == "added") echo "<p style='color:green;'>✅ Record added successfully!</p>";
    if ($_GET['msg'] == "deleted") echo "<p style='color:green;'>🗑️ Record deleted successfully!</p>";
    if ($_GET['msg'] == "updated") echo "<p style='color:blue;'>✏️ Record updated successfully!</p>";
}

?>

<h2>📌 Add Logger Path</h2>
<form method="post">
    Logger ID: <input type="text" name="logger_id" required>
    Path: <input type="text" name="path" required>
    <button type="submit" name="add">➕ Add</button>
</form>

<hr>

<h2>📂 Current File Router Records</h2>
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>Serial No</th>
        <th>Logger ID</th>
        <th>Path</th>
        <th>Action</th>
    </tr>

<?php
// Fetch all records
/*$result = mysqli_query($conn, "SELECT * FROM file_router");
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
                <td>".$row['ID']."</td>
                <td>".$row['logger_id']."</td>
                <td>".$row['path']."</td>
                <td>
                    <a href='?edit=".$row['ID']."'>✏️ Edit</a> | 
                    <a href='?delete=".$row['ID']."' onclick='return confirm(\"Delete this record?\");'>❌ Delete</a>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='4'>No records found</td></tr>";
}*/

    foreach($dataloggersConfig as $key => $row){
                echo "<tr>
                <td>".$row['ID']."</td>
                <td>".$row['logger_id']."</td>
                <td>".$row['path']."</td>
                <td>
                    <a href='?edit=".$row['ID']."'>✏️ Edit</a> | 
                    <a href='?delete=".$row['ID']."' onclick='return confirm(\"Delete this record?\");'>❌ Delete</a>
                </td>
              </tr>";

    }
?>
</table>

<?php
// --- EDIT FORM ---
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    #$result = mysqli_query($conn, "SELECT * FROM file_router WHERE ID=$id");
    #$row = mysqli_fetch_assoc($result);


    ?>

    <hr>
    <h2>✏️ Edit Record</h2>
    <form method="post">
        <input type="hidden" name="ID" value="<?php echo $row['ID']; ?>">
        Logger ID: <input type="text" name="logger_id" value="<?php echo $row['logger_id']; ?>" required>
        Path: <input type="text" name="path" value="<?php echo $row['path']; ?>" required>
        <button type="submit" name="update">💾 Update</button>
    </form>

<?php } ?>

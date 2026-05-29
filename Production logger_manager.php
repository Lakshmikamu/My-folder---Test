<?php
require_once("db_actions.php");

$database = new Database('db_config.json');
$db = $database->connect();
$table = 'file_router';
$crud = new CRUD($db, $table);

$dataloggersConfig = $crud->read();

// --- ADD ---
if (isset($_POST['add'])) {
    $data = ['logger_id' => $_POST['logger_id'], 'path' => $_POST['path']];
    if ($crud->create($data)) {
        header("Location: logger_manager.php?msg=added");
        exit();
    }
}

// --- DELETE ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $deleteConditions = ['ID' => $id];
    if ($crud->delete($deleteConditions)) {
        header("Location: logger_manager.php?msg=deleted");
        exit();
    }
}

// --- UPDATE ---
if (isset($_POST['update'])) {
    $data = [
        'logger_id' => $_POST['logger_id'],
        'path' => $_POST['path']
    ];
    $cond = ['ID' => $_POST['ID']];

    if ($crud->update($data, $cond)) {
        header("Location: logger_manager.php?msg=updated");
        exit();
    }
}

// --- Messages ---
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == "added") echo "<p style='color:green;'>✅ Added</p>";
    if ($_GET['msg'] == "deleted") echo "<p style='color:green;'>🗑️ Deleted</p>";
    if ($_GET['msg'] == "updated") echo "<p style='color:blue;'>✏️ Updated</p>";
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
<table border="1" cellpadding="5">
<tr>
<th>ID</th>
<th>Logger</th>
<th>Path</th>
<th>Action</th>
</tr>

<?php
foreach($dataloggersConfig as $row){
echo "<tr>
<td>{$row['ID']}</td>
<td>{$row['logger_id']}</td>
<td>{$row['path']}</td>
<td>
<a href='?edit={$row['ID']}'>✏️ Edit</a> |

<button onclick=\"confirmDelete('{$row['ID']}','{$row['logger_id']}')\"
style='background:#dc3545;color:white;border:none;padding:5px 10px;border-radius:5px;cursor:pointer;'>
❌ Delete
</button>

</td>
</tr>";
}
?>
</table>

<?php
// EDIT FORM
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];

    foreach($dataloggersConfig as $r){
        if($r['ID'] == $id){
            $row = $r;
            break;
        }
    }
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

<!-- 🔥 SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirmDelete(id, actualLogger) {

    Swal.fire({
        title: '⚠️ Confirm Delete',
        html: `
            <p>Type Logger ID to confirm:</p>
            <h3 style="color:red;">${actualLogger}</h3>
            <input id="swal-input" class="swal2-input" placeholder="Enter Logger ID">
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '🗑️ Delete',
        confirmButtonColor: '#d33',

        preConfirm: () => {
            const entered = document.getElementById('swal-input').value.trim();

            if (!entered) {
                Swal.showValidationMessage('⚠️ Enter Logger ID');
                return false;
            }

            if (entered !== actualLogger) {
                Swal.showValidationMessage('❌ Wrong Logger ID!');
                return false;
            }

            return true;
        }

    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "?delete=" + id;
        }
    });
}
</script>
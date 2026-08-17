<?php

require_once "../config/database.php";

$id = $_GET['id'];

$sql = "SELECT * FROM projects WHERE project_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$project = $result->fetch_assoc();

if (isset($_POST['update'])) {

    $project_name = $_POST['project_name'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = $_POST['status'];

    $sql = "UPDATE projects
            SET project_name=?, description=?, start_date=?, end_date=?, status=?
            WHERE project_id=?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssi",
        $project_name,
        $description,
        $start_date,
        $end_date,
        $status,
        $id
    );

    if ($stmt->execute()) {
        header("Location: view.php");
        exit();
    } else {
        echo "Error updating project.";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Project</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include "../config/page_actions.php"; ?>
<div class="container mt-5">

    <h2>Edit Project</h2>

    <form method="POST">

        <div class="mb-3">
            <label>Project Name</label>
            <input type="text" name="project_name" class="form-control"
                   value="<?php echo $project['project_name']; ?>" required>
        </div>

        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control"><?php echo $project['description']; ?></textarea>
        </div>

        <div class="mb-3">
            <label>Start Date</label>
            <input type="date" name="start_date" class="form-control"
                   value="<?php echo $project['start_date']; ?>">
        </div>

        <div class="mb-3">
            <label>End Date</label>
            <input type="date" name="end_date" class="form-control"
                   value="<?php echo $project['end_date']; ?>">
        </div>

        <div class="mb-3">
            <label>Status</label>

            <select name="status" class="form-control">
                <option value="Planning" <?php if($project['status']=="Planning") echo "selected"; ?>>Planning</option>
                <option value="Active" <?php if($project['status']=="Active") echo "selected"; ?>>Active</option>
                <option value="Completed" <?php if($project['status']=="Completed") echo "selected"; ?>>Completed</option>
                <option value="On Hold" <?php if($project['status']=="On Hold") echo "selected"; ?>>On Hold</option>
            </select>

        </div>

        <button type="submit" name="update" class="btn btn-success">
            Update Project
        </button>

    </form>

</div>

</body>
</html>
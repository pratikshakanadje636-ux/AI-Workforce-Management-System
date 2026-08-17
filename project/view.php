<?php

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| PROJECT SEARCH
|--------------------------------------------------------------------------
*/

$search = $_GET['search'] ?? '';
$search = trim($search);


/*
|--------------------------------------------------------------------------
| PROJECT QUERY
|--------------------------------------------------------------------------
*/

if ($search !== '') {

    $search_safe = $conn->real_escape_string($search);

    $sql = "
        SELECT
            projects.*,
            COUNT(tasks.task_id) AS total_tasks

        FROM projects

        LEFT JOIN tasks
            ON projects.project_id = tasks.project_id

        WHERE
            projects.project_name LIKE '%$search_safe%'
            OR projects.description LIKE '%$search_safe%'
            OR projects.status LIKE '%$search_safe%'

        GROUP BY projects.project_id

        ORDER BY projects.project_id DESC
    ";

} else {

    $sql = "
        SELECT
            projects.*,
            COUNT(tasks.task_id) AS total_tasks

        FROM projects

        LEFT JOIN tasks
            ON projects.project_id = tasks.project_id

        GROUP BY projects.project_id

        ORDER BY projects.project_id DESC
    ";
}


$result = $conn->query($sql);

if (!$result) {
    die("SQL Error: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>View Projects</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<link
rel="stylesheet"
href="../assets/css/style.css">

<link
rel="stylesheet"
href="../assets/css/dark-mode.css">

<style>

/* =========================================================
   PROJECT PAGE - ADMIN DARK MODE
========================================================= */

body.admin-dark-mode {
    background: #0f172a !important;
    color: #e5e7eb !important;
}


/* CONTAINER */

body.admin-dark-mode .container {
    color: #e5e7eb;
}


/* HEADING */

body.admin-dark-mode h2 {
    color: #f8fafc !important;
}


/* SEARCH INPUT */

body.admin-dark-mode .form-control {
    background: #1e293b !important;
    color: #f8fafc !important;
    border-color: #475569 !important;
}

body.admin-dark-mode .form-control::placeholder {
    color: #94a3b8 !important;
}

body.admin-dark-mode .form-control:focus {
    background: #1e293b !important;
    color: #ffffff !important;
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.20) !important;
}


/* TABLE */

body.admin-dark-mode .table {
    background: #1e293b !important;
    color: #e5e7eb !important;
}


/* TABLE HEADER */

body.admin-dark-mode .table-dark th {
    background: #020617 !important;
    color: #f8fafc !important;
    border-color: #334155 !important;
}


/* TABLE BODY */

body.admin-dark-mode .table tbody {
    background: #1e293b !important;
}

body.admin-dark-mode .table tbody tr {
    background: #1e293b !important;
    color: #e5e7eb !important;
}

body.admin-dark-mode .table tbody td {
    background: #1e293b !important;
    color: #e5e7eb !important;
    border-color: #334155 !important;
}


/* TABLE STRIPES */

body.admin-dark-mode .table-striped > tbody > tr:nth-of-type(odd) > * {
    background-color: #1e293b !important;
    color: #e5e7eb !important;
}


/* TABLE HOVER */

body.admin-dark-mode .table tbody tr:hover td {
    background: #334155 !important;
    color: #ffffff !important;
}


/* NO PROJECTS */

body.admin-dark-mode .table tbody tr td.text-center {
    color: #94a3b8 !important;
}


/* BUTTONS */

body.admin-dark-mode .btn-info {
    color: #ffffff !important;
}

body.admin-dark-mode .btn-secondary {
    color: #ffffff !important;
}

</style>

</head>


<body>


<?php include "../config/page_actions.php"; ?>


<div class="container mt-5">


<!-- =====================================================
     PROJECT HEADER
====================================================== -->

<div class="d-flex justify-content-between align-items-center mb-4">

    <h2>Projects</h2>

    <a href="add.php" class="btn btn-primary">
        + Add Project
    </a>

</div>


<!-- =====================================================
     SEARCH BAR
====================================================== -->

<form method="GET" action="view.php" class="mb-4">

    <div class="input-group">

        <input
            type="text"
            name="search"
            class="form-control"
            placeholder="Search projects by name, description or status..."
            value="<?php echo htmlspecialchars($search); ?>"
        >

        <button
            type="submit"
            class="btn btn-primary">

            🔍 Search

        </button>


        <?php if ($search !== '') { ?>

            <a
                href="view.php"
                class="btn btn-secondary">

                Clear

            </a>

        <?php } ?>

    </div>

</form>


<!-- =====================================================
     PROJECT TABLE
====================================================== -->

<table class="table table-bordered table-striped">

<thead class="table-dark">

<tr>

<th>ID</th>
<th>Project Name</th>
<th>Description</th>
<th>Start Date</th>
<th>End Date</th>
<th>Status</th>
<th>Total Tasks</th>
<th>Action</th>

</tr>

</thead>


<tbody>


<?php if ($result->num_rows > 0) { ?>


    <?php while ($row = $result->fetch_assoc()) { ?>


    <tr>


        <td>
            <?php echo $row['project_id']; ?>
        </td>


        <td>
            <?php echo htmlspecialchars($row['project_name']); ?>
        </td>


        <td>
            <?php echo htmlspecialchars($row['description']); ?>
        </td>


        <td>
            <?php echo $row['start_date']; ?>
        </td>


        <td>
            <?php echo $row['end_date']; ?>
        </td>


        <td>
            <?php echo htmlspecialchars($row['status']); ?>
        </td>


        <td>

            <a
                href="../task/view.php?project_id=<?php echo $row['project_id']; ?>"
                class="btn btn-info btn-sm">

                <?php echo $row['total_tasks']; ?> Task(s)

            </a>

        </td>


        <td>

            <a
                href="edit.php?id=<?php echo $row['project_id']; ?>"
                class="btn btn-warning btn-sm">

                Edit

            </a>


            <a
                href="delete.php?id=<?php echo $row['project_id']; ?>"
                class="btn btn-danger btn-sm"
                onclick="return confirm('Are you sure you want to delete this project?');">

                Delete

            </a>

        </td>


    </tr>


    <?php } ?>


<?php } else { ?>


    <tr>

        <td colspan="8" class="text-center py-4">

            No projects found.

        </td>

    </tr>


<?php } ?>


</tbody>

</table>


</div>


</body>

</html>
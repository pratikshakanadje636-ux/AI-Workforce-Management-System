<?php

require_once "config/database.php";

$search = $_GET['search'] ?? '';
$search = trim($search);

$employees   = [];
$projects    = [];
$tasks       = [];
$departments = [];

if ($search !== '') {

    $search_safe = $conn->real_escape_string($search);

    /*
    |--------------------------------------------------------------------------
    | EMPLOYEE SEARCH
    |--------------------------------------------------------------------------
    */

    $employee_sql = "
        SELECT
            employees.employee_id,
            employees.employee_code,
            employees.full_name,
            employees.gender,
            employees.phone,
            employees.designation,
            employees.department_id,
            departments.department_name
        FROM employees
        LEFT JOIN departments
            ON employees.department_id = departments.department_id
        WHERE
            employees.full_name LIKE '%$search_safe%'
            OR employees.employee_code LIKE '%$search_safe%'
            OR employees.designation LIKE '%$search_safe%'
            OR employees.phone LIKE '%$search_safe%'
            OR departments.department_name LIKE '%$search_safe%'
        ORDER BY employees.employee_id DESC
    ";

    $employee_result = $conn->query($employee_sql);

    if (!$employee_result) {
        die("Employee SQL Error: " . $conn->error);
    }

    while ($row = $employee_result->fetch_assoc()) {
        $employees[] = $row;
    }


    /*
    |--------------------------------------------------------------------------
    | PROJECT SEARCH
    |--------------------------------------------------------------------------
    */

    $project_sql = "
        SELECT
            project_id,
            project_name,
            description,
            start_date,
            end_date,
            status
        FROM projects
        WHERE
            project_name LIKE '%$search_safe%'
            OR description LIKE '%$search_safe%'
            OR status LIKE '%$search_safe%'
        ORDER BY project_id DESC
    ";

    $project_result = $conn->query($project_sql);

    if (!$project_result) {
        die("Project SQL Error: " . $conn->error);
    }

    while ($row = $project_result->fetch_assoc()) {
        $projects[] = $row;
    }


    /*
    |--------------------------------------------------------------------------
    | TASK SEARCH
    |--------------------------------------------------------------------------
    */

    $task_sql = "
        SELECT
            tasks.task_id,
            tasks.task_title,
            tasks.description,
            tasks.priority,
            tasks.status,
            tasks.start_date,
            tasks.due_date,
            projects.project_name,
            employees.full_name
        FROM tasks

        LEFT JOIN projects
            ON tasks.project_id = projects.project_id

        LEFT JOIN employees
            ON tasks.employee_id = employees.employee_id

        WHERE
            tasks.task_title LIKE '%$search_safe%'
            OR tasks.description LIKE '%$search_safe%'
            OR tasks.priority LIKE '%$search_safe%'
            OR tasks.status LIKE '%$search_safe%'
            OR projects.project_name LIKE '%$search_safe%'
            OR employees.full_name LIKE '%$search_safe%'

        ORDER BY tasks.task_id DESC
    ";

    $task_result = $conn->query($task_sql);

    if (!$task_result) {
        die("Task SQL Error: " . $conn->error);
    }

    while ($row = $task_result->fetch_assoc()) {
        $tasks[] = $row;
    }


    /*
    |--------------------------------------------------------------------------
    | DEPARTMENT SEARCH
    |--------------------------------------------------------------------------
    */

    $department_sql = "
        SELECT
            department_id,
            department_name,
            created_at
        FROM departments
        WHERE
            department_name LIKE '%$search_safe%'
        ORDER BY department_id DESC
    ";

    $department_result = $conn->query($department_sql);

    if (!$department_result) {
        die("Department SQL Error: " . $conn->error);
    }

    while ($row = $department_result->fetch_assoc()) {
        $departments[] = $row;
    }
}


/*
|--------------------------------------------------------------------------
| TOTAL RESULTS
|--------------------------------------------------------------------------
*/

$total_results =
    count($employees) +
    count($projects) +
    count($tasks) +
    count($departments);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Global Search - AI Workforce Management</title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
    rel="stylesheet">

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

/* =========================================================
   PAGE
========================================================= */

body {

    margin: 0;

    background: #f4f7fc;

    font-family:
        "Segoe UI",
        Arial,
        sans-serif;

    color: #172033;
}


/* =========================================================
   SEARCH PAGE
========================================================= */

.search-page {

    max-width: 1250px;

    margin: 45px auto;

    padding: 0 25px;
}


/* =========================================================
   HEADER
========================================================= */

.search-header {

    margin-bottom: 30px;
}

.search-header h1 {

    font-size: 32px;

    font-weight: 700;

    margin-bottom: 8px;
}

.search-header p {

    color: #6b7280;

    margin: 0;

    font-size: 16px;
}


/* =========================================================
   SEARCH BOX
========================================================= */

.global-search-box {

    background: white;

    padding: 8px;

    border-radius: 16px;

    box-shadow:
        0 8px 25px rgba(30, 64, 175, 0.08);

    margin-bottom: 35px;
}

.search-input-wrapper {

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 0 15px;
}

.search-input-wrapper i {

    color: #2563eb;

    font-size: 19px;
}

.search-input {

    border: none;

    outline: none;

    width: 100%;

    padding: 15px 5px;

    font-size: 16px;
}

.search-input::placeholder {

    color: #9ca3af;
}

.search-btn {

    border: none;

    background: #2563eb;

    color: white;

    padding: 13px 25px;

    border-radius: 11px;

    font-weight: 600;

    cursor: pointer;
}

.search-btn:hover {

    background: #1d4ed8;
}


/* =========================================================
   SEARCH INFORMATION
========================================================= */

.search-info {

    background: white;

    padding: 20px 25px;

    border-radius: 14px;

    margin-bottom: 30px;

    box-shadow:
        0 5px 18px rgba(0,0,0,0.05);
}

.search-info strong {

    color: #2563eb;
}


/* =========================================================
   SECTION
========================================================= */

.result-section {

    margin-bottom: 35px;
}

.section-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-bottom: 15px;
}

.section-title {

    display: flex;

    align-items: center;

    gap: 10px;

    font-size: 21px;

    font-weight: 700;
}

.section-count {

    background: #e8f0ff;

    color: #2563eb;

    padding: 4px 12px;

    border-radius: 20px;

    font-size: 13px;

    font-weight: 600;
}


/* =========================================================
   RESULT CARD
========================================================= */

.result-card {

    background: white;

    border-radius: 14px;

    padding: 22px;

    margin-bottom: 14px;

    box-shadow:
        0 5px 18px rgba(0,0,0,0.06);

    border: 1px solid #edf0f5;

    transition: 0.2s;
}

.result-card:hover {

    transform: translateY(-2px);

    box-shadow:
        0 10px 25px rgba(0,0,0,0.09);
}


/* =========================================================
   CARD TOP
========================================================= */

.card-top {

    display: flex;

    justify-content: space-between;

    align-items: flex-start;

    gap: 20px;

    margin-bottom: 10px;
}

.card-title {

    font-size: 19px;

    font-weight: 700;

    margin-bottom: 4px;
}

.card-subtitle {

    color: #6b7280;

    font-size: 14px;
}


/* =========================================================
   CARD DESCRIPTION
========================================================= */

.card-description {

    color: #64748b;

    font-size: 14px;

    line-height: 1.6;

    margin: 10px 0;
}


/* =========================================================
   META INFORMATION
========================================================= */

.card-meta {

    display: flex;

    flex-wrap: wrap;

    gap: 10px;

    margin-top: 12px;
}

.meta-item {

    background: #f5f7fb;

    padding: 6px 11px;

    border-radius: 7px;

    font-size: 13px;

    color: #475569;
}


/* =========================================================
   STATUS
========================================================= */

.status {

    display: inline-block;

    padding: 5px 12px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: 600;

    background: #e8f0ff;

    color: #2563eb;
}


/* =========================================================
   PRIORITY
========================================================= */

.priority {

    display: inline-block;

    padding: 5px 12px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: 600;

    background: #fff4d6;

    color: #a16207;
}


/* =========================================================
   VIEW BUTTON
========================================================= */

.view-btn {

    display: inline-block;

    margin-top: 14px;

    text-decoration: none;

    background: #2563eb;

    color: white;

    padding: 8px 15px;

    border-radius: 8px;

    font-size: 13px;

    font-weight: 600;
}

.view-btn:hover {

    background: #1d4ed8;

    color: white;
}


/* =========================================================
   NO RESULTS
========================================================= */

.no-results {

    background: white;

    padding: 45px 25px;

    text-align: center;

    border-radius: 15px;

    box-shadow:
        0 5px 18px rgba(0,0,0,0.05);

    color: #64748b;
}

.no-results i {

    font-size: 45px;

    color: #94a3b8;

    margin-bottom: 15px;
}

.no-results h3 {

    color: #1e293b;

    margin-bottom: 8px;
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 768px) {

    .search-page {

        margin: 25px auto;

        padding: 0 15px;
    }

    .search-header h1 {

        font-size: 26px;
    }

    .search-btn {

        padding: 11px 16px;
    }

    .card-top {

        flex-direction: column;
    }

}

</style>

</head>


<body>
    


<div class="search-page">


<!-- =====================================================
     HEADER
====================================================== -->

<div class="search-header">

    <h1>
        <i class="fa-solid fa-magnifying-glass"></i>
        Global Search
    </h1>

    <p>
        Search employees, projects, tasks and departments
        from one place.
    </p>

</div>


<!-- =====================================================
     SEARCH BAR
====================================================== -->

<form method="GET" action="search.php">

    <div class="global-search-box">

        <div class="search-input-wrapper">

            <i class="fa-solid fa-magnifying-glass"></i>

            <input
                type="text"
                name="search"
                class="search-input"
                placeholder="Search employees, projects, tasks, departments..."
                value="<?php echo htmlspecialchars($search); ?>"
                autocomplete="off"
                autofocus
            >

            <button
                type="submit"
                class="search-btn">

                Search

            </button>

        </div>

    </div>

</form>


<?php if ($search !== '') { ?>


<!-- =====================================================
     SEARCH INFORMATION
====================================================== -->

<div class="search-info">

    Search results for:

    <strong>
        "<?php echo htmlspecialchars($search); ?>"
    </strong>

    <span class="ms-2">

        <?php echo $total_results; ?>

        result(s) found

    </span>

</div>


<!-- =====================================================
     EMPLOYEES
====================================================== -->

<?php if (count($employees) > 0) { ?>

<div class="result-section">

    <div class="section-header">

        <div class="section-title">

            <i class="fa-solid fa-users text-primary"></i>

            Employees

        </div>

        <div class="section-count">

            <?php echo count($employees); ?>

        </div>

    </div>


    <?php foreach ($employees as $employee) { ?>

    <div class="result-card">

        <div class="card-top">

            <div>

                <div class="card-title">

                    <?php
                    echo htmlspecialchars(
                        $employee['full_name']
                    );
                    ?>

                </div>

                <div class="card-subtitle">

                    Employee Code:

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $employee['employee_code']
                        );
                        ?>
                    </strong>

                </div>

            </div>

        </div>


        <div class="card-meta">

            <?php if (!empty($employee['designation'])) { ?>

                <span class="meta-item">

                    <i class="fa-solid fa-briefcase"></i>

                    <?php
                    echo htmlspecialchars(
                        $employee['designation']
                    );
                    ?>

                </span>

            <?php } ?>


            <?php if (!empty($employee['department_name'])) { ?>

                <span class="meta-item">

                    <i class="fa-solid fa-building"></i>

                    <?php
                    echo htmlspecialchars(
                        $employee['department_name']
                    );
                    ?>

                </span>

            <?php } ?>


            <?php if (!empty($employee['phone'])) { ?>

                <span class="meta-item">

                    <i class="fa-solid fa-phone"></i>

                    <?php
                    echo htmlspecialchars(
                        $employee['phone']
                    );
                    ?>

                </span>

            <?php } ?>


            <?php if (!empty($employee['gender'])) { ?>

                <span class="meta-item">

                    <i class="fa-solid fa-user"></i>

                    <?php
                    echo htmlspecialchars(
                        $employee['gender']
                    );
                    ?>

                </span>

            <?php } ?>

        </div>


        <a
            href="employee/view.php"
            class="view-btn">

            View Employees

        </a>

    </div>

    <?php } ?>

</div>

<?php } ?>


<!-- =====================================================
     PROJECTS
====================================================== -->

<?php if (count($projects) > 0) { ?>

<div class="result-section">

    <div class="section-header">

        <div class="section-title">

            <i class="fa-solid fa-folder text-primary"></i>

            Projects

        </div>

        <div class="section-count">

            <?php echo count($projects); ?>

        </div>

    </div>


    <?php foreach ($projects as $project) { ?>

    <div class="result-card">

        <div class="card-top">

            <div>

                <div class="card-title">

                    <?php
                    echo htmlspecialchars(
                        $project['project_name']
                    );
                    ?>

                </div>

                <div class="card-subtitle">

                    Project ID:

                    <?php
                    echo htmlspecialchars(
                        $project['project_id']
                    );
                    ?>

                </div>

            </div>


            <span class="status">

                <?php
                echo htmlspecialchars(
                    $project['status']
                );
                ?>

            </span>

        </div>


        <div class="card-description">

            <?php

            echo htmlspecialchars(
                $project['description']
                ?? 'No description available'
            );

            ?>

        </div>


        <div class="card-meta">

            <span class="meta-item">

                <i class="fa-regular fa-calendar"></i>

                Start:

                <?php
                echo htmlspecialchars(
                    $project['start_date'] ?? '-'
                );
                ?>

            </span>


            <span class="meta-item">

                <i class="fa-regular fa-calendar"></i>

                End:

                <?php
                echo htmlspecialchars(
                    $project['end_date'] ?? '-'
                );
                ?>

            </span>

        </div>


        <a
            href="project/view.php"
            class="view-btn">

            View Projects

        </a>

    </div>

    <?php } ?>

</div>

<?php } ?>


<!-- =====================================================
     TASKS
====================================================== -->

<?php if (count($tasks) > 0) { ?>

<div class="result-section">

    <div class="section-header">

        <div class="section-title">

            <i class="fa-solid fa-list-check text-primary"></i>

            Tasks

        </div>

        <div class="section-count">

            <?php echo count($tasks); ?>

        </div>

    </div>


    <?php foreach ($tasks as $task) { ?>

    <div class="result-card">

        <div class="card-top">

            <div>

                <div class="card-title">

                    <?php
                    echo htmlspecialchars(
                        $task['task_title']
                    );
                    ?>

                </div>

                <div class="card-subtitle">

                    Project:

                    <strong>

                        <?php
                        echo htmlspecialchars(
                            $task['project_name']
                            ?? 'No project'
                        );
                        ?>

                    </strong>

                </div>

            </div>


            <span class="status">

                <?php
                echo htmlspecialchars(
                    $task['status']
                );
                ?>

            </span>

        </div>


        <div class="card-description">

            <?php

            echo htmlspecialchars(
                $task['description']
                ?? 'No description available'
            );

            ?>

        </div>


        <div class="card-meta">

            <span class="priority">

                Priority:

                <?php
                echo htmlspecialchars(
                    $task['priority']
                );
                ?>

            </span>


            <span class="meta-item">

                Assigned To:

                <?php
                echo htmlspecialchars(
                    $task['full_name']
                    ?? 'Unassigned'
                );
                ?>

            </span>


            <span class="meta-item">

                Due:

                <?php
                echo htmlspecialchars(
                    $task['due_date']
                    ?? '-'
                );
                ?>

            </span>

        </div>


        <a
            href="task/view.php"
            class="view-btn">

            View Tasks

        </a>

    </div>

    <?php } ?>

</div>

<?php } ?>


<!-- =====================================================
     DEPARTMENTS
====================================================== -->

<?php if (count($departments) > 0) { ?>

<div class="result-section">

    <div class="section-header">

        <div class="section-title">

            <i class="fa-solid fa-building text-primary"></i>

            Departments

        </div>

        <div class="section-count">

            <?php echo count($departments); ?>

        </div>

    </div>


    <?php foreach ($departments as $department) { ?>

    <div class="result-card">

        <div class="card-top">

            <div>

                <div class="card-title">

                    <?php
                    echo htmlspecialchars(
                        $department['department_name']
                    );
                    ?>

                </div>

                <div class="card-subtitle">

                    Department ID:

                    <?php
                    echo htmlspecialchars(
                        $department['department_id']
                    );
                    ?>

                </div>

            </div>

        </div>


        <a
            href="department/view.php"
            class="view-btn">

            View Departments

        </a>

    </div>

    <?php } ?>

</div>

<?php } ?>


<!-- =====================================================
     NO RESULTS
====================================================== -->

<?php if ($total_results === 0) { ?>

<div class="no-results">

    <i class="fa-solid fa-magnifying-glass"></i>

    <h3>
        No results found
    </h3>

    <p>
        We couldn't find any employees, projects,
        tasks or departments matching
        "<strong><?php echo htmlspecialchars($search); ?></strong>".
    </p>

</div>

<?php } ?>


<?php } else { ?>


<!-- =====================================================
     INITIAL STATE
====================================================== -->

<div class="no-results">

    <i class="fa-solid fa-magnifying-glass"></i>

    <h3>
        Start searching
    </h3>

    <p>
        Search for an employee, project, task or department.
    </p>

</div>

<?php } ?>


</div>


</body>

</html>
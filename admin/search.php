<?php

require_once "config/database.php";

$search = $_GET['search'] ?? '';
$search = trim($search);

$projects = [];

if ($search !== '') {

    $search_safe = $conn->real_escape_string($search);

    /*
    |--------------------------------------------------------------------------
    | SEARCH PROJECTS
    |--------------------------------------------------------------------------
    */

    $sql = "
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

    $result = $conn->query($sql);

    if (!$result) {
        die("SQL Error: " . $conn->error);
    }

    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
}

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

    <style>

        body {
            background: #f5f7fb;
            font-family: Arial, sans-serif;
        }

        .search-container {
            max-width: 1200px;
            margin: 50px auto;
        }

        .search-title {
            margin-bottom: 30px;
        }

        .result-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .result-type {
            color: #2563eb;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .result-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .result-description {
            color: #666;
            margin-bottom: 10px;
        }

        .status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            background: #e8f0ff;
            color: #2563eb;
            font-size: 13px;
        }

        .no-results {
            background: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            color: #666;
        }

    </style>

</head>

<body>
   <?php include "navbar.php"; ?>

<div class="search-container">

    <h1 class="search-title">
        🔍 Global Search
    </h1>

    <h4 class="mb-4">
        Search results for:
        <strong>
            <?php echo htmlspecialchars($search); ?>
        </strong>
    </h4>


    <!-- =====================================================
         PROJECT RESULTS
    ====================================================== -->

    <?php if (count($projects) > 0) { ?>

        <h3 class="mb-3">
            📁 Projects
        </h3>


        <?php foreach ($projects as $project) { ?>

            <div class="result-card">

                <div class="result-type">
                    PROJECT
                </div>

                <div class="result-title">

                    <?php echo htmlspecialchars($project['project_name']); ?>

                </div>

                <div class="result-description">

                    <?php
                    echo htmlspecialchars(
                        $project['description'] ?? 'No description available'
                    );
                    ?>

                </div>

                <div class="mb-2">

                    <strong>Start Date:</strong>

                    <?php
                    echo htmlspecialchars(
                        $project['start_date'] ?? '-'
                    );
                    ?>

                    &nbsp;&nbsp;

                    <strong>End Date:</strong>

                    <?php
                    echo htmlspecialchars(
                        $project['end_date'] ?? '-'
                    );
                    ?>

                </div>


                <div>

                    <span class="status">

                        <?php
                        echo htmlspecialchars($project['status']);
                        ?>

                    </span>

                </div>


                <div class="mt-3">

                    <a
                        href="project/view.php"
                        class="btn btn-primary btn-sm">

                        View Projects

                    </a>

                </div>

            </div>

        <?php } ?>


    <?php } else if ($search !== '') { ?>

        <div class="no-results">

            <h4>
                No results found
            </h4>

            <p>
                No project matches
                <strong>
                    "<?php echo htmlspecialchars($search); ?>"
                </strong>
                were found.
            </p>

        </div>

    <?php } else { ?>

        <div class="no-results">

            <h4>
                Start searching
            </h4>

            <p>
                Search for employees, projects, tasks or departments.
            </p>

        </div>

    <?php } ?>

</div>

</body>

</html>
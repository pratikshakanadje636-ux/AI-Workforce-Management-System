```php
<?php

session_start();

require_once "../config/database.php";

/* ===========================
   MANAGER LOGIN CHECK
=========================== */

if (
    !isset($_SESSION['user_id']) ||
    $_SESSION['role_id'] != 2
) {
    header("Location: ../authentication/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ===========================
   GET MANAGER INFORMATION
=========================== */

$manager_name = "Manager";

$sql = "
SELECT
    users.name,
    users.email,
    employees.profile_picture

FROM users

LEFT JOIN employees
    ON users.user_id = employees.user_id

WHERE users.user_id = ?

LIMIT 1
";

$stmt = $conn->prepare($sql);

if ($stmt) {

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $manager = $result->fetch_assoc();

        $manager_name =
            $manager['name']
            ?? "Manager";

        $profile_picture =
            trim(
                $manager['profile_picture']
                ?? ''
            );

    } else {

        $profile_picture = '';

    }

} else {

    $profile_picture = '';

}

$manager_initial = strtoupper(
    substr(
        $manager_name,
        0,
        1
    )
);


/* ===========================
   PROJECT STATISTICS
=========================== */

$total_projects = 0;
$active_projects = 0;
$planning_projects = 0;
$completed_projects = 0;
$on_hold_projects = 0;


/* Total */

$sql = "
SELECT COUNT(*) AS total
FROM projects
";

$result = $conn->query($sql);

if ($result) {

    $row = $result->fetch_assoc();

    $total_projects =
        $row['total'] ?? 0;
}


/* Active */

$sql = "
SELECT COUNT(*) AS total
FROM projects
WHERE status = 'Active'
";

$result = $conn->query($sql);

if ($result) {

    $row = $result->fetch_assoc();

    $active_projects =
        $row['total'] ?? 0;
}


/* Planning */

$sql = "
SELECT COUNT(*) AS total
FROM projects
WHERE status = 'Planning'
";

$result = $conn->query($sql);

if ($result) {

    $row = $result->fetch_assoc();

    $planning_projects =
        $row['total'] ?? 0;
}


/* Completed */

$sql = "
SELECT COUNT(*) AS total
FROM projects
WHERE status = 'Completed'
";

$result = $conn->query($sql);

if ($result) {

    $row = $result->fetch_assoc();

    $completed_projects =
        $row['total'] ?? 0;
}


/* On Hold */

$sql = "
SELECT COUNT(*) AS total
FROM projects
WHERE status = 'On Hold'
";

$result = $conn->query($sql);

if ($result) {

    $row = $result->fetch_assoc();

    $on_hold_projects =
        $row['total'] ?? 0;
}


/* ===========================
   PROJECT LIST
=========================== */

$projects = [];

$sql = "
SELECT
    project_id,
    project_name,
    description,
    start_date,
    end_date,
    status,
    created_at
FROM projects
ORDER BY project_id DESC
";

$result = $conn->query($sql);

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $projects[] = $row;

    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Projects | AI Workforce
</title>


<!-- Bootstrap -->

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
    rel="stylesheet"
>


<!-- Bootstrap Icons -->

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
>


<link
    rel="stylesheet"
    href="manager.css"
>


<style>

/* =========================================================
   MANAGER PROFILE PICTURE
========================================================= */

.avatar img {

    width: 100%;

    height: 100%;

    border-radius: 50%;

    object-fit: cover;

    display: block;

}


/* =========================================================
   SIDEBAR LOGO
========================================================= */

.brand-icon {

    width: 45px;

    height: 45px;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 0 !important;

    margin: 0 !important;

    background: transparent !important;

    background-color: transparent !important;

    border: none !important;

    box-shadow: none !important;

    border-radius: 0 !important;

    font-size: 0;

    flex-shrink: 0;

    overflow: visible;

}


.brand-icon img {

    width: 38px;

    height: 38px;

    display: block;

    object-fit: contain;

    background: transparent !important;

    border: none !important;

    box-shadow: none !important;

}

</style>

</head>


<body>


<?php include "../config/page_actions.php"; ?>


<!-- ===========================
     SIDEBAR
=========================== -->

<aside class="sidebar">


    <div class="brand">


        <div class="brand-icon">

            <img
                src="../assets/images/logo/logo-sidebar.png"
                alt="AI Workforce"
            >

        </div>


        <div class="brand-text">

            <h5>
                AI Workforce
            </h5>


            <small>
                Manager Portal
            </small>

        </div>


    </div>


    <a
        href="dashboard.php"
        class="nav-link-custom"
    >

        <i class="bi bi-grid-1x2-fill"></i>

        <span class="nav-text">

            Dashboard

        </span>

    </a>


    <a
        href="team.php"
        class="nav-link-custom"
    >

        <i class="bi bi-people-fill"></i>

        <span class="nav-text">

            My Team

        </span>

    </a>


    <a
        href="tasks.php"
        class="nav-link-custom"
    >

        <i class="bi bi-list-task"></i>

        <span class="nav-text">

            Tasks

        </span>

    </a>


    <a
        href="performance.php"
        class="nav-link-custom"
    >

        <i class="bi bi-bar-chart-fill"></i>

        <span class="nav-text">

            Performance

        </span>

    </a>


    <a
        href="projects.php"
        class="nav-link-custom active"
    >

        <i class="bi bi-kanban-fill"></i>

        <span class="nav-text">

            Projects

        </span>

    </a>


    <a
        href="profile.php"
        class="nav-link-custom"
    >

        <i class="bi bi-person-fill"></i>

        <span class="nav-text">

            My Profile

        </span>

    </a>


    <div style="height: 30%;"></div>


    <a
        href="../authentication/logout.php"
        class="nav-link-custom"
    >

        <i class="bi bi-box-arrow-right"></i>

        <span class="nav-text">

            Logout

        </span>

    </a>


</aside>


<!-- ===========================
     MAIN
=========================== -->

<div class="main">


    <!-- TOPBAR -->

    <header class="topbar">


        <div class="portal-title">

            <i class="bi bi-kanban-fill text-primary"></i>

            Manager Projects

        </div>


        <div class="user-area">


            <div class="text-end d-none d-sm-block">


                <strong>

                    <?php

                    echo htmlspecialchars(
                        $manager_name,
                        ENT_QUOTES,
                        'UTF-8'
                    );

                    ?>

                </strong>


                <div
                    style="
                    font-size:12px;
                    color:#64748b;
                    "
                >

                    Manager

                </div>


            </div>


            <!-- MANAGER PROFILE AVATAR -->

            <div class="avatar">

                <?php if (!empty($profile_picture)): ?>

                    <img
                        src="../assets/images/profiles/<?php echo htmlspecialchars($profile_picture, ENT_QUOTES, 'UTF-8'); ?>"
                        alt="Profile Picture"
                    >

                <?php else: ?>

                    <?php

                    echo htmlspecialchars(
                        $manager_initial,
                        ENT_QUOTES,
                        'UTF-8'
                    );

                    ?>

                <?php endif; ?>

            </div>


        </div>


    </header>


    <!-- CONTENT -->

    <main class="content">


        <!-- PAGE HEADER -->

        <div class="page-header">


            <h2>

                Project Management 📁

            </h2>


            <p>

                Monitor and track projects across the organization.

            </p>


        </div>


        <!-- ===========================
             STATISTICS
        =========================== -->

        <div class="row g-4">


            <!-- TOTAL -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">


                    <div class="stat-icon blue">

                        <i class="bi bi-kanban"></i>

                    </div>


                    <div class="stat-number">

                        <?php

                        echo $total_projects;

                        ?>

                    </div>


                    <div class="stat-label">

                        Total Projects

                    </div>


                </div>

            </div>


            <!-- ACTIVE -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">


                    <div class="stat-icon green">

                        <i class="bi bi-play-circle"></i>

                    </div>


                    <div class="stat-number">

                        <?php

                        echo $active_projects;

                        ?>

                    </div>


                    <div class="stat-label">

                        Active Projects

                    </div>


                </div>

            </div>


            <!-- PLANNING -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">


                    <div class="stat-icon orange">

                        <i class="bi bi-calendar-event"></i>

                    </div>


                    <div class="stat-number">

                        <?php

                        echo $planning_projects;

                        ?>

                    </div>


                    <div class="stat-label">

                        Planning Projects

                    </div>


                </div>

            </div>


            <!-- COMPLETED -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">


                    <div class="stat-icon purple">

                        <i class="bi bi-check-circle"></i>

                    </div>


                    <div class="stat-number">

                        <?php

                        echo $completed_projects;

                        ?>

                    </div>


                    <div class="stat-label">

                        Completed Projects

                    </div>


                </div>

            </div>


        </div>


        <!-- ===========================
             PROJECT LIST
        =========================== -->

        <div class="dashboard-card">


            <div class="card-heading">


                <h5>

                    <i class="bi bi-kanban text-primary"></i>

                    All Projects

                </h5>


                <span
                    class="badge bg-primary"
                >

                    <?php

                    echo count($projects);

                    ?>

                    Projects

                </span>


            </div>


            <div class="card-body-custom">


                <?php if (count($projects) > 0): ?>


                    <?php foreach ($projects as $project): ?>


                        <div class="project-row">


                            <div
                                class="d-flex justify-content-between align-items-start gap-3"
                            >


                                <div>


                                    <div class="project-title">


                                        <?php

                                        echo htmlspecialchars(
                                            $project['project_name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );

                                        ?>


                                    </div>


                                    <div class="project-description">


                                        <?php


                                        if (
                                            !empty(
                                                $project['description']
                                            )
                                        ) {


                                            echo htmlspecialchars(
                                                $project['description'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );


                                        } else {


                                            echo "No project description available.";


                                        }


                                        ?>


                                    </div>


                                </div>


                                <div>


                                    <?php


                                    $status =
                                        $project['status']
                                        ?? 'Planning';


                                    if (
                                        $status == 'Active'
                                    ) {


                                        $status_class =
                                            'status-active';


                                    } elseif (
                                        $status == 'Completed'
                                    ) {


                                        $status_class =
                                            'status-completed';


                                    } elseif (
                                        $status == 'On Hold'
                                    ) {


                                        $status_class =
                                            'status-hold';


                                    } else {


                                        $status_class =
                                            'status-planning';


                                    }


                                    ?>


                                    <span
                                        class="
                                        status-badge
                                        <?php
                                        echo $status_class;
                                        ?>
                                        "
                                    >


                                        <?php

                                        echo htmlspecialchars(
                                            $status,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );

                                        ?>


                                    </span>


                                </div>


                            </div>


                            <!-- PROJECT INFO -->

                            <div class="project-info">


                                <!-- START DATE -->

                                <div class="project-info-item">


                                    <i class="bi bi-calendar-event"></i>


                                    <span>


                                        Start:


                                        <?php


                                        if (
                                            !empty(
                                                $project['start_date']
                                            )
                                        ) {


                                            echo date(
                                                'd M Y',
                                                strtotime(
                                                    $project['start_date']
                                                )
                                            );


                                        } else {


                                            echo "Not set";


                                        }


                                        ?>


                                    </span>


                                </div>


                                <!-- END DATE -->

                                <div class="project-info-item">


                                    <i class="bi bi-calendar-check"></i>


                                    <span>


                                        End:


                                        <?php


                                        if (
                                            !empty(
                                                $project['end_date']
                                            )
                                        ) {


                                            echo date(
                                                'd M Y',
                                                strtotime(
                                                    $project['end_date']
                                                )
                                            );


                                        } else {


                                            echo "Not set";


                                        }


                                        ?>


                                    </span>


                                </div>


                                <!-- PROJECT ID -->

                                <div class="project-info-item">


                                    <i class="bi bi-hash"></i>


                                    <span>


                                        Project ID:


                                        <?php

                                        echo $project['project_id'];

                                        ?>


                                    </span>


                                </div>


                            </div>


                        </div>


                    <?php endforeach; ?>


                <?php else: ?>


                    <!-- EMPTY STATE -->

                    <div class="empty-state">


                        <div class="empty-icon">


                            <i class="bi bi-folder-x"></i>


                        </div>


                        <h5>

                            No Projects Found

                        </h5>


                        <p>

                            There are currently no projects in the system.

                        </p>


                    </div>


                <?php endif; ?>


            </div>


        </div>


    </main>


</div>


</body>

</html>
```

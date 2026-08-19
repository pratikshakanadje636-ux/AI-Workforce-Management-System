```php
<?php

session_start();

require_once "../config/database.php";

/* =========================================================
   MANAGER LOGIN CHECK
========================================================= */

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role_id']) ||
    $_SESSION['role_id'] != 2
) {
    header("Location: ../authentication/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


/* =========================================================
   GET MANAGER INFORMATION
========================================================= */

$sql = "
SELECT
    users.user_id,
    users.email,
    users.status,

    employees.employee_id,
    employees.full_name,
    employees.employee_code,
    employees.designation,
    employees.department_id,
    employees.profile_picture,

    departments.department_name

FROM users

LEFT JOIN employees
    ON users.user_id = employees.user_id

LEFT JOIN departments
    ON employees.department_id = departments.department_id

WHERE users.user_id = ?
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows != 1) {
    die("Manager profile not found.");
}

$manager = $result->fetch_assoc();

$manager_name = $manager['full_name'] ?? 'Manager';

$manager_initial = strtoupper(
    substr($manager_name, 0, 1)
);

$profile_picture =
    trim(
        $manager['profile_picture'] ?? ''
    );

$department_name =
    $manager['department_name'] ?? 'Department';

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Team | AI Workforce</title>


    <!-- =====================================================
         BOOTSTRAP
    ====================================================== -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- =====================================================
         BOOTSTRAP ICONS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    >


    <!-- =====================================================
         MANAGER CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="manager.css"
    >


    <style>

        /* =====================================================
           MANAGER PROFILE PICTURE
        ====================================================== */

        .avatar img {

            width: 100%;

            height: 100%;

            border-radius: 50%;

            object-fit: cover;

            display: block;

        }


        /* =====================================================
           SIDEBAR LOGO
        ====================================================== */

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

            object-fit: contain;

            display: block;

        }

    </style>

</head>


<body>


<!-- =========================================================
     SIDEBAR
========================================================= -->

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


    <!-- DASHBOARD -->

    <a
        href="dashboard.php"
        class="nav-link-custom"
    >

        <i class="bi bi-grid-1x2-fill"></i>

        <span class="nav-text">

            Dashboard

        </span>

    </a>


    <!-- MY TEAM -->

    <a
        href="team.php"
        class="nav-link-custom active"
    >

        <i class="bi bi-people-fill"></i>

        <span class="nav-text">

            My Team

        </span>

    </a>


    <!-- TASKS -->

    <a
        href="tasks.php"
        class="nav-link-custom"
    >

        <i class="bi bi-list-task"></i>

        <span class="nav-text">

            Tasks

        </span>

    </a>


    <!-- PERFORMANCE -->

    <a
        href="performance.php"
        class="nav-link-custom"
    >

        <i class="bi bi-bar-chart-fill"></i>

        <span class="nav-text">

            Performance

        </span>

    </a>


    <!-- PROJECTS -->

    <a
        href="projects.php"
        class="nav-link-custom"
    >

        <i class="bi bi-kanban-fill"></i>

        <span class="nav-text">

            Projects

        </span>

    </a>


    <!-- PROFILE -->

    <a
        href="profile.php"
        class="nav-link-custom"
    >

        <i class="bi bi-person-fill"></i>

        <span class="nav-text">

            My Profile

        </span>

    </a>


    <!-- SPACER -->

    <div style="height: 30%;"></div>


    <!-- LOGOUT -->

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



<!-- =========================================================
     MAIN
========================================================= -->

<div class="main">


    <!-- =====================================================
         TOPBAR
    ====================================================== -->

    <header class="topbar">


        <!-- PAGE TITLE -->

        <div class="portal-title">

            <i class="bi bi-people-fill text-primary"></i>

            My Team

        </div>


        <!-- USER -->

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
                    color:#94a3b8;
                    "
                >

                    Manager

                </div>

            </div>


            <!-- PROFILE AVATAR -->

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



    <!-- =====================================================
         CONTENT
    ====================================================== -->

    <main class="content">


        <!-- =================================================
             PAGE HEADER
        ================================================== -->

        <div class="page-header">

            <div>

                <h2>
                    My Team 👥
                </h2>

                <p>
                    View and monitor employees in your department.
                </p>

            </div>


            <!-- REAL TIME INDICATOR -->

            <div
                id="liveIndicator"
                class="small text-muted"
            >

                <i class="bi bi-circle-fill text-success"></i>

                Live

            </div>

        </div>



        <!-- =================================================
             SUMMARY CARD
        ================================================== -->

        <div class="summary-card">


            <div class="row align-items-center">


                <!-- DEPARTMENT -->

                <div class="col-md-8">

                    <h4 id="departmentName">

                        <?php

                        echo htmlspecialchars(
                            $department_name,
                            ENT_QUOTES,
                            'UTF-8'
                        );

                        ?>

                    </h4>


                    <p>
                        Your assigned team members
                    </p>

                </div>


                <!-- TEAM COUNT -->

                <div
                    class="col-md-4 text-md-end mt-3 mt-md-0"
                >

                    <div
                        class="summary-number"
                        id="teamCount"
                    >
                        0
                    </div>

                    <div>
                        Team Members
                    </div>

                </div>


            </div>

        </div>



        <!-- =================================================
             LAST UPDATED
        ================================================== -->

        <div
            class="d-flex justify-content-between align-items-center mb-3"
        >

            <div class="small text-muted">

                <i class="bi bi-clock-history me-1"></i>

                Last updated:

                <span id="lastUpdated">
                    Loading...
                </span>

            </div>


            <button
                type="button"
                class="btn btn-sm btn-outline-primary"
                onclick="loadTeam()"
                id="refreshButton"
            >

                <i class="bi bi-arrow-clockwise"></i>

                Refresh

            </button>

        </div>



        <!-- =================================================
             LOADING
        ================================================== -->

        <div
            id="teamLoading"
            class="text-center py-5"
        >

            <div
                class="spinner-border text-primary"
                role="status"
            ></div>

            <p class="mt-3 text-muted">
                Loading team members...
            </p>

        </div>



        <!-- =================================================
             TEAM CONTAINER
        ================================================== -->

        <div
            id="teamContainer"
            class="row g-4"
        ></div>



        <!-- =================================================
             EMPTY STATE
        ================================================== -->

        <div
            id="emptyState"
            class="empty-state"
            style="display:none;"
        >

            <i class="bi bi-people"></i>

            <h5>
                No Team Members Found
            </h5>

            <p>
                There are currently no employees assigned
                to your department.
            </p>

        </div>



        <!-- =================================================
             ERROR STATE
        ================================================== -->

        <div
            id="errorState"
            class="empty-state"
            style="display:none;"
        >

            <i class="bi bi-exclamation-triangle"></i>

            <h5>
                Unable to Load Team
            </h5>

            <p id="errorMessage">
                Something went wrong while loading team information.
            </p>

            <button
                type="button"
                class="btn btn-primary mt-2"
                onclick="loadTeam()"
            >

                <i class="bi bi-arrow-clockwise"></i>

                Try Again

            </button>

        </div>



        <!-- =================================================
             FOOTER
        ================================================== -->

        <div class="text-center mt-4">

            <small class="text-muted">

                © <?php echo date("Y"); ?>

                AI Workforce Management System

            </small>

        </div>


    </main>


</div>



<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script>

/* =========================================================
   GLOBAL STATE
========================================================= */

let isLoading = false;

let firstLoad = true;


/* =========================================================
   LOAD TEAM
========================================================= */

async function loadTeam() {


    /* -----------------------------------------------------
       PREVENT MULTIPLE REQUESTS
    ----------------------------------------------------- */

    if (isLoading) {
        return;
    }

    isLoading = true;


    /* -----------------------------------------------------
       ELEMENTS
    ----------------------------------------------------- */

    const loading =
        document.getElementById("teamLoading");

    const container =
        document.getElementById("teamContainer");

    const emptyState =
        document.getElementById("emptyState");

    const errorState =
        document.getElementById("errorState");

    const errorMessage =
        document.getElementById("errorMessage");

    const teamCount =
        document.getElementById("teamCount");

    const departmentName =
        document.getElementById("departmentName");

    const lastUpdated =
        document.getElementById("lastUpdated");

    const refreshButton =
        document.getElementById("refreshButton");

    const liveIndicator =
        document.getElementById("liveIndicator");


    /* -----------------------------------------------------
       LOADING STATE
    ----------------------------------------------------- */

    if (firstLoad) {

        loading.style.display = "block";

    }


    emptyState.style.display = "none";

    errorState.style.display = "none";


    /* -----------------------------------------------------
       REFRESH BUTTON
    ----------------------------------------------------- */

    refreshButton.disabled = true;

    refreshButton.innerHTML = `
        <span
            class="spinner-border spinner-border-sm me-1"
        ></span>
        Refreshing
    `;


    try {


        /* =================================================
           API REQUEST
        ================================================= */

        const response = await fetch(
            "api/team_data.php?_=" + Date.now(),
            {
                method: "GET",

                cache: "no-store",

                headers: {
                    "Accept": "application/json"
                }
            }
        );


        /* -------------------------------------------------
           HTTP ERROR
        ------------------------------------------------- */

        if (!response.ok) {

            throw new Error(
                "Server returned HTTP " +
                response.status
            );

        }


        /* =================================================
           READ JSON
        ================================================= */

        const data =
            await response.json();


        /* =================================================
           API ERROR
        ================================================= */

        if (!data.success) {

            throw new Error(
                data.message ||
                "Unable to load team information."
            );

        }


        /* =================================================
           UPDATE SUMMARY
        ================================================= */

        teamCount.textContent =
            data.total ?? 0;


        departmentName.textContent =
            data.department_name ||
            "Department";


        /* =================================================
           HIDE LOADING
        ================================================= */

        loading.style.display = "none";


        /* =================================================
           CLEAR OLD CARDS
        ================================================= */

        container.innerHTML = "";


        /* =================================================
           CHECK EMPTY
        ================================================= */

        if (
            !data.employees ||
            data.employees.length === 0
        ) {

            emptyState.style.display = "block";

        }


        /* =================================================
           CREATE TEAM CARDS
        ================================================= */

        else {

            data.employees.forEach(
                member => {

                    container.insertAdjacentHTML(
                        "beforeend",
                        createTeamCard(member)
                    );

                }
            );

        }


        /* =================================================
           LAST UPDATED
        ================================================= */

        lastUpdated.textContent =
            new Date().toLocaleTimeString(
                [],
                {
                    hour: "2-digit",
                    minute: "2-digit",
                    second: "2-digit"
                }
            );


        /* =================================================
           LIVE STATUS
        ================================================= */

        liveIndicator.innerHTML = `
            <i class="bi bi-circle-fill text-success"></i>
            Live
        `;


        firstLoad = false;


    }


    catch (error) {


        console.error(
            "Team API Error:",
            error
        );


        /* -------------------------------------------------
           DON'T DESTROY EXISTING DATA DURING AUTO REFRESH
        ------------------------------------------------- */

        if (firstLoad) {

            loading.style.display = "none";

            errorState.style.display = "block";

            errorMessage.textContent =
                error.message ||
                "Unable to load team information.";

        }


        /* -------------------------------------------------
           LIVE STATUS
        ------------------------------------------------- */

        liveIndicator.innerHTML = `
            <i class="bi bi-circle-fill text-danger"></i>
            Connection error
        `;

    }


    finally {


        /* -------------------------------------------------
           RESET BUTTON
        ------------------------------------------------- */

        refreshButton.disabled = false;

        refreshButton.innerHTML = `
            <i class="bi bi-arrow-clockwise"></i>
            Refresh
        `;


        isLoading = false;

    }

}


/* =========================================================
   CREATE TEAM CARD
========================================================= */

function createTeamCard(member) {


    /* =====================================================
       BASIC INFORMATION
    ===================================================== */

    const name =
        member.full_name ||
        "Unknown Employee";


    const initial =
        name
            .trim()
            .charAt(0)
            .toUpperCase();


    const employeeCode =
        member.employee_code ||
        "Employee";


    const designation =
        member.designation ||
        "Not Assigned";


    const phone =
        member.phone ||
        "Not Provided";


    /* =====================================================
       PROJECTS
    ===================================================== */

    const completedProjects =
        Number(
            member.completed_projects || 0
        );


    /* =====================================================
       PERFORMANCE
    ===================================================== */

    const performance =
        Math.min(
            Math.max(
                Number(
                    member.performance_score || 0
                ),
                0
            ),
            100
        );


    /* =====================================================
       WORKLOAD
    ===================================================== */

    const workload =
        Math.min(
            Math.max(
                Number(
                    member.workload || 0
                ),
                0
            ),
            100
        );


    let workloadClass =
        "workload-low";


    let workloadText =
        "Low";


    if (workload > 70) {

        workloadClass =
            "workload-high";

        workloadText =
            "High";

    }

    else if (workload > 40) {

        workloadClass =
            "workload-medium";

        workloadText =
            "Medium";

    }


    /* =====================================================
       JOINING DATE
    ===================================================== */

    const joiningDate =
        member.joining_date
            ? formatDate(member.joining_date)
            : "Not available";


    /* =====================================================
       RETURN CARD
    ===================================================== */

    return `

        <div class="col-xl-4 col-md-6">

            <div class="team-card">


                <!-- =========================================
                     CARD HEADER
                ========================================== -->

                <div class="team-card-header">

                    <div
                        class="d-flex align-items-center gap-3"
                    >

                        <div class="member-avatar">

                            ${escapeHtml(initial)}

                        </div>


                        <div>

                            <div class="member-name">

                                ${escapeHtml(name)}

                            </div>


                            <div class="member-code">

                                ${escapeHtml(employeeCode)}

                            </div>

                        </div>

                    </div>

                </div>



                <!-- =========================================
                     CARD BODY
                ========================================== -->

                <div class="team-card-body">


                    <!-- DESIGNATION -->

                    <div class="info-item">

                        <span class="info-label">

                            <i
                                class="bi bi-person-badge me-1"
                            ></i>

                            Designation

                        </span>


                        <span class="info-value">

                            ${escapeHtml(designation)}

                        </span>

                    </div>



                    <!-- PHONE -->

                    <div class="info-item">

                        <span class="info-label">

                            <i
                                class="bi bi-telephone me-1"
                            ></i>

                            Phone

                        </span>


                        <span class="info-value">

                            ${escapeHtml(phone)}

                        </span>

                    </div>



                    <!-- JOINING DATE -->

                    <div class="info-item">

                        <span class="info-label">

                            <i
                                class="bi bi-calendar-event me-1"
                            ></i>

                            Joined

                        </span>


                        <span class="info-value">

                            ${escapeHtml(joiningDate)}

                        </span>

                    </div>



                    <!-- COMPLETED PROJECTS -->

                    <div class="info-item">

                        <span class="info-label">

                            <i
                                class="bi bi-kanban me-1"
                            ></i>

                            Completed Projects

                        </span>


                        <span class="info-value">

                            ${completedProjects}

                        </span>

                    </div>



                    <!-- WORKLOAD -->

                    <div class="info-item">

                        <span class="info-label">

                            <i
                                class="bi bi-speedometer2 me-1"
                            ></i>

                            Workload

                        </span>


                        <span class="info-value">

                            <span
                                class="
                                    workload-badge
                                    ${workloadClass}
                                "
                            >

                                ${workloadText}

                                (${workload}%)

                            </span>

                        </span>

                    </div>



                    <!-- PERFORMANCE -->

                    <div class="mt-4">

                        <div
                            class="performance-label"
                        >

                            <span>
                                Performance
                            </span>


                            <span
                                class="performance-value"
                            >

                                ${performance}%

                            </span>

                        </div>


                        <div class="progress">

                            <div
                                class="progress-bar bg-primary"
                                role="progressbar"

                                style="
                                    width:${performance}%
                                "

                                aria-valuenow="${performance}"

                                aria-valuemin="0"

                                aria-valuemax="100"

                            ></div>

                        </div>

                    </div>


                </div>


            </div>

        </div>

    `;

}


/* =========================================================
   DATE FORMAT
========================================================= */

function formatDate(dateString) {


    const date =
        new Date(
            dateString + "T00:00:00"
        );


    if (
        isNaN(
            date.getTime()
        )
    ) {

        return "Not available";

    }


    return date.toLocaleDateString(
        "en-GB",
        {
            day: "2-digit",
            month: "short",
            year: "numeric"
        }
    );

}


/* =========================================================
   HTML ESCAPE
========================================================= */

function escapeHtml(value) {

    return String(value)

        .replace(
            /&/g,
            "&amp;"
        )

        .replace(
            /</g,
            "&lt;"
        )

        .replace(
            />/g,
            "&gt;"
        )

        .replace(
            /"/g,
            "&quot;"
        )

        .replace(
            /'/g,
            "&#039;"
        );

}


/* =========================================================
   INITIAL LOAD
========================================================= */

loadTeam();


/* =========================================================
   REAL-TIME REFRESH
   Every 10 seconds
========================================================= */

setInterval(
    loadTeam,
    10000
);


</script>


</body>

</html>
```

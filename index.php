<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>AI Workforce Management System</title>

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

    <!-- Google Font -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>

        .footer-logo {
    display: inline-flex;
    align-items: center;
    text-decoration: none;
    margin-bottom: 15px;
}

.footer-logo img {
    height: 55px;
    width: auto;
}

body.home-dark-mode .footer-logo .logo-light {
    display: none;
}

body.home-dark-mode .footer-logo .logo-dark {
    display: block;
}
        /* =========================================================
   AI WORKFORCE LOGO
========================================================= */

.logo-brand {
    display: flex;
    align-items: center;
    text-decoration: none;
}

.logo-brand img {
    height: 52px;
    width: auto;
    object-fit: contain;
}

/* Light logo visible by default */
.logo-dark {
    display: none;
}

/* Dark mode logo */
body.home-dark-mode .logo-light {
    display: none;
}

body.home-dark-mode .logo-dark {
    display: block;
}

/* Remove old navbar text styling */
.navbar-brand {
    color: transparent !important;
}

@media (max-width: 768px) {

    .logo-brand img {
        height: 44px;
    }

}

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f7f9fc;
            color: #172033;
        }

        /* =========================
           NAVBAR
        ========================= */

        .navbar {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(12px);
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-weight: 800;
            color: #1769ff !important;
            font-size: 21px;
        }

        .nav-link {
            font-weight: 500;
            margin: 0 8px;
            color: #39445a !important;
        }

        .nav-link:hover {
            color: #1769ff !important;
        }

        /* =========================
           HERO
        ========================= */

        .hero {
            min-height: 90vh;
            display: flex;
            align-items: center;
            padding: 80px 0;
            background:
                radial-gradient(
                    circle at top right,
                    rgba(23,105,255,0.15),
                    transparent 35%
                ),
                linear-gradient(
                    135deg,
                    #f7f9ff,
                    #ffffff
                );
        }

        .hero h1 {
            font-size: clamp(42px, 6vw, 72px);
            font-weight: 800;
            line-height: 1.05;
        }

        .hero h1 span {
            color: #1769ff;
        }

        .hero-text {
            font-size: 19px;
            color: #647084;
            line-height: 1.8;
            margin-top: 25px;
            max-width: 650px;
        }

        .hero-buttons {
            margin-top: 35px;
        }

        .btn-main {
            background: #1769ff;
            color: white;
            border-radius: 10px;
            padding: 13px 28px;
            font-weight: 600;
            border: none;
            transition: 0.3s;
        }

        .btn-main:hover {
            background: #0b55d8;
            color: white;
            transform: translateY(-2px);
        }

        .btn-outline-main {
            border: 1px solid #1769ff;
            color: #1769ff;
            border-radius: 10px;
            padding: 12px 28px;
            font-weight: 600;
            margin-left: 10px;
            transition: 0.3s;
        }

        .btn-outline-main:hover {
            background: #1769ff;
            color: white;
        }

        .hero-image {
            width: 100%;
            border-radius: 25px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.15);
        }

        /* =========================
           STATS
        ========================= */

        .stats {
            margin-top: -40px;
            position: relative;
            z-index: 5;
        }

        .stat-card {
            background: white;
            padding: 28px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 10px 35px rgba(0,0,0,0.08);
            transition: 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-7px);
        }

        .stat-card h2 {
            color: #1769ff;
            font-weight: 800;
        }

        .stat-card p {
            margin: 0;
            color: #6c7484;
        }

        /* =========================
           SECTION
        ========================= */

        section {
            padding: 100px 0;
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: 40px;
            font-weight: 800;
        }

        .section-title p {
            color: #70798b;
            max-width: 650px;
            margin: 15px auto;
        }

        /* =========================
           FEATURES
        ========================= */

        .feature-card {
            background: white;
            border-radius: 18px;
            overflow: hidden;
            height: 100%;
            box-shadow: 0 8px 30px rgba(0,0,0,0.07);
            transition: 0.35s;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 45px rgba(0,0,0,0.12);
        }

        .feature-card img {
            width: 100%;
            height: 210px;
            object-fit: cover;
        }

        .feature-content {
            padding: 28px;
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            background: #eaf2ff;
            color: #1769ff;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 23px;
            margin-bottom: 18px;
        }

        .feature-content h4 {
            font-weight: 700;
        }

        .feature-content p {
            color: #70798b;
            line-height: 1.7;
        }

        /* =========================
           AI SECTION
        ========================= */

        .ai-section {
            background: #111c35;
            color: white;
        }

        .ai-section .section-title p {
            color: #b9c3d7;
        }

        .ai-image {
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        }

        .ai-list {
            margin-top: 30px;
        }

        .ai-item {
            display: flex;
            gap: 15px;
            margin-bottom: 22px;
        }

        .ai-item i {
            color: #4d9cff;
            font-size: 22px;
        }

        .ai-item h5 {
            margin-bottom: 5px;
        }

        .ai-item p {
            color: #b9c3d7;
            margin: 0;
        }

        /* =========================
           WORKFLOW
        ========================= */

        .step-card {
            text-align: center;
            padding: 30px 20px;
        }

        .step-number {
            width: 65px;
            height: 65px;
            background: #1769ff;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: auto;
            font-size: 24px;
            font-weight: 800;
        }

        .step-card h5 {
            margin-top: 20px;
            font-weight: 700;
        }

        .step-card p {
            color: #70798b;
        }

        /* =========================
           DASHBOARD PREVIEW
        ========================= */

        .dashboard-preview {
            background: white;
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
        }

        .dashboard-preview img {
            width: 100%;
            border-radius: 12px;
        }

        /* =========================
           CTA
        ========================= */

        .cta {
            background:
                linear-gradient(
                    135deg,
                    #1769ff,
                    #4c3cff
                );
            color: white;
            text-align: center;
            border-radius: 25px;
            padding: 80px 30px;
        }

        .cta h2 {
            font-size: 42px;
            font-weight: 800;
        }

        .cta p {
            color: #e0e7ff;
            max-width: 650px;
            margin: 20px auto 30px;
            line-height: 1.7;
        }

        .btn-white {
            background: white;
            color: #1769ff;
            padding: 14px 32px;
            border-radius: 10px;
            font-weight: 700;
            border: none;
        }

        .btn-white:hover {
            background: #f0f4ff;
            color: #0b55d8;
        }

        /* =========================
           FOOTER
        ========================= */

        footer {
            background: #0d1528;
            color: #b9c3d7;
            padding: 50px 0 25px;
        }

        footer h5 {
            color: white;
            font-weight: 700;
        }

        footer p {
            line-height: 1.7;
        }

        .footer-bottom {
            border-top: 1px solid #263149;
            margin-top: 30px;
            padding-top: 20px;
            text-align: center;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media(max-width: 768px) {

            .hero {
                text-align: center;
                padding: 60px 0;
            }

            .hero-image {
                margin-top: 50px;
            }

            .btn-outline-main {
                margin-left: 0;
                margin-top: 10px;
            }

            .section-title h2 {
                font-size: 32px;
            }

            .cta h2 {
                font-size: 32px;
            }

        }

/* =========================================================
   HOME PAGE DARK MODE BUTTON
========================================================= */

.home-dark-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    padding: 8px 14px;

    background: #f1f5f9;
    color: #172033;

    border: 1px solid #cbd5e1;
    border-radius: 8px;

    font-size: 14px;
    font-weight: 600;

    cursor: pointer;

    transition: 0.3s ease;
}

.home-dark-btn:hover {
    background: #e2e8f0;
    transform: translateY(-1px);
}


/* =========================================================
   HOME PAGE DARK MODE
========================================================= */

body.home-dark-mode {
    background: #0f172a;
    color: #e5e7eb;
}


/* NAVBAR */

body.home-dark-mode .navbar {
    background: rgba(15, 23, 42, 0.96);
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.35);
}

body.home-dark-mode .navbar-brand {
    color: #60a5fa !important;
}

body.home-dark-mode .nav-link {
    color: #cbd5e1 !important;
}

body.home-dark-mode .nav-link:hover {
    color: #60a5fa !important;
}

body.home-dark-mode .home-dark-btn {
    background: #1e293b;
    color: #f8fafc;
    border-color: #475569;
}

body.home-dark-mode .home-dark-btn:hover {
    background: #334155;
}


/* HERO */

body.home-dark-mode .hero {
    background:
        radial-gradient(
            circle at top right,
            rgba(37, 99, 235, 0.20),
            transparent 35%
        ),
        linear-gradient(
            135deg,
            #0f172a,
            #111827
        );
}

body.home-dark-mode .hero-text {
    color: #cbd5e1;
}


/* STATS */

body.home-dark-mode .stat-card {
    background: #1e293b;
    color: #f8fafc;
    box-shadow: 0 10px 35px rgba(0, 0, 0, 0.30);
}

body.home-dark-mode .stat-card p {
    color: #94a3b8;
}


/* NORMAL SECTIONS */

body.home-dark-mode section:not(.ai-section) {
    background: #0f172a;
}

body.home-dark-mode .section-title p {
    color: #94a3b8;
}


/* FEATURE CARDS */

body.home-dark-mode .feature-card {
    background: #1e293b;
    color: #f8fafc;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.30);
}

body.home-dark-mode .feature-content p {
    color: #94a3b8;
}

body.home-dark-mode .feature-icon {
    background: #172554;
    color: #60a5fa;
}


/* WORKFLOW */

body.home-dark-mode .step-card {
    color: #f8fafc;
}

body.home-dark-mode .step-card p {
    color: #94a3b8;
}


/* DASHBOARD PREVIEW */

body.home-dark-mode .dashboard-preview {
    background: #1e293b;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
}

body.home-dark-mode .dashboard-preview .stat-card {
    background: #0f172a;
}

body.home-dark-mode .dashboard-preview .bg-light {
    background: #0f172a !important;
}

body.home-dark-mode .dashboard-preview .text-muted {
    color: #94a3b8 !important;
}


/* CTA */

body.home-dark-mode .cta {
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.30);
}


/* FOOTER */

body.home-dark-mode footer {
    background: #020617;
}


/* MOBILE */

@media (max-width: 768px) {

    .home-dark-btn {
        margin-top: 8px;
    }

}
/* =========================================================
   HOME DARK MODE - REMAINING SECTIONS
========================================================= */

body.home-dark-mode {
    background: #0f172a;
    color: #e5e7eb;
}


/* Navbar */
body.home-dark-mode .navbar {
    background: rgba(15, 23, 42, 0.95);
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.35);
}

body.home-dark-mode .navbar-brand {
    color: #60a5fa !important;
}

body.home-dark-mode .nav-link {
    color: #cbd5e1 !important;
}

body.home-dark-mode .nav-link:hover {
    color: #60a5fa !important;
}


/* Hero */
body.home-dark-mode .hero {
    background:
        radial-gradient(
            circle at top right,
            rgba(37, 99, 235, 0.20),
            transparent 35%
        ),
        linear-gradient(
            135deg,
            #0f172a,
            #111827
        );
}

body.home-dark-mode .hero-text {
    color: #cbd5e1;
}


/* Stats */
body.home-dark-mode .stat-card {
    background: #1e293b;
    color: #e5e7eb;
    box-shadow: 0 10px 35px rgba(0, 0, 0, 0.30);
}

body.home-dark-mode .stat-card p {
    color: #94a3b8;
}


/* Normal sections */
body.home-dark-mode section {
    background: #0f172a;
}

body.home-dark-mode .section-title h2 {
    color: #f8fafc;
}

body.home-dark-mode .section-title p {
    color: #94a3b8;
}


/* Feature cards */
body.home-dark-mode .feature-card {
    background: #1e293b;
    color: #e5e7eb;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.30);
}

body.home-dark-mode .feature-content h4 {
    color: #f8fafc;
}

body.home-dark-mode .feature-content p {
    color: #94a3b8;
}

body.home-dark-mode .feature-icon {
    background: #263b63;
    color: #60a5fa;
}


/* Dashboard preview section
   This fixes the white area in your screenshot */
body.home-dark-mode section.bg-light {
    background: #111827 !important;
}

body.home-dark-mode .dashboard-preview {
    background: #1e293b;
    color: #e5e7eb;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
}


/* Dashboard inner AI recommendation box */
body.home-dark-mode .dashboard-preview .bg-light {
    background: #0f172a !important;
}

body.home-dark-mode .dashboard-preview .text-muted {
    color: #94a3b8 !important;
}


/* Dashboard numbers */
body.home-dark-mode .dashboard-preview .stat-card h2 {
    color: #60a5fa;
}


/* Workflow */
body.home-dark-mode .step-card h5 {
    color: #f8fafc;
}

body.home-dark-mode .step-card p {
    color: #94a3b8;
}


/* Keep AI section dark */
body.home-dark-mode .ai-section {
    background: #0b1224;
}


/* CTA */
body.home-dark-mode .cta {
    background:
        linear-gradient(
            135deg,
            #1769ff,
            #4c3cff
        );
}


/* Footer */
body.home-dark-mode footer {
    background: #080d1a;
}


/* Bootstrap text helpers */
body.home-dark-mode .text-muted {
    color: #94a3b8 !important;
}


/* Smooth transition */
body,
.navbar,
.hero,
section,
.stat-card,
.feature-card,
.dashboard-preview {
    transition:
        background 0.3s ease,
        color 0.3s ease,
        box-shadow 0.3s ease;
}
    </style>

</head>


<body>



<!-- =========================
     NAVBAR
========================= -->

<nav class="navbar navbar-expand-lg">

    <div class="container">

       <a class="navbar-brand logo-brand" href="#home">

    <img
        src="assets/images/logo/logo-horizontal-light.png"
        class="logo-light"
        alt="AI Workforce Management System"
    >

    <img
        src="assets/images/logo/logo-horizontal-dark.png"
        class="logo-dark"
        alt="AI Workforce Management System"
    >

</a>
        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div
            class="collapse navbar-collapse"
            id="navbarNav"
        >

            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a class="nav-link" href="#home">
                        Home
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#features">
                        Features
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#ai">
                        AI System
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#workflow">
                        How It Works
                    </a>
                <li class="nav-item ms-lg-2">
   <button
    type="button"
    class="home-dark-btn"
    id="homeDarkButton">
    🌙 Dark
</button>
</li>

<li class="nav-item ms-lg-2">

    <a
        href="authentication/login.php"
        class="btn btn-primary px-4"
    >
        Login
    </a>

</li>

            </ul>

        </div>

    </div>

</nav>


<!-- =========================
     HERO
========================= -->

<section
    class="hero"
    id="home"
>

    <div class="container">

        <div class="row align-items-center g-5">

            <div class="col-lg-6">

                <span class="badge text-bg-primary mb-3 px-3 py-2">
    AI-Powered Workforce Platform
</span>

                <h1>
                    Manage Your
                    <span>Workforce</span>
                    Smarter.
                </h1>

                <p class="hero-text">

                    A smart workforce management system
                     designed to manage employees, projects and tasks,
                      featuring intelligent AI-powered analysis that 
                      evaluates performance, workload and department
                       alignment to deliver data-driven employee recommendations 
                       for task assignments.

                </p>

                <div class="hero-buttons">

                    <a
                        href="authentication/login.php"
                        class="btn btn-main"
                    >
                        Get Started
                        <i class="bi bi-arrow-right"></i>
                    </a>

                    <a
                        href="#features"
                        class="btn btn-outline-main"
                    >
                        Explore Features
                    </a>

                </div>

            </div>


            <div class="col-lg-6">

                <img
                    src="https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1200&q=80"
                    class="hero-image"
                    alt="Workforce Team"
                >

            </div>

        </div>

    </div>

</section>


<!-- =========================
     STATS
========================= -->

<div class="container stats">

    <div class="row g-4">

        <div class="col-md-3">

            <div class="stat-card">

                <h2>
                    <i class="bi bi-people"></i>
                </h2>

                <p>Employee Management</p>

            </div>

        </div>


        <div class="col-md-3">

            <div class="stat-card">

                <h2>
                    <i class="bi bi-kanban"></i>
                </h2>

                <p>Project Management</p>

            </div>

        </div>


        <div class="col-md-3">

            <div class="stat-card">

                <h2>
                    <i class="bi bi-list-check"></i>
                </h2>

                <p>Task Management</p>

            </div>

        </div>


        <div class="col-md-3">

            <div class="stat-card">

                <h2>
                    <i class="bi bi-graph-up-arrow"></i>
                </h2>

                <p>Performance Analytics</p>

            </div>

        </div>

    </div>

</div>


<!-- =========================
     FEATURES
========================= -->

<section id="features">

    <div class="container">

        <div class="section-title">

            <h2>
                Powerful Workforce Features
            </h2>

            <p>
                Everything required to manage employees,
                tasks and workforce productivity in one system.
            </p>

        </div>


        <div class="row g-4">


            <!-- FEATURE 1 -->

            <div class="col-lg-4">

                <div class="feature-card">

                    <img
                        src="https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&w=900&q=80"
                        alt="Employee Management"
                    >

                    <div class="feature-content">

                        <div class="feature-icon">

                            <i class="bi bi-people-fill"></i>

                        </div>

                        <h4>
                            Employee Management
                        </h4>

                        <p>
                            Manage employee information,
                            departments, designations and
                            workforce records efficiently.
                        </p>

                    </div>

                </div>

            </div>


            <!-- FEATURE 2 -->

            <div class="col-lg-4">

                <div class="feature-card">

                    <img
                        src="https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=900&q=80"
                        alt="Task Management"
                    >

                    <div class="feature-content">

                        <div class="feature-icon">

                            <i class="bi bi-kanban-fill"></i>

                        </div>

                        <h4>
                            Smart Task Management
                        </h4>

                        <p>
                            Create tasks, assign employees,
                            set priorities and track task
                            status from one place.
                        </p>

                    </div>

                </div>

            </div>


            <!-- FEATURE 3 -->

            <div class="col-lg-4">

                <div class="feature-card">

                    <img
                        src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=900&q=80"
                        alt="Analytics"
                    >

                    <div class="feature-content">

                        <div class="feature-icon">

                            <i class="bi bi-bar-chart-fill"></i>

                        </div>

                        <h4>
                            Performance Analytics
                        </h4>

                        <p>
                            Monitor completed tasks,
                            pending workload and employee
                            performance.
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =========================
     AI SECTION
========================= -->

<section
    class="ai-section"
    id="ai"
>

    <div class="container">

        <div class="section-title">

            <h2>
                Intelligence Behind The Workforce
            </h2>

            <p>
                AI helps identify suitable employees based
                on productivity, workload and organizational
                requirements.
            </p>

        </div>


        <div class="row align-items-center g-5">

            <div class="col-lg-6">

                <img
                    src="https://images.unsplash.com/photo-1677442136019-21780ecad995?auto=format&fit=crop&w=1200&q=80"
                    class="ai-image"
                    alt="Artificial Intelligence"
                >

            </div>


            <div class="col-lg-6">

                <h3 class="fw-bold">
                    AI Employee Recommendation
                </h3>

                <p class="text-light opacity-75 mt-3">

                    The system analyzes employee information
                    and task performance to recommend suitable
                    employees for assignments.

                </p>


                <div class="ai-list">


                    <div class="ai-item">

                        <i class="bi bi-check-circle-fill"></i>

                        <div>

                            <h5>
                                Performance Analysis
                            </h5>

                            <p>
                                Evaluates task completion
                                and employee productivity.
                            </p>

                        </div>

                    </div>


                    <div class="ai-item">

                        <i class="bi bi-check-circle-fill"></i>

                        <div>

                            <h5>
                                Workload Analysis
                            </h5>

                            <p>
                                Considers pending tasks
                                before recommending an employee.
                            </p>

                        </div>

                    </div>


                    <div class="ai-item">

                        <i class="bi bi-check-circle-fill"></i>

                        <div>

                            <h5>
                                Department Matching
                            </h5>

                            <p>
                                Matches employees according
                                to department and designation.
                            </p>

                        </div>

                    </div>


                    <div class="ai-item">

                        <i class="bi bi-check-circle-fill"></i>

                        <div>

                            <h5>
                                AI Recommendation Score
                            </h5>

                            <p>
                                Combines multiple factors to
                                identify a suitable candidate.
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =========================
     WORKFLOW
========================= -->

<section id="workflow">

    <div class="container">

        <div class="section-title">

            <h2>
                How The System Works
            </h2>

            <p>
                A simple workflow connecting managers,
                employees and AI-powered analytics.
            </p>

        </div>


        <div class="row g-4">


            <div class="col-md-3">

                <div class="step-card">

                    <div class="step-number">
                        1
                    </div>

                    <h5>
                        Create Project
                    </h5>

                    <p>
                        Manager creates and manages projects.
                    </p>

                </div>

            </div>


            <div class="col-md-3">

                <div class="step-card">

                    <div class="step-number">
                        2
                    </div>

                    <h5>
                        Assign Task
                    </h5>

                    <p>
                        Tasks are assigned to suitable employees.
                    </p>

                </div>

            </div>


            <div class="col-md-3">

                <div class="step-card">

                    <div class="step-number">
                        3
                    </div>

                    <h5>
                        Track Progress
                    </h5>

                    <p>
                        Employee completes and updates tasks.
                    </p>

                </div>

            </div>


            <div class="col-md-3">

                <div class="step-card">

                    <div class="step-number">
                        4
                    </div>

                    <h5>
                        Analyze
                    </h5>

                    <p>
                        System generates productivity insights.
                    </p>

                </div>

            </div>


        </div>

    </div>

</section>


<!-- =========================
     DASHBOARD PREVIEW
========================= -->

<section class="bg-light">

    <div class="container">

        <div class="section-title">

            <h2>
                Powerful Dashboard Analytics
            </h2>

            <p>
                Managers can monitor workforce performance
                through a centralized dashboard.
            </p>

        </div>


        <div class="dashboard-preview">

            <div class="row g-3">


                <div class="col-md-3">

                    <div class="stat-card">

                        <h2>24</h2>

                        <p>
                            Employees
                        </p>

                    </div>

                </div>


                <div class="col-md-3">

                    <div class="stat-card">

                        <h2 class="text-success">
                            48
                        </h2>

                        <p>
                            Active Tasks
                        </p>

                    </div>

                </div>


                <div class="col-md-3">

                    <div class="stat-card">

                        <h2 class="text-warning">
                            156
                        </h2>

                        <p>
                            Completed Tasks
                        </p>

                    </div>

                </div>


                <div class="col-md-3">

                    <div class="stat-card">

                        <h2 class="text-danger">
                            87%
                        </h2>

                        <p>
                            Avg Performance
                        </p>

                    </div>

                </div>

            </div>


            <div class="mt-4 p-4 bg-light rounded">

                <h5 class="fw-bold">
                    🤖 AI Recommended Employee
                </h5>

                <p class="text-muted">
                    Employee recommendation based on
                    performance, workload and department.
                </p>

                <div class="progress"
                     style="height:12px;">

                    <div
                        class="progress-bar bg-success"
                        style="width:87%;"
                    ></div>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =========================
     CTA
========================= -->

<section>

    <div class="container">

        <div class="cta">

            <h2>
                Ready to Manage Your Workforce Smarter?
            </h2>

            <p>
                Access the secure workforce management
                platform and start managing employees,
                projects and tasks efficiently.
            </p>

            <a
                href="authentication/login.php"
                class="btn btn-white"
            >
                🔐 Login to Workspace
            </a>

        </div>

    </div>

</section>


<!-- =========================
     FOOTER
========================= -->

<footer>

    <div class="container">

        <div class="row g-4">


            <div class="col-lg-6">

                <a href="#home" class="footer-logo">

    <img
        src="assets/images/logo/logo-horizontal-light.png"
        class="logo-light"
        alt="AI Workforce Management System"
    >

    <img
        src="assets/images/logo/logo-horizontal-dark.png"
        class="logo-dark"
        alt="AI Workforce Management System"
    >

</a>

                <p class="mt-3">

                    An AI-driven workforce management
                    platform for employee management,
                    task assignment and productivity analysis.

                </p>

            </div>


            <div class="col-lg-3">

                <h5>
                    Platform
                </h5>

                <p class="mt-3 mb-1">
                    Employee Management
                </p>

                <p class="mb-1">
                    Task Management
                </p>

                <p class="mb-1">
                    AI Recommendations
                </p>

                <p>
                    Performance Analytics
                </p>

            </div>


            <div class="col-lg-3">

                <h5>
                    Access
                </h5>

                <p class="mt-3">

                    <a
                        href="authentication/login.php"
                        class="text-decoration-none text-light"
                    >
                        🔐 Login
                    </a>

                </p>

            </div>

        </div>


        <div class="footer-bottom">

            <p class="mb-0">

                © 2026 AI Workforce Management System
                |
                Developed by
                <strong>Pratiksha Kanadje</strong>

            </p>

        </div>

    </div>

</footer>


<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js">
</script>


<!-- HOME PAGE DARK MODE -->

<script>

document.addEventListener("DOMContentLoaded", function () {

    const darkButton = document.getElementById("homeDarkButton");

    if (!darkButton) {
        console.log("Dark button not found");
        return;
    }


    /* LOAD SAVED MODE */

    if (localStorage.getItem("homeDarkMode") === "enabled") {

        document.body.classList.add("home-dark-mode");

        darkButton.innerHTML = "☀️ Light";

    }


    /* DARK MODE BUTTON */

    darkButton.addEventListener("click", function () {

        document.body.classList.toggle("home-dark-mode");


        if (document.body.classList.contains("home-dark-mode")) {

            localStorage.setItem(
                "homeDarkMode",
                "enabled"
            );

            darkButton.innerHTML = "☀️ Light";

        } else {

            localStorage.setItem(
                "homeDarkMode",
                "disabled"
            );

            darkButton.innerHTML = "🌙 Dark";

        }

    });

});


</script>



</body>

</html>
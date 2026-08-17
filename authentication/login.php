
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login | AI Workforce Management System</title>

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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;

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

            color: #172033;

            transition:
                background 0.3s ease,
                color 0.3s ease;
        }


        /* =====================================================
           LOGIN PAGE
        ===================================================== */

        .login-page {
            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 30px 15px;
        }


        .login-wrapper {
            width: 100%;
            max-width: 450px;
        }


        /* =====================================================
           LOGIN CARD
        ===================================================== */

        .login-card {
            background: rgba(255,255,255,0.96);

            border: 1px solid #e5eaf2;

            border-radius: 20px;

            padding: 38px;

            box-shadow:
                0 25px 60px rgba(15,23,42,0.12);

            backdrop-filter: blur(12px);

            transition:
                background 0.3s ease,
                border 0.3s ease,
                box-shadow 0.3s ease;
        }


        .login-title {
            text-align: center;
            margin-bottom: 8px;

            font-size: 30px;
            font-weight: 800;

            color: #172033;
        }


        .login-subtitle {
            text-align: center;

            color: #70798b;

            font-size: 14px;

            line-height: 1.6;

            margin-bottom: 30px;
        }


        /* =====================================================
           FORM
        ===================================================== */

        .form-label {
            font-size: 14px;
            font-weight: 600;

            color: #39445a;

            margin-bottom: 8px;
        }


        .input-group-custom {
            position: relative;
        }


        .form-control {
            height: 50px;

            border-radius: 10px;

            border: 1px solid #d9e0eb;

            padding-left: 45px;

            font-size: 14px;

            color: #172033;

            box-shadow: none;

            transition: 0.3s;
        }


        .form-control:focus {
            border-color: #1769ff;

            box-shadow:
                0 0 0 3px rgba(23,105,255,0.10);
        }


        .input-icon {
            position: absolute;

            left: 15px;
            top: 50%;

            transform: translateY(-50%);

            color: #7b8799;

            font-size: 17px;

            z-index: 5;
        }


        /* =====================================================
           PASSWORD BUTTON
        ===================================================== */

        .password-toggle {
            position: absolute;

            right: 14px;
            top: 50%;

            transform: translateY(-50%);

            border: none;

            background: transparent;

            color: #7b8799;

            cursor: pointer;

            z-index: 5;
        }


        .password-toggle:hover {
            color: #1769ff;
        }


        /* =====================================================
           LOGIN BUTTON
        ===================================================== */

        .login-btn {
            width: 100%;

            height: 50px;

            border: none;

            border-radius: 10px;

            background: #1769ff;

            color: white;

            font-size: 15px;

            font-weight: 700;

            transition: 0.3s;
        }


        .login-btn:hover {
            background: #0b55d8;

            transform: translateY(-2px);

            box-shadow:
                0 10px 25px rgba(23,105,255,0.25);
        }


        /* =====================================================
           BACK TO HOME
        ===================================================== */

        .back-home {
            display: block;

            text-align: center;

            margin-top: 22px;

            color: #1769ff;

            text-decoration: none;

            font-size: 14px;

            font-weight: 600;
        }


        .back-home:hover {
            color: #0b55d8;
        }


        /* =====================================================
           FOOTER
        ===================================================== */

        .login-footer {
            text-align: center;

            margin-top: 25px;

            color: #8993a4;

            font-size: 12px;
        }


        /* =====================================================
           LOGIN DARK MODE
        ===================================================== */

        body.admin-dark-mode {

            background:
                radial-gradient(
                    circle at top right,
                    rgba(37,99,235,0.20),
                    transparent 35%
                ),
                linear-gradient(
                    135deg,
                    #0f172a,
                    #111827
                );

            color: #e5e7eb;
        }


        body.admin-dark-mode .login-card {

            background: rgba(30,41,59,0.96);

            border-color: #334155;

            box-shadow:
                0 25px 60px rgba(0,0,0,0.40);
        }


        body.admin-dark-mode .login-title {
            color: #f8fafc;
        }


        body.admin-dark-mode .login-subtitle {
            color: #94a3b8;
        }


        body.admin-dark-mode .form-label {
            color: #cbd5e1;
        }


        body.admin-dark-mode .form-control {

            background: #0f172a;

            border-color: #475569;

            color: #f8fafc;
        }


        body.admin-dark-mode .form-control::placeholder {
            color: #64748b;
        }


        body.admin-dark-mode .input-icon {
            color: #94a3b8;
        }


        body.admin-dark-mode .password-toggle {
            color: #94a3b8;
        }


        body.admin-dark-mode .password-toggle:hover {
            color: #60a5fa;
        }


        body.admin-dark-mode .back-home {
            color: #60a5fa;
        }


        body.admin-dark-mode .login-footer {
            color: #64748b;
        }


        /* =====================================================
           MOBILE
        ===================================================== */

        @media (max-width: 576px) {

            .login-card {
                padding: 28px 22px;
            }

            .login-title {
                font-size: 26px;
            }

        }

        .login-error {
    margin-top: 10px;

    padding: 9px 12px;

    border-radius: 8px;

    background: rgba(239, 68, 68, 0.08);

    border: 1px solid rgba(239, 68, 68, 0.20);

    color: #dc2626;

    font-size: 13px;

    font-weight: 600;

    display: flex;

    align-items: center;

    gap: 7px;
}
body.admin-dark-mode .login-error {
    background: rgba(239, 68, 68, 0.12);

    border-color: rgba(248, 113, 113, 0.25);

    color: #f87171;
}
    </style>

</head>


<body>


<?php include "../config/page_actions.php"; ?>


<!-- =====================================================
     LOGIN PAGE
===================================================== -->

<div class="login-page">

    <div class="login-wrapper">


        <!-- LOGIN CARD -->

        <div class="login-card">


            <h1 class="login-title">
                Welcome Back
            </h1>


            <p class="login-subtitle">
                Sign in to access your AI Workforce Management System.
            </p>


            <!-- LOGIN FORM -->

            <form
                action="login_process.php"
                method="POST"
            >


                <!-- EMAIL -->

                <div class="mb-4">

                    <label
                        for="email"
                        class="form-label"
                    >
                        Email Address
                    </label>


                    <div class="input-group-custom">

                        <i class="bi bi-envelope input-icon"></i>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            placeholder="Enter your email"
                            autocomplete="email"
                            required
                        >

                    </div>

                <div class="mb-4">

    <label
        for="password"
        class="form-label"
    >
        Password
    </label>

    <div class="input-group-custom">

        <i class="bi bi-lock input-icon"></i>

        <input
            type="password"
            id="password"
            name="password"
            class="form-control"
            placeholder="Enter your password"
            autocomplete="current-password"
            required
        >

        <button
            type="button"
            class="password-toggle"
            id="passwordToggle"
            aria-label="Show password"
        >
            <i class="bi bi-eye"></i>
        </button>

    </div>


    <?php

    $login_error = $_GET['error'] ?? '';

    if ($login_error === 'invalid') {

        echo '
        <div class="login-error">
            <i class="bi bi-exclamation-circle"></i>
            Invalid email or password.
        </div>';

    }

    elseif ($login_error === 'empty') {

        echo '
        <div class="login-error">
            <i class="bi bi-exclamation-circle"></i>
            Please enter your email and password.
        </div>';

    }

    elseif ($login_error === 'inactive') {

        echo '
        <div class="login-error">
            <i class="bi bi-exclamation-circle"></i>
            Your account is inactive. Please contact the administrator.
        </div>';

    }

    elseif ($login_error === 'role') {

        echo '
        <div class="login-error">
            <i class="bi bi-exclamation-circle"></i>
            Invalid user role.
        </div>';

    }

    ?>

</div>

                </div>


                <!-- LOGIN -->

                <button
                    type="submit"
                    class="login-btn"
                >
                    Login
                    <i class="bi bi-arrow-right ms-1"></i>
                </button>


            </form>


            <!-- BACK TO HOME -->

            <a
                href="../index.php"
                class="back-home"
            >
                <i class="bi bi-arrow-left"></i>
                Back to Home
            </a>


        </div>


        <!-- FOOTER -->

        <div class="login-footer">

            © 2026 AI Workforce Management System

        </div>


    </div>

</div>


<!-- =====================================================
     JAVASCRIPT
===================================================== -->

<script>

document.addEventListener("DOMContentLoaded", function () {

    /* =====================================================
       PASSWORD SHOW / HIDE
    ===================================================== */

    const passwordInput =
        document.getElementById("password");

    const passwordToggle =
        document.getElementById("passwordToggle");


    if (passwordInput && passwordToggle) {

        passwordToggle.addEventListener(
            "click",
            function () {

                if (passwordInput.type === "password") {

                    passwordInput.type = "text";

                    passwordToggle.innerHTML =
                        '<i class="bi bi-eye-slash"></i>';

                    passwordToggle.setAttribute(
                        "aria-label",
                        "Hide password"
                    );

                } else {

                    passwordInput.type = "password";

                    passwordToggle.innerHTML =
                        '<i class="bi bi-eye"></i>';

                    passwordToggle.setAttribute(
                        "aria-label",
                        "Show password"
                    );

                }

            }
        );

    }

});

</script>


</body>

</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ugay's Elegant | Login</title>

    <link rel="stylesheet" href="assets/css/login.css">

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet">
</head>

<body>

    <div class="login-page">

        <!-- Left Branding Section -->
        <div class="brand-section">

            <div class="brand-content">

                <div class="logo-icon">
                    <span>✂</span>
                </div>

                <h1>Ugay's <span>Elegant</span></h1>

                <p class="tagline">
                    Tailoring & Rental Shop
                </p>

                <div class="brand-line"></div>

                <p class="brand-description">
                    Where every stitch tells a story
                    and every outfit makes a statement.
                </p>

            </div>

            <div class="decoration decoration-one">✦</div>
            <div class="decoration decoration-two">♡</div>
            <div class="decoration decoration-three">✧</div>

        </div>


        <!-- Right Login Section -->
        <div class="login-section">

            <div class="login-container">

                <div class="mobile-logo">
                    <div class="mobile-logo-icon">✂</div>
                    <h2>Ugay's <span>Elegant</span></h2>
                    <p>Tailoring & Rental Shop</p>
                </div>

                <div class="welcome-text">
                    <p class="small-title">WELCOME BACK</p>

                    <h2>Sign in to your account</h2>

                    <p>
                        Enter your credentials to access the
                        management system.
                    </p>
                </div>


                <form action="dashboard.php" method="POST">

                    <!-- Username -->
                    <div class="form-group">

                        <label for="username">
                            Username
                        </label>

                        <div class="input-wrapper">

                            <span class="input-icon">👤</span>

                            <input
                                type="text"
                                id="username"
                                name="username"
                                placeholder="Enter your username"
                                required
                            >

                        </div>

                    </div>


                    <!-- Password -->
                    <div class="form-group">

                        <div class="password-label">

                            <label for="password">
                                Password
                            </label>

                            <a href="#">
                                Forgot password?
                            </a>

                        </div>

                        <div class="input-wrapper">

                            <span class="input-icon">🔒</span>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Enter your password"
                                required
                            >

                            <button
                                type="button"
                                class="show-password"
                                onclick="togglePassword()"
                            >
                                👁
                            </button>

                        </div>

                    </div>


                    <!-- Remember Me -->
                    <div class="remember-row">

                        <label class="remember">

                            <input
                                type="checkbox"
                                name="remember"
                            >

                            <span>Remember me</span>

                        </label>

                    </div>


                    <!-- Login Button -->
                    <button
                        type="submit"
                        class="login-button"
                    >
                        <span>LOGIN</span>
                        <span class="arrow">→</span>
                    </button>

                </form>


                <div class="login-footer">

                    <p>
                        © 2026 Ugay's Elegant
                    </p>

                    <span>
                        Tailoring & Rental Shop
                    </span>

                </div>

            </div>

        </div>

    </div>


    <script>

        function togglePassword() {

            const password =
                document.getElementById("password");

            if (password.type === "password") {

                password.type = "text";

            } else {

                password.type = "password";

            }

        }

    </script>

</body>
</html>
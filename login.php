<!DOCTYPE html>
<html>
<head>
    <title>Login - Neptune</title>
    <link rel="stylesheet" href="assets/css/neptune.css">
</head>
<body>

<div class="neptune-page">
    <div class="neptune-shell">

        <img src="assets/images/darealneptune.png" class="neptune-bg" alt="Neptune">

        <nav class="neptune-nav">
            <div class="neptune-brand">NEPTUNE</div>
            <a href="register.php" class="neptune-nav-btn">Register</a>
        </nav>

        <main class="neptune-login-content">

            <section>
                <h1 class="neptune-hero-title neptune-animate">
                    Welcome to<br>
                    Neptune.
                </h1>
            </section>

            <section>
                <div class="neptune-login-card neptune-animate">
                    <h1>Account Login</h1>

                    <form class="neptune-form" action="actions/login_action.php" method="POST">

                        <input class="neptune-input" type="email" name="email" placeholder="Email" required>

                        <input class="neptune-input" type="password" name="password" placeholder="Password" required>

                        <button class="neptune-submit" type="submit">Proceed</button>

                    </form>
                </div>

                <a href="#" class="neptune-forgot">Forgot Password?</a>
            </section>

        </main>

    </div>
</div>
<script>
window.addEventListener("load", () => {
    document.body.classList.add("loaded");
});
</script>
</body>
</html>
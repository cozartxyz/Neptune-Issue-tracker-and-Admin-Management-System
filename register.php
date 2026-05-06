<!DOCTYPE html>
<html>
<head>
    <title>Register - Neptune</title>
    <link rel="stylesheet" href="assets/css/neptune.css">
</head>
<body>

<div class="neptune-page">
    <div class="neptune-shell">

        <img src="assets/images/darealneptune.png" class="neptune-bg" alt="Neptune">

        <nav class="neptune-nav">
            <div class="neptune-brand">NEPTUNE</div>
            <a href="login.php" class="neptune-nav-btn">Login</a>
        </nav>

        <main class="neptune-content">

            <section class="neptune-about neptune-animate">
                <h2>🌐 About Neptune?</h2>
                <p>
                    Neptune is a secure internal issue tracking and account recovery platform
                    designed for controlled user requests, admin approval workflows and protected authentication.
                </p>
            </section>

            <section class="neptune-card neptune-animate">
                <h1>Register</h1>

                <form class="neptune-form" action="actions/register_action.php" method="POST">

                    <input class="neptune-input" type="text" name="employee_no" placeholder="Employee Number" required>

                    <input class="neptune-input" type="text" name="first_name" placeholder="First Name" required>

                    <input class="neptune-input" type="text" name="surname" placeholder="Surname" required>

                    <input class="neptune-input" type="email" name="email" placeholder="Email" required>

                    <input class="neptune-input" type="text" name="department" placeholder="Department" required>

                    <input class="neptune-input" type="password" name="password" placeholder="Password" required>

                    <button class="neptune-submit" type="submit">Register</button>

                </form>
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
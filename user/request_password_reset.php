<?php
require_once "../includes/auth.php";
requireUserOrAdmin();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Request Password Reset</title>
</head>
<body>

<h2>Request Password Reset</h2>
<p><a href="dashboard.php">Back to Dashboard</a></p>

<form action="../actions/create_issue.php" method="POST">
    <input type="hidden" name="request_type" value="password_reset">

    <label>Title:</label>
    <input type="text" name="title" value="Password Reset Request">
    <br><br>

    <button type="submit">Submit Password Reset Request</button>
</form>
<script>
window.addEventListener("load", () => {
    document.body.classList.add("loaded");
});
</script>
</body>
</html>
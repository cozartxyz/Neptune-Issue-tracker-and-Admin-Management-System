<?php
require_once "../includes/auth.php";
requireUserOrAdmin();
?>

<!DOCTYPE html>
<html>
<head>
<title>Create Request</title>

<script>
function toggleDescriptionField() {
    const requestType = document.getElementById("request_type").value;
    const descriptionWrapper = document.getElementById("description_wrapper");

    if (requestType === "other") {
        descriptionWrapper.style.display = "block";
    } else {
        descriptionWrapper.style.display = "none";
    }
}
</script>

</head>
<body>

<h2>Create Request</h2>
<p><a href="dashboard.php">Back to Dashboard</a></p>

<form action="../actions/create_issue.php" method="POST">

<label>Request Type:</label>
<select name="request_type" id="request_type" onchange="toggleDescriptionField()" required>
<option value="">Select Request Type</option>
<option value="email_reset">Email Reset</option>
<option value="password_reset">Password Reset</option>
<option value="other">Other</option>
</select>

<br><br>

<label>Title:</label>
<input type="text" name="title" required>

<br><br>

<label>Category:</label>
<select name="category_id" required>
<option value="">Select Category</option>
<option value="1">Software</option>
<option value="2">New Request</option>
<option value="3">Hardware Fault</option>
<option value="4">Other</option>
</select>

<br><br>

<div id="description_wrapper" style="display:none;">
<label>Description:</label>
<textarea name="description" rows="6" cols="50"></textarea>
</div>

<br><br>

<button type="submit">Submit Request</button>

</form>

<script>
toggleDescriptionField();
</script>
<script>
window.addEventListener("load", () => {
    document.body.classList.add("loaded");
});
</script>
</body>
</html>
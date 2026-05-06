<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/includes/functions.php";

$token = $_GET["token"] ?? "";

if ($token === "") {
    neptuneMessage("Invalid reset link.");
}

$stmt = $pdo->prepare("
    SELECT issues.*, users.first_name, users.surname
    FROM issues
    INNER JOIN users ON issues.created_by = users.id
    WHERE issues.reset_token = ?
    AND issues.request_type = 'password_reset'
    AND issues.status = 'Approved'
    AND issues.reset_token_expires_at > NOW()
    LIMIT 1
");

$stmt->execute([$token]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    neptuneMessage("Invalid or expired reset link.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reset Password - Neptune</title>

<link rel="stylesheet" href="assets/css/neptune.css">

<style>
.reset-content {
    position: relative;
    z-index: 3;
    min-height: calc(100vh - 70px);
    display: flex;
    align-items: center;
    justify-content: center;
}

.reset-card {
    width: 430px;
    padding: 28px;
    border-radius: 24px;
    background: rgba(7,11,19,.46);
    border: 1px solid rgba(255,255,255,.14);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    box-shadow:
        0 18px 40px rgba(0,0,0,.45),
        inset 0 0 16px rgba(255,255,255,.02);
}

.reset-card h1 {
    font-size: 22px;
    font-weight: 300;
    color: rgba(255,255,255,.86);
    margin-bottom: 12px;
}

.reset-card p {
    font-size: 14px;
    font-weight: 300;
    color: rgba(255,255,255,.66);
    margin-bottom: 22px;
}

.reset-form {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.reset-input {
    width: 100%;
    height: 42px;
    background: rgba(255,255,255,.02);
    border: 1px solid rgba(255,255,255,.16);
    border-radius: 4px;
    padding: 0 12px;
    color: white;
    font-size: 14px;
    font-weight: 300;
    outline: none;
}

.reset-input::placeholder {
    color: rgba(255,255,255,.66);
}

.reset-input:focus {
    border-color: rgba(22,119,255,.7);
    box-shadow: 0 0 10px rgba(22,119,255,.18);
}

.reset-submit {
    margin-top: 4px;
    height: 36px;
    border: none;
    border-radius: 8px;
    background: linear-gradient(90deg,#001fff,#0012d8);
    color: white;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 0 14px rgba(0,42,255,.24);
}

.reset-submit:hover {
    filter: brightness(1.08);
}
</style>
</head>

<body>

<div class="neptune-page">
<div class="neptune-shell">

<img src="assets/images/darealneptune.png" class="neptune-bg" alt="Neptune">

<div class="neptune-nav">
    <div class="neptune-brand">NEPTUNE</div>
</div>

<div class="reset-card neptune-animate">

    <div class="reset-card">
        <h1>Reset Password</h1>

        <p>
            Hello <?php echo htmlspecialchars($request["first_name"] . " " . $request["surname"]); ?>,
            enter a new password below.
        </p>

        <form class="reset-form" action="actions/process_password_reset.php" method="POST">

            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

            <input 
                type="password" 
                name="new_password" 
                class="reset-input" 
                placeholder="New Password"
                required
            >

            <button type="submit" class="reset-submit">
                Update Password
            </button>

        </form>
    </div>

</div>

</div>
</div>
<script>
window.addEventListener("load", () => {
    document.body.classList.add("loaded");
});
</script>
</body>
</html>
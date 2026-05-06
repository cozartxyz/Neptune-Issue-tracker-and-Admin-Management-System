<?php
require_once "../includes/auth.php";
requireAdmin();

require_once "../config/db.php";

$emailStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM issues 
    WHERE request_type = 'email_reset'
    AND status = 'Pending'
");
$emailStmt->execute();
$emailCount = (int)$emailStmt->fetchColumn();

$passwordStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM issues 
    WHERE request_type = 'password_reset'
    AND status = 'Pending'
");
$passwordStmt->execute();
$passwordCount = (int)$passwordStmt->fetchColumn();
$otherStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM issues 
    WHERE request_type = 'other'
    AND status NOT IN ('Resolved', 'Closed', 'Completed', 'Rejected')
");
$otherStmt->execute();
$otherCount = (int)$otherStmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>

<link rel="stylesheet" href="../assets/css/neptune.css">

<style>
.admin-content {
    position: relative;
    z-index: 3;
    min-height: calc(100vh - 70px);
    padding: 40px 80px;
}

.admin-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    align-items: center;
    gap: 80px;
}

.admin-panel {
    width: 390px;
    min-height: 410px;
    padding: 28px;
    border-radius: 24px;
    background: rgba(7,11,19,.42);
    border: 1px solid rgba(255,255,255,.12);
    box-shadow:
        0 18px 40px rgba(0,0,0,.45),
        inset 0 0 16px rgba(255,255,255,.02);
}

.admin-panel h2 {
    font-size: 18px;
    font-weight: 300;
    margin-bottom: 34px;
    color: rgba(255,255,255,.88);
}

.admin-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 26px;
}

.admin-label {
    font-size: 18px;
    font-weight: 300;
}

.email-label {
    color: rgba(255,255,255,.78);
}

.password-label {
    color: #f0b000;
}

.other-label {
    color: #76ff03;
}

.admin-count {
    width: 48px;
    height: 54px;
    border-radius: 10px;
    border: 1px solid rgba(255,255,255,.18);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    font-weight: 300;
    color: white;
}

.admin-right {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 34px;
}

.clock-card {
    width: 350px;
    height: 180px;
    border-radius: 24px;
    background: rgba(7,11,19,.40);
    border: 1px solid rgba(255,255,255,.14);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
}

.clock-time {
    font-size: 36px;
    font-weight: 200;
    color: rgba(255,255,255,.95);
}

.info-card {
    width: 410px;
    padding: 24px;
    border-radius: 24px;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.12);
    backdrop-filter: blur(8px);
}

.info-card h3 {
    font-size: 18px;
    font-weight: 300;
    margin-bottom: 12px;
}

.info-card p {
    font-size: 14px;
    line-height: 1.7;
    font-weight: 300;
    color: rgba(255,255,255,.72);
}

.bottom-dock {
    position: absolute;
    left: 50%;
    bottom: 20px;
    transform: translateX(-50%);
    z-index: 4;

    min-width: 330px;
    height: 58px;
    padding: 0 28px;
    border-radius: 999px;

    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.12);
    backdrop-filter: blur(10px);

    display: flex;
    align-items: center;
    justify-content: center;
    gap: 30px;
}

.dock-link {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    color: white;
    font-size: 22px;
}

.dock-link:hover {
    background: rgba(255,255,255,.10);
}
</style>
</head>

<body>

<div class="neptune-page">
<div class="neptune-shell">

<img src="../assets/images/darealneptune.png" class="neptune-bg" alt="Neptune">

<div class="neptune-nav">
    <div class="neptune-brand">NEPTUNE</div>
    <a href="../actions/logout.php" class="neptune-nav-btn">Logout</a>
</div>

<div class="admin-content">

    <div class="admin-grid">

        <div class="admin-panel neptune-animate">
            <h2>Requests overview</h2>

            <div class="admin-row">
                <div class="admin-label email-label">Email Requests</div>
                <div class="admin-count"><?php echo $emailCount; ?></div>
            </div>

            <div class="admin-row">
                <div class="admin-label password-label">Password Requests</div>
                <div class="admin-count"><?php echo $passwordCount; ?></div>
            </div>

            <div class="admin-row">
                <div class="admin-label other-label">Other Requests</div>
                <div class="admin-count"><?php echo $otherCount; ?></div>
            </div>
        </div>

        <div class="admin-right">

            <div class="clock-card">
                <div class="clock-time" id="clockTime">00:00 PM</div>
            </div>

            <div class="info-card neptune-animate">
                <h3>Admin Control Centre</h3>
                <p>
                    Review user requests, approve account recovery actions,
                    manage support tickets and control user access from the dock below.
                </p>
            </div>

        </div>

    </div>

</div>

<div class="bottom-dock">
    <a href="dashboard.php" class="dock-link">⌂</a>
    <a href="manage_users.php" class="dock-link">☻</a>
    <a href="manage_requests.php" class="dock-link">▣</a>
    <a href="manage_tickets.php" class="dock-link">✉</a>
    <a href="audit_logs.php" class="dock-link">◎</a>
</div>

</div>
</div>

<script>
function updateClock() {
    const now = new Date();

    const time = new Intl.DateTimeFormat('en-GB', {
        timeZone: 'Europe/London',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    }).format(now);

    document.getElementById("clockTime").textContent = time.toUpperCase();
}

updateClock();
setInterval(updateClock, 1000);
</script>
<script>
window.addEventListener("load", () => {
    document.body.classList.add("loaded");
});
</script>
</body>
</html>
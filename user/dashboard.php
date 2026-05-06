<?php
session_start();
require_once '../config/db.php';

$showRefresh = !empty($_SESSION["success_message"]);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| GET USER DETAILS
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare("
    SELECT first_name
    FROM users
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$userId]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

$firstName    = $user['first_name'] ?? 'User';

/*
|--------------------------------------------------------------------------
| COUNTS + STATUS HELPER
|--------------------------------------------------------------------------
*/
function getRequestData($pdo, $userId, $type)
{
    $stmt = $pdo->prepare("
        SELECT status
        FROM issues
        WHERE created_by = ?
        AND request_type = ?
        ORDER BY id DESC
    ");

    $stmt->execute([$userId, $type]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $count = count($rows);
    $status = 'none';

    if ($count > 0) {
        $latest = $rows[0]['status'];

        if ($latest === 'Completed') {
            $status = 'complete';
        } elseif ($latest === 'Pending' || $latest === 'Approved') {
            $status = 'ongoing';
        }
    }

    return [
        'count'  => $count,
        'status' => $status
    ];
}

$emailData    = getRequestData($pdo, $userId, 'email_reset');
$passwordData = getRequestData($pdo, $userId, 'password_reset');
$otherData    = getRequestData($pdo, $userId, 'other');

function statusClass($status)
{
    if ($status === 'complete') return 'status-complete';
    if ($status === 'ongoing') return 'status-ongoing';
    return 'status-none';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Dashboard</title>

<link rel="stylesheet" href="../assets/css/neptune.css">

<style>
.dashboard-content{
    position:relative;
    z-index:3;
    min-height:calc(100vh - 70px);
    padding:40px 70px;
}

.dashboard-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:70px;
    align-items:center;
}

.request-panel{
    width:360px;
    min-height:410px;
    padding:28px;
    border-radius:24px;
    background:rgba(7,11,19,.42);
    border:1px solid rgba(255,255,255,.12);
    box-shadow:
        0 18px 40px rgba(0,0,0,.45),
        inset 0 0 16px rgba(255,255,255,.02);
}

.request-panel h2{
    font-size:18px;
    font-weight:300;
    margin-bottom:30px;
}

.request-row{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:26px;
}

.request-label{
    font-size:18px;
    font-weight:300;
}

.request-count{
    width:48px;
    height:54px;
    border-radius:10px;
    border:1px solid rgba(255,255,255,.18);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:24px;
    color:#fff;
}

.status-none{ color:rgba(255,255,255,.78); }
.status-ongoing{ color:#f0b000; }
.status-complete{ color:#76ff03; }

.right-stack{
    display:flex;
    flex-direction:column;
    gap:42px;
    align-items:center;
}

.clock-card{
    width:350px;
    height:235px;
    border-radius:26px;
    background:rgba(7,11,19,.40);
    border:1px solid rgba(255,255,255,.14);
    backdrop-filter:blur(8px);
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
}

.clock-time{
    font-size:74px;
    font-weight:200;
    line-height:1;
}

.clock-period{
    font-size:76px;
    font-weight:200;
    line-height:1;
}

.action-card{
    width:390px;
    padding:26px;
    border-radius:24px;
    background:rgba(255,255,255,.10);
    border:1px solid rgba(255,255,255,.14);
    backdrop-filter:blur(8px);
}

.action-card h3{
    text-align:center;
    font-size:18px;
    font-weight:300;
    margin-bottom:18px;
}

.action-btn {
    display: block;
    width: 100%;
    height: 34px;
    line-height: 34px;
    margin-bottom: 10px;
    text-align: center;
    text-decoration: none;
    border: none;
    border-radius: 8px;
    background: #1677ff;
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
}

.bottom-dock{
    position:absolute;
    left:50%;
    bottom:20px;
    transform:translateX(-50%);
    z-index:4;

    width:230px;
    height:58px;
    border-radius:999px;

    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.12);
    backdrop-filter:blur(10px);

    display:flex;
    align-items:center;
    justify-content:center;
    gap:34px;
}

.dock-link{
    width:42px;
    height:42px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
    color:#fff;
    font-size:22px;
}

.dock-link:hover{
    background:rgba(255,255,255,.10);
}

.dashboard-alert{
    width: fit-content;
    margin: 0 auto 20px auto;
    padding: 12px 24px;
    border-radius: 12px;
    background: rgba(22,119,255,.18);
    border: 1px solid rgba(22,119,255,.35);
    color: rgba(255,255,255,.92);
    font-size: 14px;
    font-weight: 300;
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

<div class="dashboard-content">

<?php if (!empty($_SESSION["success_message"])): ?>
    <div class="dashboard-alert">
        <?php echo htmlspecialchars($_SESSION["success_message"]); ?>
    </div>
    <?php unset($_SESSION["success_message"]); ?>
<?php endif; ?>

<div class="dashboard-grid">

<!-- LEFT -->
<div class="request-panel neptune-animate">

<h2>My Requests</h2>

<div class="request-row">
    <div class="request-label <?= statusClass($emailData['status']); ?>">
        Email Requests
    </div>
    <div class="request-count"><?= $emailData['count']; ?></div>
</div>

<div class="request-row">
    <div class="request-label <?= statusClass($passwordData['status']); ?>">
        Password Requests
    </div>
    <div class="request-count"><?= $passwordData['count']; ?></div>
</div>

<div class="request-row">
    <div class="request-label <?= statusClass($otherData['status']); ?>">
        Other Requests
    </div>
    <div class="request-count"><?= $otherData['count']; ?></div>
</div>

</div>

<!-- RIGHT -->
<div class="right-stack">

<div class="clock-card neptune-animate">
    <div class="clock-time" id="clockTime">00:00</div>
    <div class="clock-period" id="clockPeriod">AM</div>
</div>

<div class="action-card neptune-animate">
    <h3>Create a request</h3>

    <form action="../actions/create_issue.php" method="POST">
    <input type="hidden" name="request_type" value="email_reset">
    <input type="hidden" name="title" value="Email Reset Request">
    <button type="submit" class="action-btn">Email Reset</button>
</form>

<form action="../actions/create_issue.php" method="POST">
    <input type="hidden" name="request_type" value="password_reset">
    <input type="hidden" name="title" value="Password Reset Request">
    <button type="submit" class="action-btn">Password Reset</button>
</form>
</div>

</div>

</div>
</div>

<div class="bottom-dock">
    <a href="dashboard.php" class="dock-link">⌂</a>
    <a href="report_issue.php" class="dock-link">✉</a>
</div>

</div>
</div>

<script>
const timezone = "Europe/London";

function updateClock() {
    const now = new Date();

    const parts = new Intl.DateTimeFormat('en-GB', {
        timeZone: timezone,
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    }).formatToParts(now);

    let hour = '';
    let minute = '';
    let period = '';

    parts.forEach(part => {
        if(part.type === 'hour') hour = part.value;
        if(part.type === 'minute') minute = part.value;
        if(part.type === 'dayPeriod') period = part.value.toUpperCase();
    });

    document.getElementById("clockTime").textContent = hour + ":" + minute;
    document.getElementById("clockPeriod").textContent = period;
}

updateClock();
setInterval(updateClock, 1000);</script>
<?php if ($showRefresh): ?>
<script>
setTimeout(() => {
    window.location.href = "dashboard.php";
}, 2500);
</script>
<?php endif; ?>
<script>
window.addEventListener("load", () => {
    document.body.classList.add("loaded");
});
</script>
</body>
</html>
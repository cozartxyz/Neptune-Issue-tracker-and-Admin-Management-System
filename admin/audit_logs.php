<?php
require_once "../includes/auth.php";
requireAdmin();

require_once "../config/db.php";

$stmt = $pdo->query("
    SELECT 
        audit_logs.*,
        users.first_name,
        users.surname,
        users.email
    FROM audit_logs
    LEFT JOIN users ON audit_logs.user_id = users.id
    ORDER BY audit_logs.created_at DESC
    LIMIT 100
");

$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Audit Logs</title>

<link rel="stylesheet" href="../assets/css/neptune.css">

<style>
.manage-content {
    position: relative;
    z-index: 3;
    min-height: calc(100vh - 70px);
    padding: 45px 70px 100px;
}

.page-title {
    font-size: 34px;
    font-weight: 200;
    margin-bottom: 28px;
    color: rgba(255,255,255,.9);
}

.logs-table-card {
    width: 100%;
    border-radius: 24px;
    padding: 22px;
    background: rgba(7,11,19,.42);
    border: 1px solid rgba(255,255,255,.12);
    backdrop-filter: blur(8px);
    box-shadow:
        0 18px 40px rgba(0,0,0,.45),
        inset 0 0 16px rgba(255,255,255,.02);
    overflow-x: auto;
}

.logs-table {
    width: 100%;
    border-collapse: collapse;
    color: rgba(255,255,255,.82);
    font-size: 14px;
}

.logs-table th {
    text-align: left;
    font-weight: 400;
    padding: 14px 12px;
    border-bottom: 1px solid rgba(255,255,255,.14);
    color: rgba(255,255,255,.95);
    white-space: nowrap;
}

.logs-table td {
    padding: 14px 12px;
    border-bottom: 1px solid rgba(255,255,255,.08);
    font-weight: 300;
    vertical-align: top;
}

.logs-table tr:hover {
    background: rgba(255,255,255,.035);
}

.badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
}

.badge-action {
    background: rgba(22,119,255,.15);
    color: #7db6ff;
    border: 1px solid rgba(22,119,255,.3);
}

.badge-target {
    background: rgba(120,80,255,.18);
    color: #bca8ff;
    border: 1px solid rgba(120,80,255,.3);
}

.muted {
    color: rgba(255,255,255,.55);
}

.user-agent {
    max-width: 260px;
    line-height: 1.4;
    color: rgba(255,255,255,.62);
    font-size: 12px;
}

.empty-state {
    padding: 40px;
    text-align: center;
    color: rgba(255,255,255,.65);
    font-weight: 300;
}

.bottom-dock {
    position: absolute;
    left: 50%;
    bottom: 20px;
    transform: translateX(-50%);
    z-index: 4;

    min-width: 390px;
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

<div class="manage-content">

    <h1 class="page-title">Audit Logs</h1>

    <div class="logs-table-card">

        <?php if (empty($logs)): ?>

            <div class="empty-state">
                No audit events have been recorded yet.
            </div>

        <?php else: ?>

        <table class="logs-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Target</th>
                    <th>Location</th>
                    <th>Timezone</th>
                    <th>IP Hash</th>
                    <th>User Agent</th>
                    <th>Created At</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($log["id"]); ?></td>

                        <td>
                            <?php if (!empty($log["first_name"])): ?>
                                <?php echo htmlspecialchars($log["first_name"] . " " . $log["surname"]); ?><br>
                                <span class="muted"><?php echo htmlspecialchars($log["email"]); ?></span>
                            <?php else: ?>
                                <span class="muted">System / Deleted User</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <span class="badge badge-action">
                                <?php echo htmlspecialchars($log["action"]); ?>
                            </span>
                        </td>

                        <td>
                            <?php if (!empty($log["target_type"])): ?>
                                <span class="badge badge-target">
                                    <?php echo htmlspecialchars($log["target_type"]); ?>
                                    #<?php echo htmlspecialchars($log["target_id"] ?? ""); ?>
                                </span>
                            <?php else: ?>
                                <span class="muted">N/A</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php
                                $location = trim(($log["geo_city"] ?? "") . ", " . ($log["geo_country"] ?? ""), " ,");
                                echo $location !== "" ? htmlspecialchars($location) : '<span class="muted">Unknown</span>';
                            ?>
                        </td>

                        <td>
                            <?php echo !empty($log["geo_timezone"]) ? htmlspecialchars($log["geo_timezone"]) : '<span class="muted">Unknown</span>'; ?>
                        </td>

                        <td>
                            <?php echo !empty($log["ip_hash"]) ? htmlspecialchars(substr($log["ip_hash"], 0, 18)) . "..." : '<span class="muted">N/A</span>'; ?>
                        </td>

                        <td class="user-agent">
                            <?php echo !empty($log["user_agent"]) ? htmlspecialchars($log["user_agent"]) : '<span class="muted">N/A</span>'; ?>
                        </td>

                        <td><?php echo htmlspecialchars($log["created_at"]); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php endif; ?>

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
window.addEventListener("load", () => {
    document.body.classList.add("loaded");
});
</script>
</body>
</html>
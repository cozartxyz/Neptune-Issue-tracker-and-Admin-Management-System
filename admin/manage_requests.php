<?php
require_once "../includes/auth.php";
requireAdmin();

require_once "../config/db.php";

$stmt = $pdo->query("
    SELECT
        issues.*,
        users.first_name,
        users.surname,
        users.email
    FROM issues
    INNER JOIN users ON issues.created_by = users.id
    WHERE issues.request_type IN ('email_reset', 'password_reset')
    ORDER BY issues.created_at DESC
");

$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Account Reset Requests</title>

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

.request-table-card {
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

.requests-table {
    width: 100%;
    border-collapse: collapse;
    color: rgba(255,255,255,.82);
    font-size: 14px;
}

.requests-table th {
    text-align: left;
    font-weight: 400;
    padding: 14px 12px;
    border-bottom: 1px solid rgba(255,255,255,.14);
    color: rgba(255,255,255,.95);
    white-space: nowrap;
}

.requests-table td {
    padding: 14px 12px;
    border-bottom: 1px solid rgba(255,255,255,.08);
    font-weight: 300;
    vertical-align: middle;
}

.requests-table tr:hover {
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

.badge-email {
    background: rgba(22,119,255,.15);
    color: #7db6ff;
    border: 1px solid rgba(22,119,255,.3);
}

.badge-password {
    background: rgba(120,80,255,.18);
    color: #bca8ff;
    border: 1px solid rgba(120,80,255,.3);
}

.badge-pending {
    background: rgba(255,176,0,.12);
    color: #f0b000;
    border: 1px solid rgba(255,176,0,.25);
}

.badge-approved {
    background: rgba(22,119,255,.15);
    color: #7db6ff;
    border: 1px solid rgba(22,119,255,.3);
}

.badge-rejected {
    background: rgba(255,70,70,.12);
    color: #ff7070;
    border: 1px solid rgba(255,70,70,.25);
}

.badge-completed {
    background: rgba(118,255,3,.12);
    color: #76ff03;
    border: 1px solid rgba(118,255,3,.25);
}

.action-group {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.action-form {
    display: inline;
}

.action-btn {
    border: none;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    color: white;
}

.btn-blue {
    background: #1677ff;
}

.btn-red {
    background: #c93131;
}

.action-btn:hover {
    filter: brightness(1.1);
}

.no-action {
    color: rgba(255,255,255,.55);
    font-weight: 300;
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

<div class="manage-content">

    <h1 class="page-title">Account Reset Requests</h1>

    <div class="request-table-card neptune-animate">

        <?php if (empty($requests)): ?>

            <div class="empty-state">
                No account reset requests have been submitted yet.
            </div>

        <?php else: ?>

        <table class="requests-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Title</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Decision Time</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($requests as $request): ?>
                    <?php
                        $typeClass = $request["request_type"] === "email_reset"
                            ? "badge-email"
                            : "badge-password";

                        $typeLabel = $request["request_type"] === "email_reset"
                            ? "Email Reset"
                            : "Password Reset";

                        $status = $request["status"];
                        $statusClass = "badge-pending";

                        if ($status === "Approved") {
                            $statusClass = "badge-approved";
                        } elseif ($status === "Rejected") {
                            $statusClass = "badge-rejected";
                        } elseif ($status === "Completed") {
                            $statusClass = "badge-completed";
                        }
                    ?>

                    <tr>
                        <td><?php echo htmlspecialchars($request["id"]); ?></td>

                        <td>
                            <span class="badge <?php echo $typeClass; ?>">
                                <?php echo $typeLabel; ?>
                            </span>
                        </td>

                        <td><?php echo htmlspecialchars($request["title"]); ?></td>

                        <td>
                            <?php echo htmlspecialchars($request["first_name"] . " " . $request["surname"]); ?>
                        </td>

                        <td><?php echo htmlspecialchars($request["email"]); ?></td>

                        <td>
                            <span class="badge <?php echo $statusClass; ?>">
                                <?php echo htmlspecialchars($request["status"]); ?>
                            </span>
                        </td>

                        <td><?php echo htmlspecialchars($request["created_at"]); ?></td>

                        <td>
                            <?php
                                echo !empty($request["admin_decision_at"])
                                    ? htmlspecialchars($request["admin_decision_at"])
                                    : '<span class="no-action">Pending</span>';
                            ?>
                        </td>

                        <td>
                            <?php if ($request["status"] === "Pending"): ?>
                                <div class="action-group">

                                    <form class="action-form" action="../actions/approve_issue_request.php" method="POST">
                                        <input type="hidden" name="issue_id" value="<?php echo $request["id"]; ?>">
                                        <button type="submit" class="action-btn btn-blue">Approve</button>
                                    </form>

                                    <form class="action-form" action="../actions/reject_issue_request.php" method="POST">
                                        <input type="hidden" name="issue_id" value="<?php echo $request["id"]; ?>">
                                        <button type="submit" class="action-btn btn-red">Reject</button>
                                    </form>

                                </div>
                            <?php else: ?>
                                <span class="no-action">No actions available</span>
                            <?php endif; ?>
                        </td>
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
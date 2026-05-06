<?php
require_once "../includes/auth.php";
requireAdmin();

require_once "../config/db.php";

$stmt = $pdo->query("
    SELECT
        issues.*,
        categories.name AS category_name,
        u1.first_name AS created_by_first_name,
        u1.surname AS created_by_surname,
        u2.first_name AS assigned_first_name,
        u2.surname AS assigned_surname
    FROM issues
    LEFT JOIN categories ON issues.category_id = categories.id
    LEFT JOIN users u1 ON issues.created_by = u1.id
    LEFT JOIN users u2 ON issues.assigned_admin = u2.id
    WHERE issues.request_type = 'other'
    ORDER BY issues.created_at DESC
");

$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

$adminStmt = $pdo->query("
    SELECT id, first_name, surname
    FROM users
    WHERE role = 'admin'
    ORDER BY first_name ASC
");

$admins = $adminStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Support Tickets</title>

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

.ticket-table-card {
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

.tickets-table {
    width: 100%;
    border-collapse: collapse;
    color: rgba(255,255,255,.82);
    font-size: 14px;
}

.tickets-table th {
    text-align: left;
    font-weight: 400;
    padding: 14px 12px;
    border-bottom: 1px solid rgba(255,255,255,.14);
    color: rgba(255,255,255,.95);
    white-space: nowrap;
}

.tickets-table td {
    padding: 14px 12px;
    border-bottom: 1px solid rgba(255,255,255,.08);
    font-weight: 300;
    vertical-align: top;
}

.tickets-table tr:hover {
    background: rgba(255,255,255,.035);
}

.description-cell {
    max-width: 280px;
    line-height: 1.5;
    color: rgba(255,255,255,.75);
}

.badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
}

.badge-pending {
    background: rgba(255,176,0,.12);
    color: #f0b000;
    border: 1px solid rgba(255,176,0,.25);
}

.badge-open {
    background: rgba(22,119,255,.15);
    color: #7db6ff;
    border: 1px solid rgba(22,119,255,.3);
}

.badge-progress {
    background: rgba(120,80,255,.18);
    color: #bca8ff;
    border: 1px solid rgba(120,80,255,.3);
}

.badge-awaiting {
    background: rgba(255,255,255,.10);
    color: rgba(255,255,255,.78);
    border: 1px solid rgba(255,255,255,.18);
}

.badge-resolved,
.badge-closed {
    background: rgba(118,255,3,.12);
    color: #76ff03;
    border: 1px solid rgba(118,255,3,.25);
}

.action-stack {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.action-form {
    display: flex;
    gap: 8px;
    align-items: center;
}

.action-select {
    height: 32px;
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,.16);
    background: rgba(255,255,255,.06);
    color: white;
    padding: 0 10px;
    font-size: 12px;
    outline: none;
}

.action-select option {
    color: black;
}

.action-btn {
    border: none;
    border-radius: 8px;
    padding: 8px 11px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    color: white;
    background: #1677ff;
}

.action-btn:hover {
    filter: brightness(1.1);
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

.empty-state {
    padding: 40px;
    text-align: center;
    color: rgba(255,255,255,.65);
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

<div class="manage-content">

    <h1 class="page-title">Support Tickets</h1>

    <div class="ticket-table-card neptune-animate">

        <?php if (empty($tickets)): ?>

            <div class="empty-state">
                No support tickets have been submitted yet.
            </div>

        <?php else: ?>

        <table class="tickets-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Raised By</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Assigned Admin</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($tickets as $ticket): ?>
                    <?php
                        $status = $ticket["status"];

                        $statusClass = "badge-pending";

                        if ($status === "Open") {
                            $statusClass = "badge-open";
                        } elseif ($status === "In Progress") {
                            $statusClass = "badge-progress";
                        } elseif ($status === "Awaiting User") {
                            $statusClass = "badge-awaiting";
                        } elseif ($status === "Resolved") {
                            $statusClass = "badge-resolved";
                        } elseif ($status === "Closed") {
                            $statusClass = "badge-closed";
                        }
                    ?>

                    <tr>
                        <td><?php echo htmlspecialchars($ticket["id"]); ?></td>

                        <td><?php echo htmlspecialchars($ticket["title"]); ?></td>

                        <td>
                            <?php echo htmlspecialchars(
                                trim(($ticket["created_by_first_name"] ?? "") . " " . ($ticket["created_by_surname"] ?? ""))
                            ); ?>
                        </td>

                        <td class="description-cell">
                            <?php echo htmlspecialchars($ticket["description"]); ?>
                        </td>

                        <td>
                            <span class="badge <?php echo $statusClass; ?>">
                                <?php echo htmlspecialchars($ticket["status"]); ?>
                            </span>
                        </td>

                        <td>
                            <?php
                                if (!empty($ticket["assigned_first_name"])) {
                                    echo htmlspecialchars($ticket["assigned_first_name"] . " " . $ticket["assigned_surname"]);
                                } else {
                                    echo '<span style="color:rgba(255,255,255,.55);">Unassigned</span>';
                                }
                            ?>
                        </td>

                        <td><?php echo htmlspecialchars($ticket["created_at"]); ?></td>

                        <td>
                            <div class="action-stack">

                                <form class="action-form" action="../actions/update_issue_status.php" method="POST">
                                    <input type="hidden" name="issue_id" value="<?php echo $ticket["id"]; ?>">

                                    <select name="status" class="action-select">
                                        <option value="Open" <?php echo $ticket["status"] === "Open" ? "selected" : ""; ?>>Open</option>
                                        <option value="In Progress" <?php echo $ticket["status"] === "In Progress" ? "selected" : ""; ?>>In Progress</option>
                                        <option value="Awaiting User" <?php echo $ticket["status"] === "Awaiting User" ? "selected" : ""; ?>>Awaiting User</option>
                                        <option value="Resolved" <?php echo $ticket["status"] === "Resolved" ? "selected" : ""; ?>>Resolved</option>
                                        <option value="Closed" <?php echo $ticket["status"] === "Closed" ? "selected" : ""; ?>>Closed</option>
                                    </select>

                                    <button type="submit" class="action-btn">Update</button>
                                </form>

                                <form class="action-form" action="../actions/assign_issue.php" method="POST">
                                    <input type="hidden" name="issue_id" value="<?php echo $ticket["id"]; ?>">

                                    <select name="admin_id" class="action-select">
                                        <option value="">Select Admin</option>

                                        <?php foreach ($admins as $admin): ?>
                                            <option value="<?php echo $admin["id"]; ?>"
                                                <?php echo (int)$ticket["assigned_admin"] === (int)$admin["id"] ? "selected" : ""; ?>>
                                                <?php echo htmlspecialchars($admin["first_name"] . " " . $admin["surname"]); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <button type="submit" class="action-btn">Assign</button>
                                </form>

                            </div>
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
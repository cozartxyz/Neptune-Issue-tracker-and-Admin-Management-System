<?php
require_once "../includes/auth.php";
requireAdmin();

require_once "../config/db.php";

$stmt = $pdo->query("
    SELECT id, employee_no, first_name, surname, email, department, role, is_approved, is_active
    FROM users
    ORDER BY id DESC
");

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Users</title>

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

.user-table-card {
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

.users-table {
    width: 100%;
    border-collapse: collapse;
    color: rgba(255,255,255,.82);
    font-size: 14px;
}

.users-table th {
    text-align: left;
    font-weight: 400;
    padding: 14px 12px;
    border-bottom: 1px solid rgba(255,255,255,.14);
    color: rgba(255,255,255,.95);
}

.users-table td {
    padding: 14px 12px;
    border-bottom: 1px solid rgba(255,255,255,.08);
    font-weight: 300;
}

.users-table tr:hover {
    background: rgba(255,255,255,.035);
}

.badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 500;
}

.badge-admin {
    background: rgba(120,80,255,.18);
    color: #bca8ff;
    border: 1px solid rgba(120,80,255,.3);
}

.badge-user {
    background: rgba(22,119,255,.15);
    color: #7db6ff;
    border: 1px solid rgba(22,119,255,.3);
}

.badge-yes {
    background: rgba(118,255,3,.12);
    color: #76ff03;
    border: 1px solid rgba(118,255,3,.25);
}

.badge-no {
    background: rgba(255,176,0,.12);
    color: #f0b000;
    border: 1px solid rgba(255,176,0,.25);
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
    padding: 7px 11px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    color: white;
}

.btn-blue {
    background: #1677ff;
}

.btn-orange {
    background: #d98b00;
}

.btn-purple {
    background: #694fff;
}

.btn-red {
    background: #c93131;
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

    <h1 class="page-title">Manage Users</h1>

    <div class="user-table-card neptune-animate">

        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Employee No</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Role</th>
                    <th>Approved</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user["id"]); ?></td>
                        <td><?php echo htmlspecialchars($user["employee_no"]); ?></td>
                        <td><?php echo htmlspecialchars($user["first_name"] . " " . $user["surname"]); ?></td>
                        <td><?php echo htmlspecialchars($user["email"]); ?></td>
                        <td><?php echo htmlspecialchars($user["department"]); ?></td>

                        <td>
                            <?php if ($user["role"] === "admin"): ?>
                                <span class="badge badge-admin">admin</span>
                            <?php else: ?>
                                <span class="badge badge-user">user</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if ((int)$user["is_approved"] === 1): ?>
                                <span class="badge badge-yes">Yes</span>
                            <?php else: ?>
                                <span class="badge badge-no">No</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if ((int)$user["is_active"] === 1): ?>
                                <span class="badge badge-yes">Yes</span>
                            <?php else: ?>
                                <span class="badge badge-no">No</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <div class="action-group">

                                <?php if ((int)$user["is_approved"] === 0): ?>
                                    <form class="action-form" action="../actions/approve_user.php" method="POST">
                                        <input type="hidden" name="user_id" value="<?php echo $user["id"]; ?>">
                                        <button class="action-btn btn-blue" type="submit">Approve</button>
                                    </form>
                                <?php endif; ?>

                                <?php if ((int)$user["is_active"] === 1): ?>
                                    <form class="action-form" action="../actions/disable_user.php" method="POST">
                                        <input type="hidden" name="user_id" value="<?php echo $user["id"]; ?>">
                                        <button class="action-btn btn-orange" type="submit">Disable</button>
                                    </form>
                                <?php else: ?>
                                    <form class="action-form" action="../actions/enable_user.php" method="POST">
                                        <input type="hidden" name="user_id" value="<?php echo $user["id"]; ?>">
                                        <button class="action-btn btn-blue" type="submit">Enable</button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($user["role"] !== "admin"): ?>
                                    <form class="action-form" action="../actions/make_admin.php" method="POST">
                                        <input type="hidden" name="user_id" value="<?php echo $user["id"]; ?>">
                                        <button class="action-btn btn-purple" type="submit">Make Admin</button>
                                    </form>
                                <?php endif; ?>

                                <form class="action-form" action="../actions/delete_user.php" method="POST" onsubmit="return confirm('Delete this user?');">
                                    <input type="hidden" name="user_id" value="<?php echo $user["id"]; ?>">
                                    <button class="action-btn btn-red" type="submit">Delete</button>
                                </form>

                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

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
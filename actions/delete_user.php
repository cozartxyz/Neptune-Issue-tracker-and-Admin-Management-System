<?php
require_once "../includes/auth.php";
requireAdmin();
require_once "../config/db.php";
require_once "../includes/functions.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    neptuneMessage("Invalid request.");
}

$userId = (int)($_POST["user_id"] ?? 0);

if ($userId <= 0) {
    neptuneMessage("Invalid user ID.");
}

if ($userId === (int)$_SESSION["user_id"]) {
    neptuneMessage("You cannot delete your own account.");
}
//db deletion
$deleteAuditLogs = $pdo->prepare("DELETE FROM audit_logs WHERE user_id = ?");
$deleteAuditLogs->execute([$userId]);

$deleteLoginLogs = $pdo->prepare("DELETE FROM login_logs WHERE user_id = ?");
$deleteLoginLogs->execute([$userId]);

$deleteUser = $pdo->prepare("DELETE FROM users WHERE id = ?");
$deleteUser->execute([$userId]);

logAuditEvent($pdo, $_SESSION["user_id"], "delete_user", "user", $userId);

header("Location: ../admin/manage_users.php");
exit;
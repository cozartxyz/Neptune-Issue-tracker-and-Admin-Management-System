<?php
require_once "../includes/auth.php";
requireAdmin();
require_once "../config/db.php";
require_once "../includes/functions.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    neptuneMessage("Invalid request.");
}

$userId = (int)($_POST["user_id"] ?? 0);
$currentStatus = (int)($_POST["current_status"] ?? 0);

if ($userId <= 0) {
    neptuneMessage("Invalid user ID.");
}
// numbers value for db
$newStatus = $currentStatus === 1 ? 0 : 1;

$stmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?");
$stmt->execute([$newStatus, $userId]);

logAuditEvent($pdo, $_SESSION["user_id"], "toggle_user_status", "user", $userId);

header("Location: ../admin/manage_users.php");
exit;
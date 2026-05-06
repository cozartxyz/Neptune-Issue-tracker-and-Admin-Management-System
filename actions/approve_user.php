<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

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

$stmt = $pdo->prepare("UPDATE users SET is_approved = 1 WHERE id = ?");
$stmt->execute([$userId]);

logAuditEvent($pdo, $_SESSION["user_id"], "approve_user", "user", $userId);

header("Location: ../admin/manage_users.php");
exit;
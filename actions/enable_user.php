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
    neptuneMessage("Invalid user.");
}


$stmt = $pdo->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
$stmt->execute([$userId]);


// require_once "../includes/functions.php";
// logAuditEvent($pdo, $_SESSION["user_id"], "enable_user", "user", $userId); not really using this anymore

header("Location: ../admin/manage_users.php");
exit;
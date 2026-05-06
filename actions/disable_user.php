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

$stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
$stmt->execute([$userId]);

header("Location: ../admin/manage_users.php");
exit;
<?php
require_once "../includes/auth.php";
requireAdmin();
require_once "../config/db.php";
require_once "../includes/functions.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    neptuneMessage("Invalid request.");
}

$issueId = (int)($_POST["issue_id"] ?? 0);
$adminId = (int)($_POST["admin_id"] ?? 0);

if ($issueId <= 0 || $adminId <= 0) {
    neptuneMessage("Invalid input.");
}

$stmt = $pdo->prepare("UPDATE issues SET assigned_admin = ? WHERE id = ?");
$stmt->execute([$adminId, $issueId]);

logAuditEvent($pdo, $_SESSION["user_id"], "assign_issue", "issue", $issueId);

header("Location: ../admin/manage_tickets.php");
exit;
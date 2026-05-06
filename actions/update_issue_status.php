<?php
require_once "../includes/auth.php";
requireAdmin();

require_once "../config/db.php";
require_once "../includes/functions.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    neptuneMessage("Invalid request.");
}

$issueId = (int)($_POST["issue_id"] ?? 0);
$status = trim($_POST["status"] ?? "");
//options for issue assigning
$allowedStatuses = [
    "Open",
    "In Progress",
    "Awaiting User",
    "Resolved",
    "Closed",
    "Pending",
    "Approved",
    "Rejected",
    "Completed"
];

if ($issueId <= 0) {
    neptuneMessage("Invalid issue ID.");
}

if (!in_array($status, $allowedStatuses, true)) {
    neptuneMessage("Invalid input.");
}

$stmt = $pdo->prepare("UPDATE issues SET status = ? WHERE id = ?");
$stmt->execute([$status, $issueId]);

logAuditEvent($pdo, (int)$_SESSION["user_id"], "update_issue_status", "issue", $issueId);

header("Location: ../admin/manage_tickets.php");
exit;
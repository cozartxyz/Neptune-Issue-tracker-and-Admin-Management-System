<?php

require_once "../includes/auth.php";
requireAdmin();

require_once "../config/db.php";
require_once "../includes/functions.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    neptuneMessage("Invalid request.");
}

$issueId = (int)($_POST["issue_id"] ?? 0);

if ($issueId <= 0) {
    neptuneMessage("Invalid issue ID.");
}

$stmt = $pdo->prepare("
    UPDATE issues
    SET
        status = ?,
        assigned_admin = ?,
        admin_decision_at = ?,
        reset_token = NULL,
        reset_token_expires_at = NULL
    WHERE id = ?
");
$stmt->execute([
    "Rejected",
    $_SESSION["user_id"],
    date("Y-m-d H:i:s"),
    $issueId
]);

logAuditEvent($pdo, (int)$_SESSION["user_id"], "reject_issue_request", "issue", $issueId);

header("Location: ../admin/manage_requests.php");
exit;
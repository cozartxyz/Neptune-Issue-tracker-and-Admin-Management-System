<?php

//ini_set('display_errors', 1);
//error_reporting(E_ALL); was for error logging
//pasword reset link processing
require_once "../config/db.php";
require_once "../includes/functions.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    neptuneMessage("Invalid request.");
}

$token = trim($_POST["token"] ?? "");
$newPassword = $_POST["new_password"] ?? "";

if ($token === "") {
    neptuneMessage("Missing token.");
}

if ($newPassword === "") {
    neptuneMessage("New password is required.");
}
//pasword reqs
if (strlen($newPassword) < 8) {
    neptuneMessage("Password must be at least 8 characters long.");
}

if (!preg_match("/[A-Z]/", $newPassword)) {
    neptuneMessage("Password must contain at least one uppercase letter.");
}

if (!preg_match("/[0-9]/", $newPassword)) {
    neptuneMessage("Password must contain at least one number.");
}

if (!preg_match("/[\W]/", $newPassword)) {
    neptuneMessage("Password must contain at least one special character.");
}

$issueStmt = $pdo->prepare("
    SELECT issues.*, users.id AS user_id, users.email
    FROM issues
    INNER JOIN users ON issues.created_by = users.id
    WHERE issues.reset_token = ?
      AND issues.request_type = 'password_reset'
      AND issues.status = 'Approved'
      AND issues.reset_token_expires_at IS NOT NULL
      AND issues.reset_token_expires_at > NOW()
    LIMIT 1
");
$issueStmt->execute([$token]);
$issue = $issueStmt->fetch(PDO::FETCH_ASSOC);

if (!$issue) {
    neptuneMessage("This password reset link is invalid or has expired.");
}

$passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

$updateUserStmt = $pdo->prepare("
    UPDATE users
    SET password_hash = ?
    WHERE id = ?
");
$updateUserStmt->execute([$passwordHash, $issue["user_id"]]);

if ($updateUserStmt->rowCount() < 1) {
    neptuneMessage("Password update query ran, but no user row was updated.");
}

$completeIssueStmt = $pdo->prepare("
    UPDATE issues
    SET
        status = 'Completed',
        reset_token = NULL,
        reset_token_expires_at = NULL,
        admin_decision_at = NOW()
    WHERE id = ?
");
$completeIssueStmt->execute([$issue["id"]]);

logAuditEvent($pdo, (int)$issue["user_id"], "process_password_reset", "issue", (int)$issue["id"]);

echo "Password updated successfully for user ID " . htmlspecialchars((string)$issue["user_id"]) .
     " (" . htmlspecialchars($issue["email"]) . "). You may now log in with your new password.";
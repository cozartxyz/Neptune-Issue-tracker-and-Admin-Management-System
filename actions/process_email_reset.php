<?php

require_once "../config/db.php";
require_once "../includes/functions.php";
//email reset link processing
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    neptuneMessage("Invalid request.");
}

$token = trim($_POST["token"] ?? "");
$newEmail = trim($_POST["new_email"] ?? "");

if ($token === "") {
    neptuneMessage("Missing token.");
}

if ($newEmail === "") {
    neptuneMessage("New email is required.");
}

if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
    neptuneMessage("Invalid email format.");
}

$issueStmt = $pdo->prepare("
    SELECT issues.*, users.id AS user_id
    FROM issues
    INNER JOIN users ON issues.created_by = users.id
    WHERE issues.reset_token = ?
      AND issues.request_type = 'email_reset'
      AND issues.status = 'Approved'
      AND issues.reset_token_expires_at IS NOT NULL
      AND issues.reset_token_expires_at > NOW()
    LIMIT 1
");
$issueStmt->execute([$token]);
$issue = $issueStmt->fetch(PDO::FETCH_ASSOC);

if (!$issue) {
    neptuneMessage("This email reset link is invalid or has expired.");
}

$currentUserStmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$currentUserStmt->execute([$issue["user_id"]]);
$currentUser = $currentUserStmt->fetch(PDO::FETCH_ASSOC);

if (!$currentUser) {
    neptuneMessage("User not found.");
}

if (strtolower($newEmail) === strtolower($currentUser["email"])) {
    neptuneMessage("You cannot reset to your current email address.");
}

$checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$checkStmt->execute([$newEmail]);
$existingUser = $checkStmt->fetch(PDO::FETCH_ASSOC);

if ($existingUser) {
    neptuneMessage("That email address is already in use.");
}

$updateUserStmt = $pdo->prepare("
    UPDATE users
    SET email = ?
    WHERE id = ?
");
$updateUserStmt->execute([$newEmail, $issue["user_id"]]);

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

logAuditEvent($pdo, (int)$issue["user_id"], "process_email_reset", "issue", (int)$issue["id"]);

echo "Email updated successfully. You may now continue using your account.";
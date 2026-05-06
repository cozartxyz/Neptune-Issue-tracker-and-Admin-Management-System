<?php

require_once "../includes/auth.php";
requireAdmin();

require_once "../config/db.php";
require_once "../includes/functions.php";
require_once "../includes/mail_functions.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    neptuneMessage("Invalid request.");
}

$issueId = (int)($_POST["issue_id"] ?? 0);

if ($issueId <= 0) {
    neptuneMessage("Invalid issue ID.");
}
//db stuffs
$stmt = $pdo->prepare("
    SELECT issues.*, users.email, users.first_name, users.surname
    FROM issues
    INNER JOIN users ON issues.created_by = users.id
    WHERE issues.id = ?
    LIMIT 1
");
$stmt->execute([$issueId]);
$issue = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$issue) {
    neptuneMessage("Issue not found.");
}

if (!in_array($issue["request_type"], ["email_reset", "password_reset"], true)) {
    neptuneMessage("Only email reset and password reset requests can be approved here.");
}
//for the password email requests token generation
$token = bin2hex(random_bytes(32));
$expiresAt = date("Y-m-d H:i:s", strtotime("+1 hour"));
$approvedStatus = "Approved";
$decisionAt = date("Y-m-d H:i:s");

$updateStmt = $pdo->prepare("
    UPDATE issues
    SET
        status = ?,
        assigned_admin = ?,
        reset_token = ?,
        reset_token_expires_at = ?,
        admin_decision_at = ?
    WHERE id = ?
");
$updateStmt->execute([
    $approvedStatus,
    $_SESSION["user_id"],
    $token,
    $expiresAt,
    $decisionAt,
    $issueId
]);
// the email
if ($issue["request_type"] === "email_reset") {
    $resetUrl = "http://localhost/issue-tracker/reset_email.php?token=" . urlencode($token);
    $subject = "Email Reset Request Approved";
    $body = "
        <h2>Email Reset Approved</h2>
        <p>Hello " . htmlspecialchars($issue["first_name"] . " " . $issue["surname"]) . ",</p>
        <p>Your email reset request has been approved.</p>
        <p>Use the link below to set your new email address:</p>
        <p><a href=\"{$resetUrl}\">Reset Email</a></p>
        <p>This link expires in 1 hour.</p>
    ";
} else {
    $resetUrl = "http://localhost/issue-tracker/reset_password.php?token=" . urlencode($token);
    $subject = "Password Reset Request Approved";
    $body = "
        <h2>Password Reset Approved</h2>
        <p>Hello " . htmlspecialchars($issue["first_name"] . " " . $issue["surname"]) . ",</p>
        <p>Your password reset request has been approved.</p>
        <p>Use the link below to set your new password:</p>
        <p><a href=\"{$resetUrl}\">Reset Password</a></p>
        <p>This link expires in 1 hour.</p>
    ";
}

sendEmail(
    $issue["email"],
    $issue["first_name"] . " " . $issue["surname"],
    $subject,
    $body
);

logAuditEvent($pdo, (int)$_SESSION["user_id"], "approve_issue_request", "issue", $issueId);

header("Location: ../admin/manage_requests.php");
exit;
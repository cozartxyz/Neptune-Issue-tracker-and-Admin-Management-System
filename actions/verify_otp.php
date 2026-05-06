<?php

session_start();

require_once "../config/db.php";
require_once "../includes/functions.php";
//verify OTP after suspiscious login
if (!isset($_SESSION["pending_otp_user_id"])) {
    neptuneMessage("No OTP verification in progress.");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    neptuneMessage("Invalid request.");
}

$otp = trim($_POST["otp"]);
$userId = (int) $_SESSION["pending_otp_user_id"];
$hashedIp = $_SESSION["pending_otp_hashed_ip"] ?? null;
$timezone = $_SESSION["pending_otp_timezone"] ?? null;

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    neptuneMessage("User not found.");
}

if (empty($user["otp_code"]) || empty($user["otp_expires_at"])) {
    neptuneMessage("OTP not found.");
}

if (strtotime($user["otp_expires_at"]) < time()) {
    neptuneMessage("OTP has expired.");
}

if (!password_verify($otp, $user["otp_code"])) {
    neptuneMessage("Invalid OTP.");
}

$clearOtpStmt = $pdo->prepare("UPDATE users SET otp_code = NULL, otp_expires_at = NULL WHERE id = ?");
$clearOtpStmt->execute([$userId]);

$loginLogStmt = $pdo->prepare("
    INSERT INTO login_logs (user_id, hashed_ip, timezone)
    VALUES (?, ?, ?)
");
$loginLogStmt->execute([$userId, $hashedIp, $timezone]);

logAuditEvent($pdo, $userId, "login_success_otp", "user", $userId);

$_SESSION["user_id"] = $user["id"];
$_SESSION["role"] = $user["role"];
$_SESSION["email"] = $user["email"];
$_SESSION["first_name"] = $user["first_name"];

unset($_SESSION["pending_otp_user_id"]);
unset($_SESSION["pending_otp_hashed_ip"]);
unset($_SESSION["pending_otp_timezone"]);

if ($user["role"] === "admin") {
    header("Location: ../admin/dashboard.php");
    exit;
}

header("Location: ../user/dashboard.php");
exit;
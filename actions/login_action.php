<?php

session_start();

require_once "../config/db.php";
require_once "../includes/functions.php";
require_once "../includes/mail_functions.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    neptuneMessage("Invalid request.");
}

$email = trim($_POST["email"]);
$password = $_POST["password"];

if (empty($email) || empty($password)) {
    neptuneMessage("Email and password are required.");
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    neptuneMessage("Invalid credentials.");
}

if ((int)$user["is_active"] !== 1) {
    neptuneMessage("Account disabled.");
}

if ((int)$user["is_approved"] !== 1) {
    neptuneMessage("Account awaiting admin approval.");
}

if (!empty($user["lockout_until"]) && strtotime($user["lockout_until"]) > time()) {
    neptuneMessage("Account temporarily locked. Try again later.");
}

if (!password_verify($password, $user["password_hash"])) {
    $failedAttempts = (int)$user["failed_attempts"] + 1;

    if ($failedAttempts >= 5) {
        $lockoutUntil = date("Y-m-d H:i:s", strtotime("+1 minute"));
        $updateStmt = $pdo->prepare("UPDATE users SET failed_attempts = ?, lockout_until = ? WHERE id = ?");
        $updateStmt->execute([$failedAttempts, $lockoutUntil, $user["id"]]);
        neptuneMessage("Too many failed attempts. Account locked for 1 minute.");
    }

    $updateStmt = $pdo->prepare("UPDATE users SET failed_attempts = ? WHERE id = ?");
    $updateStmt->execute([$failedAttempts, $user["id"]]);
    neptuneMessage("Invalid credentials.");
}

$resetStmt = $pdo->prepare("UPDATE users SET failed_attempts = 0, lockout_until = NULL WHERE id = ?");
$resetStmt->execute([$user["id"]]);

$ip = getClientIp();
$hashedIp = hashIp($ip);
$geoData = getGeolocationData($ip);
$currentTimezone = $geoData["timezone"];

$lastLoginStmt = $pdo->prepare("
    SELECT hashed_ip, timezone
    FROM login_logs
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 1
");
$lastLoginStmt->execute([$user["id"]]);
$lastLogin = $lastLoginStmt->fetch(PDO::FETCH_ASSOC);

$requiresVerification = false;
//check hashed ip and timezone against last logged login details to detect sus logins
if ($lastLogin) {
    if ($lastLogin["hashed_ip"] !== $hashedIp || $lastLogin["timezone"] !== $currentTimezone) {
        $requiresVerification = true;
    }
}
//send OTP MAIL
if ($requiresVerification) {
    $otp = (string) random_int(100000, 999999);
    $otpHash = password_hash($otp, PASSWORD_DEFAULT);
    $otpExpiresAt = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    $otpStmt = $pdo->prepare("UPDATE users SET otp_code = ?, otp_expires_at = ? WHERE id = ?");
    $otpStmt->execute([$otpHash, $otpExpiresAt, $user["id"]]);

    $fullName = $user["first_name"] . " " . $user["surname"];
    $subject = "Your Issue Tracker Login OTP";
    $body = "
        <h2>Login Verification</h2>
        <p>Hello {$fullName},</p>
        <p>Your one-time verification code is:</p>
        <h3>{$otp}</h3>
        <p>This code expires in 10 minutes.</p>
    ";

    sendEmail($user["email"], $fullName, $subject, $body);

    $_SESSION["pending_otp_user_id"] = $user["id"];
    $_SESSION["pending_otp_hashed_ip"] = $hashedIp;
    $_SESSION["pending_otp_timezone"] = $currentTimezone;

    logAuditEvent($pdo, (int)$user["id"], "login_otp_required", "user", (int)$user["id"]);

    header("Location: ../verify_otp.php");
    exit;
}

$loginLogStmt = $pdo->prepare("
    INSERT INTO login_logs (user_id, hashed_ip, timezone)
    VALUES (?, ?, ?)
");
$loginLogStmt->execute([$user["id"], $hashedIp, $currentTimezone]);

logAuditEvent($pdo, (int)$user["id"], "login_success", "user", (int)$user["id"]);

$_SESSION["user_id"] = $user["id"];
$_SESSION["role"] = $user["role"];
$_SESSION["email"] = $user["email"];
$_SESSION["first_name"] = $user["first_name"];
//admin or user
if ($user["role"] === "admin") {
    header("Location: ../admin/dashboard.php");
    exit;
}

header("Location: ../user/dashboard.php");
exit;
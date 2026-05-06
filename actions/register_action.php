<?php

//load db
require_once "../config/db.php";

//fnc
require_once "../includes/functions.php";
require_once "../includes/mail_functions.php";

//req check
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    neptuneMessage("Invalid request method.");
}

//form val
$employeeNo = trim($_POST["employee_no"]);
$firstName = trim($_POST["first_name"]);
$surname = trim($_POST["surname"]);
$email = trim($_POST["email"]);
$department = trim($_POST["department"]);
$password = $_POST["password"];

if (
    empty($employeeNo) ||
    empty($firstName) ||
    empty($surname) ||
    empty($email) ||
    empty($department) ||
    empty($password)
) {
    neptuneMessage("All fields are required.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    neptuneMessage("Invalid email format.");
}

//regex goat
if (strlen($password) < 8) {
    neptuneMessage("Password must be at least 8 characters long.");
}

if (!preg_match("/[A-Z]/", $password)) {
    neptuneMessage("Password must contain at least one uppercase letter.");
}

if (!preg_match("/[0-9]/", $password)) {
    neptuneMessage("Password must contain at least one number.");
}

if (!preg_match("/[\W]/", $password)) {
    neptuneMessage("Password must contain at least one special character.");
}

//check if email already exists
$checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$checkStmt->execute([$email]);
$existingUser = $checkStmt->fetch();

if ($existingUser) {
    neptuneMessage("An account with this email already exists.");
}

//hash
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$insertStmt = $pdo->prepare("
    INSERT INTO users (
        employee_no,
        first_name,
        surname,
        email,
        department,
        password_hash,
        role,
        is_approved,
        is_active
    ) VALUES (?, ?, ?, ?, ?, ?, 'user', 0, 1)
");

$insertStmt->execute([
    $employeeNo,
    $firstName,
    $surname,
    $email,
    $department,
    $passwordHash
]);

$userId = (int)$pdo->lastInsertId();

logAuditEvent($pdo, $userId, "register", "user", $userId);

//mailing stuff broski
$fullName = $firstName . " " . $surname;

$subject = "Welcome to Issue Tracker";

$body = "
    <h2>Welcome to Cozart Neptune Issue Tracker</h2>
    <p>Hello {$fullName},</p>
    <p>Your account has been created successfully and is now awaiting admin approval.</p>
    <p>You will be able to log in once an administrator approves your account.</p>
";

$mailSent = sendEmail($email, $fullName, $subject, $body);

if (!$mailSent) {
    neptuneMessage("Registration worked, but welcome email failed to send.");
}

echo "Registration successful. Your account is awaiting admin approval.";
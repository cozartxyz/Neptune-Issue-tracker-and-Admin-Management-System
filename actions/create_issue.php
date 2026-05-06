<?php

require_once "../includes/auth.php";
requireUserOrAdmin();

require_once "../config/db.php";
require_once "../includes/functions.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    neptuneMessage("Invalid request.");
}

$requestType = trim($_POST["request_type"] ?? "");
$title = trim($_POST["title"] ?? "");
$description = trim($_POST["description"] ?? "");

/* i use subject field instaed now
$categoryId = isset($_POST["category_id"]) ? (int)$_POST["category_id"] : 0;
*/

$allowedTypes = ["email_reset", "password_reset", "other"];

if (!in_array($requestType, $allowedTypes, true)) {
    neptuneMessage("Invalid request type.");
}


if ($requestType === "email_reset") {

    if ($title === "") {
        $title = "Email Reset Request";
    }

    // CATEGORY DISABLED
    $description = "";
}


if ($requestType === "password_reset") {

    if ($title === "") {
        $title = "Password Reset Request";
    }

    // CATEGORY DISABLED
    $description = "";
}


if ($requestType === "other") {

    if ($title === "") {
        neptuneMessage("Title is required.");
    }

    /*
    Dont need ts no more
    if ($categoryId <= 0) {
        neptuneMessage("A valid category is required.");
    }
    */

    if ($description === "") {
        neptuneMessage("Description is required for support issues.");
    }
}


$stmt = $pdo->prepare("
    INSERT INTO issues (
        title,
        description,
        created_by,
        assigned_admin,
        status,
        request_type
    ) VALUES (?, ?, ?, NULL, 'Pending', ?)
");

$stmt->execute([
    $title,
    $description,
    $_SESSION["user_id"],
    $requestType
]);

$issueId = (int)$pdo->lastInsertId();

logAuditEvent(
    $pdo,
    (int)$_SESSION["user_id"],
    "create_issue",
    "issue",
    $issueId
);


if ($requestType === "email_reset") {
    $_SESSION["success_message"] = "Email reset request sent to admin.";
    header("Location: ../user/dashboard.php");
    exit;
}

if ($requestType === "password_reset") {
    $_SESSION["success_message"] = "Password reset request sent to admin.";
    header("Location: ../user/dashboard.php");
    exit;
}

$_SESSION["success_message"] = "Support issue submitted successfully.";
header("Location: ../user/dashboard.php");
exit;
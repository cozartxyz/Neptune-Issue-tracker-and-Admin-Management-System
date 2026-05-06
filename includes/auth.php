<?php

require_once "../includes/functions.php";

session_start();

function requireLogin(): void
{
    if (!isset($_SESSION["user_id"])) {
        header("Location: ../login.php");
        exit;
    }
}

function requireAdmin(): void
{
    requireLogin();

    if ($_SESSION["role"] !== "admin") {
        neptuneMessage("Access denied.");
    }
}

function requireUserOrAdmin(): void
{
    requireLogin();
}
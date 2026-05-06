<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once "../includes/functions.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Report Issue</title>

<link rel="stylesheet" href="../assets/css/neptune.css">

<style>

/* PAGE CONTENT */
.report-content{
    position:relative;
    z-index:3;
    min-height:calc(100vh - 70px);

    display:flex;
    align-items:center;
    justify-content:center;
}

/* CARD */
.report-card{
    width:520px;
    padding:28px;
    border-radius:24px;

    background:rgba(7,11,19,.42);
    border:1px solid rgba(255,255,255,.12);

    backdrop-filter:blur(8px);
    -webkit-backdrop-filter:blur(8px);

    box-shadow:
        0 18px 40px rgba(0,0,0,.45),
        inset 0 0 16px rgba(255,255,255,.02);
}

/* TITLE */
.report-card h1{
    font-size:20px;
    font-weight:300;
    color:rgba(255,255,255,.82);
    margin-bottom:20px;
    text-align:center;
}

/* FORM */
.report-form{
    display:flex;
    flex-direction:column;
    gap:14px;
}

/* INPUT */
.report-input{
    width:100%;
    height:42px;

    background:rgba(255,255,255,.02);
    border:1px solid rgba(255,255,255,.16);
    border-radius:4px;

    padding:0 12px;
    color:white;

    font-size:14px;
    font-weight:300;
    outline:none;
}

.report-input::placeholder{
    color:rgba(255,255,255,.66);
}

/* TEXTAREA (FIXED SIZE) */
.report-textarea{
    width:100%;
    height:140px;

    resize:none;

    background:rgba(255,255,255,.02);
    border:1px solid rgba(255,255,255,.16);
    border-radius:6px;

    padding:10px 12px;
    color:white;

    font-size:14px;
    font-weight:300;
    outline:none;
}

.report-textarea::placeholder{
    color:rgba(255,255,255,.66);
}

/* FOCUS */
.report-input:focus,
.report-textarea:focus{
    border-color:rgba(22,119,255,.7);
    box-shadow:0 0 10px rgba(22,119,255,.18);
}

/* BUTTON */
.report-submit{
    margin-top:6px;
    height:36px;
    border:none;
    border-radius:8px;

    background:linear-gradient(90deg,#001fff,#0012d8);

    color:white;
    font-size:14px;
    font-weight:700;
    cursor:pointer;

    box-shadow:0 0 14px rgba(0,42,255,.24);
}

.report-submit:hover{
    filter:brightness(1.08);
}

/* BOTTOM DOCK (NEW STYLE) */
.bottom-dock{
    position:absolute;
    left:50%;
    bottom:20px;
    transform:translateX(-50%);
    z-index:4;

    width:230px;
    height:58px;
    border-radius:999px;

    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.12);
    backdrop-filter:blur(10px);

    display:flex;
    align-items:center;
    justify-content:center;
    gap:34px;
}

.dock-link{
    width:42px;
    height:42px;
    border-radius:50%;

    display:flex;
    align-items:center;
    justify-content:center;

    text-decoration:none;
    color:white;
    font-size:22px;
}

.dock-link:hover{
    background:rgba(255,255,255,.10);
}

</style>
</head>

<body>

<div class="neptune-page">
<div class="neptune-shell">

<img src="../assets/images/darealneptune.png" class="neptune-bg" alt="Neptune">

<!-- NAV -->
<div class="neptune-nav">
    <div class="neptune-brand">NEPTUNE</div>
    <a href="../actions/logout.php" class="neptune-nav-btn">Logout</a>
</div>

<!-- MAIN -->
<div class="report-content">

<div class="report-card neptune-animate">

<h1>Submit Issue</h1>

<form class="report-form" action="../actions/create_issue.php" method="POST">

    <input type="hidden" name="request_type" value="other">

    <input 
        type="text" 
        name="title" 
        class="report-input" 
        placeholder="Subject"
        required
    >

    <textarea 
        name="description" 
        class="report-textarea" 
        placeholder="Describe your issue..."
        required
    ></textarea>

    <button type="submit" class="report-submit">
        Submit
    </button>

</form>

</div>

</div>

<!-- DOCK -->
<div class="bottom-dock">
    <a href="dashboard.php" class="dock-link">⌂</a>
    <a href="report_issue.php" class="dock-link">✉</a>
</div>

</div>
</div>
<script>
window.addEventListener("load", () => {
    document.body.classList.add("loaded");
});
</script>
</body>
</html>
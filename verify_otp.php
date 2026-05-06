<?php
session_start();

if (!isset($_SESSION["pending_otp_user_id"])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>OTP Verification</title>

<link rel="stylesheet" href="assets/css/neptune.css">

<style>
.otp-content {
    position: relative;
    z-index: 3;
    min-height: calc(100vh - 70px);
    display: flex;
    align-items: center;
    justify-content: center;
}

.otp-card {
    width: 430px;
    padding: 26px 32px;
    border-radius: 24px;
    background: rgba(7,11,19,.46);
    border: 1px solid rgba(255,255,255,.14);
    backdrop-filter: blur(8px);
    box-shadow:
        0 18px 40px rgba(0,0,0,.45),
        inset 0 0 16px rgba(255,255,255,.02);
    text-align: center;
}

.otp-card h1 {
    font-size: 20px;
    font-weight: 300;
    margin-bottom: 28px;
    color: rgba(255,255,255,.86);
}

.otp-boxes {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-bottom: 30px;
}

.otp-box {
    width: 50px;
    height: 58px;
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,.18);
    background: rgba(255,255,255,.02);
    color: white;
    font-size: 24px;
    font-weight: 300;
    text-align: center;
    outline: none;
}

.otp-box:focus {
    border-color: rgba(22,119,255,.75);
    box-shadow: 0 0 10px rgba(22,119,255,.2);
}

.otp-submit {
    width: 100%;
    height: 34px;
    border: none;
    border-radius: 8px;
    background: linear-gradient(90deg,#001fff,#0012d8);
    color: white;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 0 14px rgba(0,42,255,.24);
}

.otp-submit:hover {
    filter: brightness(1.08);
}
</style>
</head>

<body>

<div class="neptune-page">
<div class="neptune-shell">

<img src="assets/images/darealneptune.png" class="neptune-bg" alt="Neptune">

<div class="neptune-nav">
    <div class="neptune-brand">NEPTUNE</div>
</div>

<div class="otp-content">

    <div class="otp-card neptune-animate"">
        <h1>Enter OTP</h1>

        <form action="actions/verify_otp.php" method="POST" id="otpForm">

            <div class="otp-boxes">
                <input class="otp-box" maxlength="1" inputmode="numeric">
                <input class="otp-box" maxlength="1" inputmode="numeric">
                <input class="otp-box" maxlength="1" inputmode="numeric">
                <input class="otp-box" maxlength="1" inputmode="numeric">
                <input class="otp-box" maxlength="1" inputmode="numeric">
                <input class="otp-box" maxlength="1" inputmode="numeric">
            </div>

            <input type="hidden" name="otp" id="otpHidden">

            <button type="submit" class="otp-submit">Proceed</button>

        </form>
    </div>

</div>

</div>
</div>

<script>
const boxes = document.querySelectorAll(".otp-box");
const hidden = document.getElementById("otpHidden");
const form = document.getElementById("otpForm");

boxes.forEach((box, index) => {
    box.addEventListener("input", () => {
        box.value = box.value.replace(/[^0-9]/g, "");

        if (box.value && index < boxes.length - 1) {
            boxes[index + 1].focus();
        }
    });

    box.addEventListener("keydown", (e) => {
        if (e.key === "Backspace" && !box.value && index > 0) {
            boxes[index - 1].focus();
        }
    });
});

form.addEventListener("submit", () => {
    hidden.value = Array.from(boxes).map(box => box.value).join("");
});
</script>
<script>
window.addEventListener("load", () => {
    document.body.classList.add("loaded");
});
</script>
</body>
</html>
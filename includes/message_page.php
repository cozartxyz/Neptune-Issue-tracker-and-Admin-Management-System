<?php
if (!isset($message)) $message = "Something happened.";
if (!isset($type)) $type = "info";
if (!isset($link)) $link = "javascript:history.back()";
if (!isset($linkText)) $linkText = "Go Back";

$color = "#1677ff";

if ($type === "error") $color = "#ff4d4d";
if ($type === "success") $color = "#1677ff";
if ($type === "warning") $color = "#ffa500";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Neptune</title>

<link rel="stylesheet" href="../assets/css/neptune.css">

<style>
.msg-wrap {
    position: relative;
    z-index: 3;
    height: calc(100vh - 70px);
    display: flex;
    align-items: center;
    justify-content: center;
}

.msg-card {
    width: 420px;
    padding: 28px;
    border-radius: 20px;
    text-align: center;

    background: rgba(7,11,19,.46);
    border: 1px solid rgba(255,255,255,.14);

    backdrop-filter: blur(8px);

    box-shadow:
        0 18px 40px rgba(0,0,0,.45),
        inset 0 0 14px rgba(255,255,255,.02);
}

.msg-title {
    font-size: 22px;
    font-weight: 300;
    margin-bottom: 12px;
    color: white;
}

.msg-text {
    font-size: 14px;
    font-weight: 300;
    color: rgba(255,255,255,.75);
    margin-bottom: 24px;
}

.msg-btn {
    display: inline-block;
    padding: 10px 24px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 700;
    color: white;
    background: <?php echo $color; ?>;
    box-shadow: 0 0 12px rgba(22,119,255,.25);
}

.msg-btn:hover {
    filter: brightness(1.1);
}
</style>
</head>

<body>

<div class="neptune-page">
<div class="neptune-shell">

<img src="../assets/images/darealneptune.png" class="neptune-bg">

<div class="neptune-nav">
    <div class="neptune-brand">NEPTUNE</div>
</div>

<div class="msg-wrap">

    <div class="msg-card neptune-animate">
        <div class="msg-title">
            <?php echo ucfirst($type); ?>
        </div>

        <div class="msg-text">
            <?php echo htmlspecialchars($message); ?>
        </div>

        <a href="<?php echo $link; ?>" class="msg-btn">
            <?php echo $linkText; ?>
        </a>
    </div>

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
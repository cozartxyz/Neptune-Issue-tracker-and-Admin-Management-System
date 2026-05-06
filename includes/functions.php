<?php

require_once "config.php";
//fetch the IP
function getClientIp(): string
{
    $response = @file_get_contents(IPIFY_API_URL);

    if ($response !== false) {
        $data = json_decode($response, true);

        if (isset($data["ip"])) {
            return $data["ip"];
        }
    }

    return $_SERVER["REMOTE_ADDR"] ?? "0.0.0.0";
}
//hash ip
function hashIp(string $ip): string
{
    return hash("sha256", $ip . IP_HASH_SALT);
}
// geoloacation func fetches ip from API and checks timezone for log
function getGeolocationData(string $ip): array
{
    $url = IPINFO_API_URL . urlencode($ip) . "/json";

    $response = @file_get_contents($url);

    if ($response === false) {
        return [
            "country" => null,
            "city" => null,
            "timezone" => null
        ];
    }

    $data = json_decode($response, true);

    if (!is_array($data)) {
        return [
            "country" => null,
            "city" => null,
            "timezone" => null
        ];
    }

    return [
        "country" => $data["country"] ?? null,
        "city" => $data["city"] ?? null,
        "timezone" => $data["timezone"] ?? null
    ];
}
//audit events
function logAuditEvent(PDO $pdo, ?int $userId, string $action, ?string $targetType, ?int $targetId): void
{
    $ip = getClientIp();
    $ipHash = hashIp($ip);
    $geoData = getGeolocationData($ip);
    $userAgent = $_SERVER["HTTP_USER_AGENT"] ?? "unknown";

    $stmt = $pdo->prepare("
        INSERT INTO audit_logs (
            user_id,
            action,
            target_type,
            target_id,
            ip_hash,
            geo_country,
            geo_city,
            geo_timezone,
            user_agent
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $userId,
        $action,
        $targetType,
        $targetId,
        $ipHash,
        $geoData["country"],
        $geoData["city"],
        $geoData["timezone"],
        $userAgent
    ]);
}
// function for standard webpage redirect when error
function neptuneMessage($message, $type = "info", $link = null, $linkText = null)
{
    if ($link === null) {
        $link = "javascript:history.back()";
    }

    if ($linkText === null) {
        $linkText = "Go Back";
    }

    include __DIR__ . "/message_page.php";
    exit;
}
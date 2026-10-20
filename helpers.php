<?php
declare(strict_types=1);

const BEDS_STATUS_FLOW = [
    "Reported",
    "Verified",
    "Dispatched",
    "On-site",
    "Resolved",
];
const BEDS_INCIDENT_TYPES = [
    "Medical Emergency",
    "Fire",
    "Road Accident",
    "Domestic Dispute",
    "Typhoon / Flooding",
    "Other",
];

final class BedsHelper
{
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
    }

    public static function today(): string
    {
        return date("Y-m-d");
    }

    public static function initials(string $name): string
    {
        $words = preg_split("/\s+/", trim($name)) ?: [];
        $words = array_slice(array_filter($words), 0, 2);
        return strtoupper(
            implode(
                "",
                array_map(
                    static fn(string $word): string => substr($word, 0, 1),
                    $words,
                ),
            ),
        );
    }

    public static function locationLabel(array $db, mixed $id): string
    {
        $location = beds_find($db["locations"], "location_id", $id);
        return $location
            ? $location["address"] . ", " . $location["zone_number"]
            : "—";
    }

    public static function userName(array $db, mixed $id): string
    {
        $user = beds_find($db["users"], "user_id", $id);
        return $user["full_name"] ?? "System";
    }

    public static function roleName(array $db, mixed $id): string
    {
        $role = beds_find($db["roles"], "role_id", $id);
        return $role["role_name"] ?? "—";
    }

    public static function statusClass(string $status): string
    {
        return "st-" . preg_replace("/[^A-Za-z]/", "", $status);
    }

    public static function statusPill(string $status): string
    {
        return '<span class="status-pill ' .
            self::e(self::statusClass($status)) .
            '"><span class="dot"></span>' .
            self::e($status) .
            "</span>";
    }

    public static function emptyState(string $title, string $subtitle): string
    {
        return '<div class="empty-state"><div class="icon">&#9711;</div><div>' .
            self::e($title) .
            "</div><p>" .
            self::e($subtitle) .
            "</p></div>";
    }

    public static function redirect(string $page, array $params = []): string
    {
        return "index.php?" .
            http_build_query(array_merge(["page" => $page], $params));
    }
}

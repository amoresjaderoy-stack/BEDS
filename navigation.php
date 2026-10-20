<?php
declare(strict_types=1);

final class BedsNavigation
{
    public static function items(): array
    {
        return [
            [
                "section" => "Operations",
                "items" => [
                    [
                        "key" => "dashboard",
                        "label" => "Dashboard",
                        "icon" => "&#9673;",
                    ],
                    [
                        "key" => "incidents",
                        "label" => "Incidents",
                        "icon" => "&#9888;",
                    ],
                    [
                        "key" => "dispatch",
                        "label" => "Dispatch Assignment",
                        "icon" => "&#8635;",
                    ],
                ],
            ],
            [
                "section" => "Registry",
                "items" => [
                    [
                        "key" => "responders",
                        "label" => "Responders",
                        "icon" => "&#128101;",
                    ],
                    [
                        "key" => "resources",
                        "label" => "Resources",
                        "icon" => "&#128230;",
                    ],
                    [
                        "key" => "locations",
                        "label" => "Locations",
                        "icon" => "&#128205;",
                    ],
                ],
            ],
            [
                "section" => "Admin",
                "items" => [
                    [
                        "key" => "users",
                        "label" => "Users & Roles",
                        "icon" => "&#128274;",
                        "roles" => [1, 3],
                    ],
                    [
                        "key" => "reports",
                        "label" => "Reports",
                        "icon" => "&#128203;",
                    ],
                ],
            ],
        ];
    }

    public static function pageTitles(): array
    {
        return [
            "dashboard" => "Dashboard",
            "incidents" => "Incident Records",
            "dispatch" => "Dispatch Assignment",
            "responders" => "Responder Management",
            "resources" => "Resource & Equipment Tracking",
            "locations" => "Location & Landmark Logging",
            "users" => "Users & Roles",
            "reports" => "Custom Incident Reports",
        ];
    }

    public static function canView(string $page, ?array $user): bool
    {
        if ($page !== "users") {
            return true;
        }
        return in_array((int) ($user["role_id"] ?? 0), [1, 3], true);
    }
}

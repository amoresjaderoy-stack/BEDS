<?php
declare(strict_types=1);

// Load hosting config if present (backend/config.php).
// Sa InfinityFree/hosting: kopyaha ang config.example.php -> config.php
// unya ilisi ang DB_HOST, DB_NAME, DB_USER, DB_PASS didto.
if (is_file(__DIR__ . "/config.php")) {
    require_once __DIR__ . "/config.php";
}

// Defaults work for local XAMPP. On hosting, define via config.php or env vars.
if (!defined("BEDS_DB_HOST")) {
    define(
        "BEDS_DB_HOST",
        (string) (getenv("DB_HOST") ?: ($_ENV["DB_HOST"] ?? "127.0.0.1")),
    );
}
if (!defined("BEDS_DB_NAME")) {
    define(
        "BEDS_DB_NAME",
        (string) (
            getenv("DB_NAME") ?: ($_ENV["DB_NAME"] ?? "barangay_emergency_db")
        ),
    );
}
if (!defined("BEDS_DB_USER")) {
    define(
        "BEDS_DB_USER",
        (string) (getenv("DB_USER") ?: ($_ENV["DB_USER"] ?? "root")),
    );
}
if (!defined("BEDS_DB_PASSWORD")) {
    define(
        "BEDS_DB_PASSWORD",
        (string) (getenv("DB_PASS") ?: ($_ENV["DB_PASS"] ?? "")),
    );
}

function beds_db_connection(): PDO
{
    static $connection = null;
    if ($connection === null) {
        $connection = new PDO(
            "mysql:host=" .
                BEDS_DB_HOST .
                ";dbname=" .
                BEDS_DB_NAME .
                ";charset=utf8mb4",
            BEDS_DB_USER,
            BEDS_DB_PASSWORD,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ],
        );
    }
    return $connection;
}

function beds_format_datetime(?DateTimeInterface $date = null): string
{
    return ($date ?? new DateTime())->format("Y-m-d H:i");
}

function beds_days_ago(int $days, int $hours = 0, int $minutes = 0): string
{
    $date = new DateTime();
    $date->modify(
        sprintf("-%d days -%d hours -%d minutes", $days, $hours, $minutes),
    );
    return beds_format_datetime($date);
}

function beds_seed_db(): array
{
    return [
        "roles" => [
            ["role_id" => 1, "role_name" => "Dispatcher"],
            ["role_id" => 2, "role_name" => "Responder"],
            ["role_id" => 3, "role_name" => "Barangay Official"],
        ],
        "users" => [
            [
                "user_id" => 1,
                "role_id" => 1,
                "full_name" => "Roberto Dela Cruz",
                "username" => "rdelacruz",
                "password" => "1234",
                "contact_no" => "0917-123-4501",
                "email" => "r.delacruz@brgylibertad.gov.ph",
                "date_created" => "2024-01-10",
            ],
            [
                "user_id" => 2,
                "role_id" => 2,
                "full_name" => "Jenny Santos",
                "username" => "jsantos",
                "password" => "1234",
                "contact_no" => "0917-223-9981",
                "email" => "j.santos@brgylibertad.gov.ph",
                "date_created" => "2024-02-02",
            ],
            [
                "user_id" => 3,
                "role_id" => 3,
                "full_name" => "Kagawad Mario Reyes",
                "username" => "admin",
                "password" => "1234",
                "contact_no" => "0917-777-2200",
                "email" => "m.reyes@brgylibertad.gov.ph",
                "date_created" => "2023-11-15",
            ],
            [
                "user_id" => 4,
                "role_id" => 2,
                "full_name" => "Allan Bermejo",
                "username" => "abermejo",
                "password" => "1234",
                "contact_no" => "0918-445-2210",
                "email" => "a.bermejo@brgylibertad.gov.ph",
                "date_created" => "2024-03-19",
            ],
        ],
        "locations" => [
            [
                "location_id" => 1,
                "address" => "Purok 3, Sitio Maligaya",
                "zone_number" => "Zone 2",
                "landmark" => "Near Libertad Elementary School",
                "barangay" => "Libertad",
            ],
            [
                "location_id" => 2,
                "address" => "Purok 7, National Highway",
                "zone_number" => "Zone 4",
                "landmark" => "Beside Gasoline Station",
                "barangay" => "Libertad",
            ],
            [
                "location_id" => 3,
                "address" => "Purok 1, Riverside",
                "zone_number" => "Zone 1",
                "landmark" => "Near Wooden Bridge",
                "barangay" => "Libertad",
            ],
            [
                "location_id" => 4,
                "address" => "Purok 5, Upper Libertad",
                "zone_number" => "Zone 3",
                "landmark" => "Behind Barangay Chapel",
                "barangay" => "Libertad",
            ],
        ],
        "responders" => [
            [
                "responder_id" => 1,
                "user_id" => 2,
                "full_name" => "Jenny Santos",
                "contact_no" => "0917-223-9981",
                "specialization" => "First Aid / EMT",
                "availability_status" => "Available",
            ],
            [
                "responder_id" => 2,
                "user_id" => 4,
                "full_name" => "Allan Bermejo",
                "contact_no" => "0918-445-2210",
                "specialization" => "Fire & Rescue",
                "availability_status" => "On Duty",
            ],
            [
                "responder_id" => 3,
                "user_id" => null,
                "full_name" => "Carlo Miñoza",
                "contact_no" => "0919-556-0032",
                "specialization" => "Traffic / Road Accident",
                "availability_status" => "Available",
            ],
            [
                "responder_id" => 4,
                "user_id" => null,
                "full_name" => "Divine Ochoa",
                "contact_no" => "0920-334-8871",
                "specialization" => "First Aid / EMT",
                "availability_status" => "Off Duty",
            ],
        ],
        "resources" => [
            [
                "resource_id" => 1,
                "resource_name" => "Ambulance Unit 1",
                "resource_type" => "Vehicle",
                "status" => "Available",
            ],
            [
                "resource_id" => 2,
                "resource_name" => "Rescue Motorcycle",
                "resource_type" => "Vehicle",
                "status" => "In Use",
            ],
            [
                "resource_id" => 3,
                "resource_name" => "First Aid Kit A",
                "resource_type" => "Medical Supply",
                "status" => "Available",
            ],
            [
                "resource_id" => 4,
                "resource_name" => "Portable Radio Set",
                "resource_type" => "Communication",
                "status" => "Available",
            ],
            [
                "resource_id" => 5,
                "resource_name" => "Fire Extinguisher Set",
                "resource_type" => "Rescue Gear",
                "status" => "Maintenance",
            ],
        ],
        "incidents" => [
            [
                "incident_id" => 1,
                "reporter_name" => "Maria Cortez",
                "reporter_contact" => "0917-000-1122",
                "incident_type" => "Medical Emergency",
                "description" =>
                    "Elderly resident collapsed, difficulty breathing.",
                "location_id" => 1,
                "reported_by" => 1,
                "status" => "On-site",
                "date_reported" => beds_days_ago(0, 8),
                "date_resolved" => null,
            ],
            [
                "incident_id" => 2,
                "reporter_name" => "Pedro Lim",
                "reporter_contact" => "0918-222-3344",
                "incident_type" => "Road Accident",
                "description" =>
                    "Motorcycle collision along the highway, one injured.",
                "location_id" => 2,
                "reported_by" => 1,
                "status" => "Dispatched",
                "date_reported" => beds_days_ago(0, 3),
                "date_resolved" => null,
            ],
            [
                "incident_id" => 3,
                "reporter_name" => "Ana Reyes",
                "reporter_contact" => "0919-444-5566",
                "incident_type" => "Fire",
                "description" =>
                    "Small kitchen fire, contained but smoke reported.",
                "location_id" => 4,
                "reported_by" => 1,
                "status" => "Verified",
                "date_reported" => beds_days_ago(0, 1, 20),
                "date_resolved" => null,
            ],
            [
                "incident_id" => 4,
                "reporter_name" => "Junjun Tan",
                "reporter_contact" => "0920-555-1290",
                "incident_type" => "Typhoon / Flooding",
                "description" => "Rising floodwater near riverside houses.",
                "location_id" => 3,
                "reported_by" => 3,
                "status" => "Reported",
                "date_reported" => beds_days_ago(0, 0, 40),
                "date_resolved" => null,
            ],
            [
                "incident_id" => 5,
                "reporter_name" => "Grace Nuñez",
                "reporter_contact" => "0921-888-0021",
                "incident_type" => "Domestic Dispute",
                "description" =>
                    "Loud altercation reported by neighbors, resolved peacefully.",
                "location_id" => 1,
                "reported_by" => 1,
                "status" => "Resolved",
                "date_reported" => beds_days_ago(2),
                "date_resolved" => beds_days_ago(2, -1),
            ],
            [
                "incident_id" => 6,
                "reporter_name" => "Fernando Cruz",
                "reporter_contact" => "0917-321-7788",
                "incident_type" => "Medical Emergency",
                "description" =>
                    "Resident with high fever requesting transport to health center.",
                "location_id" => 2,
                "reported_by" => 1,
                "status" => "Resolved",
                "date_reported" => beds_days_ago(4),
                "date_resolved" => beds_days_ago(4, -1),
            ],
        ],
        "dispatch_assignment" => [
            [
                "assignment_id" => 1,
                "incident_id" => 1,
                "responder_id" => 1,
                "resource_id" => 1,
                "dispatch_time" => beds_days_ago(0, 8),
                "arrival_time" => beds_days_ago(0, 7, 40),
            ],
            [
                "assignment_id" => 2,
                "incident_id" => 2,
                "responder_id" => 3,
                "resource_id" => 2,
                "dispatch_time" => beds_days_ago(0, 3),
                "arrival_time" => null,
            ],
            [
                "assignment_id" => 3,
                "incident_id" => 5,
                "responder_id" => 2,
                "resource_id" => 4,
                "dispatch_time" => beds_days_ago(2),
                "arrival_time" => beds_days_ago(2),
            ],
            [
                "assignment_id" => 4,
                "incident_id" => 6,
                "responder_id" => 1,
                "resource_id" => 3,
                "dispatch_time" => beds_days_ago(4),
                "arrival_time" => beds_days_ago(4),
            ],
        ],
        "incident_status_log" => [
            [
                "log_id" => 1,
                "incident_id" => 1,
                "status" => "Reported",
                "updated_by" => 1,
                "timestamp" => beds_days_ago(0, 8, 10),
            ],
            [
                "log_id" => 2,
                "incident_id" => 1,
                "status" => "Verified",
                "updated_by" => 1,
                "timestamp" => beds_days_ago(0, 8, 5),
            ],
            [
                "log_id" => 3,
                "incident_id" => 1,
                "status" => "Dispatched",
                "updated_by" => 1,
                "timestamp" => beds_days_ago(0, 8),
            ],
            [
                "log_id" => 4,
                "incident_id" => 1,
                "status" => "On-site",
                "updated_by" => 1,
                "timestamp" => beds_days_ago(0, 7, 40),
            ],
            [
                "log_id" => 5,
                "incident_id" => 2,
                "status" => "Reported",
                "updated_by" => 1,
                "timestamp" => beds_days_ago(0, 3, 15),
            ],
            [
                "log_id" => 6,
                "incident_id" => 2,
                "status" => "Verified",
                "updated_by" => 1,
                "timestamp" => beds_days_ago(0, 3, 10),
            ],
            [
                "log_id" => 7,
                "incident_id" => 2,
                "status" => "Dispatched",
                "updated_by" => 1,
                "timestamp" => beds_days_ago(0, 3),
            ],
            [
                "log_id" => 8,
                "incident_id" => 3,
                "status" => "Reported",
                "updated_by" => 1,
                "timestamp" => beds_days_ago(0, 1, 25),
            ],
            [
                "log_id" => 9,
                "incident_id" => 3,
                "status" => "Verified",
                "updated_by" => 1,
                "timestamp" => beds_days_ago(0, 1, 20),
            ],
            [
                "log_id" => 10,
                "incident_id" => 4,
                "status" => "Reported",
                "updated_by" => 3,
                "timestamp" => beds_days_ago(0, 0, 40),
            ],
            [
                "log_id" => 11,
                "incident_id" => 5,
                "status" => "Reported",
                "updated_by" => 1,
                "timestamp" => beds_days_ago(2, 0, 30),
            ],
            [
                "log_id" => 12,
                "incident_id" => 5,
                "status" => "Resolved",
                "updated_by" => 1,
                "timestamp" => beds_days_ago(2, -1),
            ],
            [
                "log_id" => 13,
                "incident_id" => 6,
                "status" => "Reported",
                "updated_by" => 1,
                "timestamp" => beds_days_ago(4, 0, 20),
            ],
            [
                "log_id" => 14,
                "incident_id" => 6,
                "status" => "Resolved",
                "updated_by" => 1,
                "timestamp" => beds_days_ago(4, -1),
            ],
        ],
        "reports" => [
            [
                "report_id" => 1,
                "generated_by" => 3,
                "report_type" => "Monthly Summary",
                "date_range_start" => date("Y-m-01", strtotime("-1 month")),
                "date_range_end" => date("Y-m-t", strtotime("-1 month")),
                "total_incidents" => 24,
                "avg_response_time" => "14 mins",
                "date_generated" => date("Y-m-d H:i", strtotime("-26 days")),
            ],
        ],
        "_seq" => [
            "incident_id" => 7,
            "location_id" => 5,
            "responder_id" => 5,
            "resource_id" => 6,
            "assignment_id" => 5,
            "log_id" => 15,
            "report_id" => 2,
            "user_id" => 5,
        ],
    ];
}

function beds_save_db(array $db): void
{
    $tables = [
        "roles" => "role_id",
        "users" => "user_id",
        "locations" => "location_id",
        "responders" => "responder_id",
        "resources" => "resource_id",
        "incidents" => "incident_id",
        "dispatch_assignment" => "assignment_id",
        "incident_status_log" => "log_id",
        "reports" => "report_id",
    ];
    $connection = beds_db_connection();
    $connection->beginTransaction();
    try {
        foreach (array_reverse($tables, true) as $table => $primaryKey) {
            $statement = $connection->query(
                "SELECT `" . $primaryKey . "` FROM `" . $table . "`",
            );
            $existingIds = $statement->fetchAll(PDO::FETCH_COLUMN);
            $currentIds = array_column($db[$table] ?? [], $primaryKey);
            $delete = $connection->prepare(
                "DELETE FROM `" . $table . "` WHERE `" . $primaryKey . "` = ?",
            );
            foreach ($existingIds as $existingId) {
                if (!in_array($existingId, $currentIds, false)) {
                    $delete->execute([$existingId]);
                }
            }
        }

        foreach ($tables as $table => $primaryKey) {
            foreach ($db[$table] ?? [] as $row) {
                $columns = array_keys($row);
                $quotedColumns = array_map(
                    static fn(string $column): string => "`" . $column . "`",
                    $columns,
                );
                $updates = array_filter(
                    $quotedColumns,
                    static fn(string $column): bool => $column !==
                        "`" . $primaryKey . "`",
                );
                $sql =
                    "INSERT INTO `" .
                    $table .
                    "` (" .
                    implode(", ", $quotedColumns) .
                    ") VALUES (" .
                    implode(", ", array_fill(0, count($columns), "?")) .
                    ") ON DUPLICATE KEY UPDATE " .
                    implode(
                        ", ",
                        array_map(
                            static fn(string $column): string => $column .
                                " = VALUES(" .
                                $column .
                                ")",
                            $updates,
                        ),
                    );
                $connection->prepare($sql)->execute(array_values($row));
            }
        }
        $connection->commit();
    } catch (Throwable $error) {
        $connection->rollBack();
        throw $error;
    }
}

function beds_load_db(): array
{
    $connection = beds_db_connection();
    $db = [];
    $sequences = [
        "incident_id" => ["incidents", "incident_id"],
        "location_id" => ["locations", "location_id"],
        "responder_id" => ["responders", "responder_id"],
        "resource_id" => ["resources", "resource_id"],
        "assignment_id" => ["dispatch_assignment", "assignment_id"],
        "log_id" => ["incident_status_log", "log_id"],
        "report_id" => ["reports", "report_id"],
        "user_id" => ["users", "user_id"],
    ];
    foreach (
        [
            "roles",
            "users",
            "locations",
            "responders",
            "resources",
            "incidents",
            "dispatch_assignment",
            "incident_status_log",
            "reports",
        ]
        as $table
    ) {
        $db[$table] = $connection
            ->query("SELECT * FROM `" . $table . "`")
            ->fetchAll();
    }
    usort(
        $db["roles"],
        static fn(array $a, array $b): int => (int) $a["role_id"] <=>
            (int) $b["role_id"],
    );
    foreach ($sequences as $key => [$table, $primaryKey]) {
        $db["_seq"][$key] = 1;
        foreach ($db[$table] as $row) {
            $db["_seq"][$key] = max(
                $db["_seq"][$key],
                (int) $row[$primaryKey] + 1,
            );
        }
    }
    return $db;
}

function beds_next_id(array &$db, string $key): int
{
    $id = (int) ($db["_seq"][$key] ?? 1);
    $db["_seq"][$key] = $id + 1;
    return $id;
}

function beds_find(array $rows, string $field, mixed $value): ?array
{
    foreach ($rows as $row) {
        if ((string) ($row[$field] ?? "") === (string) $value) {
            return $row;
        }
    }
    return null;
}

function beds_find_index(array $rows, string $field, mixed $value): ?int
{
    foreach ($rows as $index => $row) {
        if ((string) ($row[$field] ?? "") === (string) $value) {
            return $index;
        }
    }
    return null;
}

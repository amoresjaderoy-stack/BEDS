<?php
declare(strict_types=1);

function beds_log_status(
    array &$db,
    int $incidentId,
    string $status,
    int $userId,
): void {
    $db["incident_status_log"][] = [
        "log_id" => beds_next_id($db, "log_id"),
        "incident_id" => $incidentId,
        "status" => $status,
        "updated_by" => $userId,
        "timestamp" => beds_format_datetime(),
    ];
}

function beds_post_action(array &$db, array $user): array
{
    $action = (string) ($_POST["action"] ?? "");
    $page = (string) ($_POST["page"] ?? "dashboard");
    $id = (int) ($_POST["id"] ?? 0);

    if ($action === "save_incident") {
        $reporterName = trim((string) ($_POST["reporter_name"] ?? ""));
        $reporterContact = trim((string) ($_POST["reporter_contact"] ?? ""));
        $incidentType = trim((string) ($_POST["incident_type"] ?? "Other"));
        $locationId = (int) ($_POST["location_id"] ?? 0);
        $description = trim((string) ($_POST["description"] ?? ""));
        if (
            $reporterName === "" ||
            $reporterContact === "" ||
            $description === ""
        ) {
            return [
                "message" =>
                    "Reporter name, contact number, and description are required.",
                "redirect" => BedsHelper::redirect("incidents", [
                    "modal" => "incident",
                    "id" => $id,
                ]),
            ];
        }
        $index = $id
            ? beds_find_index($db["incidents"], "incident_id", $id)
            : null;
        if ($index !== null) {
            $oldStatus = $db["incidents"][$index]["status"];
            $newStatus = trim((string) ($_POST["status"] ?? $oldStatus));
            $db["incidents"][$index] = array_merge($db["incidents"][$index], [
                "reporter_name" => $reporterName,
                "reporter_contact" => $reporterContact,
                "incident_type" => $incidentType,
                "location_id" => $locationId,
                "description" => $description,
                "status" => $newStatus,
                "date_resolved" =>
                    $newStatus === "Resolved"
                        ? ($db["incidents"][$index]["date_resolved"] ?:
                        beds_format_datetime())
                        : null,
            ]);
            if ($oldStatus !== $newStatus) {
                beds_log_status($db, $id, $newStatus, (int) $user["user_id"]);
            }
            return [
                "message" => "Incident updated.",
                "redirect" => BedsHelper::redirect("incidents"),
            ];
        }
        $newId = beds_next_id($db, "incident_id");
        $db["incidents"][] = [
            "incident_id" => $newId,
            "reporter_name" => $reporterName,
            "reporter_contact" => $reporterContact,
            "incident_type" => $incidentType,
            "description" => $description,
            "location_id" => $locationId,
            "reported_by" => $user["user_id"],
            "status" => "Reported",
            "date_reported" => beds_format_datetime(),
            "date_resolved" => null,
        ];
        beds_log_status($db, $newId, "Reported", (int) $user["user_id"]);
        return [
            "message" => "Incident logged.",
            "redirect" => BedsHelper::redirect("incidents"),
        ];
    }

    if ($action === "delete_incident") {
        $db["incidents"] = array_values(
            array_filter(
                $db["incidents"],
                static fn(array $row): bool => (int) $row["incident_id"] !==
                    $id,
            ),
        );
        $db["incident_status_log"] = array_values(
            array_filter(
                $db["incident_status_log"],
                static fn(array $row): bool => (int) $row["incident_id"] !==
                    $id,
            ),
        );
        $db["dispatch_assignment"] = array_values(
            array_filter(
                $db["dispatch_assignment"],
                static fn(array $row): bool => (int) $row["incident_id"] !==
                    $id,
            ),
        );
        return [
            "message" => "Incident deleted.",
            "redirect" => BedsHelper::redirect("incidents"),
        ];
    }

    if ($action === "create_dispatch") {
        $incidentId = (int) ($_POST["incident_id"] ?? 0);
        $responderId = (int) ($_POST["responder_id"] ?? 0);
        $resourceId = (int) ($_POST["resource_id"] ?? 0);
        if (!$incidentId || !$responderId || !$resourceId) {
            return [
                "message" => "Select an incident, responder, and resource.",
                "redirect" => BedsHelper::redirect("dispatch"),
            ];
        }
        $db["dispatch_assignment"][] = [
            "assignment_id" => beds_next_id($db, "assignment_id"),
            "incident_id" => $incidentId,
            "responder_id" => $responderId,
            "resource_id" => $resourceId,
            "dispatch_time" => beds_format_datetime(),
            "arrival_time" => null,
        ];
        $incidentIndex = beds_find_index(
            $db["incidents"],
            "incident_id",
            $incidentId,
        );
        if (
            $incidentIndex !== null &&
            array_search(
                $db["incidents"][$incidentIndex]["status"],
                BEDS_STATUS_FLOW,
                true,
            ) < array_search("Dispatched", BEDS_STATUS_FLOW, true)
        ) {
            $db["incidents"][$incidentIndex]["status"] = "Dispatched";
            beds_log_status(
                $db,
                $incidentId,
                "Dispatched",
                (int) $user["user_id"],
            );
        }
        $responderIndex = beds_find_index(
            $db["responders"],
            "responder_id",
            $responderId,
        );
        if ($responderIndex !== null) {
            $db["responders"][$responderIndex]["availability_status"] =
                "On Duty";
        }
        $resourceIndex = beds_find_index(
            $db["resources"],
            "resource_id",
            $resourceId,
        );
        if ($resourceIndex !== null) {
            $db["resources"][$resourceIndex]["status"] = "In Use";
        }
        return [
            "message" => "Responder dispatched.",
            "redirect" => BedsHelper::redirect("dispatch"),
        ];
    }

    if ($action === "mark_arrived") {
        $assignmentIndex = beds_find_index(
            $db["dispatch_assignment"],
            "assignment_id",
            $id,
        );
        if ($assignmentIndex !== null) {
            $db["dispatch_assignment"][$assignmentIndex][
                "arrival_time"
            ] = beds_format_datetime();
            $incidentId =
                (int) $db["dispatch_assignment"][$assignmentIndex][
                    "incident_id"
                ];
            $incidentIndex = beds_find_index(
                $db["incidents"],
                "incident_id",
                $incidentId,
            );
            if (
                $incidentIndex !== null &&
                array_search(
                    $db["incidents"][$incidentIndex]["status"],
                    BEDS_STATUS_FLOW,
                    true,
                ) < array_search("On-site", BEDS_STATUS_FLOW, true)
            ) {
                $db["incidents"][$incidentIndex]["status"] = "On-site";
                beds_log_status(
                    $db,
                    $incidentId,
                    "On-site",
                    (int) $user["user_id"],
                );
            }
        }
        return [
            "message" => "Marked as arrived on-site.",
            "redirect" => BedsHelper::redirect("dispatch"),
        ];
    }

    if ($action === "delete_dispatch") {
        $db["dispatch_assignment"] = array_values(
            array_filter(
                $db["dispatch_assignment"],
                static fn(array $row): bool => (int) $row["assignment_id"] !==
                    $id,
            ),
        );
        return [
            "message" => "Assignment removed.",
            "redirect" => BedsHelper::redirect("dispatch"),
        ];
    }

    $crudMap = [
        "save_responder" => [
            "table" => "responders",
            "id_field" => "responder_id",
            "fields" => [
                "full_name",
                "contact_no",
                "specialization",
                "availability_status",
            ],
            "required" => ["full_name", "contact_no"],
            "redirect" => "responders",
            "new_message" => "Responder added.",
            "edit_message" => "Responder updated.",
        ],
        "save_resource" => [
            "table" => "resources",
            "id_field" => "resource_id",
            "fields" => ["resource_name", "resource_type", "status"],
            "required" => ["resource_name"],
            "redirect" => "resources",
            "new_message" => "Resource added.",
            "edit_message" => "Resource updated.",
        ],
        "save_location" => [
            "table" => "locations",
            "id_field" => "location_id",
            "fields" => ["address", "zone_number", "barangay", "landmark"],
            "required" => ["address", "zone_number"],
            "redirect" => "locations",
            "new_message" => "Location added.",
            "edit_message" => "Location updated.",
        ],
    ];
    if (isset($crudMap[$action])) {
        $config = $crudMap[$action];
        $values = [];
        foreach ($config["fields"] as $field) {
            $values[$field] = trim((string) ($_POST[$field] ?? ""));
        }
        foreach ($config["required"] as $field) {
            if ($values[$field] === "") {
                return [
                    "message" => "Please complete all required fields.",
                    "redirect" => BedsHelper::redirect($config["redirect"], [
                        "modal" => substr($action, 5),
                        "id" => $id,
                    ]),
                ];
            }
        }
        $index = $id
            ? beds_find_index($db[$config["table"]], $config["id_field"], $id)
            : null;
        if ($index !== null) {
            $db[$config["table"]][$index] = array_merge(
                $db[$config["table"]][$index],
                $values,
            );
            return [
                "message" => $config["edit_message"],
                "redirect" => BedsHelper::redirect($config["redirect"]),
            ];
        }
        $new = [$config["id_field"] => beds_next_id($db, $config["id_field"])];
        foreach ($values as $key => $value) {
            $new[$key] = $value;
        }
        if ($action === "save_responder") {
            $new["user_id"] = null;
        }
        $db[$config["table"]][] = $new;
        return [
            "message" => $config["new_message"],
            "redirect" => BedsHelper::redirect($config["redirect"]),
        ];
    }

    foreach (
        [
            "delete_responder" => [
                "responders",
                "responder_id",
                "Responder removed.",
            ],
            "delete_resource" => [
                "resources",
                "resource_id",
                "Resource removed.",
            ],
            "delete_location" => [
                "locations",
                "location_id",
                "Location removed.",
            ],
        ]
        as $deleteAction => [$table, $field, $message]
    ) {
        if ($action === $deleteAction) {
            $db[$table] = array_values(
                array_filter(
                    $db[$table],
                    static fn(array $row): bool => (int) $row[$field] !== $id,
                ),
            );
            return [
                "message" => $message,
                "redirect" => BedsHelper::redirect($page),
            ];
        }
    }

    if ($action === "save_user") {
        $values = [
            "full_name" => trim((string) ($_POST["full_name"] ?? "")),
            "username" => trim((string) ($_POST["username"] ?? "")),
            "password" => (string) ($_POST["password"] ?? ""),
            "role_id" => (int) ($_POST["role_id"] ?? 0),
            "contact_no" => trim((string) ($_POST["contact_no"] ?? "")),
            "email" => trim((string) ($_POST["email"] ?? "")),
        ];
        $index = $id ? beds_find_index($db["users"], "user_id", $id) : null;
        if ($values["full_name"] === "" || $values["username"] === "") {
            return [
                "message" => "Full name and username are required.",
                "redirect" => BedsHelper::redirect("users", [
                    "modal" => "user",
                    "id" => $id,
                ]),
            ];
        }
        if ($index === null && $values["password"] === "") {
            return [
                "message" => "Password is required for a new account.",
                "redirect" => BedsHelper::redirect("users", [
                    "modal" => "user",
                    "id" => $id,
                ]),
            ];
        }
        if ($values["password"] !== "" && strlen($values["password"]) < 6) {
            return [
                "message" => "Password must be at least 6 characters long.",
                "redirect" => BedsHelper::redirect("users", [
                    "modal" => "user",
                    "id" => $id,
                ]),
            ];
        }
        if (!preg_match('/^[A-Za-z0-9._-]{3,30}$/', $values["username"])) {
            return [
                "message" =>
                    "Username must be 3-30 characters and may only contain letters, numbers, dots, underscores, or hyphens.",
                "redirect" => BedsHelper::redirect("users", [
                    "modal" => "user",
                    "id" => $id,
                ]),
            ];
        }
        if (
            $values["email"] !== "" &&
            !filter_var($values["email"], FILTER_VALIDATE_EMAIL)
        ) {
            return [
                "message" => "Please enter a valid email address.",
                "redirect" => BedsHelper::redirect("users", [
                    "modal" => "user",
                    "id" => $id,
                ]),
            ];
        }
        foreach ($db["users"] as $otherUser) {
            if ((int) $otherUser["user_id"] === $id) {
                continue;
            }
            if (
                strcasecmp($otherUser["username"], $values["username"]) === 0
            ) {
                return [
                    "message" => "That username is already taken.",
                    "redirect" => BedsHelper::redirect("users", [
                        "modal" => "user",
                        "id" => $id,
                    ]),
                ];
            }
            if (
                $values["email"] !== "" &&
                trim((string) ($otherUser["email"] ?? "")) !== "" &&
                strcasecmp($otherUser["email"], $values["email"]) === 0
            ) {
                return [
                    "message" =>
                        "That email address is already registered to another account.",
                    "redirect" => BedsHelper::redirect("users", [
                        "modal" => "user",
                        "id" => $id,
                    ]),
                ];
            }
        }
        if (
            beds_find($db["roles"], "role_id", $values["role_id"]) === null
        ) {
            $values["role_id"] =
                $index !== null
                    ? (int) $db["users"][$index]["role_id"]
                    : 2;
        }
        if ($values["password"] !== "") {
            $values["password"] = password_hash(
                $values["password"],
                PASSWORD_DEFAULT,
            );
        } else {
            unset($values["password"]);
        }
        if ($index !== null) {
            $db["users"][$index] = array_merge($db["users"][$index], $values);
            return [
                "message" => "User updated.",
                "redirect" => BedsHelper::redirect("users"),
            ];
        }
        $values["user_id"] = beds_next_id($db, "user_id");
        $values["date_created"] = BedsHelper::today();
        $db["users"][] = $values;
        return [
            "message" => "User added.",
            "redirect" => BedsHelper::redirect("users"),
        ];
    }

    if ($action === "delete_user") {
        if ($id === (int) $user["user_id"]) {
            return [
                "message" => "You cannot remove the account currently in use.",
                "redirect" => BedsHelper::redirect("users"),
            ];
        }
        $db["users"] = array_values(
            array_filter(
                $db["users"],
                static fn(array $row): bool => (int) $row["user_id"] !== $id,
            ),
        );
        return [
            "message" => "User removed.",
            "redirect" => BedsHelper::redirect("users"),
        ];
    }

    if ($action === "generate_report") {
        $start = (string) ($_POST["date_range_start"] ?? "");
        $end = (string) ($_POST["date_range_end"] ?? "");
        if ($start === "" || $end === "") {
            return [
                "message" => "Select a date range.",
                "redirect" => BedsHelper::redirect("reports"),
            ];
        }
        $inRange = array_values(
            array_filter(
                $db["incidents"],
                static fn(array $incident): bool => substr(
                    $incident["date_reported"],
                    0,
                    10,
                ) >= $start &&
                    substr($incident["date_reported"], 0, 10) <= $end,
            ),
        );
        $resolved = array_values(
            array_filter(
                $inRange,
                static fn(array $incident): bool => !empty(
                    $incident["date_resolved"]
                ),
            ),
        );
        $minutes = 0;
        foreach ($resolved as $incident) {
            $minutes += max(
                0,
                (strtotime($incident["date_resolved"]) -
                    strtotime($incident["date_reported"])) /
                    60,
            );
        }
        $avg = count($resolved)
            ? round($minutes / count($resolved)) . " mins"
            : "N/A";
        $reportId = beds_next_id($db, "report_id");
        $db["reports"][] = [
            "report_id" => $reportId,
            "generated_by" => $user["user_id"],
            "report_type" =>
                (string) ($_POST["report_type"] ?? "Weekly Summary"),
            "date_range_start" => $start,
            "date_range_end" => $end,
            "total_incidents" => count($inRange),
            "avg_response_time" => $avg,
            "date_generated" => beds_format_datetime(),
        ];
        return [
            "message" => "Report generated.",
            "redirect" => BedsHelper::redirect("reports", [
                "modal" => "report",
                "id" => $reportId,
            ]),
        ];
    }

    return ["message" => "", "redirect" => BedsHelper::redirect($page)];
}

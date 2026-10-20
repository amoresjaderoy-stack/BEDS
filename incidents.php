<?php
$statusFilter = (string) ($_GET["status"] ?? "all");
$typeFilter = (string) ($_GET["type"] ?? "all");
$query = trim((string) ($_GET["q"] ?? ""));
$list = $db["incidents"];
usort(
    $list,
    static fn(array $a, array $b): int => strcmp(
        $b["date_reported"],
        $a["date_reported"],
    ),
);
$list = array_values(
    array_filter(
        $list,
        static fn(array $i): bool => ($statusFilter === "all" ||
            $i["status"] === $statusFilter) &&
            ($typeFilter === "all" || $i["incident_type"] === $typeFilter) &&
            ($query === "" ||
                stripos(
                    $i["reporter_name"] .
                        $i["incident_type"] .
                        $i["description"],
                    $query,
                ) !== false),
    ),
);
?>
<div class="panel"><div class="panel-head" style="flex-wrap:wrap;gap:10px;"><form class="toolbar" method="get" action="index.php"><input type="hidden" name="page" value="incidents"><input class="search-input" name="q" placeholder="Search reporter, type, description…" value="<?= BedsHelper::e(
    $query,
) ?>"><select class="select-filter" name="status"><option value="all">All Statuses</option><?php foreach (
    BEDS_STATUS_FLOW
    as $status
): ?><option value="<?= BedsHelper::e($status) ?>" <?= $statusFilter === $status
    ? "selected"
    : "" ?>><?= BedsHelper::e(
    $status,
) ?></option><?php endforeach; ?></select><select class="select-filter" name="type"><option value="all">All Types</option><?php foreach (
    BEDS_INCIDENT_TYPES
    as $type
): ?><option value="<?= BedsHelper::e($type) ?>" <?= $typeFilter === $type
    ? "selected"
    : "" ?>><?= BedsHelper::e(
    $type,
) ?></option><?php endforeach; ?></select><button class="btn btn-sm btn-ghost" type="submit">Filter</button></form><a class="btn btn-sm btn-accent" href="<?= BedsHelper::e(
    BedsHelper::redirect("incidents", ["modal" => "incident"]),
) ?>">+ Log New Incident</a></div><div class="panel-body"><?php if (!$list): ?><?= BedsHelper::emptyState(
        "No incidents match your filters.",
        "Try clearing search or filters.",
    ) ?><?php else: ?><table><thead><tr><th>ID</th><th>Type</th><th>Reporter</th><th>Contact</th><th>Location</th><th>Status</th><th>Reported</th><th></th></tr></thead><tbody><?php foreach (
    $list
    as $incident
): ?><tr><td class="mono">#<?= str_pad(
    (string) $incident["incident_id"],
    4,
    "0",
    STR_PAD_LEFT,
) ?></td><td><?= BedsHelper::e(
    $incident["incident_type"],
) ?></td><td><?= BedsHelper::e(
    $incident["reporter_name"],
) ?></td><td class="mono text-muted"><?= BedsHelper::e(
    $incident["reporter_contact"],
) ?></td><td><?= BedsHelper::e(
    BedsHelper::locationLabel($db, $incident["location_id"]),
) ?></td><td><?= BedsHelper::statusPill(
    $incident["status"],
) ?></td><td class="mono text-muted"><?= BedsHelper::e(
    $incident["date_reported"],
) ?></td><td><div class="action-row"><a class="btn btn-sm btn-ghost" href="<?= BedsHelper::e(
    BedsHelper::redirect("incidents", [
        "modal" => "incident_detail",
        "id" => $incident["incident_id"],
    ]),
) ?>">View</a><a class="btn btn-sm btn-ghost" href="<?= BedsHelper::e(
    BedsHelper::redirect("incidents", [
        "modal" => "incident",
        "id" => $incident["incident_id"],
    ]),
) ?>">Edit</a><form method="post" action="index.php"><input type="hidden" name="action" value="delete_incident"><input type="hidden" name="page" value="incidents"><input type="hidden" name="id" value="<?= (int) $incident[
    "incident_id"
] ?>"><button class="btn btn-sm btn-danger" type="submit">Delete</button></form></div></td></tr><?php endforeach; ?></tbody></table><?php
endif; ?></div></div>

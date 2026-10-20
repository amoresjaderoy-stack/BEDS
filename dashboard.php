<?php
$active = array_values(
    array_filter(
        $db["incidents"],
        static fn(array $incident): bool => $incident["status"] !== "Resolved",
    ),
);
$resolvedToday = array_values(
    array_filter(
        $db["incidents"],
        static fn(array $incident): bool => $incident["status"] ===
            "Resolved" &&
            str_starts_with(
                (string) ($incident["date_resolved"] ?? ""),
                BedsHelper::today(),
            ),
    ),
);
$availableResponders = count(
    array_filter(
        $db["responders"],
        static fn(array $row): bool => $row["availability_status"] ===
            "Available",
    ),
);
$availableResources = count(
    array_filter(
        $db["resources"],
        static fn(array $row): bool => $row["status"] === "Available",
    ),
);
$logs = $db["incident_status_log"];
usort(
    $logs,
    static fn(array $a, array $b): int => strcmp(
        $b["timestamp"],
        $a["timestamp"],
    ),
);
$logs = array_slice($logs, 0, 10);
?>
<div class="ticker-wrap"><div class="ticker-label">Live Log</div><div class="ticker-track"><div class="ticker-content"><?php foreach (
    array_merge($logs, $logs)
    as $log
):
    $inc = beds_find(
        $db["incidents"],
        "incident_id",
        $log["incident_id"],
    ); ?><span><b>#<?= str_pad(
    (string) $log["incident_id"],
    4,
    "0",
    STR_PAD_LEFT,
) ?></b> <?= BedsHelper::e(
    $inc["incident_type"] ?? "",
) ?> → <b><?= BedsHelper::e($log["status"]) ?></b> · <?= BedsHelper::e(
    $log["timestamp"],
) ?></span><?php
endforeach; ?></div></div></div>
<div class="stat-grid"><div class="stat-card" style="--accentline:var(--accent)"><div class="stat-label">Active Incidents</div><div class="stat-value"><?= count(
    $active,
) ?></div><div class="stat-foot">Across all statuses except Resolved</div></div><div class="stat-card" style="--accentline:var(--good)"><div class="stat-label">Resolved Today</div><div class="stat-value"><?= count(
    $resolvedToday,
) ?></div><div class="stat-foot"><?= count(
    array_filter(
        $db["incidents"],
        static fn(array $i): bool => $i["status"] === "Resolved",
    ),
) ?> resolved all-time</div></div><div class="stat-card" style="--accentline:var(--info)"><div class="stat-label">Responders Available</div><div class="stat-value"><?= $availableResponders ?> <span style="font-size:16px;color:var(--muted);">/ <?= count(
     $db["responders"],
 ) ?></span></div><div class="stat-foot">Ready for immediate deployment</div></div><div class="stat-card" style="--accentline:var(--warn)"><div class="stat-label">Resources Ready</div><div class="stat-value"><?= $availableResources ?> <span style="font-size:16px;color:var(--muted);">/ <?= count(
     $db["resources"],
 ) ?></span></div><div class="stat-foot">Vehicles, medical & rescue gear</div></div></div>
<div class="panel"><div class="panel-head"><h2>Active Incidents</h2><a class="btn btn-sm btn-accent" href="<?= BedsHelper::e(
    BedsHelper::redirect("incidents"),
) ?>">Open Incident Log →</a></div><div class="panel-body"><?php if (
    $active
): ?><table><thead><tr><th>ID</th><th>Type</th><th>Reporter</th><th>Location</th><th>Status</th><th>Reported</th></tr></thead><tbody><?php foreach (
    array_slice($active, 0, 6)
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
) ?></td><td><?= BedsHelper::e(
    BedsHelper::locationLabel($db, $incident["location_id"]),
) ?></td><td><?= BedsHelper::statusPill(
    $incident["status"],
) ?></td><td class="mono text-muted"><?= BedsHelper::e(
    $incident["date_reported"],
) ?></td></tr><?php endforeach; ?></tbody></table><?php else: ?><?= BedsHelper::emptyState(
        "No active incidents right now.",
        "All clear.",
    ) ?><?php endif; ?></div></div>
<div class="grid-2"><div class="panel"><div class="panel-head"><h2>Responder Status</h2></div><div class="panel-body"><table><thead><tr><th>Name</th><th>Specialization</th><th>Status</th></tr></thead><tbody><?php foreach (
    $db["responders"]
    as $responder
): ?><tr><td><?= BedsHelper::e(
    $responder["full_name"],
) ?></td><td class="text-muted"><?= BedsHelper::e(
    $responder["specialization"],
) ?></td><td><?= BedsHelper::statusPill(
    $responder["availability_status"],
) ?></td></tr><?php endforeach; ?></tbody></table></div></div><div class="panel"><div class="panel-head"><h2>Resource Status</h2></div><div class="panel-body"><table><thead><tr><th>Resource</th><th>Type</th><th>Status</th></tr></thead><tbody><?php foreach (
    $db["resources"]
    as $resource
): ?><tr><td><?= BedsHelper::e(
    $resource["resource_name"],
) ?></td><td class="text-muted"><?= BedsHelper::e(
    $resource["resource_type"],
) ?></td><td><?= BedsHelper::statusPill(
    $resource["status"],
) ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>

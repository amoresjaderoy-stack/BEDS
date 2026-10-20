<?php $undispatched = array_values(
    array_filter(
        $db["incidents"],
        static fn(array $i): bool => $i["status"] !== "Resolved",
    ),
);
$assignments = $db["dispatch_assignment"];
usort(
    $assignments,
    static fn(array $a, array $b): int => strcmp(
        $b["dispatch_time"],
        $a["dispatch_time"],
    ),
);
?>
<div class="panel"><div class="panel-head"><h2>New Assignment</h2></div><div class="panel-body pad"><form method="post" action="index.php"><input type="hidden" name="action" value="create_dispatch"><input type="hidden" name="page" value="dispatch"><div class="grid-2"><div class="field"><label>Incident</label><select name="incident_id" required><?php foreach (
    $undispatched
    as $incident
): ?><option value="<?= (int) $incident["incident_id"] ?>">#<?= str_pad(
    (string) $incident["incident_id"],
    4,
    "0",
    STR_PAD_LEFT,
) ?> — <?= BedsHelper::e($incident["incident_type"]) ?> (<?= BedsHelper::e(
     $incident["status"],
 ) ?>)</option><?php endforeach; ?></select></div><div class="field"><label>Responder</label><select name="responder_id" required><?php foreach (
    $db["responders"]
    as $responder
): ?><option value="<?= (int) $responder["responder_id"] ?>"><?= BedsHelper::e(
    $responder["full_name"],
) ?> — <?= BedsHelper::e($responder["specialization"]) ?> <?= $responder[
     "availability_status"
 ] !== "Available"
     ? "(busy)"
     : "" ?></option><?php endforeach; ?></select></div><div class="field"><label>Resource</label><select name="resource_id" required><?php foreach (
    $db["resources"]
    as $resource
): ?><option value="<?= (int) $resource["resource_id"] ?>"><?= BedsHelper::e(
    $resource["resource_name"],
) ?> <?= $resource["status"] !== "Available"
     ? "(" . BedsHelper::e($resource["status"]) . ")"
     : "" ?></option><?php endforeach; ?></select></div><div class="field" style="display:flex;align-items:flex-end;"><button class="btn btn-primary" style="width:100%;" type="submit">One-Click Dispatch</button></div></div><div class="hint">Dispatching sets the incident status to “Dispatched”, marks the responder On Duty, and marks the resource In Use.</div></form></div></div>
<div class="panel"><div class="panel-head"><h2>Assignment Log</h2></div><div class="panel-body"><?php if (!$assignments): ?><?= BedsHelper::emptyState(
        "No dispatch records yet.",
        "Assign a responder above to get started.",
    ) ?><?php else: ?><table><thead><tr><th>ID</th><th>Incident</th><th>Responder</th><th>Resource</th><th>Dispatch Time</th><th>Arrival Time</th><th></th></tr></thead><tbody><?php foreach (
    $assignments
    as $assignment
):

    $incident = beds_find(
        $db["incidents"],
        "incident_id",
        $assignment["incident_id"],
    );
    $responder = beds_find(
        $db["responders"],
        "responder_id",
        $assignment["responder_id"],
    );
    $resource = beds_find(
        $db["resources"],
        "resource_id",
        $assignment["resource_id"],
    );
    ?><tr><td class="mono">#A<?= str_pad(
    (string) $assignment["assignment_id"],
    3,
    "0",
    STR_PAD_LEFT,
) ?></td><td><?= $incident
    ? "#" .
        str_pad((string) $incident["incident_id"], 4, "0", STR_PAD_LEFT) .
        " " .
        BedsHelper::e($incident["incident_type"])
    : "—" ?></td><td><?= BedsHelper::e(
    $responder["full_name"] ?? "—",
) ?></td><td><?= BedsHelper::e(
    $resource["resource_name"] ?? "—",
) ?></td><td class="mono text-muted"><?= BedsHelper::e(
    $assignment["dispatch_time"],
) ?></td><td class="mono text-muted"><?= BedsHelper::e(
    $assignment["arrival_time"] ?? "—",
) ?></td><td><div class="action-row"><?php if (
    !$assignment["arrival_time"]
): ?><form method="post" action="index.php"><input type="hidden" name="action" value="mark_arrived"><input type="hidden" name="page" value="dispatch"><input type="hidden" name="id" value="<?= (int) $assignment[
    "assignment_id"
] ?>"><button class="btn btn-sm btn-ghost" type="submit">Mark Arrived</button></form><?php endif; ?><form method="post" action="index.php"><input type="hidden" name="action" value="delete_dispatch"><input type="hidden" name="page" value="dispatch"><input type="hidden" name="id" value="<?= (int) $assignment[
    "assignment_id"
] ?>"><button class="btn btn-sm btn-danger" type="submit">Remove</button></form></div></td></tr><?php
endforeach; ?></tbody></table><?php
endif; ?></div></div>

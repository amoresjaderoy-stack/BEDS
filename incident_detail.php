<?php $incident = beds_find($db["incidents"], "incident_id", $id);
$modalTitle = $incident
    ? "Incident #" .
        str_pad((string) $id, 4, "0", STR_PAD_LEFT) .
        " — " .
        $incident["incident_type"]
    : "Incident Detail";
include __DIR__ . "/modal_header.php";
$currentIndex = array_search($incident["status"] ?? "", BEDS_STATUS_FLOW, true);
$logs = array_values(
    array_filter(
        $db["incident_status_log"],
        static fn(array $log): bool => (int) $log["incident_id"] === $id,
    ),
);
usort(
    $logs,
    static fn(array $a, array $b): int => strcmp(
        $a["timestamp"],
        $b["timestamp"],
    ),
);
$assignments = array_values(
    array_filter(
        $db["dispatch_assignment"],
        static fn(array $assignment): bool => (int) $assignment[
            "incident_id"
        ] === $id,
    ),
);
?>
<div class="modal-body"><div class="stepper" style="margin:-22px -22px 18px;"><?php foreach (
    BEDS_STATUS_FLOW
    as $index => $status
):
    if ($index > 0): ?><div class="step-line <?= $index <= $currentIndex
    ? "done"
    : "" ?>"></div><?php endif; ?><div class="step <?= $index < $currentIndex
    ? "done"
    : ($index === $currentIndex
        ? "current"
        : "") ?>">
<div class="step-circle"><?= $index < $currentIndex
    ? "✓"
    : $index + 1 ?></div><div class=
"step-label"><?= BedsHelper::e($status) ?></div></div><?php
endforeach; ?></div><div class="grid-2" 
style="margin-bottom:16px;"><div><div class="hint">Reporter</div><div><?= BedsHelper::e(
    $incident["reporter_name"] ?? "—",
) ?></div></div><div><div class="hint">
    Contact</div><div class="mono"><?= BedsHelper::e(
        $incident["reporter_contact"] ?? "—",
    ) ?></div></div><div><div class="hint">Location</div><div><?= BedsHelper::e(
    BedsHelper::locationLabel($db, $incident["location_id"] ?? 0),
) ?></div></div><div><div class="hint">Landmark</div><div><?= BedsHelper::e(
    beds_find($db["locations"], "location_id", $incident["location_id"] ?? 0)[
        "landmark"
    ] ?? "—",
) ?></div></div><div class="field-full">
        <div class="hint">Description</div><div><?= BedsHelper::e(
            $incident["description"] ?? "—",
        ) ?></div></div></div><div class="panel">
            <div class="panel-head"><h2 style="font-size:13px;"
            >Dispatch Assignments</h2></div><div class="panel-body pad"><?php if (
                $assignments
            ):
                foreach ($assignments as $assignment):

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
                    ?><div style="display:flex;justify-content:space-between;padding:
             6px 0;font-size:13px;border-bottom:1px solid var(--border);"><span>
                <?= BedsHelper::e($responder["full_name"] ?? "—") ?> · 
                <?= BedsHelper::e($resource["resource_name"] ?? "—") ?></span><
                span class="mono text-muted"><?= BedsHelper::e(
                    $assignment["dispatch_time"],
                ) ?></span></div><?php
                endforeach;
            else:
                 ?><div class="text-muted"
                 style="font-size:13px;">No responders dispatched yet.
                </div><?php
            endif; ?></div></div><div class="panel mb-0">
                    <div class="panel-head"><h2 style="font-size:13px;">
                        Status Timeline</h2></div><div class="timeline"><?php foreach (
                            $logs
                            as $log
                        ): ?><div class="tl-item"><div class="tl-dot"><
                            /div><div class="tl-content"><div class="tl-status"><?= BedsHelper::e(
                                $log["status"],
                            ) ?></div><div class="tl-meta"><?= BedsHelper::e(
    $log["timestamp"],
) ?> · updated by <?= BedsHelper::e(
     BedsHelper::userName($db, $log["updated_by"]),
 ) ?></div></div></div><?php endforeach; ?></div></div></div><div class="modal-foot"><a class="btn btn-sm btn-ghost" href="<
                             ?= BedsHelper::e(BedsHelper::redirect('incidents')) ?>">Close</a><a class="btn btn-sm btn-accent" href="<
                             ?= BedsHelper::e(BedsHelper::redirect('dispatch')) ?>">Go to Dispatch →</a></div>

<div class="panel"><div class="panel-head"><h2>Responder Roster</h2><a class="btn btn-sm btn-accent" href="<?= BedsHelper::e(
    BedsHelper::redirect("responders", ["modal" => "responder"]),
) ?>">+ Add Responder</a></div><div class="panel-body"><?php if (!$db["responders"]): ?><?= BedsHelper::emptyState(
        "No responders registered.",
        "Add your first responder.",
    ) ?><?php else: ?><table><thead><tr><th>ID</th><th>Full Name</th><th>Contact</th><th>Specialization</th><th>Availability</th><th></th></tr></thead><tbody><?php foreach (
    $db["responders"]
    as $row
): ?><tr><td class="mono">#R<?= str_pad(
    (string) $row["responder_id"],
    3,
    "0",
    STR_PAD_LEFT,
) ?></td><td><?= BedsHelper::e(
    $row["full_name"],
) ?></td><td class="mono text-muted"><?= BedsHelper::e(
    $row["contact_no"],
) ?></td><td><?= BedsHelper::e(
    $row["specialization"],
) ?></td><td><?= BedsHelper::statusPill(
    $row["availability_status"],
) ?></td><td><div class="action-row"><a class="btn btn-sm btn-ghost" href="<?= BedsHelper::e(
    BedsHelper::redirect("responders", [
        "modal" => "responder",
        "id" => $row["responder_id"],
    ]),
) ?>">Edit</a><form method="post" action="index.php"><input type="hidden" name="action" value="delete_responder"><input type="hidden" name="page" value="responders"><input type="hidden" name="id" value="<?= (int) $row[
    "responder_id"
] ?>"><button class="btn btn-sm btn-danger" type="submit">Remove</button></form></div></td></tr><?php endforeach; ?></tbody></table><?php
endif; ?></div></div>

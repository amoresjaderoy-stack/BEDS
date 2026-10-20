<div class="panel"><div class="panel-head"><h2>Resources & Equipment</h2><a class="btn btn-sm btn-accent" href="<?= BedsHelper::e(
    BedsHelper::redirect("resources", ["modal" => "resource"]),
) ?>">+ Add Resource</a></div><div class="panel-body"><?php if (!$db["resources"]): ?><?= BedsHelper::emptyState(
        "No resources logged.",
        "Add ambulances, kits, radios, or gear.",
    ) ?><?php else: ?><table><thead><tr><th>ID</th><th>Resource</th><th>Type</th><th>Status</th><th></th></tr></thead><tbody><?php foreach (
    $db["resources"]
    as $row
): ?><tr><td class="mono">#E<?= str_pad(
    (string) $row["resource_id"],
    3,
    "0",
    STR_PAD_LEFT,
) ?></td><td><?= BedsHelper::e(
    $row["resource_name"],
) ?></td><td class="text-muted"><?= BedsHelper::e(
    $row["resource_type"],
) ?></td><td><?= BedsHelper::statusPill(
    $row["status"],
) ?></td><td><div class="action-row"><a class="btn btn-sm btn-ghost" href="<?= BedsHelper::e(
    BedsHelper::redirect("resources", [
        "modal" => "resource",
        "id" => $row["resource_id"],
    ]),
) ?>">Edit</a><form method="post" action="index.php"><input type="hidden" name="action" value="delete_resource"><input type="hidden" name="page" value="resources"><input type="hidden" name="id" value="<?= (int) $row[
    "resource_id"
] ?>"><button class="btn btn-sm btn-danger" type="submit">Remove</button></form></div></td></tr><?php endforeach; ?></tbody></table><?php
endif; ?></div></div>

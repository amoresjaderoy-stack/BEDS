<div class="panel"><div class="panel-head"><h2>Locations & Landmarks</h2><a class="btn btn-sm btn-accent" href="<?= BedsHelper::e(
    BedsHelper::redirect("locations", ["modal" => "location"]),
) ?>">+ Add Location</a></div><div class="panel-body"><?php if (!$db["locations"]): ?><?= BedsHelper::emptyState(
        "No locations saved.",
        "Add addresses to speed up incident logging.",
    ) ?><?php else: ?><table><thead><tr><th>ID</th><th>Address</th><th>Zone</th><th>Landmark</th><th>Barangay</th><th></th></tr></thead><tbody><?php foreach (
    $db["locations"]
    as $row
): ?><tr><td class="mono">#L<?= str_pad(
    (string) $row["location_id"],
    3,
    "0",
    STR_PAD_LEFT,
) ?></td><td><?= BedsHelper::e(
    $row["address"],
) ?></td><td><span class="badge"><?= BedsHelper::e(
    $row["zone_number"],
) ?></span></td><td class="text-muted"><?= BedsHelper::e(
    $row["landmark"] ?: "—",
) ?></td><td><?= BedsHelper::e(
    $row["barangay"],
) ?></td><td><div class="action-row"><a class="btn btn-sm btn-ghost" href="<?= BedsHelper::e(
    BedsHelper::redirect("locations", [
        "modal" => "location",
        "id" => $row["location_id"],
    ]),
) ?>">Edit</a><form method="post" action="index.php"><input type="hidden" name="action" value="delete_location"><input type="hidden" name="page" value="locations"><input type="hidden" name="id" value="<?= (int) $row[
    "location_id"
] ?>"><button class="btn btn-sm btn-danger" type="submit">Remove</button></form></div></td></tr><?php endforeach; ?></tbody></table><?php
endif; ?></div></div>

<div class="panel"><div class="panel-head"><h2>System Users</h2><a class="btn btn-sm btn-accent" href="<?= BedsHelper::e(
    BedsHelper::redirect("users", ["modal" => "user"]),
) ?>">+ Add User</a></div><div class="panel-body"><table><thead><tr><th>ID</th><th>Full Name</th><th>Username</th><th>Role</th><th>Contact</th><th>Email</th><th></th></tr></thead><tbody><?php foreach (
    $db["users"]
    as $row
): ?><tr><td class="mono">#U<?= str_pad(
    (string) $row["user_id"],
    3,
    "0",
    STR_PAD_LEFT,
) ?></td><td><?= BedsHelper::e(
    $row["full_name"],
) ?></td><td class="mono"><?= BedsHelper::e(
    $row["username"],
) ?></td><td><span class="badge"><?= BedsHelper::e(
    BedsHelper::roleName($db, $row["role_id"]),
) ?></span></td><td class="mono text-muted"><?= BedsHelper::e(
    $row["contact_no"],
) ?></td><td class="text-muted"><?= BedsHelper::e(
    $row["email"],
) ?></td><td><div class="action-row"><a class="btn btn-sm btn-ghost" href="<?= BedsHelper::e(
    BedsHelper::redirect("users", ["modal" => "user", "id" => $row["user_id"]]),
) ?>">Edit</a><?php if (
    (int) $row["user_id"] !== (int) $currentUser["user_id"]
): ?><form method="post" action="index.php"><input type="hidden" name="action" value="delete_user"><input type="hidden" name="page" value="users"><input type="hidden" name="id" value="<?= (int) $row[
    "user_id"
] ?>"><button class="btn btn-sm btn-danger" type="submit">Remove</button></form><?php endif; ?></div></td></tr><?php endforeach; ?></tbody></table></div></div>
<div class="panel"><div class="panel-head"><h2>Roles</h2></div><div class="panel-body"><table><thead><tr><th>ID</th><th>Role Name</th><th>Users</th></tr></thead><tbody><?php foreach (
    $db["roles"]
    as $role
): ?><tr><td class="mono">#<?= (int) $role[
    "role_id"
] ?></td><td><?= BedsHelper::e($role["role_name"]) ?></td><td><?= count(
    array_filter(
        $db["users"],
        static fn(array $user): bool => (int) $user["role_id"] ===
            (int) $role["role_id"],
    ),
) ?></td></tr><?php endforeach; ?></tbody></table></div></div>

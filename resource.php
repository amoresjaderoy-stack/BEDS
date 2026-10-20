<?php $row = $id ? beds_find($db["resources"], "resource_id", $id) : null;
$modalTitle = $row ? "Edit Resource" : "Add Resource";
include __DIR__ . "/modal_header.php";
$types = ["Vehicle", "Medical Supply", "Communication", "Rescue Gear", "Other"];
$statuses = ["Available", "In Use", "Maintenance"];
?>
<div class="modal-body"><form method="post" action="index.php"><input type="hidden" 
name="action" value="save_resource"><input type="hidden" name="page" 
value="resources"><input type="hidden" name="id" value="<?= (int) $id ?>"><div
 class="grid-2"><div class="field field-full"><label>Resource Name</label><input
  name="resource_name" value="<?= BedsHelper::e(
      $row["resource_name"] ?? "",
  ) ?>" required></div><div class="field"><label>Type</label><select name=
  "resource_type"><?php foreach ($types as $type): ?><option <?= ($row[
    "resource_type"
] ??
    "") ===
$type
    ? "selected"
    : "" ?>><?= BedsHelper::e(
    $type,
) ?></option><?php endforeach; ?></select></div><div 
    class="field"><label>Status</label><select name="status"><?php foreach (
        $statuses
        as $status
    ): ?><option <?= ($row["status"] ?? "") === $status
    ? "selected"
    : "" ?>><?= BedsHelper::e(
    $status,
) ?></option><?php endforeach; ?></select></div></div></div><div class="modal-foot"><a class="btn btn-sm btn-ghost" 
     href="<?= BedsHelper::e(
         BedsHelper::redirect("resources"),
     ) ?>">Cancel</a><button class=
     "btn btn-sm btn-accent" type="submit"><?= $row
         ? "Save Changes"
         : "Add Resource" ?></button></div></form>

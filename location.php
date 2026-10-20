<?php $row = $id ? beds_find($db["locations"], "location_id", $id) : null;
$modalTitle = $row ? "Edit Location" : "Add Location";
include __DIR__ . "/modal_header.php";
?>
<div class="modal-body"><form method="post" action="index.php"><input type="hidden" name=
"action" value="save_location"><input type="hidden" name="page" value="locations">
<input type="hidden" name="id" value="<?= (int) $id ?>"><div class="grid-2"><div class="field field-full"><label>Address</label><input name="address" value="<?= BedsHelper::e(
    $row["address"] ?? "",
) ?>" required></div><div class="field"><label>Zone Number</label><input name="zone_number" value="<?= BedsHelper::e(
    $row["zone_number"] ?? "",
) ?>" required></div><div class="field"><label>Barangay</label><input name="barangay" value="<?= BedsHelper::e(
    $row["barangay"] ?? "Libertad",
) ?>"></div><div class="field field-full"><label>Landmark</label><input name="landmark" value="<?= BedsHelper::e(
    $row["landmark"] ?? "",
) ?>"></div></div></div><div class="modal-foot"><a class="btn btn-sm btn-ghost" href="<?= BedsHelper::e(
    BedsHelper::redirect("locations"),
) ?>">Cancel</a><button class="btn btn-sm btn-accent" type="submit"><?= $row
    ? "Save Changes"
    : "Add Location" ?></button></div></form>

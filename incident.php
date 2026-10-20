<?php $incident = $id ? beds_find($db["incidents"], "incident_id", $id) : null;
$modalTitle = $incident
    ? "Edit Incident #" . str_pad((string) $id, 4, "0", STR_PAD_LEFT)
    : "Log New Incident";
include __DIR__ . "/modal_header.php";
?>
<div class="modal-body"><form method="post" action="index.php"><input type="hidden" name="action" value="save_incident"><input type="hidden" name="page" value="incidents"><input type="hidden" name="id" value="<?= (int) $id ?>">
  <div class="grid-2"><div class="field"><label>Reporter Name</label><input name="reporter_name" value="<?= BedsHelper::e(
      $incident["reporter_name"] ?? "",
  ) ?>" required></div><div class="field"><label>Reporter Contact No.</label><input name="reporter_contact" value="<?= BedsHelper::e(
    $incident["reporter_contact"] ?? "",
) ?>" required></div><div class="field"><label>Incident Type</label><select name="incident_type"><?php foreach (
    BEDS_INCIDENT_TYPES
    as $type
): ?><option <?= ($incident["incident_type"] ?? "") === $type
    ? "selected"
    : "" ?>><?= BedsHelper::e(
    $type,
) ?></option><?php endforeach; ?></select></div><div class="field"><label>Location</label><select name="location_id"><?php foreach (
    $db["locations"]
    as $location
): ?><option value="<?= (int) $location["location_id"] ?>" <?= (int) ($incident[
    "location_id"
] ?? 0) === (int) $location["location_id"]
    ? "selected"
    : "" ?>><?= BedsHelper::e(
    BedsHelper::locationLabel($db, $location["location_id"]),
) ?></option><?php endforeach; ?></select></div><div class="field field-full"><label>Description</label><textarea name="description" rows="3" required><?= BedsHelper::e(
    $incident["description"] ?? "",
) ?></textarea></div><?php if (
    $incident
): ?><div class="field"><label>Status</label><select name="status"><?php foreach (
    BEDS_STATUS_FLOW
    as $status
): ?><option <?= $incident["status"] === $status
    ? "selected"
    : "" ?>><?= BedsHelper::e(
    $status,
) ?></option><?php endforeach; ?></select></div><?php endif; ?></div>
  <div class="hint">This form writes an Incident record and its status history to the PHP JSON datastore.</div>
</div><div class="modal-foot"><a class="btn btn-sm btn-ghost" href="<?= BedsHelper::e(
    BedsHelper::redirect("incidents"),
) ?>">Cancel</a><button class="btn btn-sm btn-accent" type="submit"><?= $incident
    ? "Save Changes"
    : "Create Incident" ?></button></div></form>

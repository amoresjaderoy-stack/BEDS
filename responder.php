<?php $row = $id ? beds_find($db["responders"], "responder_id", $id) : null;
$modalTitle = $row ? "Edit Responder" : "Add Responder";
include __DIR__ . "/modal_header.php";
$statuses = ["Available", "On Duty", "Off Duty"];
?>
<div class="modal-body"><form method="post" action="index.php"><input
 type="hidden" name="action" value="save_responder"><input type="hidden" name="page" value="
 responders"><input type="hidden" name="id" value="<?= (int) $id ?>"><div class="grid-2"><div class="field field-full"><label>Full Name
 </label><input name="full_name" value="<?= BedsHelper::e(
     $row["full_name"] ?? "",
 ) ?>
 " required></div><div class="field"><label>Contact No.</label><input name="contact_no"
  value="<?= BedsHelper::e(
      $row["contact_no"] ?? "",
  ) ?>" required></div><div class="field"><label>
    Specialization</label><input name="specialization" value="<?= BedsHelper::e(
        $row["specialization"] ?? "",
    ) ?>"></div><div class="field"><label>Availability</label>
 <select name="availability_status"><?php foreach (
     $statuses
     as $status
 ): ?><option <?= ($row["availability_status"] ?? "") === $status
    ? "selected"
    : "" ?>><?= BedsHelper::e(
    $status,
) ?></option><?php endforeach; ?></select></div></div></div><div class="modal-foot"><a class="btn 
  btn-sm btn-ghost" href="<?= BedsHelper::e(
      BedsHelper::redirect("responders"),
  ) ?>"
  >Cancel</a><button class="btn btn-sm btn-accent" type="submit"><?= $row
      ? "Save Changes"
      : "Add Responder" ?></button></div></form>

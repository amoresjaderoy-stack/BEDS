<?php $row = $id ? beds_find($db["users"], "user_id", $id) : null;
$modalTitle = $row ? "Edit User" : "Add User";
include __DIR__ . "/modal_header.php";
?>
<form method="post" action="index.php">
  <input type="hidden" name="action" value="save_user">
  <input type="hidden" name="page" value="users">
  <input type="hidden" name="id" value="<?= (int) $id ?>">
  <div class="modal-body">
    <div class="grid-2">
      <div class="field field-full"><label>Full Name</label><input name="full_name" value="<?= BedsHelper::e($row["full_name"] ?? "") ?>" required></div>
      <div class="field"><label>Username</label><input name="username" value="<?= BedsHelper::e($row["username"] ?? "") ?>" minlength="3" maxlength="30" required></div>
      <div class="field"><label>Password <?= $row ? '<span class="text-muted">(optional)</span>' : "" ?></label><input type="password" name="password" placeholder="<?= $row ? "Leave blank to keep current" : "At least 6 characters" ?>" autocomplete="new-password" minlength="6" <?= $row ? "" : "required" ?>></div>
      <div class="field"><label>Role</label><select name="role_id"><?php foreach (
          $db["roles"]
          as $role
      ): ?><option value="<?= (int) $role["role_id"] ?>" <?= (int) ($row["role_id"] ?? 0) === (int) $role["role_id"]
    ? "selected"
    : "" ?>><?= BedsHelper::e($role["role_name"]) ?></option><?php endforeach; ?></select></div>
      <div class="field"><label>Contact No.</label><input name="contact_no" value="<?= BedsHelper::e($row["contact_no"] ?? "") ?>"></div>
      <div class="field field-full"><label>Email</label><input name="email" type="email" value="<?= BedsHelper::e($row["email"] ?? "") ?>"></div>
    </div>
    <?php if ($row): ?><div class="hint">Passwords are stored securely. Leave the password field blank to keep the current one.</div><?php endif; ?>
  </div>
  <div class="modal-foot"><a class="btn btn-sm btn-ghost" href="<?= BedsHelper::e(BedsHelper::redirect("users")) ?>">Cancel</a><button class="btn btn-sm btn-accent" type="submit"><?= $row ? "Save Changes" : "Add User" ?></button></div>
</form>

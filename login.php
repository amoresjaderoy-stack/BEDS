<?php
$authMode = ($authMode ?? "login") === "register" ? "register" : "login";
$registerValues = $registerValues ?? [
    "full_name" => "",
    "username" => "",
    "email" => "",
    "contact_no" => "",
    "role_id" => 2,
];
$loginError = $loginError ?? "";
$registerError = $registerError ?? "";
$authNotice = $authNotice ?? "";
?>
<div id="login-screen">
  <div class="scan-line"></div>
  <div class="login-box <?= $authMode === "register" ? "login-box-wide" : "" ?>">
    <div class="login-eyebrow"><span class="led-dot"></span> LIVE SYSTEM · BRGY. LIBERTAD, BOGO CITY</div>
    <div class="login-title">BEDS</div>
    <div class="login-sub">Barangay Emergency Dispatch System</div>

    <div class="auth-tabs">
      <a class="auth-tab <?= $authMode === "login" ? "active" : "" ?>" href="index.php">Sign In</a>
      <a class="auth-tab <?= $authMode === "register" ? "active" : "" ?>" href="index.php?mode=register">Create Account</a>
    </div>

    <?php if ($authNotice !== ""): ?>
      <div class="login-success" role="status"><?= BedsHelper::e($authNotice) ?></div>
    <?php endif; ?>

    <?php if ($authMode === "login"): ?>
      <?php if ($loginError !== ""): ?>
        <div class="login-error" role="alert" style="display:block;"><?= BedsHelper::e($loginError) ?></div>
      <?php endif; ?>
      <form method="post" action="index.php">
        <input type="hidden" name="action" value="login">
        <div class="field"><label for="login-username">Username or Email</label><input type="text" id="login-username" name="username" placeholder="Enter your username or email" autocomplete="username" required autofocus></div>
        <div class="field"><label for="login-password">Password</label><input type="password" id="login-password" name="password" placeholder="••••••••" autocomplete="current-password" required></div>
        <button class="btn btn-primary" type="submit">Sign In to Console</button>
      </form>
      <div class="login-hint">No account yet? <a href="index.php?mode=register">Create an account</a> to access the console.</div>
    <?php else: ?>
      <?php if ($registerError !== ""): ?>
        <div class="login-error" role="alert" style="display:block;"><?= BedsHelper::e($registerError) ?></div>
      <?php endif; ?>
      <form method="post" action="index.php" class="auth-form">
        <input type="hidden" name="action" value="register">
        <div class="grid-2">
          <div class="field field-full"><label for="register-name">Full Name</label><input type="text" id="register-name" name="full_name" value="<?= BedsHelper::e($registerValues["full_name"]) ?>" placeholder="e.g. Juan Dela Cruz" autocomplete="name" required autofocus></div>
          <div class="field"><label for="register-username">Username</label><input type="text" id="register-username" name="username" value="<?= BedsHelper::e($registerValues["username"]) ?>" placeholder="e.g. jdelacruz" autocomplete="username" minlength="3" maxlength="30" required></div>
          <div class="field"><label for="register-email">Email <span class="text-muted">(optional)</span></label><input type="email" id="register-email" name="email" value="<?= BedsHelper::e($registerValues["email"]) ?>" placeholder="name@example.com" autocomplete="email"></div>
          <div class="field"><label for="register-contact">Contact No.</label><input type="tel" id="register-contact" name="contact_no" value="<?= BedsHelper::e($registerValues["contact_no"]) ?>" placeholder="09XX-XXX-XXXX" autocomplete="tel"></div>
          <div class="field"><label for="register-role">Role</label><select id="register-role" name="role_id" required><?php foreach (
              $db["roles"]
              as $role
          ): ?><option value="<?= (int) $role["role_id"] ?>" <?= (int) $registerValues[
    "role_id"
] === (int) $role["role_id"]
    ? "selected"
    : "" ?>><?= BedsHelper::e($role["role_name"]) ?></option><?php endforeach; ?></select></div>
          <div class="field"><label for="register-password">Password</label><input type="password" id="register-password" name="password" placeholder="At least 6 characters" autocomplete="new-password" minlength="6" required></div>
          <div class="field"><label for="register-confirm">Confirm Password</label><input type="password" id="register-confirm" name="confirm_password" placeholder="Re-enter password" autocomplete="new-password" minlength="6" required></div>
        </div>
        <button class="btn btn-primary" type="submit">Create Account</button>
      </form>
      <div class="login-hint">Already registered? <a href="index.php">Sign in instead</a>.</div>
    <?php endif; ?>
  </div>
</div>

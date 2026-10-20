<div id="app" style="display:block;">
  <div class="shell">
    <aside class="sidebar" id="sidebar">
      <div class="brand"><div class="brand-mark"><span class="led-dot"></span> BEDS</div><div class="brand-sub">BRGY. LIBERTAD · BOGO CITY</div></div>
      <nav>
        <?php foreach (BedsNavigation::items() as $section): ?>
          <div class="nav-section-label"><?= BedsHelper::e(
              $section["section"],
          ) ?></div>
          <?php foreach ($section["items"] as $item): ?>
            <?php if (
                !empty($item["roles"]) &&
                !in_array((int) $currentUser["role_id"], $item["roles"], true)
            ) {
                continue;
            } ?>
            <a class="nav-item <?= $page === $item["key"]
                ? "active"
                : "" ?>" href="<?= BedsHelper::e(
    BedsHelper::redirect($item["key"]),
) ?>"><span class="icon"><?= $item["icon"] ?></span><span><?= BedsHelper::e(
    $item["label"],
) ?></span></a>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </nav>
      <div class="user-box"><div class="avatar"><?= BedsHelper::e(
          BedsHelper::initials($currentUser["full_name"]),
      ) ?></div><div class="user-meta"><div class="user-name"><?= BedsHelper::e(
    $currentUser["full_name"],
) ?></div><div class="user-role"><?= BedsHelper::e(
    BedsHelper::roleName($db, $currentUser["role_id"]),
) ?></div></div><a class="logout-btn" href="index.php?logout=1">Exit</a></div>
    </aside>
    <div class="main">
      <div class="topbar"><div><h1 class="topbar-title"><?= BedsHelper::e(
          $title,
      ) ?></h1></div><div class="topbar-time mono"><?= BedsHelper::e(
    beds_format_datetime(),
) ?></div></div>
      <div class="content" id="view-root">
        <?php if (
            $flashMessage
        ): ?><div class="panel" style="padding:12px 18px;border-color:var(--good);color:var(--good);margin-bottom:18px;"><?= BedsHelper::e(
    $flashMessage,
) ?></div><?php endif; ?>

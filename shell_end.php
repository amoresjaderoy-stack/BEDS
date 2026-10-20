</div>
    </div>
  </div>
</div>
<div class="modal-overlay <?= !empty($modal)
    ? "open"
    : "" ?>" id="modal-overlay">
  <?php if (!empty($modal)): ?>
    <div class="modal">
      <?php
      $modalFile =
          __DIR__ .
          "/../modals/" .
          preg_replace("/[^a-z_]/", "", (string) $modal) .
          ".php";
      if (is_file($modalFile)) {
          include $modalFile;
      }
      ?>
    </div>
  <?php endif; ?>
</div>

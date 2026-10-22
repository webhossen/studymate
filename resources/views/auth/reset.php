<?php if (empty($valid)): ?>

  <div class="sm-auth-icon is-danger" aria-hidden="true">⚠️</div>

  <h2 class="h5 text-center mb-2">This link is no longer valid</h2>
  <p class="text-muted small text-center mb-4">
    Reset links expire after one hour and can only be used once.
    Request a fresh one and we&rsquo;ll get you back in.
  </p>

  <a href="<?= e(url('password/forgot')) ?>" class="btn btn-sm-navy btn-lg w-100">Request a new link</a>

  <div class="text-center mt-4">
    <a href="<?= e(url('login')) ?>" class="text-primary text-decoration-none fw-semibold">Back to sign in</a>
  </div>

<?php else: ?>

  <div class="sm-auth-icon" aria-hidden="true">🔒</div>

  <h2 class="h5 text-center mb-2">Choose a new password</h2>
  <p class="text-muted small text-center mb-4">
    Pick something you haven&rsquo;t used before. You&rsquo;ll sign in with it straight away.
  </p>

  <form method="post" action="<?= e(url('password/reset/' . e($token ?? ''))) ?>" novalidate id="smResetForm">
    <?= csrf_input() ?>
    <div class="form-group mb-3">
      <label class="form-label" for="password">New password</label>
      <input type="password" name="password" id="password" class="form-control" required
             autocomplete="new-password" minlength="8" aria-describedby="passwordHelp">
      <div class="form-text" id="passwordHelp">Minimum 8 characters.</div>
    </div>
    <div class="form-group mb-3">
      <label class="form-label" for="password_confirm">Confirm new password</label>
      <input type="password" name="password_confirm" id="password_confirm" class="form-control" required
             autocomplete="new-password" aria-describedby="confirmHelp">
      <div class="form-text" id="confirmHelp">Both passwords must match.</div>
    </div>

    <ul class="sm-pw-rules mb-3" id="smPwRules">
      <li data-rule="length"><span class="sm-pw-rule-icon">○</span> At least 8 characters</li>
      <li data-rule="match"><span class="sm-pw-rule-icon">○</span> Both passwords match</li>
    </ul>

    <button type="submit" class="btn btn-sm-navy btn-lg w-100" id="smResetSubmit">Reset password</button>
  </form>

  <div class="text-center mt-4">
    <a href="<?= e(url('login')) ?>" class="text-primary text-decoration-none fw-semibold">Back to sign in</a>
  </div>

  <script>
  (function () {
    var form = document.getElementById('smResetForm');
    if (!form) return;
    var password = document.getElementById('password');
    var confirmField = document.getElementById('password_confirm');
    var rules = document.getElementById('smPwRules');
    var submit = document.getElementById('smResetSubmit');

    function refresh() {
      var lengthOk = password.value.length >= 8;
      var matchOk = confirmField.value.length > 0 && password.value === confirmField.value;
      rules.querySelector('[data-rule="length"]').classList.toggle('is-met', lengthOk);
      rules.querySelector('[data-rule="length"] .sm-pw-rule-icon').textContent = lengthOk ? '✓' : '○';
      rules.querySelector('[data-rule="match"]').classList.toggle('is-met', matchOk);
      rules.querySelector('[data-rule="match"] .sm-pw-rule-icon').textContent = matchOk ? '✓' : '○';
      submit.disabled = !(lengthOk && matchOk);
    }

    password.addEventListener('input', refresh);
    confirmField.addEventListener('input', refresh);
    refresh();
  })();
  </script>

<?php endif; ?>

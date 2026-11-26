<?php
declare(strict_types=1);
$isAdminPage = true;
require VIEW_PATH . '/partials/head.php';
?>
    <main id="main-content" class="min-vh-100 d-flex align-items-center py-5 ad-admin-login-bg">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-md-7 col-lg-5">
            <div class="ad-form-card p-4 p-md-5">
              <div class="d-flex align-items-center gap-2 mb-4">
                <img src="/assets/icons/logo-mark.svg" alt="Logo ArchonDigital" width="34" height="34" />
                <span class="font-display fw-bold fs-5 text-navy">ArchonDigital Admin</span>
              </div>

              <h1 class="h4 fw-bold text-navy mb-2">Masuk Panel Pengelola Client Brief</h1>
              <p class="small text-muted mb-4">
                Masuk menggunakan akun administrator yang telah dibuat untuk meninjau dan menindaklanjuti permintaan klien.
              </p>

              <?php if (!empty($notice)): ?>
                <div class="alert alert-warning small border-0 mb-3" role="status">
                  <?= e($notice) ?>
                </div>
              <?php endif; ?>

              <?php if (!empty($error)): ?>
                <div class="alert alert-danger small border-0 mb-3" role="alert">
                  <?= e($error) ?>
                </div>
              <?php endif; ?>

              <form method="POST" action="/admin/login.php" novalidate>
                <?= CsrfMiddleware::inputField() ?>

                <div class="mb-3">
                  <label for="email" class="form-label">Email Admin</label>
                  <input
                    type="email"
                    class="form-control"
                    id="email"
                    name="email"
                    value="<?= e($oldEmail ?? '') ?>"
                    required
                    autocomplete="username"
                    placeholder="admin@archondigital.web.id"
                  />
                </div>

                <div class="mb-4">
                  <label for="password" class="form-label">Kata Sandi</label>
                  <input
                    type="password"
                    class="form-control"
                    id="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="Masukkan kata sandi"
                  />
                </div>

                <button type="submit" class="btn-ad-primary w-100 mb-3">
                  Masuk ke Dashboard
                </button>
              </form>

              <div class="text-center pt-2 border-top">
                <a class="small text-muted text-decoration-none" href="/">
                  &larr; Kembali ke Formulir Client Brief
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </body>
</html>

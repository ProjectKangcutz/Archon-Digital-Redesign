<?php
declare(strict_types=1);
$isAdminPage = true;
require VIEW_PATH . '/partials/head.php';
$refUrl = SecurityService::sanitizeReferenceUrl($brief['reference_url'] ?? null);
?>
    <header class="ad-navbar sticky-top">
      <div class="container d-flex align-items-center justify-content-between gap-3">
        <a class="ad-brand" href="/admin/index.php">
          <img src="/assets/icons/logo-mark.svg" alt="Logo ArchonDigital" width="32" height="32" />
          <span>ArchonDigital Admin</span>
        </a>
        <div class="d-flex align-items-center gap-3">
          <a class="ad-nav-link" href="/admin/index.php">&larr; Kembali ke Daftar</a>
          <form method="POST" action="/admin/logout.php" class="d-inline">
            <?= CsrfMiddleware::inputField() ?>
            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">Keluar</button>
          </form>
        </div>
      </div>
    </header>

    <main id="main-content" class="py-4 py-lg-5">
      <div class="container">
        <?php if (!empty($flashSuccess)): ?>
          <div class="alert alert-success border-0 shadow-sm mb-4" role="status">
            <?= e($flashSuccess) ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($flashError)): ?>
          <div class="alert alert-danger border-0 shadow-sm mb-4" role="alert">
            <?= e($flashError) ?>
          </div>
        <?php endif; ?>

        <div class="row g-4">
          <!-- Left Column: Brief Details -->
          <div class="col-lg-8">
            <div class="ad-form-card mb-4">
              <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4 pb-3 border-bottom">
                <div>
                  <span class="small fw-semibold text-cyan-dark d-block mb-1 tabular-nums">
                    <?= e($brief['reference_code']) ?>
                  </span>
                  <h1 class="h3 fw-bold text-navy mb-1"><?= e($brief['business_name']) ?></h1>
                  <p class="small text-muted mb-0">
                    Dikirim oleh <strong><?= e($brief['full_name']) ?></strong> pada <?= e(date('d M Y, H:i', strtotime((string) $brief['created_at']))) ?> WIB
                  </p>
                </div>

                <a
                  class="btn-ad-wa"
                  href="<?= e($clientWhatsappUrl) ?>"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  Hubungi via WhatsApp
                </a>
              </div>

              <div class="row g-3 mb-4">
                <div class="col-sm-6">
                  <div class="ad-detail-item">
                    <span class="ad-detail-label">Nomor WhatsApp</span>
                    <span class="ad-detail-value tabular-nums"><?= e($brief['whatsapp']) ?></span>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="ad-detail-item">
                    <span class="ad-detail-label">Email</span>
                    <span class="ad-detail-value"><?= e($brief['email'] ?: 'Tidak dicantumkan') ?></span>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="ad-detail-item">
                    <span class="ad-detail-label">Jenis Layanan</span>
                    <span class="ad-detail-value"><?= e($brief['website_type']) ?></span>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="ad-detail-item">
                    <span class="ad-detail-label">Kisaran Anggaran</span>
                    <span class="ad-detail-value"><?= e($brief['budget_range']) ?></span>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="ad-detail-item">
                    <span class="ad-detail-label">Perkiraan Waktu</span>
                    <span class="ad-detail-value"><?= e($brief['target_timeline']) ?></span>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="ad-detail-item">
                    <span class="ad-detail-label">Referensi Website</span>
                    <?php if ($refUrl !== null): ?>
                      <a class="ad-detail-value text-break" href="<?= e($refUrl) ?>" target="_blank" rel="noopener noreferrer">
                        <?= e($refUrl) ?>
                      </a>
                    <?php else: ?>
                      <span class="ad-detail-value text-muted">Tidak dicantumkan</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <div class="mb-4">
                <h2 class="h6 fw-bold text-navy mb-2">Tujuan Pembuatan Website</h2>
                <div class="ad-detail-prose"><?= nl2br(e($brief['website_goals'])) ?></div>
              </div>

              <div class="mb-4">
                <h2 class="h6 fw-bold text-navy mb-2">Deskripsi Singkat Bisnis</h2>
                <div class="ad-detail-prose"><?= nl2br(e($brief['business_description'])) ?></div>
              </div>

              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <h2 class="h6 fw-bold text-navy mb-2">Halaman yang Dibutuhkan</h2>
                  <div class="ad-detail-prose"><?= nl2br(e($brief['required_pages'])) ?></div>
                </div>
                <div class="col-md-6">
                  <h2 class="h6 fw-bold text-navy mb-2">Fitur yang Dibutuhkan</h2>
                  <div class="ad-detail-prose"><?= nl2br(e($brief['required_features'])) ?></div>
                </div>
              </div>

              <div>
                <h2 class="h6 fw-bold text-navy mb-2">Catatan Tambahan Klien</h2>
                <div class="ad-detail-prose">
                  <?= !empty($brief['additional_notes']) ? nl2br(e($brief['additional_notes'])) : '<span class="text-muted">Tidak ada catatan tambahan.</span>' ?>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column: Admin Workflow Controls -->
          <div class="col-lg-4">
            <div class="ad-form-card mb-4">
              <h2 class="h5 fw-bold text-navy mb-3">Pengelolaan &amp; Status</h2>
              <form method="POST" action="/admin/detail.php?id=<?= (int) $brief['id'] ?>">
                <?= CsrfMiddleware::inputField() ?>
                <input type="hidden" name="action" value="update" />

                <div class="mb-3">
                  <label for="status" class="form-label small fw-semibold">Status Penanganan</label>
                  <select class="form-select" id="status" name="status">
                    <?php foreach ($statusLabels as $key => $label): ?>
                      <option value="<?= e($key) ?>" <?= $brief['status'] === $key ? 'selected' : '' ?>>
                        <?= e($label) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="mb-3">
                  <label for="admin_notes" class="form-label small fw-semibold">Catatan Internal Admin</label>
                  <textarea
                    class="form-control"
                    id="admin_notes"
                    name="admin_notes"
                    rows="5"
                    maxlength="4000"
                    placeholder="Catat hasil peninjauan atau tindak lanjut di sini..."
                  ><?= e($brief['admin_notes'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn-ad-primary w-100">Simpan Perubahan</button>
              </form>

              <?php if (!empty($brief['reviewed_by_name'])): ?>
                <p class="small text-muted mt-3 mb-0">
                  Terakhir ditinjau oleh: <strong><?= e($brief['reviewed_by_name']) ?></strong>
                </p>
              <?php endif; ?>
            </div>

            <!-- Archive & Delete Actions -->
            <div class="ad-form-card">
              <h2 class="h6 fw-bold text-navy mb-3">Tindakan Arsip &amp; Data</h2>

              <?php if ($brief['status'] !== 'archived'): ?>
                <form method="POST" action="/admin/detail.php?id=<?= (int) $brief['id'] ?>" class="mb-3">
                  <?= CsrfMiddleware::inputField() ?>
                  <input type="hidden" name="action" value="archive" />
                  <button type="submit" class="btn btn-outline-secondary w-100 rounded-pill">
                    Arsipkan Brief Ini
                  </button>
                </form>
              <?php endif; ?>

              <button
                type="button"
                class="btn btn-outline-danger w-100 rounded-pill"
                data-bs-toggle="modal"
                data-bs-target="#deleteBriefModal"
              >
                Hapus Brief Permanen
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteBriefModal" tabindex="-1" aria-labelledby="deleteBriefModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
          <div class="modal-header">
            <h2 class="modal-title h5 fw-bold text-navy" id="deleteBriefModalLabel">Konfirmasi Penghapusan Data</h2>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
          </div>
          <div class="modal-body">
            Apakah Anda yakin ingin menghapus Client Brief <strong><?= e($brief['reference_code']) ?></strong> secara permanen? Tindakan ini tidak dapat dibatalkan.
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
            <form method="POST" action="/admin/detail.php?id=<?= (int) $brief['id'] ?>">
              <?= CsrfMiddleware::inputField() ?>
              <input type="hidden" name="action" value="delete" />
              <button type="submit" class="btn btn-danger rounded-pill px-4">Ya, Hapus Permanen</button>
            </form>
          </div>
        </div>
      </div>
    </div>

<?php require VIEW_PATH . '/partials/footer.php'; ?>

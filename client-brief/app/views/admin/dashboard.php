<?php
declare(strict_types=1);
$isAdminPage = true;
require VIEW_PATH . '/partials/head.php';

$items = $listData['items'] ?? [];
$total = (int) ($listData['total'] ?? 0);
$page = (int) ($listData['page'] ?? 1);
$totalPages = (int) ($listData['total_pages'] ?? 1);

$statusBadgeClass = static function (string $status): string {
    return match ($status) {
        'new' => 'ad-badge-new',
        'reviewing' => 'ad-badge-reviewing',
        'contacted' => 'ad-badge-contacted',
        'completed' => 'ad-badge-completed',
        'archived' => 'ad-badge-archived',
        default => 'ad-badge-archived',
    };
};

$buildPageUrl = static function (int $targetPage) use ($filters): string {
    $params = array_filter([
        'q' => $filters['q'] ?? '',
        'status' => $filters['status'] ?? '',
        'date_from' => $filters['date_from'] ?? '',
        'date_to' => $filters['date_to'] ?? '',
        'page' => $targetPage > 1 ? (string) $targetPage : '',
    ], static fn (string $v): bool => $v !== '');
    return '/admin/index.php' . ($params ? '?' . http_build_query($params) : '');
};
?>
    <header class="ad-navbar sticky-top">
      <div class="container d-flex align-items-center justify-content-between gap-3">
        <a class="ad-brand" href="/admin/index.php">
          <img src="/assets/icons/logo-mark.svg" alt="Logo ArchonDigital" width="32" height="32" />
          <span>ArchonDigital Admin</span>
        </a>

        <div class="d-flex align-items-center gap-3">
          <span class="small text-muted d-none d-md-inline">Masuk sebagai <strong class="text-navy"><?= e($admin['name']) ?></strong></span>
          <a class="ad-nav-link d-none d-sm-inline" href="/" target="_blank" rel="noopener noreferrer">Lihat Form Publik</a>
          <form method="POST" action="/admin/logout.php" class="d-inline">
            <?= CsrfMiddleware::inputField() ?>
            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">Keluar</button>
          </form>
        </div>
      </div>
    </header>

    <main id="main-content" class="py-4 py-lg-5">
      <div class="container">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
          <div>
            <h1 class="h3 fw-bold text-navy mb-1">Ringkasan &amp; Daftar Client Brief</h1>
            <p class="small text-muted mb-0">Kelola permintaan pembuatan website yang masuk dari calon klien ArchonDigital.</p>
          </div>
          <div class="small text-muted">
            Diarsipkan: <strong class="text-navy tabular-nums"><?= (int) ($summary['archived'] ?? 0) ?></strong>
          </div>
        </div>

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

        <!-- Summary Metric Cards -->
        <div class="row g-3 mb-4">
          <div class="col-6 col-md-4 col-lg">
            <div class="ad-metric-card">
              <div class="ad-metric-label">Total Brief Masuk</div>
              <div class="ad-metric-value tabular-nums"><?= (int) ($summary['total_all'] ?? 0) ?></div>
            </div>
          </div>
          <div class="col-6 col-md-4 col-lg">
            <div class="ad-metric-card">
              <div class="ad-metric-label">Brief Baru</div>
              <div class="ad-metric-value tabular-nums text-primary"><?= (int) ($summary['new'] ?? 0) ?></div>
            </div>
          </div>
          <div class="col-6 col-md-4 col-lg">
            <div class="ad-metric-card">
              <div class="ad-metric-label">Sedang Ditinjau</div>
              <div class="ad-metric-value tabular-nums"><?= (int) ($summary['reviewing'] ?? 0) ?></div>
            </div>
          </div>
          <div class="col-6 col-md-6 col-lg">
            <div class="ad-metric-card">
              <div class="ad-metric-label">Sudah Dihubungi</div>
              <div class="ad-metric-value tabular-nums"><?= (int) ($summary['contacted'] ?? 0) ?></div>
            </div>
          </div>
          <div class="col-12 col-md-6 col-lg">
            <div class="ad-metric-card">
              <div class="ad-metric-label">Selesai</div>
              <div class="ad-metric-value tabular-nums text-success"><?= (int) ($summary['completed'] ?? 0) ?></div>
            </div>
          </div>
        </div>

        <!-- Search & Filter Bar -->
        <div class="ad-form-card mb-4">
          <form method="GET" action="/admin/index.php" class="row g-3 align-items-end">
            <div class="col-md-4">
              <label for="q" class="form-label small fw-semibold">Cari Nama, Bisnis, atau Referensi</label>
              <input
                type="text"
                class="form-control"
                id="q"
                name="q"
                value="<?= e($filters['q'] ?? '') ?>"
                placeholder="Contoh: Budi / Kopi / BRF-2026..."
              />
            </div>

            <div class="col-md-3">
              <label for="status" class="form-label small fw-semibold">Filter Status</label>
              <select class="form-select" id="status" name="status">
                <option value="">Semua Status</option>
                <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Aktif (Kecuali Arsip)</option>
                <?php foreach ($statusLabels as $key => $label): ?>
                  <option value="<?= e($key) ?>" <?= ($filters['status'] ?? '') === $key ? 'selected' : '' ?>>
                    <?= e($label) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-6 col-md-2">
              <label for="date_from" class="form-label small fw-semibold">Dari Tanggal</label>
              <input type="date" class="form-control" id="date_from" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>" />
            </div>

            <div class="col-6 col-md-2">
              <label for="date_to" class="form-label small fw-semibold">Sampai Tanggal</label>
              <input type="date" class="form-control" id="date_to" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>" />
            </div>

            <div class="col-md-1 d-grid">
              <button type="submit" class="btn-ad-primary px-3">Filter</button>
            </div>
          </form>

          <?php if (array_filter($filters)): ?>
            <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center small">
              <span class="text-muted">Menampilkan <strong class="text-navy tabular-nums"><?= $total ?></strong> hasil yang cocok.</span>
              <a href="/admin/index.php" class="text-decoration-none fw-semibold">Reset Filter</a>
            </div>
          <?php endif; ?>
        </div>

        <!-- Briefs Table -->
        <div class="ad-form-card p-0 overflow-hidden">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-4">Referensi</th>
                  <th>Klien &amp; Bisnis</th>
                  <th>Layanan &amp; Anggaran</th>
                  <th>Status</th>
                  <th>Tanggal Masuk</th>
                  <th class="pe-4 text-end">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($items)): ?>
                  <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                      Belum ada data Client Brief yang sesuai dengan kriteria pencarian Anda.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($items as $row): ?>
                    <tr>
                      <td class="ps-4 fw-semibold text-navy tabular-nums text-nowrap">
                        <?= e($row['reference_code']) ?>
                      </td>
                      <td>
                        <div class="fw-semibold text-navy"><?= e($row['business_name']) ?></div>
                        <div class="small text-muted"><?= e($row['full_name']) ?> &middot; <?= e($row['whatsapp']) ?></div>
                      </td>
                      <td>
                        <div class="small fw-semibold text-navy"><?= e($row['website_type']) ?></div>
                        <div class="small text-muted"><?= e($row['budget_range']) ?></div>
                      </td>
                      <td>
                        <span class="ad-status-badge <?= e($statusBadgeClass((string) $row['status'])) ?>">
                          <?= e($statusLabels[$row['status']] ?? $row['status']) ?>
                        </span>
                      </td>
                      <td class="small text-muted tabular-nums text-nowrap">
                        <?= e(date('d M Y, H:i', strtotime((string) $row['created_at']))) ?>
                      </td>
                      <td class="pe-4 text-end">
                        <a class="btn btn-sm btn-outline-dark rounded-pill px-3" href="/admin/detail.php?id=<?= (int) $row['id'] ?>">
                          Detail
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <?php if ($totalPages > 1): ?>
            <nav class="p-3 border-top d-flex justify-content-between align-items-center" aria-label="Navigasi halaman">
              <span class="small text-muted tabular-nums">Halaman <?= $page ?> dari <?= $totalPages ?></span>
              <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                  <a class="page-link" href="<?= e($buildPageUrl(max(1, $page - 1))) ?>">Sebelumnya</a>
                </li>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                  <a class="page-link" href="<?= e($buildPageUrl(min($totalPages, $page + 1))) ?>">Berikutnya</a>
                </li>
              </ul>
            </nav>
          <?php endif; ?>
        </div>
      </div>
    </main>

<?php require VIEW_PATH . '/partials/footer.php'; ?>

<?php
declare(strict_types=1);
require VIEW_PATH . '/partials/head.php';

$mainSiteUrl = (string) app_config('main_site_url');
$nonce = bin2hex(random_bytes(16));
$val = static fn (string $k, string $default = ''): string => (string) ($old[$k] ?? $default);
$err = static fn (string $k): ?string => isset($errors[$k]) ? (string) $errors[$k] : null;
?>
    <header class="ad-navbar sticky-top">
      <div class="container d-flex align-items-center justify-content-between gap-3">
        <a class="ad-brand" href="/">
          <img src="/assets/icons/logo-mark.svg" alt="Logo ArchonDigital" width="32" height="32" />
          <span>ArchonDigital</span>
        </a>
        <div class="d-flex align-items-center gap-3">
          <a class="ad-nav-link" href="<?= e($mainSiteUrl) ?>">Kembali ke Website Utama</a>
          <a class="btn-ad-primary btn-sm-pill" href="https://wa.me/6285159117451" target="_blank" rel="noopener noreferrer">
            WhatsApp Resmi
          </a>
        </div>
      </div>
    </header>

    <main id="main-content">
      <!-- Intro Hero Band -->
      <section class="ad-hero-compact">
        <div class="ad-tech-grid-bg" aria-hidden="true"></div>
        <div class="container position-relative">
          <div class="row justify-content-center">
            <div class="col-lg-9">
              <p class="ad-hero-kicker">Client Brief ArchonDigital</p>
              <h1 class="ad-hero-title">Mulai Ceritakan Website yang Anda Butuhkan</h1>
              <p class="ad-hero-lead mb-3">
                Isi formulir kebutuhan website agar ArchonDigital dapat memahami bisnis, tujuan, dan fitur yang Anda perlukan sebelum proses konsultasi.
              </p>
              <p class="ad-hero-note mb-0">
                Tidak perlu mengunggah file atau mengirim dokumen sensitif di formulir ini. Logo, foto, dan materi bisnis dapat dikirim langsung melalui WhatsApp setelah tahap konsultasi.
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- Client Brief Form -->
      <section class="ad-form-section">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-9">
              <?php if (!empty($errors)): ?>
                <div class="alert alert-danger border-0 shadow-sm mb-4" role="alert">
                  <strong>Periksa kembali pengisian formulir Anda:</strong>
                  <p class="mb-0 mt-1 small">
                    <?= e($err('_global') ?? 'Beberapa kolom wajib belum lengkap atau formatnya perlu diperbaiki.') ?>
                  </p>
                </div>
              <?php endif; ?>

              <form id="clientBriefForm" method="POST" action="/" novalidate class="ad-brief-form">
                <?= CsrfMiddleware::inputField() ?>
                <input type="hidden" name="submission_nonce" value="<?= e($nonce) ?>" />

                <!-- Honeypot anti-spam -->
                <div class="ad-hp-field" aria-hidden="true">
                  <label for="company_verification_code">Kode Verifikasi Perusahaan</label>
                  <input type="text" id="company_verification_code" name="company_verification_code" tabindex="-1" autocomplete="off" value="" />
                </div>

                <!-- Kelompok A — Informasi Kontak -->
                <fieldset class="ad-form-card mb-4">
                  <legend class="ad-group-legend">
                    <span class="ad-group-step">Kelompok A</span>
                    Informasi Kontak
                  </legend>
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label for="full_name" class="form-label">Nama lengkap <span class="text-danger">*</span></label>
                      <input
                        type="text"
                        class="form-control <?= $err('full_name') ? 'is-invalid' : '' ?>"
                        id="full_name"
                        name="full_name"
                        value="<?= e($val('full_name')) ?>"
                        required
                        maxlength="120"
                        autocomplete="name"
                        placeholder="Contoh: Budi Santoso"
                      />
                      <?php if ($err('full_name')): ?>
                        <div class="invalid-feedback d-block"><?= e($err('full_name')) ?></div>
                      <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                      <label for="business_name" class="form-label">Nama bisnis / brand <span class="text-danger">*</span></label>
                      <input
                        type="text"
                        class="form-control <?= $err('business_name') ? 'is-invalid' : '' ?>"
                        id="business_name"
                        name="business_name"
                        value="<?= e($val('business_name')) ?>"
                        required
                        maxlength="150"
                        autocomplete="organization"
                        placeholder="Contoh: Kopi Nusantara Sejahtera"
                      />
                      <?php if ($err('business_name')): ?>
                        <div class="invalid-feedback d-block"><?= e($err('business_name')) ?></div>
                      <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                      <label for="whatsapp" class="form-label">Nomor WhatsApp aktif <span class="text-danger">*</span></label>
                      <input
                        type="tel"
                        class="form-control <?= $err('whatsapp') ? 'is-invalid' : '' ?>"
                        id="whatsapp"
                        name="whatsapp"
                        value="<?= e($val('whatsapp')) ?>"
                        required
                        maxlength="20"
                        inputmode="tel"
                        placeholder="Contoh: 085159117451 atau 6285159117451"
                      />
                      <div class="form-text">Gunakan nomor WhatsApp Indonesia aktif agar mudah dihubungi.</div>
                      <?php if ($err('whatsapp')): ?>
                        <div class="invalid-feedback d-block"><?= e($err('whatsapp')) ?></div>
                      <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                      <label for="email" class="form-label">Email <span class="text-muted fw-normal">(opsional)</span></label>
                      <input
                        type="email"
                        class="form-control <?= $err('email') ? 'is-invalid' : '' ?>"
                        id="email"
                        name="email"
                        value="<?= e($val('email')) ?>"
                        maxlength="160"
                        autocomplete="email"
                        placeholder="nama@perusahaan.com"
                      />
                      <?php if ($err('email')): ?>
                        <div class="invalid-feedback d-block"><?= e($err('email')) ?></div>
                      <?php endif; ?>
                    </div>
                  </div>
                </fieldset>

                <!-- Kelompok B — Jenis Layanan -->
                <fieldset class="ad-form-card mb-4">
                  <legend class="ad-group-legend">
                    <span class="ad-group-step">Kelompok B</span>
                    Jenis Layanan
                  </legend>
                  <p class="small text-muted mb-3">Pilih kategori website yang paling sesuai dengan kebutuhan Anda.</p>
                  <div class="row g-3">
                    <?php foreach ($websiteTypes as $idx => $type): ?>
                      <?php $checked = $val('website_type') === $type; ?>
                      <div class="col-sm-6">
                        <label class="ad-option-tile <?= $checked ? 'is-selected' : '' ?>" for="website_type_<?= $idx ?>">
                          <input
                            class="form-check-input me-2"
                            type="radio"
                            name="website_type"
                            id="website_type_<?= $idx ?>"
                            value="<?= e($type) ?>"
                            <?= $checked ? 'checked' : '' ?>
                            required
                          />
                          <span class="fw-semibold"><?= e($type) ?></span>
                        </label>
                      </div>
                    <?php endforeach; ?>
                  </div>
                  <?php if ($err('website_type')): ?>
                    <div class="invalid-feedback d-block mt-2"><?= e($err('website_type')) ?></div>
                  <?php endif; ?>
                </fieldset>

                <!-- Kelompok C — Kebutuhan Website -->
                <fieldset class="ad-form-card mb-4">
                  <legend class="ad-group-legend">
                    <span class="ad-group-step">Kelompok C</span>
                    Kebutuhan Website
                  </legend>
                  <div class="row g-3">
                    <div class="col-12">
                      <label for="website_goals" class="form-label">Tujuan pembuatan website <span class="text-danger">*</span></label>
                      <textarea
                        class="form-control <?= $err('website_goals') ? 'is-invalid' : '' ?>"
                        id="website_goals"
                        name="website_goals"
                        rows="3"
                        required
                        maxlength="1500"
                        data-char-count="1500"
                        placeholder="Contoh: Meningkatkan kredibilitas perusahaan, menampilkan katalog produk, dan memudahkan pelanggan menghubungi WhatsApp."
                      ><?= e($val('website_goals')) ?></textarea>
                      <?php if ($err('website_goals')): ?>
                        <div class="invalid-feedback d-block"><?= e($err('website_goals')) ?></div>
                      <?php endif; ?>
                    </div>

                    <div class="col-12">
                      <label for="business_description" class="form-label">Deskripsi singkat bisnis <span class="text-danger">*</span></label>
                      <textarea
                        class="form-control <?= $err('business_description') ? 'is-invalid' : '' ?>"
                        id="business_description"
                        name="business_description"
                        rows="3"
                        required
                        maxlength="1500"
                        data-char-count="1500"
                        placeholder="Jelaskan bidang usaha, produk atau layanan utama, dan target pelanggan Anda."
                      ><?= e($val('business_description')) ?></textarea>
                      <?php if ($err('business_description')): ?>
                        <div class="invalid-feedback d-block"><?= e($err('business_description')) ?></div>
                      <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                      <label for="required_pages" class="form-label">Halaman yang dibutuhkan <span class="text-danger">*</span></label>
                      <textarea
                        class="form-control <?= $err('required_pages') ? 'is-invalid' : '' ?>"
                        id="required_pages"
                        name="required_pages"
                        rows="3"
                        required
                        maxlength="1000"
                        data-char-count="1000"
                        placeholder="Contoh: Beranda, Tentang Kami, Layanan, Katalog Produk, Kontak"
                      ><?= e($val('required_pages')) ?></textarea>
                      <?php if ($err('required_pages')): ?>
                        <div class="invalid-feedback d-block"><?= e($err('required_pages')) ?></div>
                      <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                      <label for="required_features" class="form-label">Fitur yang dibutuhkan <span class="text-danger">*</span></label>
                      <textarea
                        class="form-control <?= $err('required_features') ? 'is-invalid' : '' ?>"
                        id="required_features"
                        name="required_features"
                        rows="3"
                        required
                        maxlength="1500"
                        data-char-count="1500"
                        placeholder="Contoh: Tombol WhatsApp, galeri foto, filter kategori, dashboard admin"
                      ><?= e($val('required_features')) ?></textarea>
                      <?php if ($err('required_features')): ?>
                        <div class="invalid-feedback d-block"><?= e($err('required_features')) ?></div>
                      <?php endif; ?>
                    </div>

                    <div class="col-12">
                      <label for="reference_url" class="form-label">Referensi website <span class="text-muted fw-normal">(opsional)</span></label>
                      <input
                        type="url"
                        class="form-control <?= $err('reference_url') ? 'is-invalid' : '' ?>"
                        id="reference_url"
                        name="reference_url"
                        value="<?= e($val('reference_url')) ?>"
                        maxlength="255"
                        placeholder="https://contoh-website.com"
                      />
                      <div class="form-text">Cantumkan alamat website yang gayanya sesuai dengan preferensi Anda, bila ada.</div>
                      <?php if ($err('reference_url')): ?>
                        <div class="invalid-feedback d-block"><?= e($err('reference_url')) ?></div>
                      <?php endif; ?>
                    </div>
                  </div>
                </fieldset>

                <!-- Kelompok D — Anggaran & Kelompok E — Waktu -->
                <div class="row g-4 mb-4">
                  <div class="col-lg-6">
                    <fieldset class="ad-form-card h-100">
                      <legend class="ad-group-legend">
                        <span class="ad-group-step">Kelompok D</span>
                        Kisaran Anggaran
                      </legend>
                      <div class="d-flex flex-column gap-2">
                        <?php foreach ($budgetRanges as $idx => $budget): ?>
                          <?php $checked = $val('budget_range') === $budget; ?>
                          <label class="ad-option-tile <?= $checked ? 'is-selected' : '' ?>" for="budget_range_<?= $idx ?>">
                            <input
                              class="form-check-input me-2"
                              type="radio"
                              name="budget_range"
                              id="budget_range_<?= $idx ?>"
                              value="<?= e($budget) ?>"
                              <?= $checked ? 'checked' : '' ?>
                              required
                            />
                            <span><?= e($budget) ?></span>
                          </label>
                        <?php endforeach; ?>
                      </div>
                      <?php if ($err('budget_range')): ?>
                        <div class="invalid-feedback d-block mt-2"><?= e($err('budget_range')) ?></div>
                      <?php endif; ?>
                    </fieldset>
                  </div>

                  <div class="col-lg-6">
                    <fieldset class="ad-form-card h-100">
                      <legend class="ad-group-legend">
                        <span class="ad-group-step">Kelompok E</span>
                        Perkiraan Waktu
                      </legend>
                      <div class="d-flex flex-column gap-2">
                        <?php foreach ($timelines as $idx => $timeline): ?>
                          <?php $checked = $val('target_timeline') === $timeline; ?>
                          <label class="ad-option-tile <?= $checked ? 'is-selected' : '' ?>" for="target_timeline_<?= $idx ?>">
                            <input
                              class="form-check-input me-2"
                              type="radio"
                              name="target_timeline"
                              id="target_timeline_<?= $idx ?>"
                              value="<?= e($timeline) ?>"
                              <?= $checked ? 'checked' : '' ?>
                              required
                            />
                            <span><?= e($timeline) ?></span>
                          </label>
                        <?php endforeach; ?>
                      </div>
                      <?php if ($err('target_timeline')): ?>
                        <div class="invalid-feedback d-block mt-2"><?= e($err('target_timeline')) ?></div>
                      <?php endif; ?>
                    </fieldset>
                  </div>
                </div>

                <!-- Kelompok F — Informasi Tambahan -->
                <fieldset class="ad-form-card mb-4">
                  <legend class="ad-group-legend">
                    <span class="ad-group-step">Kelompok F</span>
                    Informasi Tambahan
                  </legend>
                  <div>
                    <label for="additional_notes" class="form-label">Catatan kebutuhan tambahan <span class="text-muted fw-normal">(opsional)</span></label>
                    <textarea
                      class="form-control <?= $err('additional_notes') ? 'is-invalid' : '' ?>"
                      id="additional_notes"
                      name="additional_notes"
                      rows="3"
                      maxlength="1500"
                      data-char-count="1500"
                      placeholder="Sampaikan informasi tambahan lain yang perlu tim ArchonDigital ketahui."
                    ><?= e($val('additional_notes')) ?></textarea>
                    <?php if ($err('additional_notes')): ?>
                      <div class="invalid-feedback d-block"><?= e($err('additional_notes')) ?></div>
                    <?php endif; ?>
                  </div>
                </fieldset>

                <!-- Persetujuan & Privasi -->
                <div class="ad-form-card mb-4">
                  <div class="ad-privacy-summary mb-3">
                    <h2 class="h6 fw-bold text-navy mb-2">Pemberitahuan Privasi Singkat</h2>
                    <p class="small text-body mb-2">
                      Data yang Anda kirimkan (informasi kontak serta kebutuhan website) dikumpulkan khusus untuk keperluan peninjauan awal, konsultasi, dan tindak lanjut permintaan pembuatan website oleh ArchonDigital. Kami tidak meminta kata sandi, nomor KTP, maupun informasi rekening pada formulir ini.
                    </p>
                    <p class="small text-muted mb-0">
                      Untuk koreksi atau permintaan penghapusan data, silakan hubungi ArchonDigital melalui WhatsApp resmi (+62 851-5911-7451) atau baca <a href="/privacy.php">Pemberitahuan Privasi Lengkap</a>.
                    </p>
                  </div>

                  <div class="form-check">
                    <input
                      class="form-check-input <?= $err('privacy_consent') ? 'is-invalid' : '' ?>"
                      type="checkbox"
                      value="1"
                      id="privacy_consent"
                      name="privacy_consent"
                      <?= !empty($old['privacy_consent']) ? 'checked' : '' ?>
                      required
                    />
                    <label class="form-check-label" for="privacy_consent">
                      Saya menyetujui penggunaan informasi yang saya berikan untuk keperluan konsultasi dan tindak lanjut permintaan pembuatan website oleh ArchonDigital. <span class="text-danger">*</span>
                    </label>
                    <?php if ($err('privacy_consent')): ?>
                      <div class="invalid-feedback d-block"><?= e($err('privacy_consent')) ?></div>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
                  <p class="small text-muted mb-0">
                    Bidang bertanda <span class="text-danger">*</span> wajib diisi sebelum mengirim.
                  </p>
                  <button type="submit" id="submitBriefBtn" class="btn-ad-cyan btn-lg-pill">
                    Kirim Client Brief
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </section>
    </main>

<?php require VIEW_PATH . '/partials/footer.php'; ?>

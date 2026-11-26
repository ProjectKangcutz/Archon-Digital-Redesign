<?php
declare(strict_types=1);
require VIEW_PATH . '/partials/head.php';

$mainSiteUrl = (string) app_config('main_site_url');
$archonWa = (string) app_config('archon_whatsapp');
$refCode = is_array($confirmation) ? (string) ($confirmation['reference_code'] ?? '') : '';
$websiteType = is_array($confirmation) ? (string) ($confirmation['website_type'] ?? '') : '';
$submittedAt = is_array($confirmation) ? (string) ($confirmation['submitted_at'] ?? '') : '';

$waMsg = $refCode !== ''
    ? "Halo ArchonDigital, saya telah mengirimkan Client Brief dengan nomor referensi {$refCode} dan ingin berdiskusi lebih lanjut."
    : 'Halo ArchonDigital, saya ingin berkonsultasi mengenai pembuatan website.';
$waUrl = SecurityService::buildSafeWhatsappUrl($archonWa, $waMsg);
?>
    <header class="ad-navbar sticky-top">
      <div class="container d-flex align-items-center justify-content-between gap-3">
        <a class="ad-brand" href="/">
          <img src="/assets/icons/logo-mark.svg" alt="Logo ArchonDigital" width="32" height="32" />
          <span>ArchonDigital</span>
        </a>
        <a class="ad-nav-link" href="<?= e($mainSiteUrl) ?>">Kembali ke Website Utama</a>
      </div>
    </header>

    <main id="main-content" class="py-5">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <article class="ad-form-card p-4 p-md-5">
              <?php if ($refCode !== ''): ?>
                <p class="ad-section-kicker mb-2">Konfirmasi Penerimaan</p>
                <h1 class="h2 fw-bold text-navy mb-3">Client Brief Anda Telah Diterima</h1>
                <p class="text-body mb-4">
                  Terima kasih telah meluangkan waktu mengisi kebutuhan website Anda. Permintaan Anda telah tercatat dalam sistem ArchonDigital dan akan segera ditinjau oleh tim kami sebelum proses konsultasi.
                </p>

                <div class="ad-ref-box mb-4">
                  <div class="small text-muted mb-1">Nomor Referensi Anda</div>
                  <div class="ad-ref-code tabular-nums"><?= e($refCode) ?></div>
                  <div class="small text-muted mt-2">
                    Kategori layanan: <strong class="text-navy"><?= e($websiteType) ?></strong> &middot; Tercatat pada <?= e($submittedAt) ?>
                  </div>
                </div>

                <p class="small text-muted mb-4">
                  Simpan nomor referensi di atas untuk memudahkan pengecekan saat konsultasi. Jika Anda ingin mengirim materi pendukung (seperti logo, foto produk, atau dokumen ringkas) maupun melanjutkan diskusi, Anda dapat menghubungi ArchonDigital melalui WhatsApp di bawah ini.
                </p>
              <?php else: ?>
                <p class="ad-section-kicker mb-2">Informasi Halaman Konfirmasi</p>
                <h1 class="h3 fw-bold text-navy mb-3">Belum Ada Pengiriman Baru pada Sesi Ini</h1>
                <p class="text-body mb-4">
                  Silakan isi formulir Client Brief terlebih dahulu untuk mendapatkan nomor referensi permintaan Anda, atau hubungi kami langsung melalui WhatsApp.
                </p>
              <?php endif; ?>

              <div class="d-flex flex-wrap gap-3">
                <a class="btn-ad-primary" href="<?= e($waUrl) ?>" target="_blank" rel="noopener noreferrer">
                  Konsultasi via WhatsApp ArchonDigital
                </a>
                <a class="btn-ad-outline-navy" href="/">Isi Formulir Baru</a>
                <a class="btn-ad-outline-navy" href="<?= e($mainSiteUrl) ?>">Kembali ke archondigital.web.id</a>
              </div>
            </article>
          </div>
        </div>
      </div>
    </main>

<?php require VIEW_PATH . '/partials/footer.php'; ?>

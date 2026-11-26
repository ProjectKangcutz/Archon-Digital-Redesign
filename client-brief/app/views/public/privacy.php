<?php
declare(strict_types=1);
require VIEW_PATH . '/partials/head.php';
$mainSiteUrl = (string) app_config('main_site_url');
?>
    <header class="ad-navbar sticky-top">
      <div class="container d-flex align-items-center justify-content-between gap-3">
        <a class="ad-brand" href="/">
          <img src="/assets/icons/logo-mark.svg" alt="Logo ArchonDigital" width="32" height="32" />
          <span>ArchonDigital</span>
        </a>
        <a class="ad-nav-link" href="/">Kembali ke Formulir Brief</a>
      </div>
    </header>

    <main id="main-content" class="py-5">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <article class="ad-form-card p-4 p-md-5">
              <p class="ad-section-kicker mb-2">Kebijakan &amp; Pemberitahuan Privasi</p>
              <h1 class="h2 fw-bold text-navy mb-4">Pengelolaan Data Client Brief ArchonDigital</h1>

              <h2 class="h5 fw-bold text-navy mt-4 mb-2">1. Informasi yang Dikumpulkan</h2>
              <p>
                Melalui formulir Client Brief di <strong>brief.archondigital.web.id</strong>, kami hanya mengumpulkan informasi yang Anda isi secara sukarela, meliputi nama lengkap, nama bisnis/brand, nomor WhatsApp aktif, alamat email (jika diisi), serta rincian kebutuhan website (tujuan, deskripsi usaha, halaman, fitur, referensi URL, kisaran anggaran, perkiraan waktu, dan catatan tambahan).
              </p>

              <h2 class="h5 fw-bold text-navy mt-4 mb-2">2. Tujuan Pengumpulan &amp; Penggunaan Informasi</h2>
              <p>
                Informasi tersebut digunakan secara eksklusif oleh ArchonDigital untuk:
              </p>
              <ul>
                <li>Meninjau dan memahami kebutuhan website bisnis Anda sebelum sesi konsultasi.</li>
                <li>Menghubungi Anda kembali melalui WhatsApp atau kontak yang Anda cantumkan guna menindaklanjuti permintaan pembuatan website.</li>
                <li>Menyusun kesepakatan ruang lingkup proyek secara akurat sebelum pengerjaan dimulai.</li>
              </ul>

              <h2 class="h5 fw-bold text-navy mt-4 mb-2">3. Pembatasan Data</h2>
              <p>
                Formulir Client Brief tidak meminta data sensitif seperti nomor KTP, kata sandi akun, ataupun informasi rekening bank. Data yang dikirimkan tidak dipublikasikan kepada pengunjung umum dan tidak dikirimkan otomatis ke layanan pemasaran pihak ketiga.
              </p>

              <h2 class="h5 fw-bold text-navy mt-4 mb-2">4. Permintaan Koreksi atau Penghapusan Data</h2>
              <p>
                Anda berhak meminta pembaruan, koreksi, maupun penghapusan data Client Brief yang telah Anda kirimkan. Cantumkan nomor referensi permintaan Anda saat menghubungi kami.
              </p>

              <h2 class="h5 fw-bold text-navy mt-4 mb-2">5. Kontak Resmi ArchonDigital</h2>
              <p class="mb-4">
                Untuk pertanyaan terkait privasi atau pengelolaan data Anda, hubungi ArchonDigital melalui WhatsApp resmi di nomor <strong>+62 851-5911-7451</strong>.
              </p>

              <div class="d-flex flex-wrap gap-3">
                <a class="btn-ad-primary" href="https://wa.me/6285159117451" target="_blank" rel="noopener noreferrer">Hubungi WhatsApp ArchonDigital</a>
                <a class="btn-ad-outline-navy" href="/">Kembali ke Formulir Brief</a>
                <a class="btn-ad-outline-navy" href="<?= e($mainSiteUrl) ?>">Website Utama</a>
              </div>
            </article>
          </div>
        </div>
      </div>
    </main>

<?php require VIEW_PATH . '/partials/footer.php'; ?>

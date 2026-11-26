<?php
declare(strict_types=1);
$mainSiteUrl = (string) app_config('main_site_url');
?>
    <footer class="ad-footer">
      <div class="container">
        <div class="row gy-3 align-items-center justify-content-between">
          <div class="col-md-6">
            <span class="fw-semibold text-white">ArchonDigital</span>
            <span class="mx-2 text-secondary">&middot;</span>
            <span class="small">Portal Client Brief Pembuatan Website</span>
          </div>
          <div class="col-md-6 text-md-end small d-flex flex-wrap gap-3 justify-content-md-end">
            <a href="<?= e($mainSiteUrl) ?>" target="_blank" rel="noopener noreferrer">Website Utama ArchonDigital</a>
            <a href="/privacy.php">Pemberitahuan Privasi</a>
            <a href="https://wa.me/6285159117451" target="_blank" rel="noopener noreferrer">WhatsApp Resmi</a>
          </div>
        </div>
        <div class="ad-footer-bottom">
          <span>&copy; <?= date('Y') ?> ArchonDigital. Seluruh hak cipta dilindungi.</span>
        </div>
      </div>
    </footer>

    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
      crossorigin="anonymous"
      defer
    ></script>
    <script src="/assets/js/app.js" defer></script>
  </body>
</html>

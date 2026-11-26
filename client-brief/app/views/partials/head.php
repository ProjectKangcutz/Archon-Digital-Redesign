<?php
declare(strict_types=1);
$title = $pageTitle ?? 'ArchonDigital Client Brief';
$desc = $metaDescription ?? 'Formulir Client Brief resmi ArchonDigital untuk penyampaian kebutuhan pembuatan website sebelum konsultasi.';
$isAdminPage = !empty($isAdminPage);
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= e($title) ?></title>
    <meta name="description" content="<?= e($desc) ?>" />
    <?php if ($isAdminPage): ?>
    <meta name="robots" content="noindex, nofollow" />
    <?php else: ?>
    <meta name="robots" content="index, follow" />
    <?php endif; ?>
    <meta name="theme-color" content="#10243B" />
    <link rel="icon" type="image/svg+xml" href="/assets/icons/favicon.svg" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap"
      rel="stylesheet"
    />
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
      crossorigin="anonymous"
    />
    <link rel="stylesheet" href="/assets/css/style.css" />
  </head>
  <body>
    <a class="skip-link" href="#main-content">Lewati ke konten utama</a>

<?php
declare(strict_types=1);

final class PublicBriefController
{
    public function showForm(): void
    {
        $errors = $_SESSION['brief_errors'] ?? [];
        $old = $_SESSION['brief_old'] ?? [];
        unset($_SESSION['brief_errors'], $_SESSION['brief_old']);

        render_view('public/form', [
            'pageTitle' => 'Client Brief — ArchonDigital',
            'metaDescription' => 'Formulir Client Brief ArchonDigital untuk menyampaikan kebutuhan pembuatan website UMKM, Company Profile, Toko Online, dan Website Custom sebelum konsultasi.',
            'errors' => $errors,
            'old' => $old,
            'websiteTypes' => ValidationService::WEBSITE_TYPES,
            'budgetRanges' => ValidationService::BUDGET_RANGES,
            'timelines' => ValidationService::TIMELINES,
        ]);
    }

    public function handleSubmit(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: /');
            exit;
        }

        if (!CsrfMiddleware::verify($_POST['csrf_token'] ?? null)) {
            $_SESSION['brief_errors'] = ['_global' => 'Token keamanan tidak valid atau kedaluwarsa. Silakan muat ulang halaman dan kirim ulang formulir.'];
            $_SESSION['brief_old'] = $_POST;
            header('Location: /');
            exit;
        }

        $rlCfg = app_config('rate_limit');
        $maxAttempts = (int) ($rlCfg['brief_max_attempts'] ?? 5);
        $windowSeconds = (int) ($rlCfg['brief_window_seconds'] ?? 900);
        $bucketKey = 'brief_submit:' . SecurityService::getClientIpHash();

        if (RateLimiter::isBlocked($bucketKey, $maxAttempts, $windowSeconds)) {
            $_SESSION['brief_errors'] = ['_global' => 'Batas pengiriman formulir sementara tercapai. Silakan tunggu beberapa menit sebelum mengirim kembali atau hubungi kami melalui WhatsApp.'];
            $_SESSION['brief_old'] = $_POST;
            header('Location: /');
            exit;
        }

        // Prevent rapid duplicate re-submission of the exact same submission nonce
        $submissionNonce = (string) ($_POST['submission_nonce'] ?? '');
        if ($submissionNonce !== '' && !empty($_SESSION['last_brief_nonce']) && hash_equals((string) $_SESSION['last_brief_nonce'], $submissionNonce)) {
            if (!empty($_SESSION['brief_confirmation'])) {
                header('Location: /confirmation.php');
                exit;
            }
        }

        $result = ValidationService::validateBrief($_POST);
        if (!empty($result['errors'])) {
            $_SESSION['brief_errors'] = $result['errors'];
            $_SESSION['brief_old'] = $_POST;
            header('Location: /');
            exit;
        }

        RateLimiter::hit($bucketKey, $maxAttempts, $windowSeconds);
        $referenceCode = BriefService::create($result['data']);

        if ($submissionNonce !== '') {
            $_SESSION['last_brief_nonce'] = $submissionNonce;
        }
        CsrfMiddleware::rotate();

        $_SESSION['brief_confirmation'] = [
            'reference_code' => $referenceCode,
            'website_type' => $result['data']['website_type'],
            'submitted_at' => date('d M Y, H:i') . ' WIB',
        ];

        header('Location: /confirmation.php');
        exit;
    }

    public function showConfirmation(): void
    {
        $confirmation = $_SESSION['brief_confirmation'] ?? null;

        render_view('public/confirmation', [
            'pageTitle' => 'Permintaan Diterima — Client Brief ArchonDigital',
            'metaDescription' => 'Konfirmasi penerimaan Client Brief ArchonDigital beserta nomor referensi permintaan website Anda.',
            'confirmation' => $confirmation,
        ]);
    }

    public function showPrivacy(): void
    {
        render_view('public/privacy', [
            'pageTitle' => 'Kebijakan & Pemberitahuan Privasi — ArchonDigital Client Brief',
            'metaDescription' => 'Informasi pengelolaan data pada formulir Client Brief ArchonDigital untuk keperluan konsultasi pembuatan website.',
        ]);
    }
}

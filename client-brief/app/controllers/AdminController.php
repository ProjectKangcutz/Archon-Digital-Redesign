<?php
declare(strict_types=1);

final class AdminController
{
    public function __construct()
    {
        SecurityService::sendNoCacheHeaders();
    }

    public function showLogin(): void
    {
        if (AuthMiddleware::check()) {
            header('Location: /admin/index.php');
            exit;
        }

        $error = $_SESSION['auth_error'] ?? null;
        $notice = $_SESSION['auth_notice'] ?? null;
        $oldEmail = $_SESSION['auth_old_email'] ?? '';
        unset($_SESSION['auth_error'], $_SESSION['auth_notice'], $_SESSION['auth_old_email']);

        render_view('admin/login', [
            'pageTitle' => 'Masuk Admin — Client Brief ArchonDigital',
            'error' => $error,
            'notice' => $notice,
            'oldEmail' => $oldEmail,
        ]);
    }

    public function handleLogin(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: /admin/login.php');
            exit;
        }

        $email = (string) ($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        if (!CsrfMiddleware::verify($_POST['csrf_token'] ?? null)) {
            $_SESSION['auth_error'] = 'Sesi formulir tidak valid. Silakan muat ulang halaman dan coba lagi.';
            $_SESSION['auth_old_email'] = $email;
            header('Location: /admin/login.php');
            exit;
        }

        $attempt = AuthService::attemptLogin($email, $password);
        if (!$attempt['ok']) {
            $_SESSION['auth_error'] = $attempt['error'];
            $_SESSION['auth_old_email'] = $email;
            header('Location: /admin/login.php');
            exit;
        }

        header('Location: /admin/index.php');
        exit;
    }

    public function handleLogout(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: /admin/index.php');
            exit;
        }

        if (!CsrfMiddleware::verify($_POST['csrf_token'] ?? null)) {
            header('Location: /admin/index.php');
            exit;
        }

        AuthService::logout();
        header('Location: /admin/login.php');
        exit;
    }

    public function dashboard(): void
    {
        $admin = AuthMiddleware::requireAdmin();

        $filters = [
            'q' => (string) ($_GET['q'] ?? ''),
            'status' => (string) ($_GET['status'] ?? ''),
            'date_from' => (string) ($_GET['date_from'] ?? ''),
            'date_to' => (string) ($_GET['date_to'] ?? ''),
        ];
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $summary = BriefService::getSummaryCounts();
        $listData = BriefService::list($filters, $page, 15);

        $flashSuccess = $_SESSION['admin_flash_success'] ?? null;
        $flashError = $_SESSION['admin_flash_error'] ?? null;
        unset($_SESSION['admin_flash_success'], $_SESSION['admin_flash_error']);

        render_view('admin/dashboard', [
            'pageTitle' => 'Dashboard Admin — Client Brief ArchonDigital',
            'admin' => $admin,
            'summary' => $summary,
            'listData' => $listData,
            'filters' => $filters,
            'statusLabels' => BriefService::STATUS_LABELS,
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
        ]);
    }

    public function detail(): void
    {
        $admin = AuthMiddleware::requireAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['admin_flash_error'] = 'Client Brief tidak ditemukan.';
            header('Location: /admin/index.php');
            exit;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->handleDetailAction($id, (int) $admin['id']);
            return;
        }

        $brief = BriefService::findById($id);
        if (!$brief) {
            $_SESSION['admin_flash_error'] = 'Data Client Brief tidak ditemukan atau telah dihapus.';
            header('Location: /admin/index.php');
            exit;
        }

        $flashSuccess = $_SESSION['admin_flash_success'] ?? null;
        $flashError = $_SESSION['admin_flash_error'] ?? null;
        unset($_SESSION['admin_flash_success'], $_SESSION['admin_flash_error']);

        // Polite follow-up WhatsApp URL using client's number WITHOUT placing sensitive data in query string
        $politeWaMessage = 'Halo, saya dari ArchonDigital. Terima kasih telah mengirimkan Client Brief. Kami ingin menindaklanjuti kebutuhan website Anda.';
        $clientWhatsappUrl = SecurityService::buildSafeWhatsappUrl((string) $brief['whatsapp'], $politeWaMessage);

        render_view('admin/detail', [
            'pageTitle' => 'Detail ' . $brief['reference_code'] . ' — Admin ArchonDigital',
            'admin' => $admin,
            'brief' => $brief,
            'statusLabels' => BriefService::STATUS_LABELS,
            'clientWhatsappUrl' => $clientWhatsappUrl,
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
        ]);
    }

    private function handleDetailAction(int $id, int $adminId): void
    {
        if (!CsrfMiddleware::verify($_POST['csrf_token'] ?? null)) {
            $_SESSION['admin_flash_error'] = 'Token keamanan tidak valid. Tindakan dibatalkan.';
            header('Location: /admin/detail.php?id=' . $id);
            exit;
        }

        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'update') {
            $status = (string) ($_POST['status'] ?? '');
            $notes = isset($_POST['admin_notes']) ? (string) $_POST['admin_notes'] : null;
            if (BriefService::updateStatusAndNotes($id, $status, $notes, $adminId)) {
                $_SESSION['admin_flash_success'] = 'Status dan catatan internal berhasil diperbarui.';
            } else {
                $_SESSION['admin_flash_error'] = 'Gagal memperbarui status. Pastikan pilihan status valid.';
            }
            header('Location: /admin/detail.php?id=' . $id);
            exit;
        }

        if ($action === 'archive') {
            if (BriefService::archive($id, $adminId)) {
                $_SESSION['admin_flash_success'] = 'Client Brief berhasil diarsipkan.';
            } else {
                $_SESSION['admin_flash_error'] = 'Gagal mengarsipkan Client Brief.';
            }
            header('Location: /admin/detail.php?id=' . $id);
            exit;
        }

        if ($action === 'delete') {
            if (BriefService::delete($id)) {
                $_SESSION['admin_flash_success'] = 'Client Brief berhasil dihapus secara permanen.';
                header('Location: /admin/index.php');
                exit;
            }
            $_SESSION['admin_flash_error'] = 'Gagal menghapus Client Brief.';
            header('Location: /admin/detail.php?id=' . $id);
            exit;
        }

        $_SESSION['admin_flash_error'] = 'Tindakan tidak dikenali.';
        header('Location: /admin/detail.php?id=' . $id);
        exit;
    }
}

<?php
declare(strict_types=1);

final class BriefService
{
    public const STATUS_LABELS = [
        'new' => 'Baru',
        'reviewing' => 'Sedang Ditinjau',
        'contacted' => 'Sudah Dihubungi',
        'completed' => 'Selesai',
        'archived' => 'Diarsipkan',
    ];

    public static function create(array $data): string
    {
        $pdo = Connection::getPdo();
        $referenceCode = self::generateUniqueReferenceCode($pdo);

        $sql = 'INSERT INTO client_briefs (
            reference_code,
            full_name,
            business_name,
            whatsapp,
            email,
            website_type,
            business_description,
            website_goals,
            required_pages,
            required_features,
            reference_url,
            budget_range,
            target_timeline,
            additional_notes,
            privacy_consent,
            status
        ) VALUES (
            :reference_code,
            :full_name,
            :business_name,
            :whatsapp,
            :email,
            :website_type,
            :business_description,
            :website_goals,
            :required_pages,
            :required_features,
            :reference_url,
            :budget_range,
            :target_timeline,
            :additional_notes,
            :privacy_consent,
            :status
        )';

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':reference_code' => $referenceCode,
            ':full_name' => $data['full_name'],
            ':business_name' => $data['business_name'],
            ':whatsapp' => $data['whatsapp'],
            ':email' => $data['email'],
            ':website_type' => $data['website_type'],
            ':business_description' => $data['business_description'],
            ':website_goals' => $data['website_goals'],
            ':required_pages' => $data['required_pages'],
            ':required_features' => $data['required_features'],
            ':reference_url' => $data['reference_url'],
            ':budget_range' => $data['budget_range'],
            ':target_timeline' => $data['target_timeline'],
            ':additional_notes' => $data['additional_notes'],
            ':privacy_consent' => (int) $data['privacy_consent'],
            ':status' => 'new',
        ]);

        return $referenceCode;
    }

    public static function getSummaryCounts(): array
    {
        $pdo = Connection::getPdo();
        $sql = "SELECT
            COUNT(*) AS total_all,
            SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) AS count_new,
            SUM(CASE WHEN status = 'reviewing' THEN 1 ELSE 0 END) AS count_reviewing,
            SUM(CASE WHEN status = 'contacted' THEN 1 ELSE 0 END) AS count_contacted,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS count_completed,
            SUM(CASE WHEN status = 'archived' THEN 1 ELSE 0 END) AS count_archived
        FROM client_briefs";

        $row = $pdo->query($sql)->fetch();
        return [
            'total_all' => (int) ($row['total_all'] ?? 0),
            'new' => (int) ($row['count_new'] ?? 0),
            'reviewing' => (int) ($row['count_reviewing'] ?? 0),
            'contacted' => (int) ($row['count_contacted'] ?? 0),
            'completed' => (int) ($row['count_completed'] ?? 0),
            'archived' => (int) ($row['count_archived'] ?? 0),
        ];
    }

    public static function list(array $filters, int $page = 1, int $perPage = 15): array
    {
        $pdo = Connection::getPdo();
        $where = [];
        $params = [];

        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $where[] = '(full_name LIKE :q1 OR business_name LIKE :q2 OR reference_code LIKE :q3)';
            $like = '%' . self::escapeLike($search) . '%';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
        }

        $status = (string) ($filters['status'] ?? '');
        if ($status === 'active') {
            $where[] = "status <> 'archived'";
        } elseif ($status !== '' && array_key_exists($status, self::STATUS_LABELS)) {
            $where[] = 'status = :status';
            $params[':status'] = $status;
        }

        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
            $where[] = 'created_at >= :date_from';
            $params[':date_from'] = $dateFrom . ' 00:00:00';
        }

        $dateTo = trim((string) ($filters['date_to'] ?? ''));
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
            $where[] = 'created_at <= :date_to';
            $params[':date_to'] = $dateTo . ' 23:59:59';
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM client_briefs $whereSql");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT id, reference_code, full_name, business_name, whatsapp, website_type, budget_range, target_timeline, status, created_at
                FROM client_briefs
                $whereSql
                ORDER BY created_at DESC, id DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'total_pages' => $totalPages,
        ];
    }

    public static function findById(int $id): ?array
    {
        $pdo = Connection::getPdo();
        $stmt = $pdo->prepare(
            'SELECT b.*, a.name AS reviewed_by_name
             FROM client_briefs b
             LEFT JOIN admins a ON a.id = b.reviewed_by_admin_id
             WHERE b.id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function updateStatusAndNotes(int $id, string $status, ?string $adminNotes, int $adminId): bool
    {
        if (!array_key_exists($status, self::STATUS_LABELS)) {
            return false;
        }
        $cleanNotes = $adminNotes !== null ? trim(str_replace("\0", '', $adminNotes)) : null;
        if ($cleanNotes !== null && mb_strlen($cleanNotes) > 4000) {
            $cleanNotes = mb_substr($cleanNotes, 0, 4000);
        }

        $pdo = Connection::getPdo();
        $stmt = $pdo->prepare(
            'UPDATE client_briefs
             SET status = :status, admin_notes = :notes, reviewed_by_admin_id = :admin_id
             WHERE id = :id'
        );
        return $stmt->execute([
            ':status' => $status,
            ':notes' => $cleanNotes !== '' ? $cleanNotes : null,
            ':admin_id' => $adminId,
            ':id' => $id,
        ]);
    }

    public static function archive(int $id, int $adminId): bool
    {
        $pdo = Connection::getPdo();
        $stmt = $pdo->prepare(
            "UPDATE client_briefs SET status = 'archived', reviewed_by_admin_id = :admin_id WHERE id = :id"
        );
        return $stmt->execute([':admin_id' => $adminId, ':id' => $id]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Connection::getPdo();
        $stmt = $pdo->prepare('DELETE FROM client_briefs WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    private static function generateUniqueReferenceCode(PDO $pdo): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $maxIdx = strlen($alphabet) - 1;
        $datePart = date('Ymd');

        for ($attempt = 0; $attempt < 8; $attempt++) {
            $rand = '';
            $bytes = random_bytes(6);
            for ($i = 0; $i < 6; $i++) {
                $rand .= $alphabet[ord($bytes[$i]) % ($maxIdx + 1)];
            }
            $code = 'BRF-' . $datePart . '-' . $rand;
            $check = $pdo->prepare('SELECT 1 FROM client_briefs WHERE reference_code = :code LIMIT 1');
            $check->execute([':code' => $code]);
            if (!$check->fetchColumn()) {
                return $code;
            }
        }

        throw new RuntimeException('Gagal membuat nomor referensi unik.');
    }

    private static function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}

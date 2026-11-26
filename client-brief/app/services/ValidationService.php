<?php
declare(strict_types=1);

final class ValidationService
{
    public const WEBSITE_TYPES = [
        'Website UMKM',
        'Company Profile',
        'E-Commerce / Toko Online',
        'Custom Website Development',
    ];

    public const BUDGET_RANGES = [
        'Di bawah Rp500.000',
        'Rp500.000–Rp1.500.000',
        'Rp1.500.000–Rp3.500.000',
        'Di atas Rp3.500.000',
        'Masih ingin berkonsultasi',
    ];

    public const TIMELINES = [
        'Segera',
        'Dalam 1 bulan',
        'Dalam 1–3 bulan',
        'Belum ditentukan',
    ];

    public static function validateBrief(array $input): array
    {
        $errors = [];

        // Honeypot check
        $honeypot = trim((string) ($input['company_verification_code'] ?? ''));
        if ($honeypot !== '') {
            $errors['_global'] = 'Pengiriman tidak dapat diproses. Silakan muat ulang halaman dan coba kembali.';
            return ['errors' => $errors, 'data' => []];
        }

        // Normalize strings
        $fullName = self::cleanText($input['full_name'] ?? '');
        $businessName = self::cleanText($input['business_name'] ?? '');
        $rawWhatsapp = self::cleanText($input['whatsapp'] ?? '');
        $email = self::cleanText($input['email'] ?? '');
        $websiteType = self::cleanText($input['website_type'] ?? '');
        $websiteGoals = self::cleanMultiline($input['website_goals'] ?? '');
        $businessDescription = self::cleanMultiline($input['business_description'] ?? '');
        $requiredPages = self::cleanMultiline($input['required_pages'] ?? '');
        $requiredFeatures = self::cleanMultiline($input['required_features'] ?? '');
        $referenceUrl = self::cleanText($input['reference_url'] ?? '');
        $budgetRange = self::cleanText($input['budget_range'] ?? '');
        $targetTimeline = self::cleanText($input['target_timeline'] ?? '');
        $additionalNotes = self::cleanMultiline($input['additional_notes'] ?? '');
        $privacyConsent = !empty($input['privacy_consent']);

        // Kelompok A — Informasi kontak
        if ($fullName === '' || mb_strlen($fullName) < 2) {
            $errors['full_name'] = 'Nama lengkap wajib diisi (minimal 2 karakter).';
        } elseif (mb_strlen($fullName) > 120) {
            $errors['full_name'] = 'Nama lengkap maksimal 120 karakter.';
        }

        if ($businessName === '' || mb_strlen($businessName) < 2) {
            $errors['business_name'] = 'Nama bisnis atau brand wajib diisi (minimal 2 karakter).';
        } elseif (mb_strlen($businessName) > 150) {
            $errors['business_name'] = 'Nama bisnis atau brand maksimal 150 karakter.';
        }

        $normalizedWa = self::normalizeIndonesianWhatsApp($rawWhatsapp);
        if ($normalizedWa === null) {
            $errors['whatsapp'] = 'Masukkan nomor WhatsApp Indonesia yang valid (contoh: 085159117451 atau 6285159117451).';
        }

        if ($email !== '') {
            if (mb_strlen($email) > 160 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                $errors['email'] = 'Format alamat email tidak valid atau melebihi 160 karakter.';
            }
        }

        // Kelompok B — Jenis layanan
        if (!in_array($websiteType, self::WEBSITE_TYPES, true)) {
            $errors['website_type'] = 'Pilih salah satu jenis layanan website yang tersedia.';
        }

        // Kelompok C — Kebutuhan website
        if ($websiteGoals === '' || mb_strlen($websiteGoals) < 10) {
            $errors['website_goals'] = 'Tujuan pembuatan website wajib diisi (minimal 10 karakter).';
        } elseif (mb_strlen($websiteGoals) > 1500) {
            $errors['website_goals'] = 'Tujuan pembuatan website maksimal 1.500 karakter.';
        }

        if ($businessDescription === '' || mb_strlen($businessDescription) < 10) {
            $errors['business_description'] = 'Deskripsi singkat bisnis wajib diisi (minimal 10 karakter).';
        } elseif (mb_strlen($businessDescription) > 1500) {
            $errors['business_description'] = 'Deskripsi singkat bisnis maksimal 1.500 karakter.';
        }

        if ($requiredPages === '' || mb_strlen($requiredPages) < 3) {
            $errors['required_pages'] = 'Sebutkan halaman yang dibutuhkan (minimal 3 karakter).';
        } elseif (mb_strlen($requiredPages) > 1000) {
            $errors['required_pages'] = 'Daftar halaman yang dibutuhkan maksimal 1.000 karakter.';
        }

        if ($requiredFeatures === '' || mb_strlen($requiredFeatures) < 3) {
            $errors['required_features'] = 'Sebutkan fitur yang dibutuhkan (minimal 3 karakter).';
        } elseif (mb_strlen($requiredFeatures) > 1500) {
            $errors['required_features'] = 'Daftar fitur yang dibutuhkan maksimal 1.500 karakter.';
        }

        $cleanReferenceUrl = null;
        if ($referenceUrl !== '') {
            if (mb_strlen($referenceUrl) > 255) {
                $errors['reference_url'] = 'URL referensi website maksimal 255 karakter.';
            } else {
                $candidateUrl = $referenceUrl;
                if (!preg_match('#^https?://#i', $candidateUrl)) {
                    $candidateUrl = 'https://' . $candidateUrl;
                }
                $sanitized = SecurityService::sanitizeReferenceUrl($candidateUrl);
                if ($sanitized === null || filter_var($sanitized, FILTER_VALIDATE_URL) === false) {
                    $errors['reference_url'] = 'Format URL referensi website tidak valid (gunakan http:// atau https://).';
                } else {
                    $cleanReferenceUrl = $sanitized;
                }
            }
        }

        // Kelompok D — Anggaran
        if (!in_array($budgetRange, self::BUDGET_RANGES, true)) {
            $errors['budget_range'] = 'Pilih salah satu kisaran anggaran yang tersedia.';
        }

        // Kelompok E — Waktu
        if (!in_array($targetTimeline, self::TIMELINES, true)) {
            $errors['target_timeline'] = 'Pilih perkiraan waktu pengerjaan.';
        }

        // Kelompok F — Informasi tambahan
        if (mb_strlen($additionalNotes) > 1500) {
            $errors['additional_notes'] = 'Catatan kebutuhan tambahan maksimal 1.500 karakter.';
        }

        // Checkbox persetujuan wajib
        if (!$privacyConsent) {
            $errors['privacy_consent'] = 'Anda wajib menyetujui penggunaan informasi untuk keperluan konsultasi sebelum mengirim formulir.';
        }

        return [
            'errors' => $errors,
            'data' => [
                'full_name' => $fullName,
                'business_name' => $businessName,
                'whatsapp' => $normalizedWa ?? $rawWhatsapp,
                'email' => $email !== '' ? $email : null,
                'website_type' => $websiteType,
                'website_goals' => $websiteGoals,
                'business_description' => $businessDescription,
                'required_pages' => $requiredPages,
                'required_features' => $requiredFeatures,
                'reference_url' => $cleanReferenceUrl,
                'budget_range' => $budgetRange,
                'target_timeline' => $targetTimeline,
                'additional_notes' => $additionalNotes !== '' ? $additionalNotes : null,
                'privacy_consent' => $privacyConsent ? 1 : 0,
            ],
        ];
    }

    public static function normalizeIndonesianWhatsApp(string $raw): ?string
    {
        $stripped = preg_replace('/[\s\-\(\)\.]+/', '', trim($raw)) ?? '';
        if ($stripped === '') {
            return null;
        }
        if (str_starts_with($stripped, '+')) {
            $stripped = substr($stripped, 1);
        }
        if (!ctype_digit($stripped)) {
            return null;
        }
        if (str_starts_with($stripped, '08')) {
            $stripped = '62' . substr($stripped, 1);
        } elseif (str_starts_with($stripped, '8')) {
            $stripped = '62' . $stripped;
        }

        // Indonesian mobile numbers normalized as 628xxxxxxxx (total 10 to 15 digits)
        if (!preg_match('/^628[1-9][0-9]{6,11}$/', $stripped)) {
            return null;
        }

        return $stripped;
    }

    private static function cleanText(mixed $val): string
    {
        $str = is_string($val) ? $val : '';
        $str = str_replace("\0", '', $str);
        return trim(preg_replace('/\s+/u', ' ', $str) ?? '');
    }

    private static function cleanMultiline(mixed $val): string
    {
        $str = is_string($val) ? $val : '';
        $str = str_replace(["\0", "\r\n", "\r"], ['', "\n", "\n"], $str);
        return trim($str);
    }
}

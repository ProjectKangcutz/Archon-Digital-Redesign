<?php
declare(strict_types=1);

final class CsrfMiddleware
{
    private const SESSION_KEY = '_csrf_token';

    public static function getToken(): string
    {
        if (empty($_SESSION[self::SESSION_KEY]) || !is_string($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    public static function inputField(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . e(self::getToken()) . '">';
    }

    public static function verify(?string $submittedToken): bool
    {
        if (empty($_SESSION[self::SESSION_KEY]) || !is_string($submittedToken) || $submittedToken === '') {
            return false;
        }
        return hash_equals($_SESSION[self::SESSION_KEY], $submittedToken);
    }

    public static function rotate(): void
    {
        $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
    }
}

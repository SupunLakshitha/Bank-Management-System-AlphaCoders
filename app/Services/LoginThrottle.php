<?php

namespace App\Services;

/**
 * Simple brute-force protection for login.
 * Counts failed attempts per username + IP address and locks that
 * combination for a while after too many failures. Uses the CodeIgniter
 * cache (writable/cache), so no database table is needed.
 */
class LoginThrottle
{
    private const MAX_ATTEMPTS = 5;     // failed attempts allowed
    private const LOCK_SECONDS = 600;   // lock time (10 minutes)

    private static function key(string $username, string $ip): string
    {
        return 'login_throttle_' . md5(strtolower(trim($username)) . '|' . $ip);
    }

    /**
     * Seconds left until the user may try again (0 = not locked).
     */
    public static function secondsLeft(string $username, string $ip): int
    {
        $data = cache(self::key($username, $ip));

        if (! is_array($data) || empty($data['locked_until'])) {
            return 0;
        }

        $left = (int) $data['locked_until'] - time();

        return $left > 0 ? $left : 0;
    }

    /**
     * Record one failed login attempt.
     */
    public static function fail(string $username, string $ip): void
    {
        $key   = self::key($username, $ip);
        $data  = cache($key);
        $count = is_array($data) ? (int) ($data['count'] ?? 0) : 0;
        $count++;

        $payload = [
            'count'        => $count,
            'locked_until' => $count >= self::MAX_ATTEMPTS ? time() + self::LOCK_SECONDS : 0,
        ];

        cache()->save($key, $payload, self::LOCK_SECONDS);
    }

    /**
     * Forget failed attempts after a successful login.
     */
    public static function clear(string $username, string $ip): void
    {
        cache()->delete(self::key($username, $ip));
    }
}
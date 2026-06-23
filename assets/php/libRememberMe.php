<?php

/**
 * Remember Me functionality
 * 
 * Handles persistent login functionality using secure tokens
 */

require_once __ROOT__ . '/assets/php/libQueryBuilder.php';

class rememberMe {
    protected $objDbConn;
    protected $tokenLength = 64;
    protected $cookieName = 'remember_token';
    protected $cookieExpiry = 30 * 24 * 60 * 60; // 30 days in seconds

    /**
     * Constructor
     * 
     * @param object|null $objDbConn Database connection object
     */
    public function __construct(&$objDbConn = null) {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
    }

    /**
     * Generate a secure random token
     * 
     * @return string Secure random token
     */
    private function generateToken(): string {
        return bin2hex(random_bytes($this->tokenLength / 2));
    }

    /**
     * Save remember token to database for user
     * 
     * @param int $userId User ID
     * @param string $token Remember token
     * @return bool Success status
     */
    public function saveToken(int $userId, string $token): bool {
        $qb = new QueryBuilder();
        $query = $qb->table('users')
            ->where('id', '=', $userId)
            ->update(['remember_token' => $token]);

        $result = $this->objDbConn->prepProcessQuery(
            $query['sql'],
            $query['types'],
            $query['params']
        );

        return $result['result'];
    }

    /**
     * Validate remember token and return user data if valid
     * 
     * @param string $token Remember token from cookie
     * @return array|null User data if valid, null otherwise
     */
    public function validateToken(string $token): ?array {
        $qb = new QueryBuilder();
        $qb->table('users u')
            ->select([
                'u.id',
                'u.locale_id',
                'u.first',
                'u.last',
                'u.email',
                'u.access',
                'u.dark'
            ])
            ->where('u.remember_token', '=', $token)
            ->where('u.enabled', '=', 1);

        $query = $qb->build();
        $result = $this->objDbConn->prepProcessQuery(
            $query['sql'],
            $query['types'],
            $query['params']
        );

        if ($result['result'] && count($result['data']) > 0) {
            return $result['data'][0];
        }

        return null;
    }

    /**
     * Clear remember token from database
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function clearToken(int $userId): bool {
        $qb = new QueryBuilder();
        $query = $qb->table('users')
            ->where('id', '=', $userId)
            ->update(['remember_token' => null]);

        $result = $this->objDbConn->prepProcessQuery(
            $query['sql'],
            $query['types'],
            $query['params']
        );

        return $result['result'];
    }

    /**
     * Set remember me cookie
     * 
     * @param string $token Remember token
     */
    public function setCookie(string $token): void {
        setcookie(
            $this->cookieName,
            $token,
            [
                'expires' => time() + $this->cookieExpiry,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]
        );
    }

    /**
     * Clear remember me cookie
     */
    public function clearCookie(): void {
        setcookie(
            $this->cookieName,
            '',
            [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]
        );
    }

    /**
     * Get remember token from cookie
     * 
     * @return string|null Token if cookie exists, null otherwise
     */
    public function getCookie(): ?string {
        return $_COOKIE[$this->cookieName] ?? null;
    }

    /**
     * Create and save remember token for user
     * 
     * @param int $userId User ID
     * @return string|null Token if successful, null otherwise
     */
    public function createRememberToken(int $userId): ?string {
        $token = $this->generateToken();

        if ($this->saveToken($userId, $token)) {
            return $token;
        }

        return null;
    }
}

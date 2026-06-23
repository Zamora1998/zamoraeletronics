<?php
class auth {
    protected $objDbConn;

    public function __construct(&$objDbConn = null) {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
        require_once __ROOT__ . '/assets/php/libQueryBuilder.php';
    }

    function authenticate(string $user, string $pass, bool $remember = false) {
        $auth = false;
        $token = '';
        $results = [];
        $errors = [];
        $return = [];

        $qb = new QueryBuilder();
        $qb->table('users u')
            ->select([
                'u.id',
                'u.locale_id',
                'u.first',
                'u.last',
                'u.pass',
                'u.email',
                'u.access',
                'u.dark'
            ])
            ->where('u.email', '=', $user)
            ->where('u.enabled', '=', 1)
            ->limit(1);

        $query = $qb->build();
        $result = $this->objDbConn->prepProcessQuery(
            $query['sql'],
            $query['types'],
            $query['params']
        );

        $results[] = $result['result'];
        if (!$result['result']) {
            $errors[] = $result['error'];
        } else {
            $data = $result['data'];
            if (count($data)) {
                $data = $data[0];
                if (password_verify($pass, $data['pass'])) {
                    $auth = true;
                    unset($data['pass']);
                    $return = $data;
                }
            }
        }

        if ($auth) {
            $dataToken = $this->updateToken($data['id'], $remember);
            $token = $dataToken['token'];
            $results[] = $dataToken['result'];
            $errors = array_merge($errors, $dataToken['errors']);
        }

        return array('result' => !in_array(false, $results, true), 'errors' => $errors, 'data' => $return, 'auth' => $auth, 'token' => $token);
    }

    function authorize(int $userId, string $token) {
        $auth = false;
        $results = [];
        $errors = [];
        $data = [];

        $qb = new QueryBuilder();
        $qb->table('users u')
            ->select([
                'u.id',
                'u.locale_id',
                'u.first',
                'u.last',
                'u.email',
                'u.access',
                'u.dark',
                'u.uuid'
            ])
            ->where('u.id', '=', $userId)
            ->where('u.uuid', '=', $token)
            ->where('u.enabled', '=', 1);

        $query = $qb->build();
        $result = $this->objDbConn->prepProcessQuery(
            $query['sql'],
            $query['types'],
            $query['params']
        );

        $results[] = $result['result'];
        if (!$result['result']) {
            $errors[] = $result['error'];
        } else {
            $data = $result['data'];
            if (count($data)) {
                $data = $data[0];
                $auth = true;
            }
        }

        return array('result' => !in_array(false, $results, true), 'errors' => $errors, 'data' => $data, 'auth' => $auth, 'token' => $token);
    }

    private function updateToken(int $userId, bool $remember = false) {
        $token = '';
        $results = [];
        $errors = [];

        $query = (new QueryBuilder())
            ->select('uuid() AS uuid')
            ->build();

        $result = $this->objDbConn->prepProcessQuery(
            $query['sql'],
            $query['types'],
            $query['params']
        );

        $results[] = $result['result'];
        if ($result) {
            $data = $result['data'];
            if (count($data)) {
                $token = $data[0]['uuid'];
            }
        }

        if ($token) {
            $qb = new QueryBuilder();
            $updateData = [
                'uuid' => $token,
                'lastused' => date('Y-m-d H:i:s')
            ];

            // If remember is true, also generate and store a remember token
            if ($remember) {
                $rememberToken = bin2hex(random_bytes(32));
                $updateData['remember_token'] = $rememberToken;
            }

            $query = $qb->table('users')
                ->where('id', '=', $userId)
                ->update($updateData);

            $result = $this->objDbConn->prepProcessQuery(
                $query['sql'],
                $query['types'],
                $query['params']
            );

            $results[] = $result['result'];
            if (!$result['result']) {
                $errors[] = $result['error'];
                $token = '';
            } elseif ($remember && isset($rememberToken)) {
                // Set the remember token cookie
                setcookie(
                    'remember_token',
                    $rememberToken,
                    [
                        'expires' => time() + (30 * 24 * 60 * 60), // 30 days
                        'path' => '/',
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'Strict'
                    ]
                );
            }
        }

        return array('result' => !in_array(false, $results, true), 'errors' => $errors, 'token' => $token);
    }

    /**
     * Validate remember token and auto-login if valid
     * 
     * @return array|null User data if valid, null otherwise
     */
    public function checkRememberToken(): ?array {
        if (!isset($_COOKIE['remember_token'])) {
            return null;
        }

        $token = $_COOKIE['remember_token'];

        $qb = new QueryBuilder();
        $qb->table('users u')
            ->select([
                'u.id',
                'u.locale_id',
                'u.first',
                'u.last',
                'u.email',
                'u.access',
                'u.dark',
                'u.uuid'
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
     * Clear remember token on logout
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function clearRememberToken(int $userId): bool {
        $qb = new QueryBuilder();
        $query = $qb->table('users')
            ->where('id', '=', $userId)
            ->update(['remember_token' => null]);

        $result = $this->objDbConn->prepProcessQuery(
            $query['sql'],
            $query['types'],
            $query['params']
        );

        // Clear the cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie(
                'remember_token',
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

        return $result['result'];
    }
}

<?php

class profile {
    protected $objDbConn;
    protected $id = 0;
    protected $email = '';
    protected $pass = '';
    protected $passCurrent = '';
    protected $first = '';
    protected $last = '';
    protected $localeId = 'en_US';
    protected $languageId = 'en';
    protected $dark;

    public function __construct(&$objDbConn = null) {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
    }

    public function select() {
        $sql = "SELECT
                    id,
                    email,
                    first,
                    last,
                    locale_id,
                    dark
                FROM users
                WHERE id = {$this->id}";

        return $this->objDbConn->processQuery($sql);
    }

    public function update() {
        $sql = "UPDATE users
                        SET first = '{$this->first}', 
                            last = '{$this->last}',
                            email = '{$this->email}',
                            locale_id = '{$this->localeId}'
                        WHERE id = {$this->id};";

        return $this->objDbConn->processQuery($sql);
    }

    public function updatePass() {
        $results = [];
        $errors = [];
        $data = [];
        $auth = false;
        $info = 0;

        $sql = "SELECT pass
                FROM users
                WHERE id = {$this->id};";

        $result = $this->objDbConn->processQuery($sql);
        $results[] = $result['result'];
        if (!$result['result']) {
            $errors[] = $result['error'];
        } else {
            $data = $result['data'];
            if (count($data)) {
                $data = $data[0];
                if (password_verify($this->passCurrent, $data['pass'])) {
                    $auth = true;
                }
            }
        }

        if ($auth) {
            // Update
            $sql = "UPDATE users
                SET pass = '{$this->pass}'
                WHERE id = {$this->id};";

            $result = $this->objDbConn->processQuery($sql);
            $results[] = $result['result'];
            if (!$result['result']) {
                $errors[] = $result['error'];
            } else {
                $info = $this->objDbConn->getAffectedRows();
            }
        }
        return array('result' => !in_array(false, $results, true), 'errors' => $errors, 'auth' => $auth, 'info' => $info);
    }

    public function updateDarkMode() {
        $sql = "UPDATE users SET dark = {$this->dark} WHERE id = {$this->id}";

        return $this->objDbConn->processQuery($sql);
    }

    public function setId(int $int) {
        $this->id = $int;
    }

    public function setFirst(string $str) {
        $this->first = $this->objDbConn->mysqlRealEscape(trim($str ?? ''));
    }

    public function setLast(string $str) {
        $this->last = $this->objDbConn->mysqlRealEscape(trim($str ?? ''));
    }

    public function setEmail(string $str) {
        $this->email = $this->objDbConn->mysqlRealEscape(trim($str ?? ''));
    }

    public function setLocaleId(string $str) {
        $this->localeId = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function setPass(string $str) {
        $this->pass = $this->objDbConn->mysqlRealEscape(password_hash(trim($str), PASSWORD_BCRYPT));
    }

    public function setCurrentPass(string $str) {
        $this->passCurrent = trim($str);
    }

    public function setLanguageId(string $str) {
        $this->languageId = $this->objDbConn->mysqlRealEscape(trim($str));
    }
    public function setDark(int $int) {
        $this->dark = $int;
    }
}

<?php
class users {
    protected $objDbConn;

    protected $userId = 0;
    protected $localeId = 'en_US';
    protected $first = '';
    protected $last = '';
    protected $pass = '';
    protected $email = '';
    protected $enabled = 0;
    protected $uuid;

    public function __construct(&$objDbConn = null) {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
    }

    private function select() {
    }

    public function insert() {
        $this->objDbConn->applyQuery("call reset_ai('users');");
        $result = false;
        $error = '';
        $sql = "INSERT INTO users (email, first, last, pass, enabled, locale_id) VALUES
                ('{$this->email}', '{$this->first}', '{$this->last}', '{$this->pass}', {$this->enabled}, '{$this->localeId}');";

        //file_put_contents(__ROOT__ . '/debugContacts.txt', var_export($sql, true));
        $result = $this->objDbConn->applyQuery($sql);
        if (!$result) {
            $error = $this->objDbConn->getError();
        } else {
            $this->userId = $this->objDbConn->getLastId();
        }
        return array('result' => $result, 'error' => $error, 'userId' => $this->userId);
    }

    private function update() {
    }

    public function setUserId(int $int) {
        $this->userId = $int;
    }
    public function setLocaleId(string $str) {
        $this->localeId = $str;
    }

    public function setFirstname(string $str) {
        $this->first = $this->objDbConn->mysqlRealEscape($str);
    }

    public function setLastname(string $str) {
        $this->last = $this->objDbConn->mysqlRealEscape($str);
    }

    public function setEmail(string $str) {
        $this->email = $this->objDbConn->mysqlRealEscape($str);
    }

    public function setPass(string $str) {
        $this->pass = $this->objDbConn->mysqlRealEscape(password_hash($str, PASSWORD_BCRYPT));
    }

    public function setEnabled(bool $bln) {
        $this->enabled = $bln;
    }
}

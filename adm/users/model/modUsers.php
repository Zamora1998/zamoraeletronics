<?php

class users {
    protected $objDbConn;
    protected $id = 0;
    protected $modId = 0;
    protected $email = '';
    protected $pass = '';
    protected $first = '';
    protected $last = '';
    protected $enabled = FALSE;
    protected $access = 0;
    protected $localeId = 'en_US';
    protected $languageId = 'es';

    function __construct(&$objDbConn = null) {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
    }

    public function selectAll() {
        $result = false;
        $error = "";
        $data = [];

        $sql = "SELECT
                    newuser.id,
                    COALESCE(labels.description, newuser.last) AS text,
                    newuser.email,
                    newuser.first,
                    COALESCE(labels.description, newuser.last) AS last,
                    newuser.locale_id,
                    newuser.access,
                    newuser.enabled
                FROM (
                    SELECT
                        0 AS id,
                        '' AS email,
                        '' AS first,
                        'lblAddUser N/A' AS last,
                        '' AS locale_id,
                        '' AS access,
                        1 AS enabled
                ) newuser
                    LEFT JOIN (
                        SELECT
                            labels.name,
                            labeldetails.description
                        FROM labels
                            INNER JOIN labeldetails ON labeldetails.label_id = labels.id
                        WHERE
                            labeldetails.language_id = '{$this->languageId}'
                    ) labels
                        ON labels.name = 'lblAddUser'
                UNION ALL
                SELECT
                    id,
                    CONCAT_WS(' - ', CONCAT_WS(' ', first, last), email) AS text,
                    email,
                    first,
                    last,
                    locale_id,
                    access,
                    enabled
                FROM users
                ORDER BY first, last;\n";

        //file_put_contents(__ROOT__ . '/debugUsers.txt', var_export($sql, true));
        $result = $this->objDbConn->applyQuery($sql);
        if (!$result) {
            $error = $this->objDbConn->getError();
        } else {
            $data = $this->objDbConn->getDataQuery($result);
        }

        return array('result' => ($result ? true : false), 'error' => $error, 'data' => $data);
    }

    public function selectTable() {
        $sql = "SELECT
                    id,
                    email,
                    first,
                    last
                FROM users
                ORDER BY first, last;";

        return $this->objDbConn->processQuery($sql);
    }

    public function select() {
        $result = false;
        $error = "";
        $values = [];

        $sql = "SELECT
                    id,
                    email,
                    first,
                    last,
                    locale_id,
                    accesses,
                    created,
                    enabled
                FROM users
                    LEFT JOIN (
                        Select
                            users.id AS user_id,
                            Group_Concat(users.access & pow(2, accesses.id - 1)) As accesses
                        From users, accesses
                        Where users.access & pow(2, accesses.id - 1) != 0
                        Group By users.id
                    ) accesses
                        ON users.id = accesses.user_id
                WHERE id = {$this->id}\n";

        //file_put_contents(__ROOT__ . '/debugMailTemplate.txt', var_export($sql, true));
        $result = $this->objDbConn->applyQuery($sql);
        if (!$result) {
            $error = $this->objDbConn->getError();
        } else {
            $data = $this->objDbConn->getDataQuery($result);
            if (!empty($data)) {
                $data = $data[0];
                $data['accesses'] = explode(',', $data['accesses'] ?? '');
            }
        }

        return array('result' => ($result ? true : false), 'error' => $error, 'data' => $data);
    }

    public function selectStaticAccesses() {
        $result = false;
        $error = "";
        $data = [];

        $sql = "SELECT Bit_Or(user.access) As access
                FROM (
                    SELECT 
                        users.access & POW(2, accesses.id - 1) AS access,
                        accesses.id AS id
                    FROM users, accesses
                    WHERE users.id = {$this->id}
                    ) user
                        INNER JOIN (
                            SELECT 
                                users.access & POW(2, accesses.id - 1) AS access,
                                accesses.id AS id
                            FROM users, accesses
                            WHERE users.id = {$this->modId}
                        ) moduser
                            ON moduser.id = user.id
                WHERE moduser.access = 0\n";

        //file_put_contents(__ROOT__ . '/debugUsers.txt', var_export($sql, true));
        $result = $this->objDbConn->applyQuery($sql);
        if (!$result) {
            $error = $this->objDbConn->getError();
        } else {
            $data = $this->objDbConn->getDataQuery($result);
            if (!empty($data)) {
                $data = $data[0];
            }
        }

        return array('result' => ($result ? true : false), 'error' => $error, 'data' => $data);
    }

    public function update() {
        if ($this->id) {
            // Update
            $sql = "UPDATE users
                        SET first = '{$this->first}', 
                            last = '{$this->last}',
                            email = '{$this->email}',
                            locale_id = '{$this->localeId}',
                            enabled = {$this->enabled},
                            access = {$this->access}
                        WHERE id = {$this->id};";
        } else {
            // Insert
            $this->objDbConn->resetAI('users');
            $sql = "INSERT INTO users (
                        email,
                        first,
                        last,
                        locale_id,
                        enabled,
                        access
                    ) VALUES (
                        '{$this->email}',
                        '{$this->first}',
                        '{$this->last}',
                        '{$this->localeId}',
                        {$this->enabled},
                        {$this->access}
                    );";
        }

        $return = $this->objDbConn->processQuery($sql);
        if (!$this->id) {
            $this->id = $this->objDbConn->getLastId();
        }
        $return['id'] = $this->id;

        return $return;
    }

    public function setId(int $int) {
        $this->id = $int;
    }

    public function setModUserId(int $int) {
        $this->modId = $int;
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

    public function setPass(string $str) {
        $this->pass = $this->objDbConn->mysqlRealEscape(trim($str ?? ''));
    }

    public function setEnabled(int  $int) {
        $this->enabled = $int;
    }

    public function setAccess(int $int) {
        $this->access = $int;
    }

    public function setLocaleId(string $str) {
        $this->localeId = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function setLanguageId(string $str) {
        $this->languageId = $this->objDbConn->mysqlRealEscape(trim($str));
    }
}

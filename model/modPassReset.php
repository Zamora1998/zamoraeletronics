<?php

class passReset
{
    protected $objDbConn;
    protected $userId = 0;
    protected $user = "";
    protected $first = "";
    protected $last = "";
    protected $key = "";
    protected $pass = '';
    protected $languageId = 1;

    public function __construct(&$objDbConn = null)
    {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
    }

    public function selectUser()
    {
        $sql = "SELECT
                    users.id,
                    users.email,
                    users.first,
                    users.last,
                    users.locale_id,
                    locales.language_id
                FROM users
                    LEFT JOIN locales ON users.locale_id = locales.id
                WHERE users.email = '{$this->user}'
                LIMIT 1;";

        $return = $this->objDbConn->processQuery($sql);
        if ($return['result'] && !empty($return['data'])) {
            $data = $return['data'][0];
            $this->userId = $data['id'];
            $this->user = $data['email'];
            $this->first = $data['first'];
            $this->last = $data['last'];
            $this->languageId = $data['language_id'];
            $return['data'] = $data;
        }

        return $return;
    }

    public function selectUserByKey()
    {
        $error = '';
        $result = false;

        $sql = "SELECT
                    users.id,
                    users.email,
                    users.first,
                    users.last,
                    users.locale_id,
                    locales.language_id
                FROM users
                    INNER JOIN passwordresets
                        ON passwordresets.user_id = users.id
                    LEFT JOIN locales
                        ON users.locale_id = locales.id
                WHERE passwordresets.`key` = '{$this->key}'
                LIMIT 1;";

        //file_put_contents(__ROOT__.'/debugPWReset.txt',var_export($sql, true));
        $result = $this->objDbConn->applyQuery($sql);
        $data = $this->objDbConn->getDataQuery($result);
        if (!$result) {
            $error = $this->objDbConn->getError();
        } else {
            foreach ($data as $record) {
                $this->userId = $record['id'];
                $this->user = $record['email'];
                $this->first = $record['first'];
                $this->last = $record['last'];
                $this->languageId = $record['language_id'];
            }
        }

        return array('result' => ($result ? 1 : 0), 'error' => $error, 'data' => $data);
    }

    public function insertNewReset()
    {
        $key = '';
        $return = ['result' => false, 'error' => 'No user'];
        if ($this->userId) {
            $this->newKey();
            $sql = "INSERT INTO passwordresets (user_id, `key`, created, attempts)
                    VALUES ($this->userId, '{$this->key}', current_timestamp(), 0) 
                    ON DUPLICATE KEY UPDATE `key` = VALUES(`key`), created = VALUES(created), attempts = VALUES(attempts);\n";

            $return = $this->objDbConn->processQuery($sql);
            if ($return['result']) {
                $key = $this->key;
            }
        }

        return $return;
    }

    public function validateKey()
    {
        $error = '';

        if ($this->key) {
            $sql = "SELECT `key`
                    FROM passwordresets
                    WHERE `key`='{$this->key}'
                       AND current_timestamp()<timestampadd(HOUR, 3, created)";

            //file_put_contents(__ROOT__.'/debugPWReset.txt',var_export($sql, true));
            $result = $this->objDbConn->applyQuery($sql);
            $data = $this->objDbConn->getDataQuery($result);
            if (!$result) {
                $error = $this->objDbConn->getError();
            }
            if (empty($data)) {
                $result = false;
            }
        }

        return array('result' => $result, 'error' => $error, 'key' => $this->key);
    }

    public function clearKeys()
    {
        $error = '';
        $sql = "DELETE
                FROM passwordresets
                WHERE current_timestamp()>timestampadd(HOUR, 3, created)";
        $result = $this->objDbConn->applyQuery($sql);
        if (!$result) {
            $error = $this->objDbConn->getError();
        }

        return array('result' => $result, 'error' => $error);
    }

    public function updatePass()
    {
        $results = [];
        $errors = [];
        $infos = [];
        $mail = [];

        $sql = "UPDATE users
                    INNER JOIN passwordresets
                        ON passwordresets.user_id = users.id
                SET pass='{$this->pass}'
                WHERE passwordresets.key='{$this->key}';";

        //file_put_contents(__ROOT__ . '/debugPWReset.txt', var_export($sql, true));
        $result = $this->objDbConn->applyQuery($sql);
        $results[] = $result;
        if (!$result) {
            $errors[] = $this->objDbConn->getError();
        } else {
            $infos = $this->objDbConn->getInfo();
        }

        //file_put_contents(__ROOT__ . '/debugPWReset.txt', var_export($sql, true));
        if (isset($infos['Rows matched']) && $infos['Rows matched']) {
            if (isset($infos['Changed']) && $infos['Changed']) {
                //get user
                $return = $this->selectUserByKey();
                $results[] = $return['result'];
                $errors[] = $return['error'];
                //send email
                $mail = $this->sendEmail(2);
                //remove key
                $return = $this->deleteKey();
                $results[] = $return['result'];
                $errors[] = $return['error'];
            } else {
                $return = $this->updateAttempt();
                $results[] = $return['result'];
                $errors[] = $return['error'];
            }
        }
        $this->deleteKeys();

        return array('result' => !in_array(false, $results, true), 'errors' => $errors, 'infos' => $infos, 'mail' => $mail);
    }

    private function updateAttempt()
    {
        $error = '';
        $sql = "UPDATE passwordresets SET attempts = attempts + 1 WHERE `key` = '{$this->key}';";

        $result = $this->objDbConn->applyQuery($sql);
        if (!$result) {
            $error = $this->objDbConn->getError();
        }

        return array('result' => $result, 'error' => $error);
    }

    public function deleteKey()
    {
        $error = '';
        $sql = "DELETE FROM passwordresets WHERE `key` = '{$this->key}';";

        //file_put_contents(__ROOT__ . '/debugPWReset.txt', var_export($sql, true));
        $result = $this->objDbConn->applyQuery($sql);
        if (!$result) {
            $error = $this->objDbConn->getError();
        }

        return array('result' => $result, 'error' => $error);
    }

    private function deleteKeys()
    {
        $error = '';
        $sql = "DELETE FROM passwordresets 
                WHERE CURRENT_TIMESTAMP() > TIMESTAMPADD(HOUR, 3, created)
                   OR attempts > 2;";

        $result = $this->objDbConn->applyQuery($sql);
        if (!$result) {
            $error = $this->objDbConn->getError();
        }
    }

    public function setKey(string $str)
    {
        $this->key = $this->objDbConn->mysqlRealEscape($str);
    }

    public function setUser(string $str)
    {
        $this->user = $this->objDbConn->mysqlRealEscape($str);
    }

    public function setUserId(int $userId)
    {
        $this->userId = $userId;
    }

    public function setFirst(string $str)
    {
        $this->first = $this->objDbConn->mysqlRealEscape($str);
    }

    public function setLast(string $str)
    {
        $this->last = $this->objDbConn->mysqlRealEscape($str);
    }

    public function setPassword(string $str)
    {
        $this->pass = $this->objDbConn->mysqlRealEscape(password_hash($str, PASSWORD_DEFAULT));
    }

    public function getUser()
    {
        return htmlspecialchars($this->user, ENT_SUBSTITUTE);
    }

    public function newKey()
    {
        $this->key = $this->random_str(128);

        return array('result' => true, 'error' => '', 'key' => $this->key);
    }

    public function setLanguageId(string $str)
    {
        $this->languageId = $this->objDbConn->mysqlRealEscape($str);
    }

    public function sendEmail($templateId)
    {
        require_once __ROOT__ . '/assets/php/generalFunctions.php';
        require_once __ROOT__ . '/mail/model/modMailComposer.php';
        require_once __ROOT__ . '/mail/model/modMailQueue.php';

        $params = ['{key}' => $this->key, '{baseUrl}' => modGeneralFunction::baseUrl()];

        $objMail = new mailComposer($_MYSQLI_);
        $objMail->setId($templateId);
        $objMail->setUserId($this->userId);
        $objMail->setLanguageId($this->languageId);
        $objMail->setParameters($params);
        $mail = $objMail->select();
        if ($mail['result']) {
            $objMailerQueue = new mailQueue($_MYSQLI_);
            $objMailerQueue->set_Maileraccount($mail['mailaccount_id']);
            $objMailerQueue->set_Fromname("Reparaciones Zamora");
            $objMailerQueue->setTo($this->user, $this->first . " " . $this->last);
            //$objMailerQueue->setBCC("support@aratours.com", 'Support - ARA Tours'); // Comentado porque no se ha definido a que correo debe llegar
            $objMailerQueue->set_Subject($mail['subject']);
            $objMailerQueue->set_Body($mail['body']);
            $objMailerQueue->set_Altbody($mail['altbody']);
            $result = $objMailerQueue->insert();
        } else {
            $result = $mail;
        }

        return $result;
    }

    private function random_str($len = 64)
    {
        $len = ($len < 8) ? 8 : $len;
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charsLen = strlen($chars);
        $rndStr = '';
        for ($i = 0; $i < $len; $i++) {
            $rndStr .= $chars[random_int(0, $charsLen - 1)];
        }
        return $rndStr;
    }
}

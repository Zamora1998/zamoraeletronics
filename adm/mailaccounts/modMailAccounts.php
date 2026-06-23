<?
class mailAccounts
{
    protected $objDbConn;
    protected $id = 0;
    protected $salt = '';
    protected $key = '';
    protected $user = '';
    protected $password = '';
    protected $host = '';
    protected $smtpsecure = '';
    protected $protocol = '';
    protected $replyto = '';
    protected $protected = 0;
    protected $debug = 0;
    protected $auth = 0;
    protected $enabled = 0;
    protected $port = 0;
    protected $hasProtected;
    protected $oauth_type = 'password';
    protected $oauth = 0;
    protected $oauth_client_id = '';
    protected $oauth_client_secret = '';
    protected $oauth_refresh_token = '';
    protected $oauth_tenant_id = '';

    public function __construct(&$objDbConn = null)
    {
        require_once __ROOT__ . '/assets/php/libCrypt.php';
        $ini_array = parse_ini_file(__ROOT__ . "/.config.ini", true);
        $this->salt = $ini_array["general"]["key"];

        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
    }

    public function selectAll()
    {
        $sql = "SELECT
                    id,
                    protocol,
                    smtpauth,
                    port,
                    smtpsecure,
                    username,
                    password,
                    host,
                    debug,
                    oauth_type,
                    oauth_tenant_id,
                    oauth,
                    oauth_client_id,
                    oauth_client_secret,
                    oauth_refresh_token
                FROM mailaccounts;";

        return $this->objDbConn->processQuery($sql);
    }
    public function selectCompanies()
    {
        $sql = "SELECT
                    id,
                    Nombre AS name
                FROM companies_accounting;";

        return $this->objDbConn->processQuery($sql);
    }

    public function selectMailAccounts()
    {
        $sql = "SELECT
                    id,
                    protocol,
                    smtpauth,
                    port,
                    smtpsecure,
                    username,
                    `password`,
                    host,
                    debug,
                    protected,
                    `enabled`,
                    replyto,
                    oauth_type,
                    oauth_tenant_id,
                    oauth,
                    oauth_client_id,
                    oauth_client_secret,
                    oauth_refresh_token,
                    if(protected, if({$this->hasProtected}, 0, 1) , 0) AS disabled
        FROM mailaccounts";

        $result = $this->objDbConn->processQuery($sql);
        if ($result['data']) {
            foreach ($result['data'] as $key => &$rec) {
                $rec['password'] = trim($this->decrypt($rec['password']));
                if (!empty($rec['oauth_client_secret'])) {
                    $rec['oauth_client_secret'] = trim($this->decrypt($rec['oauth_client_secret']));
                }
                if (!empty($rec['oauth_refresh_token'])) {
                    $rec['oauth_refresh_token'] = trim($this->decrypt($rec['oauth_refresh_token']));
                }
            }
        }

        return $result;
    }

    public function selectAccount()
    {
        $sql = "SELECT
                    id,
                    protocol,
                    smtpauth,
                    port,
                    smtpsecure,
                    username,
                    `password`,
                    host,
                    debug,
                    protected,
                    `enabled`,
                    replyto,
                    oauth_type,
                    oauth_tenant_id,
                    oauth,
                    oauth_client_id,
                    oauth_client_secret,
                    oauth_refresh_token
        FROM mailaccounts Where id = '{$this->id}'";

        $result = $this->objDbConn->processQuery($sql);
        if ($result['data']) {
            foreach ($result['data'] as $key => &$rec) {
                $rec['password'] = trim($this->decrypt($rec['password']));
                if (!empty($rec['oauth_client_secret'])) {
                    $rec['oauth_client_secret'] = trim($this->decrypt($rec['oauth_client_secret']));
                }
                if (!empty($rec['oauth_refresh_token'])) {
                    $rec['oauth_refresh_token'] = trim($this->decrypt($rec['oauth_refresh_token']));
                }
            }
        }

        return $result;
    }

    public function insertAccountDetails()
    {
        $this->objDbConn->resetAI('mailaccounts');
        $encryptedPassword = $this->encrypt($this->password);
        $encryptedClientSecret = $this->oauth_client_secret ? $this->encrypt($this->oauth_client_secret) : '';
        $encryptedRefreshToken = $this->oauth_refresh_token ? $this->encrypt($this->oauth_refresh_token) : '';
        if ($this->id > 0) {
            return $this->updateAccountDetails();
        } else {
            $sql = "INSERT INTO mailaccounts (
                protocol,
                smtpauth,
                port,
                smtpsecure,
                username,
                `password`,
                host,
                debug,
                replyto,
                protected,
                `enabled`,
                oauth_type,
                oauth_tenant_id,
                oauth,
                oauth_client_id,
                oauth_client_secret,
                oauth_refresh_token)
                VALUES ('{$this->protocol}','{$this->auth}','{$this->port}','{$this->smtpsecure}','{$this->user}','{$encryptedPassword}',
                '{$this->host}','{$this->debug}','{$this->replyto}','{$this->protected}','{$this->enabled}',
                '{$this->oauth_type}','{$this->oauth_tenant_id}','{$this->oauth}','{$this->oauth_client_id}','{$encryptedClientSecret}','{$encryptedRefreshToken}')";

            return $this->objDbConn->processQuery($sql);
        }
    }

    public function updateAccountDetails()
    {
        $this->objDbConn->resetAI('mailaccounts');
        $encryptedPassword = $this->encrypt($this->password);
        $encryptedClientSecret = $this->oauth_client_secret ? $this->encrypt($this->oauth_client_secret) : '';
        $encryptedRefreshToken = $this->oauth_refresh_token ? $this->encrypt($this->oauth_refresh_token) : '';

        $sql = "UPDATE mailaccounts SET
                protocol = '{$this->protocol}',smtpauth = '{$this->auth}',
                port = '{$this->port}',smtpsecure = '{$this->smtpsecure}',
                username = '{$this->user}',`password` = '{$encryptedPassword}',
                host = '{$this->host}',debug = '{$this->debug}',replyto = '{$this->replyto}',
                protected = '{$this->protected}',`enabled` = '{$this->enabled}',
                oauth_type = '{$this->oauth_type}',
                oauth_tenant_id = '{$this->oauth_tenant_id}',
                oauth = '{$this->oauth}',
                oauth_client_id = '{$this->oauth_client_id}',
                oauth_client_secret = '{$encryptedClientSecret}',
                oauth_refresh_token = '{$encryptedRefreshToken}'
                WHERE id = {$this->id};";

        return $this->objDbConn->processQuery($sql);
    }

    public function deleteAccount()
    {
        $sql = "DELETE FROM mailaccounts
                WHERE id = {$this->id};\n";

        return $this->objDbConn->processQuery($sql);
    }

    public function setOauth(int $int)
    {
        $this->oauth = $int;
    }

    public function set_oauth_client_id(string $str)
    {
        $this->oauth_client_id = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function set_oauth_client_secret(string $str)
    {
        $this->oauth_client_secret = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function set_oauth_refresh_token(string $str)
    {
        $this->oauth_refresh_token = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function set_oauth_type(string $str)
    {
        $this->oauth_type = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function set_oauth_tenant_id(string $str)
    {
        $this->oauth_tenant_id = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function setUser(string $str)
    {
        $this->user = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function setPassword(string $str)
    {
        $this->password = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function setHost(string $str)
    {
        $this->host = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function setPort(int $port)
    {
        $this->port = $port;
    }

    public function setSMTP(string $str)
    {
        $this->smtpsecure = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function setProtocol(string $str)
    {
        $this->protocol = $this->objDbConn->mysqlRealEscape(trim($str));
    }
    public function setReplyto(string $str)
    {
        $this->replyto = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function setAuth(int $auth)
    {
        $this->auth = $auth;
    }

    public function setDebug(int $debug)
    {
        $this->debug = $debug;
    }

    public function setProtected(int $protected)
    {
        $this->protected = $protected;
    }

    public function setEnabled(int $enabled)
    {
        $this->enabled = $enabled;
    }

    public function setHasProtected(int $int)
    {
        $this->hasProtected = $int;
    }

    private function decrypt(string $str)
    {
        $objCrypt = new Crypt();
        return trim($objCrypt->decrypt($this->salt, $str));
    }

    private function encrypt(string $str)
    {
        $objCrypt = new Crypt();
        return trim($objCrypt->encrypt($this->salt, $str));
    }
}

<?
class GetToken {
    public $objDbConn;
    protected $objSettings;
    protected $urltoken = '';
    protected $clientid = '';
    protected $grandType = '';
    protected $username = '';
    protected $pass = '';

    public function __construct() {
        require_once __ROOT__ . '/assets/php/generalFunctions.php';

        $this->objSettings = new settings($_MYSQLI_);

        $settings = $this->objSettings->getSettings(['grant_type', 'client_id', 'password', 'username', 'urltoken']);

        $this->urltoken = $settings['urltoken'];
        $this->grandType = $settings['grant_type'];
        $this->clientid = $settings['client_id'];
        $this->pass = $settings['password'];
        $this->username = $settings['username'];
    }
    public function GenerateToken() {
        $data = [
            'grant_type' => $this->grandType,
            'client_id' => $this->clientid,
            'password' => $this->pass,
            'username' => $this->username
        ];
        $ch = curl_init();

        // Configurar la solicitud cURL
        curl_setopt($ch, CURLOPT_URL, $this->urltoken);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'CURL via PHP');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length' => strlen(http_build_query($data))
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Obtener el código de estado HTTP
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);  // Obtener el código de estado HTTP
        curl_close($ch);

        if (curl_errno($ch)) {
            return array('result' =>  false, 'error' => 'Error in cURL: ' . curl_error($ch), 'data' => '');
        }
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $body = substr($response, $headerSize);
        $responseData = json_decode($body, true);
        // Comprobar si el código HTTP es 200
        if ($httpCode === 200) {
            $token = $responseData['access_token'];
            return array('result' =>  true , 'error' => '', 'data' => $token);
        } else {
            $error = $responseData['error_description'];
            return array('result' =>  true, 'error' => $error , 'data' => '');
        }
    }
}

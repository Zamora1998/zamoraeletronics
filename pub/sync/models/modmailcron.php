
<?
class modRegistration
{
    protected $objDbConn;
    protected $id = 0;
    protected $date = '';
    protected $age = 0;
    protected $cedula = 0;
    protected $beneficiaryId = 0;
    protected $beneficiaryName = '';
    protected $email = '';
    protected $firstName = '';
    protected $gender = '';
    protected $idcard = 0;
    protected $lastName = '';
    protected $languageId = 'es';
    protected $phone = 0;
    protected $secondLastName = '';
    protected $shirtSize = '';
    protected $name = '';
    protected $description = '';
    protected $location = '';
    protected $latitude = 0;
    protected $longitude = 0;
    protected $distance_km = 0;
    protected $max_participants = 0;
    protected $start_datetime = '';
    protected $end_datetime = '';
    protected $registration_open = '';
    protected $registration_close = '';
    protected $status = '';
    protected $uid = '';
    protected $routeCoords;
    protected $salt = '';
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
    public function selectPendingParticipants()
    {
        $sql = "SELECT 
                    ep.id AS participant_id,
                    ep.name,
                    ep.last_name,
                    ep.second_last_name,
                    ep.idcard,
                    ep.age,
                    ep.poliza_beneficiary_name,
                    ep.poliza_beneficiary_id,
                    ep.email,
                    ep.phone,
                    ep.talla,
                    ep.genero,
                    ep.bib_number,
                    r.name AS route_name,
                    r.distance_km AS route_distance_km,
                    r.cost AS route_cost,
                    r.start_time,
                    e.name AS event_name,
                    e.start_datetime,
                    e.end_datetime,
                    e.location
                FROM events_participants ep
                LEFT JOIN events_routes r ON ep.route_uid = r.uid
                LEFT JOIN events e ON r.event_id = e.id
                WHERE ep.sentmail = 0
                ORDER BY e.start_datetime DESC, ep.name;";
        return $this->objDbConn->processQuery($sql);
    }

    public function sendEmail($participant)
    {
        require_once __ROOT__ . '/assets/php/generalFunctions.php';
        require_once __ROOT__ . '/mail/model/modMailComposer.php';
        require_once __ROOT__ . '/mail/model/modMailQueue.php';

        // Obtener plantilla
        $objMail = new mailComposer($_MYSQLI_);
        $objMail->setId(6); // ID de la plantilla
        $objMail->setUserId(5);
        $objMail->setLanguageId($this->languageId);
        $mail = $objMail->select();
        if ($mail['result']) {

            // Reemplazar variables del correo con los datos del participante
            $body = str_replace([
                '{participant_name}',
                '{participant_fullname}',
                '{idcard}',
                '{email}',
                '{talla}',
                '{route_name}',
                '{route_distance_km}',
                '{route_cost}',
                '{event_name}',
                '{start_time}',
                '{location}'
            ], [
                $participant['name'],
                $participant['name'] . ' ' . $participant['last_name'] . ' ' . $participant['second_last_name'],
                $participant['idcard'],
                $participant['email'],
                $participant['talla'],
                $participant['route_name'],
                $participant['route_distance_km'],
                $participant['route_cost'],
                $participant['event_name'],
                date('d/m/Y H:i', strtotime($participant['start_time'])),
                $participant['location']
            ], $mail['body']);

            $altbody = $mail['altbody'];

            // Configurar mail queue
            $objMailerQueue = new mailQueue($_MYSQLI_);
            $objMailerQueue->set_Maileraccount($mail['mailaccount_id']);
            $objMailerQueue->set_Fromname("RZamora");
            $objMailerQueue->setTo($participant['email'], $participant['name'] . " " . $participant['last_name']);
            $objMailerQueue->set_Subject($mail['subject']  . ' - ' . $participant['idcard']);
            $objMailerQueue->set_Body($body);
            $objMailerQueue->set_Altbody($altbody);

            $result = $objMailerQueue->insert();
        } else {
            $result = $mail;
        }

        return $result;
    }



    // 4️⃣ Marcar participante como enviado
    public function markAsSent()
    {
        $sql = "UPDATE events_participants SET sentmail = 1 WHERE id = {$this->id}";
        return $this->objDbConn->processQuery($sql);
    }
    public function setId(int $int)
    {
        $this->id = $int;
    }
    public function setDate(string $str)
    {
        $this->date = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function setDescription(string $str)
    {
        $this->description = $this->objDbConn->mysqlRealEscape(trim($str));
    }
    public function setUUID(string $str)
    {
        $this->uid = $this->objDbConn->mysqlRealEscape($str);
    }

    public function setAge(int $int)
    {
        $this->age = $int;
    }
    public function setCedula(int $int)
    {
        $this->cedula = $int;
    }
    public function setBeneficiaryId(int $int)
    {
        $this->beneficiaryId = $int;
    }

    public function setBeneficiaryName(string $beneficiaryName)
    {
        $this->beneficiaryName = $this->objDbConn->mysqlRealEscape(trim($beneficiaryName));
    }

    public function setEmail(string $email)
    {
        $this->email = $this->objDbConn->mysqlRealEscape(trim($email));
    }

    public function setFirstName(string $firstName)
    {
        $this->firstName = $this->objDbConn->mysqlRealEscape(trim($firstName));
    }

    public function setGender(string $gender)
    {
        $this->gender = $this->objDbConn->mysqlRealEscape(trim($gender));
    }

    public function setIdcard(int $int)
    {
        $this->idcard = $int;
    }

    public function setLastName(string $lastName)
    {
        $this->lastName = $this->objDbConn->mysqlRealEscape(trim($lastName));
    }

    public function setPhone(int $int)
    {
        $this->phone = $int;
    }

    public function setSecondLastName(string $secondLastName)
    {
        $this->secondLastName = $this->objDbConn->mysqlRealEscape(trim($secondLastName));
    }

    public function setShirtSize(string $shirtSize)
    {
        $this->shirtSize = $this->objDbConn->mysqlRealEscape(trim($shirtSize));
    }
    public function setName(string $name)
    {
        $this->name = $this->objDbConn->mysqlRealEscape(trim($name));
    }


    private function decrypt(string $str)
    {
        $objCrypt = new Crypt();

        return trim($objCrypt->decrypt($this->salt, $str));
    }
}

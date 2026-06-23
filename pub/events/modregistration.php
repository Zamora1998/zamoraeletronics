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

    public function selectEvents()
    {
        $sql = "SELECT 
            e.id,
            e.name AS event_name,
            e.description,
            e.start_datetime,
            e.end_datetime,
            e.location,
            e.distance_km,
            e.max_participants,
            e.registration_open,
            e.registration_close,
            e.imageone,
            e.imagetwo,
            r.uid AS route_uid,
            r.name AS route_name,
            r.cost,
            r.start_time,
            r.end_time,
            r.description AS route_description,
            r.distance_km AS route_distance_km,
            r.coordinates,
            (e.max_participants - IFNULL(ev.registered_total,0)) AS registered_count
        FROM events AS e
        LEFT JOIN events_routes r 
            ON e.id = r.event_id
        LEFT JOIN (
            SELECT r.event_id, COUNT(ep.id) AS registered_total
            FROM events_routes r
            LEFT JOIN events_participants ep ON ep.route_uid = r.uid
            GROUP BY r.event_id
        ) AS ev ON ev.event_id = e.id
        WHERE e.status = 'published'
        ORDER BY e.start_datetime DESC;";
        return $this->objDbConn->processQuery($sql);
    }

    public function select2Events()
    {
        $sql = "SELECT 
                    uid AS id,
                    name AS name
                FROM events
                WHERE events.status = 'published'";
        return $this->objDbConn->processQuery($sql);
    }

    public function selecteventroutes()
    {
        $sql = "SELECT 
            e.id,
            e.name AS event_name,
            r.id AS route_id,
            r.name AS route_name,
            r.description AS route_description,
            r.distance_km,
            r.coordinates,
            r.created_at AS route_created_at
        FROM events e
        LEFT JOIN events_routes r 
            ON e.id = r.event_id
        ORDER BY e.id, r.id";
        return $this->objDbConn->processQuery($sql);
    }

    public function createEntryCUC()
    {
        $datetimeCR = new DateTime("now", new DateTimeZone('America/Costa_Rica'));
        $registeredAt = $datetimeCR->format('Y-m-d H:i:s');

        $sql = "INSERT INTO events_participants (
                route_uid,
                name,
                last_name,
                second_last_name,
                idcard,
                age,
                poliza_beneficiary_name,
                poliza_beneficiary_id,
                email,
                phone,
                talla,
                genero,
                bib_number,
                ispay,
                sentmail,
                registered_at
            ) VALUES (
                '{$this->uid}',
                '{$this->firstName}',
                '{$this->lastName}',
                '{$this->secondLastName}',
                '{$this->idcard}',
                '{$this->age}',
                '{$this->beneficiaryName}',
                '{$this->beneficiaryId}',
                '{$this->email}',
                '{$this->phone}',
                '{$this->shirtSize}',
                '{$this->gender}',
                '2',
                0,
                0,
                '{$registeredAt}'
            )";

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

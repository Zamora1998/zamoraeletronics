<?
class modRegistrations
{
    protected $objDbConn;
    protected $id = 0;
    protected $statusPay = 0;
    protected $date = '';
    protected $dateformat = '';
    protected $company = '';
    protected $companyID = '';
    protected $elSchedule = '';
    protected $protected = 0;
    protected $price = 0;
    protected $hasProtected;
    protected $name = '';
    protected $description = '';
    protected $location = '';
    protected $route = '';
    protected $latitude = 0;
    protected $longitude = 0;
    protected $distance_km = 0;
    protected $max_participants = 0;
    protected $start_datetime = '';
    protected $end_datetime = '';
    protected $registration_open = '';
    protected $registration_close = '';
    protected $status = '';
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

    public function selectRegistrations()
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
            ep.ispay,
            ep.bib_number,
            r.name AS route_name,
            r.distance_km AS route_distance_km,
            r.cost AS route_cost,
            e.name AS event_name,
            e.start_datetime,
            e.end_datetime,
            e.location
        FROM events_participants ep
        LEFT JOIN events_routes r
            ON ep.route_uid = r.uid
        LEFT JOIN events e
            ON r.event_id = e.id
        ORDER BY e.start_datetime DESC, ep.name;";
        return $this->objDbConn->processQuery($sql);
    }

    public function updateState(){

    $sql = "UPDATE events_participants SET ispay = {$this->statusPay}
        WHERE id = {$this->id}";
        return $this->objDbConn->processQuery($sql);
    }
    public function DeleteRoute()
    {
        $sql = "DELETE FROM events_routes WHERE id = {$this->id}";

        return $this->objDbConn->processQuery($sql);
    }


    public function setId(int $int)
    {
        $this->id = $int;
    }
        public function setStatusPay(int $int)
    {
        $this->statusPay = $int;
    }

    public function setDate(string $str)
    {
        $this->date = $this->objDbConn->mysqlRealEscape(trim($str));
    }
    public function setRoute(string $str)
    {
        $this->route = $this->objDbConn->mysqlRealEscape(trim($str));
    }
    public function setDateFormat(string $str)
    {
        $this->dateformat = $this->objDbConn->mysqlRealEscape(trim($str));
    }
    public function setDescription(string $str)
    {
        $this->description = $this->objDbConn->mysqlRealEscape(trim($str));
    }
    public function setCompany(string $str)
    {
        $this->company = $str;
    }
    public function setCompanyID(int $int)
    {
        $this->companyID = $int;
    }
    public function SetSchedule(string $str)
    {
        $this->elSchedule = $this->objDbConn->mysqlRealEscape(trim($str));
    }
    public function setProtected(int $protected)
    {
        $this->protected = $protected;
    }
    public function setHasProtected(int $int)
    {
        $this->hasProtected = $int;
    }

    public function setName(string $name)
    {
        $this->name = $this->objDbConn->mysqlRealEscape(trim($name));
    }

    public function setLocation(string $location)
    {
        $this->location = $this->objDbConn->mysqlRealEscape(trim($location));
    }

    public function setLatitude(float $latitude)
    {
        $this->latitude = $latitude;
    }

    public function setLongitude(float $longitude)
    {
        $this->longitude = $longitude;
    }

    public function setDistanceKm(float $distance_km)
    {
        $this->distance_km = $distance_km;
    }
    public function setPrice(float $value)
    {
        $this->price = $value;
    }

    public function setMaxParticipants(int $max_participants)
    {
        $this->max_participants = $max_participants;
    }

    public function setStartDatetime(string $start_datetime)
    {
        $this->start_datetime = $this->objDbConn->mysqlRealEscape(trim($start_datetime));
    }

    public function setEndDatetime(string $end_datetime)
    {
        $this->end_datetime = $this->objDbConn->mysqlRealEscape(trim($end_datetime));
    }

    public function setRegistrationOpen(string $registration_open)
    {
        $this->registration_open = $this->objDbConn->mysqlRealEscape(trim($registration_open));
    }

    public function setRegistrationClose(string $registration_close)
    {
        $this->registration_close = $this->objDbConn->mysqlRealEscape(trim($registration_close));
    }

    public function setStatus(string $status)
    {
        $this->status = $this->objDbConn->mysqlRealEscape(trim($status));
    }

    private function decrypt(string $str)
    {
        $objCrypt = new Crypt();

        return trim($objCrypt->decrypt($this->salt, $str));
    }
}

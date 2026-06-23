<?
class modevents
{
    protected $objDbConn;
    protected $id = 0;
    protected $date = '';
    protected $dateformat = '';
    protected $company = '';
    protected $companyID = '';
    protected $elSchedule = '';
    protected $userId = 0;
    protected $protected = 0;
    protected $price = 0;
    protected $eventId = 0;
    protected $hasProtected;
    protected $name = '';
    protected $description = '';
    protected $location = '';
    protected $startime = '';
    protected $endtime = '';
    protected $route = '';
    protected $imageone = '';
    protected $imagetwo = '';
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
    protected $event_type_id = 0;
    protected $salt = '';
    public function __construct(&$objDbConn = null)
    {
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
                    e.uid,
                    e.name AS event_name,
                    e.description,
                    e.start_datetime,
                    e.end_datetime,
                    e.location,
                    e.distance_km,
                    e.max_participants,
                    e.registration_open,
                    e.registration_close,
                    CASE e.status
                        WHEN 'draft' THEN 'Borrador'
                        WHEN 'published' THEN 'Publicado'
                        WHEN 'closed' THEN 'Cerrado'
                        WHEN 'initiated' THEN 'Iniciado'
                    END AS status_description,
                    e.created_at,
                    e.updated_at,
                    et.id AS event_type_id,
                    et.name AS event_type_name
                FROM events AS e
                LEFT JOIN event_types et
                    ON e.event_type_id = et.id
                ORDER BY e.start_datetime DESC;";
        return $this->objDbConn->processQuery($sql);
    }

    public function selectDefault()
    {
        $sql = "SELECT 
                        e.id,
                        e.uid,
                        e.name AS event_name,
                        e.description,
                        e.start_datetime,
                        e.end_datetime,
                        e.location,
                        e.distance_km,
                        e.max_participants,
                        e.registration_open,
                        e.registration_close,
                        CASE e.status
                            WHEN 'draft' THEN 'Borrador'
                            WHEN 'published' THEN 'Publicado'
                            WHEN 'closed' THEN 'Cerrado'
                        END AS status_description,
                        e.created_at,
                        e.updated_at
                    FROM events AS e
                    ORDER BY e.id DESC
                    LIMIT 1;";

        return $this->objDbConn->processQuery($sql);
    }


    public function selectEvent()
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
                    e.latitude,
                    e.longitude,
                    e.registration_close,
                    e.status AS status_description,
                    e.created_at,
                    e.updated_at,
                    et.id AS event_type_id,
                    et.name AS event_type_name
                FROM events AS e
                LEFT JOIN event_types et
                    ON e.event_type_id = et.id
                WHERE e.id = {$this->id}
                ORDER BY e.start_datetime DESC;";
        return $this->objDbConn->processQuery($sql);
    }

    public function selectEvenRoutes()
    {
        $sql = "SELECT 
            e.id,
            e.name AS event_name,
            r.id AS route_id,
            r.name AS route_name,
            r.distance_km AS route_distance_km,
            r.coordinates
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
        AND e.id = {$this->id}
        ORDER BY e.start_datetime DESC;";
        return $this->objDbConn->processQuery($sql);
    }

    public function select2Events()
    {
        $sql = "SELECT 
                    id,
                    name AS name
                FROM events";
        return $this->objDbConn->processQuery($sql);
    }

    public function select2EventTypes()
    {
        $sql = "SELECT 
                    id,
                    name
                FROM event_types
                ORDER BY name";
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
                r.cost,
                r.distance_km,
                r.coordinates,
                r.created_at AS route_created_at
            FROM events e
            INNER JOIN events_routes r 
                ON e.id = r.event_id
            WHERE e.id = {$this->eventId}
            ORDER BY e.id, r.id";
        return $this->objDbConn->processQuery($sql);
    }

    public function updateEvents()
    {
        // Prepare event_type SQL fragment (use NULL when not set or zero)
        $eventTypeSQLUpdate = is_numeric($this->event_type_id) && intval($this->event_type_id) > 0
            ? "event_type_id = " . intval($this->event_type_id)
            : "event_type_id = NULL";

        $eventTypeSQLInsert = is_numeric($this->event_type_id) && intval($this->event_type_id) > 0
            ? intval($this->event_type_id)
            : "NULL";

        if (!empty($this->id) && $this->id > 0) {
            // --- UPDATE ---
            $sql = "UPDATE events SET
            name = '{$this->name}',
            description = '{$this->description}',
            start_datetime = '{$this->start_datetime}',
            end_datetime = '{$this->end_datetime}',
            location = '{$this->location}',
            latitude = {$this->latitude},
            longitude = {$this->longitude},
            distance_km = {$this->distance_km},
            max_participants = {$this->max_participants},
            registration_open = '{$this->registration_open}',
            registration_close = '{$this->registration_close}',
            status = '{$this->status}',\n            " . $eventTypeSQLUpdate . "\n        WHERE id = {$this->id}";
        } else {
            $sql = "INSERT INTO events (
            name,
            description,
            start_datetime,
            end_datetime,
            location,
            latitude,
            longitude,
            distance_km,
            max_participants,
            registration_open,
            registration_close,
            status,
            event_type_id
        ) VALUES (
            '{$this->name}',
            '{$this->description}',
            '{$this->start_datetime}',
            '{$this->end_datetime}',
            '{$this->location}',
            {$this->latitude},
            {$this->longitude},
            {$this->distance_km},
            {$this->max_participants},
            '{$this->registration_open}',
            '{$this->registration_close}',
            '{$this->status}',
            " . $eventTypeSQLInsert . "
        )";
        }

        return $this->objDbConn->processQuery($sql);
    }


    public function updateImages()
    {

        // --- UPDATE ---
        $sql = "UPDATE events SET
            imageone = '{$this->imageone}',
            imagetwo = '{$this->imagetwo}'

        WHERE id = {$this->id}";

        return $this->objDbConn->processQuery($sql);
    }
    public function updateRoute()
    {
        $jsonCoords = is_array($this->routeCoords)
            ? json_encode($this->routeCoords)
            : $this->routeCoords;

        $sql = "INSERT INTO events_routes (
                event_id,
                name,
                description,
                start_time,
                end_time,
                distance_km,
                cost,
                coordinates
            ) VALUES (
                {$this->id},
                '{$this->name}',
                '{$this->description}',
                '{$this->startime}',
                '{$this->endtime}',
                {$this->distance_km},
                {$this->price},
                '$jsonCoords'
            )";

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
    public function setImageOne(string $str)
    {
        $this->imageone = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function setImageTwo(string $str)
    {
        $this->imagetwo = $this->objDbConn->mysqlRealEscape(trim($str));
    }
    public function setStartTime(string $str)
    {
        $this->startime = $this->objDbConn->mysqlRealEscape(trim($str));
    }
    public function setEndTime(string $str)
    {
        $this->endtime = $this->objDbConn->mysqlRealEscape(trim($str));
    }
    public function setName(string $name)
    {
        $this->name = $this->objDbConn->mysqlRealEscape(trim($name));
    }
    public function setRouteCoords(array|string $coords)
    {
        // Si es string, decodificarlo
        if (is_string($coords)) {
            $coords = json_decode(trim($coords), true);
        }

        if (!is_array($coords)) {
            throw new Exception("Coordenadas inválidas");
        }

        $cleanCoords = [];

        foreach ($coords as $point) {
            if (is_array($point) && count($point) === 2) {
                $lat = $this->objDbConn->mysqlRealEscape(trim($point[0]));
                $lng = $this->objDbConn->mysqlRealEscape(trim($point[1]));
                $cleanCoords[] = [$lat, $lng];
            }
        }

        $this->routeCoords = json_encode($cleanCoords);
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
    public function setEventTypeId(int $event_type_id)
    {
        $this->event_type_id = $event_type_id;
    }
    public function setUserId(int $id)
    {
        $this->userId = $id;
    }


    private function decrypt(string $str)
    {
        $objCrypt = new Crypt();

        return trim($objCrypt->decrypt($this->salt, $str));
    }
    public function setEventId(int $id)
    {
        $this->eventId = $id;
    }

    public function saveRegistrationConfig(int $eventId, array $config)
    {
        // First delete existing config for this event
        $sqlDelete = "DELETE FROM events_registrations WHERE event_id = {$eventId}";
        $this->objDbConn->processQuery($sqlDelete);

        // Insert new config
        if (empty($config)) {
            return ['result' => true];
        }

        $values = [];
        foreach ($config as $field) {
            $fieldName = $this->objDbConn->mysqlRealEscape(
                $field['field_name'] ?? $field['field-name'] ?? ''
            );
            $isEnabled = isset($field['is_enabled']) && $field['is_enabled'] ? 1 : 0;
            $isRequired = isset($field['is_required']) && $field['is_required'] ? 1 : 0;
            $label = isset($field['label']) ? "'" . $this->objDbConn->mysqlRealEscape($field['label']) . "'" : "NULL";

            $values[] = "({$eventId}, '{$fieldName}', {$isEnabled}, {$isRequired}, {$label})";
        }

        $sqlInsert = "INSERT INTO events_registrations (event_id, field_name, is_enabled, is_required, label) VALUES " . implode(',', $values);
        return $this->objDbConn->processQuery($sqlInsert);
    }

    public function getRegistrationConfig(int $eventId)
    {
        $sql = "SELECT field_name, is_enabled, is_required, label FROM events_registrations WHERE event_id = {$eventId}";
        return $this->objDbConn->processQuery($sql);
    }
}

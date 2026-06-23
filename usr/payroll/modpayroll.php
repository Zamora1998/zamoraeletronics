<?
class payroll {
    protected $objDbConn;
    protected $id = 0;
    protected $date = '';
    protected $dateformat = '';
    protected $company = '';
    protected $companyID = ''; 
    protected $elSchedule = '';
    protected $status = 0;
    protected $protected = 0;
    protected $hasProtected;
    protected $params = [];

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


    public function selectTemplateCompany() {
        $sql = "SELECT
                ca.Nombre AS text,
                cmt.mail_template_id AS id,
                ca.id as Companyid
                FROM
                company_mail_template cmt
                LEFT JOIN
                companies_accounting ca ON cmt.company_id = ca.id
                LEFT JOIN
                mailtemplates mt ON cmt.mail_template_id = mt.id;";
        return $this->objDbConn->processQuery($sql);
    }
    
    public function selectPaymentsMonths (){
        $sql = "SELECT 
                c.nombre,
                c.cedula,
                c.codigo,
                c.correo_electronico,
                c.cuenta_bancaria,
                p.salario_mensual,
                p.salario_laborado,
                p.dias_laborados,
                p.comisiones,
                p.incapacidades,
                p.ccss,
                p.impuesto_renta,
                p.fecha_pago
            FROM 
                collaborators c
            LEFT JOIN 
                payments p ON c.cedula = p.cedula AND c.id_company = p.id_company
            WHERE 
                c.id_company = {$this->companyID} AND
                p.fecha_pago = '{$this->dateformat}';";

        return $this->objDbConn->processQuery($sql);
    }

    public function SelectMailaccount() {
        $sql = "SELECT 
                    ma.protocol,
                    ma.smtpauth,
                    ma.port,
                    ma.smtpsecure,
                    ma.username,
                    ma.password,
                    ma.host,
                    ma.debug,
                    ma.protected,
                    ma.enabled,
                    ma.replyto
                FROM mailtemplates mt
                JOIN mailaccounts ma ON mt.mailaccount_id = ma.id
                WHERE mt.id = {$this->id}";

        $result = $this->objDbConn->applyQuery($sql);
        $data = [];
        $error = '';

        if (!$result) {
            $error = $this->objDbConn->getError();
        } else {
            $rows = $this->objDbConn->getDataQuery($result);
            foreach ($rows as $rec) {
                // Siempre desencripta la contraseña
                $rec['password'] = $this->decrypt($rec['password']);
                $data[] = $rec;
            }
        }

        return array('result' => ($result ? true : false), 'error' => $error, 'data' => $data);
    }
    public function insertColaboratorData() {
        $errors = [];
        $results = [];

        $planilla = $this->params;
        $companyId = (int)$this->companyID;
        $fechaPago = $this->dateformat;
        $db = $this->objDbConn;

        if (!$companyId || !$fechaPago) {
            return [
                'result' => false,
                'errors' => ['Company ID o fecha de pago no definidos'],
                'results' => []
            ];
        }

        foreach ($planilla as $cedula => $datos) {
            $cedula = (int)$cedula;
            $codigo = $db->mysqlRealEscape($datos['Codigo']);
            $nombre = $db->mysqlRealEscape(urldecode($datos['NombreEmpleado']));
            $correo = $db->mysqlRealEscape($datos['Correo']);
            $cuenta = $db->mysqlRealEscape($datos['CuentaBancaria']);

            $salarioMensual  = (float)$datos['SalarioMensual'];
            $salarioLaborado = (float)$datos['SalarioLaborado'];
            $diasLaborados   = (int)$datos['DiasLaborados'];
            $comisiones      = (float)$datos['Comisiones'];
            $incapacidades   = (float)$datos['Incapacidades'];
            $ccss            = (float)$datos['CCSSDeduccion'];
            $impuestoRenta   = (float)$datos['DeduccionRenta'];

            // Verificar si existe el colaborador
            $sql = "SELECT COUNT(*) AS total FROM collaborators WHERE cedula = $cedula";
            $result = $db->applyQuery($sql);

            if (!$result) {
                $errors[] = "Error en SELECT de colaborador: $sql";
                $results[] = false;
                continue;
            }

            $row = mysqli_fetch_assoc($result);
            $exists = (int)($row['total'] ?? 0);

            if (!$exists) {
                $sqlInsert = "
                    INSERT INTO collaborators 
                    (id_company, codigo, nombre, correo_electronico, cedula, cuenta_bancaria)
                    VALUES ($companyId, '$codigo', '$nombre', '$correo', $cedula, '$cuenta')
                ";
                $resInsert = $db->applyQuery($sqlInsert);
                if (!$resInsert) {
                    $errors[] = "Error insertando colaborador: $sqlInsert";
                    $results[] = false;
                    continue;
                } else {
                    $results[] = true;
                }
            } else {
                // Ya existe, pero se considera que no falló
                $results[] = true;
            }

            // Verificar si ya hay un pago
            $sqlPago = "
                SELECT COUNT(*) AS total FROM payments 
                WHERE cedula = $cedula AND fecha_pago = '$fechaPago' AND id_company = $companyId
            ";
            $resultPago = $db->applyQuery($sqlPago);

            if (!$resultPago) {
                $errors[] = "Error en SELECT de pago: $sqlPago";
                $results[] = false;
                continue;
            }

            $rowPago = mysqli_fetch_assoc($resultPago);
            $pagoExiste = (int)($rowPago['total'] ?? 0);

            if (!$pagoExiste) {
                $sqlInsertPago = "
                    INSERT INTO payments 
                    (id_company, cedula, salario_mensual, salario_laborado, dias_laborados, comisiones, incapacidades, ccss, impuesto_renta, fecha_pago)
                    VALUES (
                        $companyId,
                        $cedula,
                        $salarioMensual,
                        $salarioLaborado,
                        $diasLaborados,
                        $comisiones,
                        $incapacidades,
                        $ccss,
                        $impuestoRenta,
                        '$fechaPago'
                    )
                ";
                $resPago = $db->applyQuery($sqlInsertPago);
                if (!$resPago) {
                    $errors[] = "Error insertando pago: $sqlInsertPago";
                    $results[] = false;
                } else {
                    $results[] = true;
                }
            } else {
                // Ya existe el pago, no es error
                $results[] = true;
            }
        }

        return [
            'result' => !in_array(false, $results, true),
            'errors' => $errors,
            'results' => $results
        ];
    }



    public function setId(int $int) {
        $this->id = $int;
    }
    public function setDate(string $str) {
        $this->date = $this->objDbConn->mysqlRealEscape(trim($str));
    }
    public function setDateFormat(string $str) {
        $this->dateformat = $this->objDbConn->mysqlRealEscape(trim($str));
    }
    public function setCompany(string $str) {
        $this->company = $str;
    }
    public function setCompanyID(int $int) {
        $this->companyID = $int;
    }
    public function SetSchedule(string $str) {
        $this->elSchedule = $this->objDbConn->mysqlRealEscape(trim($str));
    }
    public function setStatus(int $status) {
        $this->status = $status;
    }
    public function setProtected(int $protected) {
        $this->protected = $protected;
    }
    public function setHasProtected(int $int) {
        $this->hasProtected = $int;
    }
    private function decrypt(string $str) {
        $objCrypt = new Crypt();

        return trim($objCrypt->decrypt($this->salt, $str));
    }
    public function setParams(array $arr) {
        $this->params = $arr;
    }
}


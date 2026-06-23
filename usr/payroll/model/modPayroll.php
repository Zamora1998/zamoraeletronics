<?php
require_once __ROOT__ . '/model/cte/cteLabels.php';

class modPayroll
{
    #region general
    protected $objDbConn;
    protected $id = 0;
    protected $date = '';
    protected $dateformat = ''; 
    protected $dateformatdata = '';
    protected $company = '';
    protected $companyID = '';
    protected $elSchedule = '';
    protected $status = 0;
    protected $protected = 0;
    protected $hasProtected;
    protected $params = [];


    public function __construct(&$objDbConn = null)
    {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
    }

    #endregion
    #region selects
    public function selectMailTemplates($companyId)
    {

        $sql = "SELECT 
                group_concat(id) as mtpid,
                group_concat(name) as mtpname,
                subject_label as subject,
                mailaccount_id as mailaccountid
            FROM mailtemplates
            WHERE idcompany = $companyId
            ORDER BY id";

        return $this->objDbConn->processQuery($sql);
    }
    public function selectPaymentsMonths()
    {
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
                payroll_collaborators c
            LEFT JOIN 
                payroll_payments p ON c.cedula = p.cedula AND c.id_company = p.id_company
            WHERE 
                c.id_company = {$this->companyID} AND
                p.fecha_pago >= '{$this->dateformat}';";

        return $this->objDbConn->processQuery($sql,);
    }
    public function selecreplytos()
    {

        $sql = "SELECT 
                id,
                username as text,
                username as name
            FROM mailaccounts
            Order by id";

        return $this->objDbConn->processQuery($sql);
    }
    #endregion
    #region insert
    public function insert()
    {

        $sql = '';

        return $this->objDbConn->processQuery($sql);
    }

    public function insertColaboratorData()
    {
        $errors = [];
        $results = [];

        $planilla = $this->params;
        $companyId = (int)$this->companyID;
        $fechaPago = $this->dateformatdata;
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
            $cuenta = $db->mysqlRealEscape($datos['Cuentabancaria']);

            $salarioMensual  = (float)$datos['SalarioMensual'];
            $salarioLaborado = (float)$datos['SalarioLaborado'];
            $diasLaborados   = (int)$datos['DiasLab'];
            $comisiones      = (float)$datos['Comisiones'];
            $incapacidades   = (float)$datos['Incapacidades'];
            $ccss            = (float)$datos['CCSSDeduccion'];
            $impuestoRenta   = (float)$datos['DeduccionImpRenta'];

            // Verificar si existe el colaborador
            $sql = "SELECT COUNT(*) AS total FROM payroll_collaborators WHERE cedula = $cedula";
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
                    INSERT INTO payroll_collaborators 
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
                SELECT COUNT(*) AS total FROM payroll_payments 
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
                    INSERT INTO payroll_payments 
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
    #endregion
    #region update
    public function update()
    {
        $sql = '';

        return $this->objDbConn->processQuery($sql);
    }
    #endregion
    #region deletes
    public function delete()
    {
        $sql = '';

        return $this->objDbConn->processQuery($sql);
    }
    #endregion
    #region setters
    public function setId(int $int)
    {
        $this->id = $int;
    }

    public function setDate(string $str)
    {
        $this->date = $this->objDbConn->mysqlRealEscape(trim($str));
    }
    public function setDateFormat(string $str)
    {
        $this->dateformat = $this->objDbConn->mysqlRealEscape(trim($str));
    }
        public function setDateFormatData(string $str)
    {
        $this->dateformatdata = $this->objDbConn->mysqlRealEscape(trim($str));
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
    public function setStatus(int $status)
    {
        $this->status = $status;
    }
    public function setProtected(int $protected)
    {
        $this->protected = $protected;
    }
    public function setHasProtected(int $int)
    {
        $this->hasProtected = $int;
    }
    public function setParams(array $arr)
    {
        $this->params = $arr;
    }
}

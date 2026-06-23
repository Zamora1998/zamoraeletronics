<?
class Companies
{
    #region globals
    protected $objDbConnCU = null;
    protected $objDbConn;
    protected $chrLocale = 'en_US';
    protected $id = 0;
    protected $name = '';
    protected $userId = 0;

    protected $type_company = '';
    protected $direcction = '';
    protected $email = '';
    protected $web = '';
    protected $date = '';
    protected $state = 0;
    protected $legal_registration = 0;
    protected $phone = 0;
    protected $templateid = 0;
    protected $companyid = 0;
    protected $templateidnew = 0;
    protected $companyidnew = 0;
    protected $allmonths = 0;
    protected $image = '';
    protected $params = [];
    protected $sqlsrv_CU = '';

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

    public function selectAll()
    {

        $sql = "SELECT
                id,
                Nombre,
                CedulaJuridica,
                TipoSociedad,
                Fecha_Ingreso,
                Estado,
                Direccion,
                Telefono,
                image,
                CorreoElectronico AS Correo,
                SitioWeb
        FROM
                companies_accounting";
        return $this->objDbConn->processQuery($sql);
    }
    public function selectCompanies()
    {
        $sql = "SELECT
                id,
                Nombre AS event_name,
                CedulaJuridica,
                TipoSociedad,
                Fecha_Ingreso,
                Estado,
                Direccion,
                Telefono,
                CorreoElectronico AS Correo,
                SitioWeb
        FROM
                companies_accounting
            WHERE Estado = 1";
        return $this->objDbConn->processQuery($sql);
    }
    public function selectDefault()
    {

        $sql = "SELECT
                id,
                Nombre AS event_name,
                CedulaJuridica,
                TipoSociedad,
                Fecha_Ingreso,
                Estado,
                Direccion,
                Telefono,
                CorreoElectronico AS Correo,
                SitioWeb,
                image
            FROM
                companies_accounting
            WHERE Estado = 1
            ORDER BY id DESC
                LIMIT 1";

        return $this->objDbConn->processQuery($sql);
    }
    public function selectCompany()
    {

        $sql = "SELECT
                id,
                Nombre AS event_name,
                CedulaJuridica,
                TipoSociedad,
                Fecha_Ingreso,
                Estado,
                Direccion,
                Telefono,
                CorreoElectronico AS Correo,
                SitioWeb,
                image
        FROM
                companies_accounting
                WHERE id = {$this->id}";

        return $this->objDbConn->processQuery($sql);
    }

    public function DeleteCompany()
    {
        $sql = "DElETE
                FROM companies_accounting
                WHERE id = {$this->id}";

        return $this->objDbConn->processQuery($sql);
    }

    public function InsertCompany()
    {
        $this->objDbConn->resetAI('companies_accounting');

        $sql = "INSERT INTO companies_accounting (
                    Nombre, 
                    CedulaJuridica, 
                    TipoSociedad,
                    Fecha_Ingreso,
                    Estado, 
                    Direccion,
                    Telefono,
                    CorreoElectronico, 
                    SitioWeb,
                    image
                    )
                VALUES (
                    '{$this->name}',
                    '{$this->legal_registration}',
                    '{$this->type_company}',
                    '{$this->date}',
                    '{$this->state}',
                    '{$this->direcction}',
                    '{$this->phone}',
                    '{$this->email}',
                    '{$this->web}',
                    '{$this->image}');";

        return $this->objDbConn->processQuery($sql);
    }

    public function UpdateCompany()
    {
        $sql = "UPDATE companies_accounting SET
                Nombre = '{$this->name}',
                CedulaJuridica = '{$this->legal_registration}',
                TipoSociedad = '{$this->type_company}',
                Fecha_Ingreso = '{$this->date}',
                Estado = {$this->state},
                Direccion = '{$this->direcction}',
                Telefono = '{$this->phone}',
                CorreoElectronico = '{$this->email}',
                SitioWeb = '{$this->web}',
                image = '{$this->image}'
            WHERE id = {$this->id};";

        return $this->objDbConn->processQuery($sql);
    }

    public function selectInfoTemplate()
    {
        $sql = "SELECT
				ca.id as idCompany,
                ca.Nombre AS NameCompany,
                cmt.mail_template_id AS CompanyTemplateid,
                mt.name as CompanyTemplateName
                FROM
                company_mail_template cmt
                LEFT JOIN
                companies_accounting ca ON cmt.company_id = ca.id
                LEFT JOIN
                mailtemplates mt ON cmt.mail_template_id = mt.id;";
        return $this->objDbConn->processQuery($sql);
    }


    public function selectCompanyName()
    {

        $sql = "SELECT
            id,
            Nombre as text
    FROM
            companies_accounting WHERE Estado = 1;";

        return $this->objDbConn->processQuery($sql);
    }

    public function SelectMailTemplateName()
    {

        $sql = "SELECT
            id,
            name as text
        FROM
            mailtemplates;";

        return $this->objDbConn->processQuery($sql);
    }

    public function insertTemCompany()
    {

        $sql = "INSERT INTO company_mail_template (company_id, mail_template_id) 
                VALUES ({$this->companyid}, {$this->templateid})";

        $result = $this->objDbConn->processQuery($sql);

        // Si hubo error
        if (!$result['result']) {
            if (isset($result['error']) && strpos($result['error'], 'Duplicate entry') !== false) {
                return ["result" => false, "error" => 1062];
            }
            return ["result" => false, "error" => $result['error']];
        }
        return ["result" => true];
    }

    public function updateCompanyTemplate()
    {
        $sql = "UPDATE company_mail_template 
                SET company_id = {$this->companyidnew}, mail_template_id = {$this->templateidnew}
                WHERE company_id = {$this->companyid} AND mail_template_id = {$this->templateid}";

        $result = $this->objDbConn->processQuery($sql);
        if (!$result['result']) {
            if (isset($result['error']) && strpos($result['error'], 'Duplicate entry') !== false) {
                return ["result" => false, "error" => 1062];
            }
            return ["result" => false, "error" => $result['error']];
        }

        return ["result" => true];
    }

    public function deleteTemCompany()
    {
        $sql = "DELETE FROM company_mail_template 
                WHERE company_id = {$this->companyid} AND mail_template_id = {$this->templateid}";
        return  $this->objDbConn->processQuery($sql);
    }

    public function setId(int $int)
    {
        $this->id = $int;
    }
    public function setTemplateID(int $int)
    {
        $this->templateid = $int;
    }
    public function setCompanyID(int $int)
    {
        $this->companyid = $int;
    }
    public function setTemplateIDNew(int $int)
    {
        $this->templateidnew = $int;
    }
    public function setCompanyIDNew(int $int)
    {
        $this->companyidnew = $int;
    }
    public function SetName(string $str)
    {
        $this->name = $str;
    }
    public function SetLegal_Registration(int $int)
    {
        $this->legal_registration = $int;
    }
    public function SetType_Company(string $str)
    {
        $this->type_company = $str;
    }
    public function SetDirecction(string $str)
    {
        $this->direcction = $str;
    }
    public function SetPhone(int $int)
    {
        $this->phone = $int;
    }
    public function SetEmail(string $str)
    {
        $this->email = $str;
    }
    public function SetWeb(string $str)
    {
        $this->web = $str;
    }
    public function SetImage(string $str)
    {
        $this->image = $str;
    }
    public function SetDate(string $str)
    {
        $this->date = $str;
    }
    public function setState(int $int)
    {
        $this->state = $int;
    }
    public function setUserId(int $id)
    {
        $this->userId = $id;
    }
}

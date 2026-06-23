<?php
require_once __ROOT__ . '/model/cte/cteLabels.php';

class RecordLogs
{
    use globalCte;

    protected $results = [];
    protected $estadoHacienda  = '';
    protected $claveEmisor  = '';
    protected $objDbConnmysql = null;
    protected $objDbConn;
    protected $chrLocale = 'en_US';
    protected $id = 0;
    protected $indicator = 0;
    protected $year = 0;
    protected $month = 0;
    protected $languageId = '';
    protected $supplierId = '';
    protected $startDate = '';
    protected $crm = '';
    protected $params = [];
    protected $sqlsrv_TP = '';
    protected $sqlsrv_PA = '';
    protected $sqlsrv_PF = '';
    protected $sqlsrv_CU = '';
    protected $months = 0;


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
    public function DeleteLogs()
    {
        if ($this->months <= 0) {
            return ['result' => false, 'error' => 'Invalid month interval.'];
        }

        // Eliminamos los logs cuya fecha de creación sea anterior a NOW() menos X meses
        $sql = "DELETE FROM sys__changelogs WHERE created_at < DATE_SUB(NOW(), INTERVAL {$this->months} MONTH);";

        return $this->objDbConn->processQuery($sql);
    }

    #endregion
    #region inserts


    #endregion
    #region deletes

    #endregion
    #region setters


    public function setMonths(int $num)
    {
        $this->months = (int)$num;
    }
}

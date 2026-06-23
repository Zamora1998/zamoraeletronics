<?
class RecordStates {
    public $objDbConn;
    protected $results = [];
    protected $estadoHacienda  = '';
    protected $claveEmisor  = '';
    public function __construct() {
        require_once __ROOT__ . '/assets/php/libDbConnSqlSrv.php';
        $this->objDbConn = new dbConnSqlSrv('FE');
    }
    public function selectStates() {

        $sql = "SELECT DISTINCT
                    id,
                    Clave_Emisor,
                    estado,
                    estado_hacienda
                FROM Acceptances
                WHERE YEAR(Acceptances.fecha) = YEAR(GETDATE())
                    AND Acceptances.estado is null 
                    AND Acceptances.mensaje = 'Procesado';";

        return $this->objDbConn->processQuery($sql);
    }

    public function updateStates(array $recordsUpdate) {
        $this->results = [];

        if (empty($recordsUpdate)) {
            return array('result' => true, 'error' => '', 'data' => $this->results);
        }

        foreach ($recordsUpdate as $record) {
            $claveEmisor = $this->setClaveEmisor($record['clave']);
            $estadoHacienda = $this->setEstadoHacienda($record['estado']);

            $sql = "UPDATE Acceptances
                SET estado = ?, estado_hacienda = ?
                WHERE Clave_Emisor = ?";

            $sql = $this->objDbConn->applyQuery($sql, [
                '202',
                'aceptado',
                $claveEmisor
            ]);

            $errorMessage = $sql['result'] ? '' : $sql['error'];

            $this->results[] = [
                'clave' => $claveEmisor,
                'estado' => $estadoHacienda,
                'success' => $sql['result'],
                'message' => $errorMessage
            ];
        }
        return array('result' => true,'error' => $errorMessage,'data' => $this->results );
    }

    public function setClaveEmisor(string $str) {
        $this->claveEmisor = $this->objDbConn->sqlRealEscape($str);
        return $this->claveEmisor;  
    }

    public function setEstadoHacienda(string $str) {
        $this->estadoHacienda = $this->objDbConn->sqlRealEscape($str);
        return $this->estadoHacienda;
    }

}

<?php
class GeneratorModel
{
    protected $objDbConn;
    private $moduleName = '';
    private $basePath = '';
    private $ctrlFile = '';
    protected $id = 0;
    private $viewFile = '';
    private $viewFileName = '';
    private $options = [];

    public function __construct(&$objDbConn = null)
    {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
    }

    public function insertRoutesAccess()
    {
        $file = $this->viewFile;
        $name = 'nav' . ucfirst($this->moduleName);
        $url = '/' . $this->moduleName;

        $sql = "INSERT INTO routes 
        (label_name, file, ispublic, isalluser, routecategory_id, method, url, icon, type)
        VALUES 
        ('$name', '$file', 0, 0, 0, 'get', '$url', '', 1)";

        $return = $this->objDbConn->processQuery($sql);
        if ($return['result']) {
            $navId = $this->objDbConn->getLastId();
        }
        $sqlt = "INSERT INTO route_accesses (route_id, access_id, param, required)
        VALUES ($navId, 1, NULL, 1)";

        return $this->objDbConn->processQuery($sqlt);
    }
    public function insertCtrlAccess()
    {
        $file = $this->ctrlFile;
        $name = 'ctrl' . ucfirst($this->moduleName);
        $url = '/controller/' . $this->moduleName;

        $sql = "INSERT INTO routes 
        (label_name, file, ispublic, isalluser, routecategory_id, method, url, icon, type)
        VALUES 
        ('$name', '$file', 0, 0, 0, 'post', '$url', '', 0)";

        $return = $this->objDbConn->processQuery($sql);
        if ($return['result']) {
            $ctrlId = $this->objDbConn->getLastId();
        }
        $sqlt = "INSERT INTO route_accesses (route_id, access_id, param, required)
        VALUES ($ctrlId, 1, NULL, 1)";

        return $this->objDbConn->processQuery($sqlt);
    }


    // Setters
    public function setModuleName($str)
    {
        $this->moduleName = strtolower(preg_replace('/[^a-z0-9_]/', '', $str));
    }

    public function setBasePath($value)
    {
        $this->basePath = trim($value);
    }

    public function setOptions($value)
    {
        $this->options = is_array($value) ? $value : [];
    }
    public function setViewFile(string $str)
    {
        $this->viewFile = $this->objDbConn->mysqlRealEscape(trim($str));
    }
    public function setName(string $str)
    {
        $this->viewFileName = $this->objDbConn->mysqlRealEscape(trim($str));
    }
    public function setCtrlFile(string $str)
    {
        $this->ctrlFile = $this->objDbConn->mysqlRealEscape(trim($str));
    }
    public function generateStructure()
    {
        $result = false;
        $error = '';
        $data = [];

        if (empty($this->moduleName)) {
            return ['result' => false, 'error' => 'Nombre de módulo inválido', 'data' => []];
        }

        if (empty($this->basePath)) {
            return ['result' => false, 'error' => 'Ruta base requerida', 'data' => []];
        }

        try {
            // Build full path
            $projectRoot = __ROOT__;
            $fullPath = $this->basePath === '.' ? $projectRoot : $projectRoot . DIRECTORY_SEPARATOR . $this->basePath;
            $modulePath = $fullPath . DIRECTORY_SEPARATOR . $this->moduleName;

            $createdFiles = [];

            // Create main module directory
            if (!file_exists($modulePath)) {
                if (!mkdir($modulePath, 0755, true)) {
                    throw new Exception("No se pudo crear la carpeta del módulo");
                }
            }

            // Create View
            if (!empty($this->options['createView'])) {
                $viewPath = $modulePath . DIRECTORY_SEPARATOR . 'view';
                if (!file_exists($viewPath)) {
                    mkdir($viewPath, 0755, true);
                }

                $viewFile = $viewPath . DIRECTORY_SEPARATOR . $this->moduleName . '.php';
                file_put_contents($viewFile, $this->getViewTemplate());
                $createdFiles[] = str_replace($projectRoot . DIRECTORY_SEPARATOR, '', $viewFile);
            }

            // Create Model
            if (!empty($this->options['createModel'])) {
                $modelPath = $modulePath . DIRECTORY_SEPARATOR . 'model';
                if (!file_exists($modelPath)) {
                    mkdir($modelPath, 0755, true);
                }

                $modelFile = $modelPath . DIRECTORY_SEPARATOR . 'mod' . ucfirst($this->moduleName) . '.php';
                file_put_contents($modelFile, $this->getModelTemplate());
                $createdFiles[] = str_replace($projectRoot . DIRECTORY_SEPARATOR, '', $modelFile);
            }

            // Create Controller
            if (!empty($this->options['createController'])) {
                $controllerPath = $modulePath . DIRECTORY_SEPARATOR . 'controller';
                if (!file_exists($controllerPath)) {
                    mkdir($controllerPath, 0755, true);
                }

                $controllerFile = $controllerPath . DIRECTORY_SEPARATOR . 'ctrl' . ucfirst($this->moduleName) . '.php';
                file_put_contents($controllerFile, $this->getControllerTemplate());
                $createdFiles[] = str_replace($projectRoot . DIRECTORY_SEPARATOR, '', $controllerFile);
            }

            // Create Assets
            if (!empty($this->options['createAssets'])) {
                $assetsPath = $modulePath . DIRECTORY_SEPARATOR . 'assets';
                $cssPath = $assetsPath . DIRECTORY_SEPARATOR . 'css';
                $jsPath = $assetsPath . DIRECTORY_SEPARATOR . 'js';

                if (!file_exists($cssPath)) {
                    mkdir($cssPath, 0755, true);
                }
                if (!file_exists($jsPath)) {
                    mkdir($jsPath, 0755, true);
                }

                $cssFile = $cssPath . DIRECTORY_SEPARATOR . $this->moduleName . '.css';
                $jsFile = $jsPath . DIRECTORY_SEPARATOR . $this->moduleName . '.js';

                file_put_contents($cssFile, $this->getCSSTemplate());
                file_put_contents($jsFile, $this->getJSTemplate());

                $createdFiles[] = str_replace($projectRoot . DIRECTORY_SEPARATOR, '', $cssFile);
                $createdFiles[] = str_replace($projectRoot . DIRECTORY_SEPARATOR, '', $jsFile);
            }

            $result = true;
            $data = [
                //'message' => 'Files  ' . count($createdFiles) . ' created successfull',
                //'path' => str_replace($projectRoot . DIRECTORY_SEPARATOR, '', $modulePath),
                //'fullPath' => $modulePath,
                //'projectRoot' => $projectRoot,
                'files' => $createdFiles
            ];
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        return ['result' => $result, 'error' => $error, 'data' => $data];
    }

    // ==================== TEMPLATE FUNCTIONS ====================

    private function getViewTemplate()
    {
        $moduleTitle = ucwords(str_replace('_', ' ', $this->moduleName));
        $date = date('Y-m-d');
        $cssPath = "/{$this->basePath}/{$this->moduleName}/assets/css/{$this->moduleName}.css";
        $jsPath = "/{$this->basePath}/{$this->moduleName}/assets/js/{$this->moduleName}.js";

        $template = <<<'PHP'
            <?php
            require_once __ROOT__ . '/components/usr/usrhead.php';
            require_once __ROOT__ . '/adm/settings/modSettings.php';
            $objLabel = new labels($_MYSQLI_);
            $labelSels = array(
                'btnCancel',
                'btnDelete',
                'btnNews',
                'btnNew',
                'btnEdit',
                'btnDetailBooking',
                'btnDetails',
                'btnExpected',
                'btnExport',
                'btnSave',
                'btnNo',
                'btnYes',
                'lblRequired',
                'lblRevenue',
                'lblSearch',
                'lblRefence',
                'lblSelect'
            );
            $labels = $objLabel->getLabels($labelSels, $chrLang);
            ?>

            <link rel="stylesheet" type="text/css" href="/lib/jquery-ui/jquery-ui.min.css" />
            <link rel="stylesheet" type="text/css" href="/lib/select2/select2.min.css" />
            <link rel="stylesheet" type="text/css" href="/lib/select2/select2-bootstrap-5-theme.min.css" />
            <link rel="stylesheet" type="text/css" href="/lib/datatables/2.3.2/datatables.min.css" />
            <link rel="stylesheet" href="/lib/flatpickr/themes/<?= $selDark ? 'dark' : 'light'; ?>.css">
            <?= $selDark ? '<link rel="stylesheet" href="/lib/flatpickr/themes/dark.css">' : ''; ?>
            <link rel="stylesheet" type="text/css" href="<?= autoVer('{cssPath}') ?>" />

            <div class="container-fluid mt-4">
                <div class="row">
                    <div class="col-12">
                    </div>
                </div>
            </div>

            <?php require_once __ROOT__ . '/components/usr/usrfoot.php' ?>
            <script src="/lib/jquery-ui/jquery-ui.min.js"></script>
            <script src="/lib/datatables/2.3.2/datatables.min.js"></script>
            <script src="/lib/select2/select2.min.js"></script>
            <script src="/lib/xlsx/xlsx.full.min.js"></script>
            <script src="/lib/flatpickr/flatpickr.min.js"></script>
            <?php if ($chrLang != 'en'): ?>
                <script src="/lib/flatpickr/l10n/<?= strtolower($chrLang) ?>.js"></script>
            <?php endif; ?>
            <script>
                var labels = <?= json_encode($labels); ?>;
            </script>
            <script src="<?= autoVer('{jsPath}'); ?>"></script>
            </body>
            </html>
            PHP;

        $template = str_replace('{moduleTitle}', $moduleTitle, $template);
        $template = str_replace('{date}', $date, $template);
        $template = str_replace('{moduleName}', $this->moduleName, $template);
        $template = str_replace('{cssPath}', $cssPath, $template);
        $template = str_replace('{jsPath}', $jsPath, $template);

        return $template;
    }

    private function getModelTemplate()
    {
        $className = ucfirst($this->moduleName) . 'Model';
        return <<<PHP
    <?php
    require_once __ROOT__ . '/model/cte/cteLabels.php';

    class $className {
        #region general
        protected \$objDbConn;
        protected \$id = 0;
        protected \$languageId = 'en';
        protected \$chrLocale = 'en_US';
        
        public function __construct(&\$objDbConn = null)
        {
            if (\$objDbConn) {
            \$this->objDbConn = \$objDbConn;
            } else {
                require_once __ROOT__ . '/assets/php/libDbConn.php';
                \$this->objDbConn = new dbConn();
            }
        }

        #endregion
        #region selects
        public function selectAll() {
            
            \$sql = "";
            
            return \$this->objDbConn->processQuery(\$sql);
        }
        #endregion
        #region insert
        public function insert() {
            
            \$sql = "";
            
            return \$this->objDbConn->processQuery(\$sql);
        }
        #endregion
        #region update
        public function update() {
            \$sql = "";
            
            return \$this->objDbConn->processQuery(\$sql);
        }
        #endregion
        #region deletes
        public function delete() {
            \$sql = "";
            
            return \$this->objDbConn->processQuery(\$sql);
        }
        #endregion
        #region setters
        public function setId(int \$int) {
            \$this->id = \$int;
        }
        public function setLanguageId(string \$str) {
            \$this->languageId = \$this->objDbConn->mysqlRealEscape(trim(\$str));
        }
    }
    PHP;
    }

    private function getControllerTemplate()
    {
        $className = 'ctrl' . ucfirst($this->moduleName);
        $modelClass = ucfirst($this->moduleName) . 'Model';
        $modelFile = 'mod' . ucfirst($this->moduleName);
        return <<<PHP
            <?php
            require_once __ROOT__ . '/assets/php/libLocale.php';
            require_once __ROOT__ . '/assets/php/generalFunctions.php';
            require_once __ROOT__ . '/adm/settings/modSettings.php';
            require_once __ROOT__ . '/{$this->basePath}/{$this->moduleName}/model/{$modelFile}.php';

            \$json = [];
            \$part;
            \$obj{$modelClass} = new $modelClass();

            switch (\$action) {
                case 'C':
                    switch (\$part) {
                        case 'I':
                            break;
                    }
                    break;
                    
                case 'R':
                    switch (\$part) {
                        case 'A':
                            \$json = \$obj{$modelClass}->selectAll();
                            break;
                            
                        case 'S':
                            break;
                    }
                    break;
                    
                case 'U':
                    switch (\$part) {
                        case 'E':
                            break;
                    }
                    break;
                    
                case 'D':
                    switch (\$part) {
                        case 'D':
                            break;
                    }
                    break;
            }
            header('Content-Type: application/json; charset=utf-8');
            echo modGeneralFunction::toJson(\$json, null);
            PHP;
    }

    private function getCSSTemplate()
    {
    }

    private function getJSTemplate()
    {

        $moduleLower = strtolower($this->moduleName);
        $moduleUpper = strtoupper($this->moduleName);
        $jsCtrlVar = "CTRL_" . $moduleUpper;

        return <<<JS
        var {$jsCtrlVar} = "/" + chrLocale + "/controller/{$moduleLower}";

        JS;
    }
}

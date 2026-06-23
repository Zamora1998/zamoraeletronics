<?
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/usr/companies/modcompanies.php';
ob_start();

$json = [];
$part;

$objCompanies = new Companies($_MYSQLI_);
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$part   = $_POST['part']   ?? $_GET['part']   ?? '';

switch ($action) {
    case 'C':
        switch ($part) {
            case 'C':
                $objCompanies->setId($id);
                $objCompanies->SetName($nombre);
                $objCompanies->SetLegal_Registration($cedulaJuridica);
                $objCompanies->SetType_Company($tipoSociedad);
                $objCompanies->SetDirecction($direccion);
                $objCompanies->SetPhone($telefono);
                $objCompanies->SetEmail($correo);
                $objCompanies->SetWeb($sitioWeb);
                $objCompanies->SetDate($fechaIngreso);
                $objCompanies->setState($stateCheckbox);
                $objCompanies->SetImage($image ?? '');
                $json = $objCompanies->InsertCompany();
                break;
            case 'T':
                $objCompanies->setTemplateID($idtemplate);
                $objCompanies->setCompanyID($idcompany);
                $json = $objCompanies->insertTemCompany();
                break;
        }
        break;
    case 'D':
        switch ($part) {
            case 'C':
                $objCompanies->setId($id);
                $json = $objCompanies->DeleteCompany();
                break;
            case 'T':
                $objCompanies->setTemplateID($idtemplate);
                $objCompanies->setCompanyID($idcompany);
                $json = $objCompanies->deleteTemCompany();
                break;
        }
        break;
    case 'R':
        switch ($part) {
            case 'A':
                $json = $objCompanies->selectAll();
                break;
            case 'T':

                $json = $objCompanies->selectInfoTemplate();
                break;
            case 'S':
                $objCompanies->setId($id ?? '');
                $json = $objCompanies->selectCompany();
                break;
        }
        break;
    case 'U':
        switch ($part) {
            case 'U':
                // Subir imagen si viene un archivo
                $imagePath = $image ?? '';
                if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __ROOT__ . '/usr/payroll/assets/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $fileTmpPath  = $_FILES['file']['tmp_name'];
                    $fileExtension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                    $newFileName  = 'company_' . time() . '_' . uniqid() . '.' . $fileExtension;
                    $destPath     = $uploadDir . $newFileName;

                    if (move_uploaded_file($fileTmpPath, $destPath)) {
                        $imagePath = '/usr/payroll/assets/' . $newFileName;
                    } else {
                        $json = ['result' => false, 'error' => 'Error moving attached file.'];
                        break;
                    }
                }

                $objCompanies->setId($id);
                $objCompanies->SetName($nombre);
                $objCompanies->SetLegal_Registration($cedulaJuridica);
                $objCompanies->SetType_Company($tipoSociedad);
                $objCompanies->SetDirecction($direccion);
                $objCompanies->SetPhone($telefono);
                $objCompanies->SetEmail($correo);
                $objCompanies->SetWeb($sitioWeb);
                $objCompanies->SetDate($fechaIngreso);
                $objCompanies->setState($stateCheckbox);
                $objCompanies->SetImage($imagePath);   // <-- usa la ruta nueva o la existente
                $json = $objCompanies->UpdateCompany();
                break;
            case 'T':
                $objCompanies->setTemplateID($oldTemplateId);
                $objCompanies->setCompanyID($oldCompanyId);
                $objCompanies->setTemplateIDNew($newTemplateId);
                $objCompanies->setCompanyIDNew($newCompanyId);
                $json = $objCompanies->UpdateCompanyTemplate();
                break;
            case 'I':
                $buffer = ob_get_clean(); // Limpia TODO lo que se haya impreso antes
                ob_start();
                header('Content-Type: application/json; charset=utf-8');

                $uploadDir = __ROOT__ . '/usr/payroll/assets/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath   = $_FILES['file']['tmp_name'];
                    $originalName  = $_FILES['file']['name'];
                    $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                    // Validar que sea imagen
                    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
                    if (!in_array($fileExtension, $allowedTypes)) {
                        echo json_encode(['result' => false, 'error' => 'Tipo de archivo no permitido: ' . $fileExtension]);
                        exit;
                    }

                    $newFileName = 'company_' . time() . '_' . uniqid() . '.' . $fileExtension;
                    $destPath    = $uploadDir . $newFileName;

                    if (move_uploaded_file($fileTmpPath, $destPath)) {
                        echo json_encode(['result' => true, 'path' => '/usr/payroll/assets/' . $newFileName]);
                    } else {
                        echo json_encode(['result' => false, 'error' => 'Error al mover el archivo. Dir: ' . $uploadDir]);
                    }
                } else {
                    $code = $_FILES['file']['error'] ?? -1;
                    $errors = [
                        UPLOAD_ERR_INI_SIZE   => 'Archivo supera upload_max_filesize del php.ini',
                        UPLOAD_ERR_FORM_SIZE  => 'Archivo supera MAX_FILE_SIZE del formulario',
                        UPLOAD_ERR_PARTIAL    => 'El archivo se subió parcialmente',
                        UPLOAD_ERR_NO_FILE    => 'No se subió ningún archivo',
                        UPLOAD_ERR_NO_TMP_DIR => 'Falta carpeta temporal',
                        UPLOAD_ERR_CANT_WRITE => 'Error al escribir en disco',
                        UPLOAD_ERR_EXTENSION  => 'Extensión de PHP bloqueó la subida',
                    ];
                    echo json_encode(['result' => false, 'error' => $errors[$code] ?? 'Error desconocido: ' . $code]);
                }
                exit;
        }
        break;
}

header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json, null);

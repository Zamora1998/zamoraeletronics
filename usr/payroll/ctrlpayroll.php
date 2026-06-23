<?

require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/adm/settings/modSettings.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/usr/payroll/modpayroll.php';
require_once __ROOT__ . '/mail/model/modMailComposer.php';
require_once __ROOT__ . '/vendor/autoload.php';               //  FPDF via Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$objPayroll = new payroll();
$objSet     = new settings();
$settings   = $objSet->getSettings(['rutaPlanilla']);
$settImage   = $objSet->getSettings(['EDMI - Soluciones EmpresarialesFondo']);
$settImageGLS   = $objSet->getSettings(['GLSFondo']);
$imagePath = $_SERVER['DOCUMENT_ROOT'] . $settImage['EDMI - Soluciones EmpresarialesFondo'];
$imagePathGLS = $_SERVER['DOCUMENT_ROOT'] . $settImageGLS['GLSFondo'];
$json  = [];
$part;

switch ($action) {
    case 'R':
        switch ($part) {

            case 'A':
                $json = $objPayroll->selectTemplateCompany();
                break;
            case 'B':
                $objPayroll->setCompanyID($companyId);
                $objPayroll->setDateFormat($startDateI);
                $json = $objPayroll->selectPaymentsMonths();
                break;
            case 'C':
                if (is_string($planilla)) {
                    $planillaDecoded = json_decode($planilla, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $json = [
                            'result' => false,
                            'error' => 'Error al decodificar planilla JSON: ' . json_last_error_msg()
                        ];
                        break;
                    }
                    $planilla = $planillaDecoded;
                }

                // ▸ datos base
                $objPayroll->setId($id);
                $objPayroll->setDate($date);
                $objPayroll->setCompany($companyName);
                $objPayroll->setParams($planilla);

                // ▸ cuenta SMTP
                $mailAccount = $objPayroll->SelectMailAccount();
                if (!$mailAccount['result'] || empty($mailAccount['data'])) {
                    $json = [
                        'result' => false,
                        'error'  => 'No se pudo obtener la configuración del mailaccount'
                    ];
                    break;
                }

                $acc          = $mailAccount['data'][0];
                $elHost       = $acc['host'];
                $elUser       = $acc['username'];
                $elPass       = $acc['password'];
                $elPort       = (int)$acc['port'];
                $elSmtpauth   = (bool)$acc['smtpauth'];
                $elSmtpsecure = $acc['smtpsecure'];
                $elDebug      = (bool)$acc['debug'];

                date_default_timezone_set('America/Costa_Rica');

                $basePath = rtrim($_SERVER['DOCUMENT_ROOT'] . $settings['rutaPlanilla'], DIRECTORY_SEPARATOR);
                $companyDir   = preg_replace('/[^\w\-]/', '_', trim($companyName));    // Limpieza del nombre

                // ▸ obtener fecha local según UTC‑06:00
                $hoy          = getdate();
                $yearDir      = $hoy['year'];                                          // e.g. 2025
                $monthDir     = str_pad($hoy['mon'], 2, '0', STR_PAD_LEFT);            // e.g. 05
                $dayDir       = str_pad($hoy['mday'], 2, '0', STR_PAD_LEFT);           // e.g. 19 si hoy es 19

                $destino = $basePath
                    . DIRECTORY_SEPARATOR . $companyDir
                    . DIRECTORY_SEPARATOR . $yearDir
                    . DIRECTORY_SEPARATOR . $monthDir
                    . DIRECTORY_SEPARATOR . $dayDir
                    . DIRECTORY_SEPARATOR;

                // Crear directorio si no existe
                if (!is_dir($destino) && !mkdir($destino, 0775, true)) {
                    $json = [
                        'result' => false,
                        'error'  => "No se pudo crear la carpeta $destino"
                    ];
                    break;
                }

                // ▸ recorrer planilla
                $batchResult = ['result' => true, 'errors' => []];
                // No se usa id com abajo porque no se setea para enviar un comprobante para una persona
                $imageToUse = (stripos($companyName, 'EDMI - Soluciones Empresariales') !== false) ? $imagePath : $imagePathGLS;

                foreach ($planilla as $cedula => $emp) {

                    $pdfPath = $destino . $cedula . '.pdf';
                    makePayrollPdf($pdfPath, $emp, $date, $imageToUse);

                    $correoEmp = $emp['Correo'] ?? $elUser;
                    $envio = sendMail(
                        $elHost,
                        $elUser,
                        $elPass,
                        $elPort,
                        $elSmtpauth,
                        $elSmtpsecure,
                        $correoEmp,
                        $elDebug,
                        $id,
                        $date,
                        $chrLang,
                        $pdfPath
                    );

                    if (!$envio['result']) {
                        $batchResult['result']          = false;
                        $batchResult['errors'][$cedula] = $envio['error'];
                    }
                }

                $json = $batchResult;
                break;

            case 'S':
                if (is_string($planilla)) {
                    $planillaDecoded = json_decode($planilla, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $json = [
                            'result' => false,
                            'error' => 'Error al decodificar planilla JSON: ' . json_last_error_msg()
                        ];
                        break;
                    }
                    $planilla = $planillaDecoded;
                }

                // ▸ datos base
                $objPayroll->setId($id);
                $objPayroll->setCompanyID($companyid);
                $objPayroll->setDate($date);
                $objPayroll->setDateFormat($dateformat);
                $objPayroll->setCompany($companyName);
                $objPayroll->setParams($planilla);
                $json = $objPayroll->insertColaboratorData();
                
                if (!$json['result']) {
                    break; 
                }

                $mailAccount = $objPayroll->SelectMailAccount();
                if (!$mailAccount['result'] || empty($mailAccount['data'])) {
                    $json = [
                        'result' => false,
                        'error'  => 'No se pudo obtener la configuración del mailaccount'
                    ];
                    break;
                }

                $acc          = $mailAccount['data'][0];
                $elHost       = $acc['host'];
                $elUser       = $acc['username'];
                $elPass       = $acc['password'];
                $elPort       = (int)$acc['port'];
                $elSmtpauth   = (bool)$acc['smtpauth'];
                $elSmtpsecure = $acc['smtpsecure'];
                $elDebug      = (bool)$acc['debug'];

                date_default_timezone_set('America/Costa_Rica');

                $basePath = rtrim($_SERVER['DOCUMENT_ROOT'] . $settings['rutaPlanilla'], DIRECTORY_SEPARATOR);
                $companyDir   = preg_replace('/[^\w\-]/', '_', trim($companyName));    // Limpieza del nombre

                // ▸ obtener fecha local según UTC‑06:00
                $hoy          = getdate();
                $yearDir      = $hoy['year'];                                          // e.g. 2025
                $monthDir     = str_pad($hoy['mon'], 2, '0', STR_PAD_LEFT);            // e.g. 05
                $dayDir       = str_pad($hoy['mday'], 2, '0', STR_PAD_LEFT);           // e.g. 19 si hoy es 19

                $destino = $basePath
                    . DIRECTORY_SEPARATOR . $companyDir
                    . DIRECTORY_SEPARATOR . $yearDir
                    . DIRECTORY_SEPARATOR . $monthDir
                    . DIRECTORY_SEPARATOR . $dayDir
                    . DIRECTORY_SEPARATOR;

                // Crear directorio si no existe
                if (!is_dir($destino) && !mkdir($destino, 0775, true)) {
                    $json = [
                        'result' => false,
                        'error'  => "No se pudo crear la carpeta $destino"
                    ];
                    break;
                }

                // ▸ recorrer planilla
                $batchResult = ['result' => true, 'errors' => []];
                $imageToUse = ($companyid == 1) ? $imagePath : $imagePathGLS;

                foreach ($planilla as $cedula => $emp) {

                    $pdfPath = $destino . $cedula . '.pdf';

                    makePayrollPdf($pdfPath, $emp, $date, $imageToUse);
                    $correoEmp = $emp['Correo'] ?? $elUser;
                    $envio = sendMail(
                        $elHost,
                        $elUser,
                        $elPass,
                        $elPort,
                        $elSmtpauth,
                        $elSmtpsecure,
                        $correoEmp,
                        $elDebug,
                        $id,
                        $date,
                        $chrLang,
                        $pdfPath
                    );

                    if (!$envio['result']) {
                        $batchResult['result']          = false;
                        $batchResult['errors'][$cedula] = $envio['error'];
                    }
                }
                $json = $batchResult;
                break;
        }
        break;
}

/* ======= salida ======= */
header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json, null);

function makePayrollPdf(string $filePath, array $emp, string $date, string $imagePath = null): void {

    $pdf = new \FPDF();
    $pdf->AddPage();
    if ($imagePath && file_exists($imagePath)) {
    // Medidas de imagen original
    $imgWidthPx = 1280;
    $imgHeightPx = 960;

    // Dimensiones finales (basadas en A4)
    $pdfWidthMM = 210;
    $pdfHeightMM = 297;

    // Calcular altura proporcional al ancho
    $imgWidthMM = $pdfWidthMM;
    $imgHeightMM = $imgWidthMM * ($imgHeightPx / $imgWidthPx); // = 157.5 mm aprox

    // Márgenes
    $marginTopBottom = 20;
    $maxHeight = $pdfHeightMM - ($marginTopBottom * 2);

    // Limitar altura si es necesario
    if ($imgHeightMM > $maxHeight) {
        $imgHeightMM = $maxHeight;
    }

    // Centrado vertical
    $x = 0;
    $y = 0;

    $pdf->Image($imagePath, $x, $y, $imgWidthMM, $imgHeightMM);
}


    $pdf->SetFont('Arial', 'B', 12);
    
    $pdf->SetXY(170, 24); // x: izquierda, y: altura desde arriba
    $pdf->Cell(0,10, sprintf(mb_strtoupper($date)),0,1,'C');
    $pdf->Ln(5);

    $pdf->SetFont('Arial', 'B', 13);

    $pdf->SetXY(71, 15.5); // x: izquierda, y: altura desde arriba
    $pdf->Cell(0, 8, $emp['NombreEmpleado'], 0, 1);

    $pdf->SetXY(68, 24.5); // x: izquierda, y: altura desde arriba
    $pdf->Cell(0, 8, $emp['Cedula'], 0, 1);

    $pdf->SetXY(108, 36.5); // x: izquierda, y: altura desde arriba
    $pdf->Cell(0, 8, $emp['CuentaBancaria'], 0, 1);

    $pdf->SetXY(110, 45.5); // x: izquierda, y: altura desde arriba
    $pdf->Cell(0, 8,  number_format($emp['SalarioMensual'], 2), 0, 1);

    $pdf->SetXY(110, 55.5); // x: izquierda, y: altura desde arriba
    $pdf->Cell(0, 8, $emp['DiasLaborados'], 0, 1);

    $pdf->SetXY(110, 65); // x: izquierda, y: altura desde arriba
    $pdf->Cell(0, 8,  number_format($emp['SalarioLaborado'], 2), 0, 1);

    $pdf->SetXY(110, 75); // x: izquierda, y: altura desde arriba
    $pdf->Cell(0, 8,  number_format($emp['Comisiones'], 2), 0, 1);

    $pdf->SetXY(110, 85); // x: izquierda, y: altura desde arriba
    $pdf->Cell(0, 8,  number_format($emp['Incapacidades'], 2), 0, 1);

    $pdf->SetXY(110, 95); // x: izquierda, y: altura desde arriba
    $pdf->Cell(0, 8,  number_format($emp['CCSSDeduccion'], 2), 0, 1);

    $pdf->SetXY(110, 105); // x: izquierda, y: altura desde arriba
    $pdf->Cell(0, 8,  number_format($emp['DeduccionRenta'], 2), 0, 1);

    $pdf->SetXY(110, 115.5); // x: izquierda, y: altura desde arriba
    $pdf->Cell(0, 8,  number_format($emp['Anticipo'], 2), 0, 1);
    
    $pdf->SetXY(110, 125); // x: izquierda, y: altura desde arriba
    $pdf->Cell(0, 8,  number_format($emp['OtrosRebajos'], 2), 0, 1);

    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetXY(63, 145); // x: izquierda, y: altura desde arriba
    $pdf->Cell(0, 8,  number_format($emp['TotalPagar'], 2), 0, 1);

    $pdf->Output('F', $filePath);
}

/* —— envía correo usando PHPMailer —— */
function sendMail($host, $user, $password, $port, $auth, $smtpsecure, $replyto, $debug, $id, $date, $chrLang = 'en', $pdfPath = null) {
    $objSettings = new settings;
    $set = $objSettings->getSettings(['debug_email', 'debug_name']);

    $mail   = new PHPMailer(true);
    $result = false;
    $error = '';

    try {
        /* ► SMTP */
        $mail->isSMTP();
        $mail->getSMTPInstance()->Timelimit = 30;
        $mail->Host       = $host;
        $mail->SMTPAuth   = $auth;
        $mail->Username   = $user;
        $mail->Password   = $password;
        $mail->SMTPSecure = ($smtpsecure === 'tls')
            ? PHPMailer::ENCRYPTION_STARTTLS
            : (($smtpsecure === 'ssl' || $smtpsecure === 'ssl/tls')
                ? PHPMailer::ENCRYPTION_SMTPS
                : '');
        $mail->Port       = $port;

        if (empty($replyto)) $replyto = $user;

        /* ► plantilla */
        $objMail = new mailComposer($_MYSQLI_);
        $objMail->setId($id);
        $objMail->setLanguageId($chrLang);
        $maildata = $objMail->select();
        if (!$maildata['result']) throw new Exception('Plantilla de correo no encontrada');

        /* ► cabeceras */
        $mail->setFrom($user, 'EDMI - Soluciones Empresariales CR');
        $mail->addAddress($replyto);
        $mail->addReplyTo($replyto);
        if ($debug) $mail->addBCC($set['debug_email'], $set['debug_name']);

        /* ► contenido */
        $mail->isHTML(true);
        $mail->Subject = $maildata['subject'] . ' - ' . $date;
        $mail->Body    = $maildata['body'];
        $mail->AltBody = $maildata['altbody'];

        /* ► adjunto PDF */
        if ($pdfPath && file_exists($pdfPath)) {
            $mail->addAttachment($pdfPath);
        }

        $mail->send();
        $result = true;
    } catch (Exception $e) {
        $error = $mail->ErrorInfo ?: $e->getMessage();
    }

    return ['result' => $result, 'error' => $error];
}

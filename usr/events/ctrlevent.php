<?
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/adm/settings/modSettings.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/usr/events/modevent.php';
require_once __ROOT__ . '/mail/model/modMailComposer.php';
require_once __ROOT__ . '/vendor/autoload.php';               //  FPDF via Composer
require_once __ROOT__ . '/model/modFileProcessor.php';
__ROOT__ . '/cache/' . $selEvent['id'];

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$objevents = new modevents();
$objSettings = new settings($_MYSQLI_);
$objevents->setEventId($selEvent['id']);
$settings = $objSettings->getSettings(['routeimages']);

$json  = [];
$part;

switch ($action) {
    case 'C':
        switch ($part) {
            case 'E':
                $objevents->setId($id);
                $objevents->setName($name ?? '');
                $objevents->setDescription($descriptionevent ?? '');
                $objevents->setLocation($location ?? '');
                $objevents->setLatitude($latitude ?? 0);
                $objevents->setLongitude($longitude ?? 0);
                $objevents->setDistanceKm($distance_km ?? 0);
                $objevents->setMaxParticipants($max_participants ?? 0);
                $objevents->setStartDatetime($start_datetime ?? '');
                $objevents->setEndDatetime($end_datetime ?? '');
                $objevents->setRegistrationOpen($registration_open ?? '');
                $objevents->setRegistrationOpen($registration_open ?? '');
                $objevents->setRegistrationClose($registration_close ?? '');
                $objevents->setStatus($status ?? '');
                $objevents->setEventTypeId(intval($event_type_id ?? 0));
                $json = $objevents->updateEvents();
                break;
            case 'R':
                $objevents->setId($selEvent['id']);
                $objevents->SetrouteCoords($routeCoords);
                $objevents->setStartTime($start_time ?? '');
                $objevents->setEndTime($end_time ?? '');
                $objevents->setDescription($description ?? '');
                $objevents->setDistanceKm($routeDistance ?? 0);

                $objevents->setPrice($Price ?? 0);
                $objevents->setName($routeName);
                $json = $objevents->updateRoute();
                break;
            case 'CR':
                if (!empty($_POST['registrationConfig'])) {
                    $objevents->setId($id);
                    $registrationConfig = [];

                    foreach ($_POST['registrationConfig'] as $field) {
                        $registrationConfig[] = [
                            'field_name'  => $field['field_name'] ?? '',
                            'is_enabled'  => $field['is_enabled'] ?? 0,
                            'is_required' => $field['is_required'] ?? 0,
                            'label'       => $field['label'] ?? null,
                        ];
                    }

                    $json = $objevents->saveRegistrationConfig(
                        $selEvent['id'],
                        $registrationConfig
                    );
                } else {
                    $json = ['result' => false, 'msg' => 'No fields received'];
                }
                break;
        }
        break;
    case 'R':
        switch ($part) {
            case 'A':
                $json = $objevents->selectEvents();
                break;
            case 'R':
                $json = $objevents->selecteventroutes();
                if (is_string($json)) {
                    $json = json_decode($json, true);
                }

                if (isset($json['data']) && is_array($json['data'])) {
                    foreach ($json['data'] as &$route) {
                        if (!empty($route['route_created_at'])) {
                            $dateUTC = new DateTime($route['route_created_at'], new DateTimeZone('UTC'));
                            $dateCR = $dateUTC->setTimezone(new DateTimeZone('America/Costa_Rica'));
                            $route['route_created_at'] = $dateCR->format('d-M-Y H:i');
                        }
                    }
                }
                break;
            case 'E':
                $objevents->setId($id ?? 0);
                $json = $objevents->selectEvent();
                break;
            case 'ER':
                $objevents->setId($id ?? 0);
                $json = $objevents->selectEvenRoutes();
                break;
            case 'S':
                break;
            case 'RC': // Get Registration Config
                $eventId = isset($id) ? $id : 0;
                $json = $objevents->getRegistrationConfig($eventId);
                break;
            case 'SC': // Save Registration Config (Standalone)
                $eventId = isset($id) ? $id : 0;
                if ($eventId > 0 && isset($registrationConfig)) {
                    $json = $objevents->saveRegistrationConfig($eventId, $registrationConfig);
                } else {
                    $json = ['result' => false, 'message' => 'Invalid ID or Config'];
                }
                break;
        }
        break;
    case 'D':
        switch ($part) {
            case 'R':
                $objevents->setId($id);
                $json = $objevents->DeleteRoute();
                break;
        }
        break;
    case 'U':
        switch ($part) {
            case 'U':
                $foldername = __ROOT__ . '/' . $settings['routeimages'] . "/";
                $filePrcObj = new FileProcessor($foldername);
                // 👇 Procesa múltiples archivos
                $filesSaved = $filePrcObj->saveMultiple($uuid);
                $imagePaths = [];
                if (!empty($filesSaved) && is_array($filesSaved)) {
                    foreach ($filesSaved as $index => $file) {
                        $path = $settings['routeimages'] . '/' . $file['name'] . '.' . $file['ext'];
                        $imagePaths[] = $path;

                        // Guarda en el objeto según el orden
                        if ($index === 0) {
                            $objevents->setImageOne($path);
                        } elseif ($index === 1) {
                            $objevents->setImageTwo($path);
                        }
                    }
                }
                $json = $objevents->setId($selEvent['id']);
                $result = $objevents->updateImages();
                $json = [
                    'result' => $result && !empty($imagePaths), // si el update fue exitoso y hay imágenes
                    'images' => $imagePaths
                ];
                break;
        }

        break;
}


/* ======= salida ======= */
header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json, null);
/*
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

function sendMail($host, $user, $password, $port, $auth, $smtpsecure, $replyto, $debug, $id, $date, $chrLang = 'en', $pdfPath = null) {
    $objSettings = new settings;
    $set = $objSettings->getSettings(['debug_email', 'debug_name']);

    $mail   = new PHPMailer(true);
    $result = false;
    $error = '';

    try {
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

        $objMail = new mailComposer($_MYSQLI_);
        $objMail->setId($id);
        $objMail->setLanguageId($chrLang);
        $maildata = $objMail->select();
        if (!$maildata['result']) throw new Exception('Plantilla de correo no encontrada');

        $mail->setFrom($user, 'EDMI - Soluciones Empresariales CR');
        $mail->addAddress($replyto);
        $mail->addReplyTo($replyto);
        if ($debug) $mail->addBCC($set['debug_email'], $set['debug_name']);

        $mail->isHTML(true);
        $mail->Subject = $maildata['subject'] . ' - ' . $date;
        $mail->Body    = $maildata['body'];
        $mail->AltBody = $maildata['altbody'];

        if ($pdfPath && file_exists($pdfPath)) {
            $mail->addAttachment($pdfPath);
        }

        $mail->send();
        $result = true;
    } catch (Exception $e) {
        $error = $mail->ErrorInfo ?: $e->getMessage();
    }

    return ['result' => $result, 'error' => $error];
}*/

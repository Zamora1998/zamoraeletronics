<?php
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/adm/settings/modSettings.php';
require_once __ROOT__ . '/usr/payroll/model/modPayroll.php';

$json = [];
$part;
$objPayrollModel = new modPayroll();
$namecompany = $selEvent['event_name'];
$idCompany = $selEvent['id'];
switch ($action) {
    case 'C':
        switch ($part) {
            case 'I':
                break;
        }
        break;

    case 'R':
        switch ($part) {
            case 'B':
                $objPayrollModel->setCompanyID($idCompany);
                $objPayrollModel->setDateFormat($startDateI);
                $json = $objPayrollModel->selectPaymentsMonths();
                break;
            case 'S':
                break;
        }
        break;
    case 'M':
        switch ($part) {
            case 'MP':
                require_once __ROOT__ . '/mail/model/modMailComposer.php';
                $mailComposer = new mailComposer($_MYSQLI_);
                $objSettings = new settings($_MYSQLI_);
                $fromname = $objSettings->getSettings(['set_Fromname_Mail'])['set_Fromname_Mail'];
                $requestDate = $_POST['date'] ?? null;
                if (empty($requestDate)) {
                    $json = ['result' => false, 'error' => 'Date is missing'];
                    break;
                }
                $mailItem = $mailData[0] ?? [];

                if (empty($mailItem)) {
                    $json = ['result' => false, 'error' => 'Mail data is empty'];
                    break;
                }
                $mailComposer->setId(intval($mailItem['mailTemplate']));
                $mailComposer->setUserId($selUser);
                $mailComposer->setParameters([
                    '{name}' => $mailItem['name'] ?? '',
                    '{CompanyName}' => $namecompany ?? '',
                    '{Period}' => $requestDate ?? ''
                ]);
                $json = $mailComposer->select();

                break;

            case 'SM':
                require_once __ROOT__ . '/mail/model/modMailQueue.php';
                require_once __ROOT__ . '/mail/model/modMailComposer.php';
                $mailComposer = new mailComposer($_MYSQLI_);
                $mailQueue = new mailQueue($_MYSQLI_);

                // Decode mailData from JSON if sent as string
                if (isset($_POST['mailData']) && is_string($_POST['mailData'])) {
                    $mailData = json_decode($_POST['mailData'], true);
                }
                $requestDate = $_POST['date'] ?? null;
                if (empty($requestDate)) {
                    $json = ['result' => false, 'error' => 'Date is missing'];
                    break;
                }
                $dateFormat = $_POST['dateformat'] ?? null;
                if (empty($dateFormat)) {
                    $json = ['result' => false, 'error' => 'Date is missing'];
                    break;
                }
                if (empty($mailData) || !is_array($mailData)) {
                    $json = ['result' => false, 'error' => 'Mail data is empty'];
                    break;
                }

                $objSettings = new settings($_MYSQLI_);
                //$fromSettings = $objSettings->getSettings(['set_Fromname_Mail', 'set_FromEmail_Mail']);
                $uploadSettings = $objSettings->getSettings(['uploadDir']);

                // Create upload directory for attachments
                $date = date('Y-m-d');
                $uploadDir = __ROOT__ . '/' . $uploadSettings['uploadDir'] . '/Payroll/' . $date . '/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $results = [];
                $successCount = 0;
                $errorCount = 0;

                // Process each mail item
                foreach ($mailData as $mailItem) {
                    $mailQueue = new mailQueue($_MYSQLI_);
                    $mailComposer = new mailComposer($_MYSQLI_);

                    $mailComposer->setLanguageId($mailItem['lang'] ?? 'en');
                    $mailComposer->setUserId($selUser);
                    $mailComposer->setParameters([
                        '{name}' => $mailItem['name'] ?? '',
                        '{CompanyName}' => $namecompany ?? '',
                        '{Period}' => $requestDate ?? '',

                    ]);

                    if (!empty($mailItem['mailTemplate'])) {
                        $templateId = intval($mailItem['mailTemplate']);
                        $mailQueue->setTemplateId($templateId);
                        $mailComposer->setId($templateId);
                    }

                    $mailResult = $mailComposer->select();
                    if (!$mailResult['result']) {
                        $errorCount++;
                        $results[] = ['id' => $mailItem['id'], 'result' => false, 'error' => 'Error loading mail template'];
                        continue;
                    }

                    // Save PDF attachment from base64
                    $attachmentPath = '';
                    if (!empty($mailItem['pdfBase64']) && !empty($mailItem['fileName'])) {
                        $pdfContent = base64_decode($mailItem['pdfBase64']);
                        $fileName = $mailItem['fileName'];
                        $destPath = $uploadDir . $fileName;

                        if (file_put_contents($destPath, $pdfContent) !== false) {
                            $attachmentPath = $uploadSettings['uploadDir'] . '/Payroll/' . $date . '/' . $fileName;
                        }
                    }

                    // Prepare mail queue entry
                    $mailAccountId = isset($mailItem['mailaccountid']) ? intval($mailItem['mailaccountid']) : 1;
                    $mailQueue->set_Maileraccount($mailAccountId);
                    $mailQueue->set_Fromname($namecompany ?? '');
                    $mailQueue->set_Subject($mailResult['subject'] ?? '');
                    $mailQueue->set_Body($mailResult['body'] ?? '');
                    $mailQueue->set_Altbody($mailResult['altbody'] ?? '');

                    // Set reply to if provided
                    if (!empty($mailItem['mailReplyTo']['email'])) {
                        $mailQueue->setReplyTo($mailItem['mailReplyTo']['email']);
                    }

                    // Set recipients
                    $mailToAddresses = [];
                    $mailToNames = [];
                    $mailTosArr = is_array($mailItem['mailTo']) ? $mailItem['mailTo'] : [$mailItem['mailTo']];
                    foreach ($mailTosArr as $toInfo) {
                        if (is_array($toInfo)) {
                            $mailToAddresses[] = $toInfo['address'] ?? '';
                            $mailToNames[] = $toInfo['name'] ?? '';
                        } else {
                            $mailToAddresses[] = $toInfo;
                            $mailToNames[] = $mailItem['name'] ?? '';
                        }
                    }
                    $mailQueue->setTos($mailToAddresses, $mailToNames);

                    // Set CC if provided
                    if (!empty($mailItem['mailCc'])) {
                        $mailCc = is_array($mailItem['mailCc']) ? $mailItem['mailCc'] : [$mailItem['mailCc']];
                        $mailQueue->setCcs($mailCc);
                    }

                    // Set attachment if saved
                    if (!empty($attachmentPath)) {
                        $mailQueue->setAttachments([$attachmentPath]);
                    }

                    $insertResult = $mailQueue->insert();

                    if (!empty($insertResult['mailId'])) {
                        $successCount++;
                        $results[] = ['id' => $mailItem['id'], 'result' => true, 'mailId' => $insertResult['mailId']];
                    } else {
                        $errorCount++;
                        $results[] = ['id' => $mailItem['id'], 'result' => false, 'error' => 'Error inserting to queue'];
                    }
                }

                $json = [
                    'result' => $errorCount === 0,
                    'successCount' => $successCount,
                    'errorCount' => $errorCount,
                    'totalProcessed' => count($mailData),
                    'details' => $results
                ];
                if ($errorCount === 0) {
                    $planillaData = isset($_POST['planillaData']) ? json_decode($_POST['planillaData'], true) : [];

                    if (!empty($planillaData) && is_array($planillaData)) {
                        require_once __ROOT__ . '/usr/payroll/model/modPayroll.php';
                        $objPayroll = new modPayroll($_MYSQLI_);
                        $objPayroll->setParams($planillaData);
                        $objPayroll->setCompanyID($idCompany ?? 0);
                        $objPayroll->setDateFormatData($dateFormat);

                        $insertResult = $objPayroll->insertColaboratorData();

                        $json['insertResult'] = $insertResult;

                        if (!$insertResult['result']) {
                            $json['result']        = false;
                            $json['insertErrors']  = $insertResult['errors'];
                        }
                    }
                }
                break;
        }
        break;

    case 'U':
        switch ($part) {
            case 'E':
                break;
        }
        break;

    case 'D':
        switch ($part) {
            case 'D':
                break;
        }
        break;
}
header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json, null);

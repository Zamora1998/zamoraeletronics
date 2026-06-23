<?
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/adm/mailtemplates/modMailTemplates.php';
require_once __ROOT__ . '/mail/model/modMailComposer.php';

$objTemplates = new mailTemplates($_MYSQLI_);
$objTemplates->setLanguageId($chrLang);
switch ($action) {
    case 'C':
        switch ($part) {
            case 'T':
                $objTemplates->setId($id);
                $objTemplates->setMailAccountId($mtAccount);
                $objTemplates->setName($mtName);
                $objTemplates->setSubject($mtSubject);
                $objTemplates->setCompany($mtCompany ?? 0);
                $objTemplates->setBody($mtBody);
                $objTemplates->setAltbody($mtAltBody);
                $result = $objTemplates->insert();

                $matches = [];
                preg_match_all('/{(.*?)}/', $mtBody, $matches);
                $objTemplates->setVariables($matches[0]);
                $json = $objTemplates->insertVariables();

                if (array_key_exists('error', $result)) {
                    $json['errors'][] = $result['error'];
                }
                $json['result'] = ($json['result'] && $result['result']);
                break;
            case 'D':
                $objTemplates->setName($name);
                $objTemplates->setId($id);
                $json = $objTemplates->duplicate();
                break;
        }
        break;
    case 'D':
        switch ($part) {
            case 'T':
                $objTemplates->setId($id);
                $json = $objTemplates->delete();
                break;
        }
        break;
    case 'U':
        switch ($part) {
            case 'V':
                $variables = [];
                foreach ($mtVariables as $key => $variable) {
                    $variables[] = array('variable' => $variable, 'label' => $mtLabels[$key]);
                }
                $objTemplates->setId($id);
                $objTemplates->setVariables($variables);
                $json = $objTemplates->updateVariables();
                break;
        }
        break;
    case 'R': //Read
        switch ($part) {
            case 'A':
                $objTemplates->setId($selEvent['id']);
                $json = $objTemplates->select();
                break;
            case 'T':
                $objTemplates->setId($id);
                $json = $objTemplates->selectTemplate();
                break;
            case 'L':
                if (!isset($term)) {
                    $term = '';
                }
                $objTemplates->setTerm($term);
                $json = $objTemplates->selectLabels();
                break;
            case 'P':
                $objComp = new mailComposer($_MYSQLI_);
                $objComp->setUserId($selUser);
                $objComp->setId($id);
                $objComp->setLanguageId($chrLang);
                $json = $objComp->compose();
                break;
        }
}

header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json, null);

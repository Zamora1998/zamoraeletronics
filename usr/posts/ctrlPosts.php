<?
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/usr/posts/modPosts.php';

$json = [];
$objPosts = new posts($_MYSQLI_);
//$objPosts->setUserId($selUser);
//$objPosts->setEventId($selEvent['id']);
switch ($action) {
    case 'C':
        $objPosts->setId($id);
        $objPosts->setName($grName);
        $objPosts->setEnabled($grEnabled);
        if (!$id) {
            $json = $objPosts->insert();
            $json['new'] = true;
        } else {
            $json = $objPosts->update();
            $json['new'] = false;
        }
        break;
    case 'R':
        switch ($part) {
            case 'A':
                $objPosts->setLanguageId($chrLang);
                $json = $objPosts->select();
                break;

            case 'P':
                $objPosts->setId($id);
                $json = $objPosts->selectPost();
                break;

            case 'CF':
                $objPosts->setId($id);
                $json = $objPosts->selectDistancesByPost();

                break;
        }
        break;
    case 'U':
        switch ($part) {
            case 'P':
                $objPosts->setId($id);
                $objPosts->setEnabled($enabled);
                $json = $objPosts->updateEnabled();
                break;
            case 'S':
                break;
        }
        break;
    case 'D':
        $objPosts->setId($id);
        //$objPosts->setUserId($selUser);
        $json = $objPosts->delete();
        break;

    default:
        break;
}

header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json);

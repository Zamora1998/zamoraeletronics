<?php
require_once __ROOT__ . '/assets/php/libLocale.php';
require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/adm/routes/modRoutes.php';

$json = [];
$part;

$objRoutes = new route($_MYSQLI_);
switch ($action) {
    case 'C':
        switch ($part) {
            case 'C':
                $objRoutes->setId($id);
                $objRoutes->setName($elCategoryName);
                $objRoutes->setStatus($elEnabled);
                $json = $objRoutes->insertCategory();
                break;
            case 'E':
                $objRoutes->setId($id);
                $objRoutes->setName($elCategoryName);
                $objRoutes->setStatus($elEnabled);
                $json = $objRoutes->updateCategory();
                break;
        }
        break;
    case 'D':
        switch ($part) {
            case 'R':
                $objRoutes->setId($id);
                $json = $objRoutes->deleteRoute();
                break;
            case 'C':
                $objRoutes->setId($id);
                $json = $objRoutes->deleteCategory();
                break;
        }
        break;
    case 'R': // Agregar manejo de lectura
        switch ($part) {
            case 'A':
                $json = $objRoutes->selectAll();
                break;
            case 'S':
                $objRoutes->setId($id);
                $json = $objRoutes->selectRoute();
                $categories = $json['categories'];
                $arrCats = [];
                $map = [];

                foreach ($categories as $category) {
                    $map[$category['id']] = [
                        'id' => $category['id'],
                        'name' => $category['name'],
                        'children' => []
                    ];
                }
                foreach ($categories as $category) {
                    $parents = explode(',', $category['parent_ids']);
                    if (count($parents) > 1) {
                        $parentId = end($parents);
                        $map[$parentId]['children'][] = &$map[$category['id']];
                    } else {
                        $arrCats[] = &$map[$category['id']];
                    }
                }
                $json['categories'] = $arrCats;
                break;

            case 'P':
                $objRoutes->setId($id);
                $json = $objRoutes->selectPermission();
                break;
            case 'E':
                $objRoutes->setId($id);
                $json = $objRoutes->selectAllAccessName();
                break;
            case 'C':
                $json = $objRoutes->selectAllCategories();
                $categories = $json['data'];
                $arrCats = array();
                //Category tree
                foreach ($categories as $category) {
                    $parents = explode(',', $category['parent_ids']);
                    $arr = array();
                    $arr['|' . $category['id']]['id'] = $category['id'];
                    $arr['|' . $category['id']]['content'] = $category['name'];
                    if ($category['parent_ids']) {
                        foreach (array_reverse($parents) as $parent) {
                            //Add root-node
                            if ($parent) {
                                $arr = ['|' . $parent => ['children' => $arr]];
                            }
                        }
                    }
                    $arrCats = array_merge_recursive($arrCats, $arr);
                }
                $json['data'] = modGeneralFunction::array_values_recursive($arrCats, 'data');
                break;

            case 'O';
                $objRoutes->setId($id);
                $json = $objRoutes->selectCategoryItem();
                break;
        }
        break;
    case 'U': // Agrega manejo actualizacion
        switch ($part) {
            case 'A':
                $objRoutes->setId($id);
                $objRoutes->setAccessIds($accessIds);
                $objRoutes->setParams($param);
                $objRoutes->setOptionals($optional);
                $objRoutes->setRequireds($required);
                $json = $objRoutes->insertAccesses();
                break;
            case 'E':
                $objRoutes->setParams($ArrAfter);
                $json = $objRoutes->updateCategoryElements();
                break;
            case 'R':
                $objRoutes->setId($id);
                $objRoutes->setName($elLabelname);
                $objRoutes->SetType($elType);
                $objRoutes->SetIcon($elIcon);
                $objRoutes->SetPosition($elPosition);
                $objRoutes->SetUrl($elUrl);
                $objRoutes->SetFile($elFile);
                $objRoutes->SetMethod($elMethod);
                $objRoutes->setCategoryId($elCategory);
                $objRoutes->setParentC($elcategoryParent);
                $objRoutes->SetAlluser($elAllusers);
                $objRoutes->SetPublic($elPublic);
                $json = $objRoutes->insertRoute();
                break;
            case 'I':
                $objRoutes->setId($id);
                $json = $objRoutes->updateIsPublic($enabled);
                break;
            case 'C':
                $objRoutes->setId($id);
                $json = $objRoutes->updateIsAll($enabled);
                break;
        }
        break;
}

header('Content-Type: application/json; charset=utf-8');
echo modGeneralFunction::toJson($json, null);

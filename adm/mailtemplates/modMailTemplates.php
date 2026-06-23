<?php

class mailTemplates
{

    protected $objDbConn;
    protected $languageId = 'en';
    protected $id = 0;
    protected $company = 0;
    protected $mailAccountId = 'null';
    protected $variableId = 0;
    protected $name = "";
    protected $body = "";
    protected $altbody = "";
    protected $subject = "";
    protected $variables = [];
    protected $term = "";
    protected $salt = '';
    protected $key = '';

    public function __construct(&$objDbConn = null)
    {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
    }

    public function select()
    {
        $sql = "SELECT
                    mailtemplates.id,
                    mailaccount_id,
                    name,
                    subject_label,
                    body,
                    altbody,
                    mailaccount_id,
                    username,
                    idCompany
                FROM mailtemplates
                    LEFT JOIN mailaccounts
                        ON mailaccounts.id = mailaccount_id
                WHERE idCompany = '{$this->id}' OR idCompany IS NULL;";

        return $this->objDbConn->processQuery($sql, true);
    }

    public function selectTemplate()
    {
        $return = [];
        $sql = "SELECT 
                    id,
                    mailaccount_id,
                    name,
                    subject_label,
                    body,
                    altbody,
                    idCompany
                FROM mailtemplates
                WHERE id = {$this->id};";

        $return = $this->objDbConn->processQuery($sql);
        if (array_key_exists('data', $return)) {
            $return['data'] = $return['data'][0];
        }

        $vars = $this->selectVariables();
        if (array_key_exists('data', $vars)) {
            $return['variables'] = $vars['data'];
        }

        return $return;
    }

    public function selectVariables()
    {
        $sql = "SELECT
                    variable,
                    ifnull(label_name, '') label_name,
                    position
                FROM mailtemplatevariables
                WHERE mailtemplate_id = {$this->id}
                ORDER BY position";

        return $this->objDbConn->processQuery($sql);
    }

    public function selectLabels()
    {

        $sql = "SELECT
                    id,
                    CONCAT_WS(' ', name, description) as text
                FROM labels
                    LEFT JOIN labeldetails
                        ON id = label_id
                WHERE language_id = '{$this->languageId}'
                    AND (name LIKE '%{$this->term}%'
                    OR description LIKE '%{$this->term}%');";

        return $this->objDbConn->processQuery($sql);
    }

    public function insert()
    {
        // Validación para asignar NULL en lugar de 0 si $this->company es 0
        $companyValue = ($this->company == 0) ? 'NULL' : $this->company;

        if (!$this->id) {
            // Inserción de datos
            $sql = "INSERT INTO mailtemplates
            (name, subject_label, body, altbody, mailaccount_id, idcompany) VALUES
            ('$this->name', '$this->subject', '$this->body', '$this->altbody', {$this->mailAccountId}, {$companyValue});";
        } else {
            // Actualización de datos
            $sql = "UPDATE mailtemplates
                SET
                    name = '{$this->name}',
                    subject_label = '{$this->subject}',
                    body = '{$this->body}',
                    altbody = '{$this->altbody}',
                    mailaccount_id = {$this->mailAccountId},
                    idcompany = {$companyValue}
                WHERE id = {$this->id};";
        }

        // Desactivar auto incremento si es necesario
        // file_put_contents(__ROOT__ . '/debugMailTemplate.txt', var_export($sql, true));
        $this->objDbConn->resetAI('mailtemplates');
        $return = $this->objDbConn->processQuery($sql);

        // Si la inserción es exitosa y no hay ID (es una inserción nueva), obtener el último ID insertado
        if ($return['result'] && !$this->id) {
            $this->id = $this->objDbConn->getLastId();
        }

        return $return;
    }

    public function insertVariables()
    {

        $values = [];
        $variables = [];

        foreach ($this->variables as $key => $value) {
            $pos = 128 + $key;
            $value = $this->objDbConn->mysqlRealEscape(trim($value));
            $values[] = "({$this->id}, '{$value}', {$pos})";
            $variables[] = $value;
            $return = [];
        }

        if (!empty($values)) {
            $sql = "INSERT IGNORE INTO mailtemplatevariables (mailtemplate_id, variable, position)
                VALUES " . implode(",\n", $values);

            //file_put_contents(__ROOT__ . '/debugMailTemplate.txt', var_export($sql, true));
            $this->objDbConn->resetAI('mailtemplatevariables');
            $result = $this->objDbConn->processQuery($sql);
            $return['result'] = $result['result'];
            if ($result['result'] && !$this->id) {
                $this->id = $this->objDbConn->getLastId();
            }
            if (array_key_exists('error', $result)) {
                $return['errors'][] = $result['error'];
            }
        } else {
            $return['result'] = true;
        }

        $sql = "DELETE FROM mailtemplatevariables
                WHERE mailtemplate_id = {$this->id}
                AND variable NOT IN ('" . implode("','", $variables) . "')";

        $result = $this->objDbConn->processQuery($sql);
        $return['result'] = $return['result'] && $result['result'];
        if (array_key_exists('error', $result)) {
            $return['errors'][] = $result['error'];
        }

        $result = $this->selectTemplate();
        $return['result'] = $return['result'] && $result['result'];
        if (array_key_exists('error', $result)) {
            $return['errors'][] = $result['error'];
        }
        if (array_key_exists('data', $result)) {
            $return['data'] = $result['data'];
        }
        if (array_key_exists('variables', $result)) {
            $return['variables'] = $result['variables'];
        }

        return $return;
    }

    public function updateVariables()
    {
        foreach ($this->variables as $key => $value) {
            $pos = 128 + $key;
            $variable = $this->objDbConn->mysqlRealEscape(trim($value['variable']));
            $label = $this->objDbConn->mysqlRealEscape(trim($value['label']));
            $values[] = "({$this->id}, '{$variable}', '{$label}', {$pos})\n";
        }

        $sql = "INSERT INTO mailtemplatevariables (mailtemplate_id, variable, label_name, position)
                    VALUES\n";
        $sql .= implode(",\n", $values);
        $sql .= "                    ON DUPLICATE KEY UPDATE label_name = VALUES(label_name), position = VALUES(position);";

        return $this->objDbConn->processQuery($sql);
    }

    public function duplicate()
    {
        $errors = [];
        $results = [];

        $this->objDbConn->resetAI('mailtemplates');
        $this->objDbConn->resetAI('mailtemplatevariables');

        // Obtener los datos del template original
        $sql = "SELECT
                    mailaccount_id,
                    name,
                    subject_label,
                    body,
                    altbody
                FROM mailtemplates
                WHERE id = {$this->id}";

        $result = $this->objDbConn->processQuery($sql);

        if (!$result['result']) {
            $errors[] = $result['error'];
            return ['result' => false, 'errors' => $errors, 'results' => $results];
        }

        $original = $result['data'][0];
        $newName = $original['name'] . ' - ' . $this->name;

        $sql = "INSERT INTO mailtemplates (
                    mailaccount_id,
                    name,
                    subject_label,
                    body,
                    altbody
                ) VALUES (
                    '{$original['mailaccount_id']}',
                    '{$newName}',
                    '{$original['subject_label']}',
                    '{$original['body']}',
                    '{$original['altbody']}'
                )";

        $resp = $this->objDbConn->processQuery($sql);

        if (!$resp['result']) {
            $errors[] = $resp['error'];
            $results[] = false;
            return ['result' => false, 'errors' => $errors, 'results' => $results];
        }

        $newId = $this->objDbConn->getLastId();
        $results[] = true;

        $sql = "SELECT 
                    mailtemplate_id,
                    variable,
                    IFNULL(label_name, '') AS label_name, 
                    position
                FROM mailtemplatevariables 
                WHERE mailtemplate_id = {$this->id}";

        $result = $this->objDbConn->processQuery($sql);

        if ($result['result'] && !empty($result['data'])) {
            foreach ($result['data'] as $var) {
                $variable = $var['variable'];
                $label = $var['label_name'];
                $position = $var['position'];

                $sql = "INSERT INTO mailtemplatevariables (mailtemplate_id, variable, label_name, position)
                        VALUES ('{$newId}', '{$variable}', '{$label}', '{$position}')";

                $result = $this->objDbConn->processQuery($sql);
                if (!$result['result']) {
                    $errors[] = "Error to insert variable '{$variable}': " . $result['error'];
                    $results[] = false;
                } else {
                    $results[] = true;
                }
            }
        }

        return ['result' => !in_array(false, $results, true), 'errors' => $errors, 'results' => $results];
    }

    public function delete()
    {

        $sql = "DELETE FROM mailtemplates
                WHERE id = {$this->id}";

        $return = $this->objDbConn->processQuery($sql);

        return $return;
    }

    public function setLanguageId(string $str)
    {
        $this->languageId = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function setMailAccountId(string $id)
    {
        if ($id) {
            $this->mailAccountId = intval($id);
        } else {
            $this->mailAccountId = 'null';
        }
    }

    public function setName(string $str)
    {
        $this->name = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function setSubject(string $str)
    {
        $this->subject = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function setBody(string $str)
    {
        $this->body = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function setAltbody(string $str)
    {
        $this->altbody = $this->objDbConn->mysqlRealEscape(trim($str));
    }

    public function setCompany(int $company)
    {
        $this->company = $company;
    }
        public function setTemplateVariableId(int $id)
    {
        $this->variableId = $id;
    }

    public function setVariables(array $arr)
    {
        $this->variables = $arr;
    }

    public function setTerm(string $str)
    {
        $this->term = $this->objDbConn->mysqlRealEscape(trim($str));
    }
}

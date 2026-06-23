<?
class contacts {
    protected $objDbConn;

    protected $contactId = 0;
    protected $firstname = '';
    protected $lastname = '';
    protected $company = '';
    protected $languageId = 'en';
    protected $phoneId = 0;
    protected $contacttypeId = 1;
    protected $countrycode = '';
    protected $number = '';
    protected $extension = '';
    protected $emailId = 0;
    protected $email = '';
    protected $socialId = 0;
    protected $socialName = '';
    protected $socialtypeId = 0;
    protected $addressId = 0;
    protected $addressLine1 = '';
    protected $addressLine2 = '';
    protected $addressZip = '';
    protected $addressCity = '';
    protected $addressCountry = '';
    protected $default = 0;

    public function __construct(&$objDbConn = null) {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
    }

    public function selectContact() {
        $results = [];
        $errors = [];
        $data = array();

        $sql = "SELECT
                    contacts.id,
                    ifnull (contactpersons.firstname, '') AS firstname,
                    ifnull (contactpersons.lastname, '') AS lastname,
                    contactpersons.media_id,
                    contactpersons.title,
                    contactcompanies.name AS companyname,
                    COALESCE (CONCAT(SUBSTR(firstname, 1, 1), SUBSTR(lastname, 1, 1)), SUBSTR(contactcompanies.name, 1, 3)) AS initials
                FROM contacts
                    LEFT JOIN contactpersons
                        ON contactpersons.contact_id = contacts.id
                    LEFT JOIN contactcompanies
                        ON contactcompanies.contact_id = contacts.id
                WHERE contacts.id = {$this->contactId};";

        //file_put_contents(__ROOT__ . '/debugContacts.txt', var_export($sql, true));
        $result = $this->objDbConn->applyQuery($sql);
        $results[] = $result;
        if (!$result) {
            $errors[] = $this->objDbConn->getError();
            $this->contactId = 0;
        } else {
            $data = $this->objDbConn->getDataQuery($result);
            if (count($data)) {
                $data = $data[0];
            } else {
                $this->contactId = 0;
            }
        }

        if ($this->contactId) {
            $sql = "SELECT 
                        contactphones.id,
                        CONCAT(
                            IF(contactphones.countrycode IS NULL, '', '+'),
                            IFNULL(contactphones.countrycode, ''),
                            contactphones.number,
                            IF(contactphones.extension IS NULL, '', 'x'),
                            IFNULL(contactphones.extension, '')
                        ) AS `number`,
                        contactphones.defaultphone AS `default`,
                        labels.description AS `type`,
                        contacttypes.id AS contacttype_id
                    FROM contactphones
                        INNER JOIN contacttypes
                            ON contactphones.contacttype_id = contacttypes.id
                        LEFT JOIN (
                            SELECT
                                labels.name,
                                labeldetails.description
                            FROM labels
                                LEFT JOIN labeldetails
                                    ON labeldetails.label_id = labels.id
                            WHERE labeldetails.language_id = '{$this->languageId}'
                        ) labels
                            ON labels.name = contacttypes.label_name
                    WHERE contactphones.contact_id = {$this->contactId}
                    ORDER BY contactphones.defaultphone DESC, contactphones.id;";

            //file_put_contents(__ROOT__ . '/debugContacts.txt', var_export($sql, true));
            $result = $this->objDbConn->applyQuery($sql);
            $results[] = $result;
            if (!$result) {
                $errors[] = $this->objDbConn->getError();
            } else {
                $data['phones'] = $this->objDbConn->getDataQuery($result);
            }

            $sql = "SELECT 
                        contactemails.id,
                        contactemails.emailaddress AS address,
                        contactemails.defaultemail AS `default`,
                        labels.description AS type,
                        contacttypes.id AS contacttype_id
                    FROM contactemails
                        INNER JOIN contacttypes
                            ON contactemails.contacttype_id = contacttypes.id
                        LEFT JOIN (
                            SELECT 
                                labels.name,
                                labeldetails.description
                            FROM labels
                                LEFT JOIN labeldetails
                                    ON labeldetails.label_id = labels.id
                            WHERE labeldetails.language_id = '{$this->languageId}'
                        ) labels
                            ON labels.name = contacttypes.label_name
                    WHERE contactemails.contact_id = {$this->contactId}
                    ORDER BY contactemails.defaultemail DESC , contactemails.id;";

            //file_put_contents(__ROOT__ . '/debugContacts.txt', var_export($sql, true));
            $result = $this->objDbConn->applyQuery($sql);
            $results[] = $result;
            if (!$result) {
                $errors[] = $this->objDbConn->getError();
            } else {
                $data['emails'] = $this->objDbConn->getDataQuery($result);
            }

            $sql = "SELECT 
                        contactsocialmedia.id,
                        contactsocialmedia.name,
                        labels.description AS type,
                        socialmedia.icon,
                        socialmedia.id AS socialmediatype_id
                    FROM contactsocialmedia
                        INNER JOIN socialmedia
                            ON contactsocialmedia.socialmedia_id = socialmedia.id
                        LEFT JOIN (
                            SELECT 
                                labels.name,
                                labeldetails.description
                            FROM labels
                                LEFT JOIN labeldetails
                                    ON labeldetails.label_id = labels.id
                            WHERE labeldetails.language_id = '{$this->languageId}'
                        ) labels
                            ON labels.name = socialmedia.label_name
                    WHERE contactsocialmedia.contact_id = {$this->contactId}
                    ORDER BY contactsocialmedia.id;";

            //file_put_contents(__ROOT__ . '/debugContacts.txt', var_export($sql, true));
            $result = $this->objDbConn->applyQuery($sql);
            $results[] = $result;
            if (!$result) {
                $errors[] = $this->objDbConn->getError();
            } else {
                $data['socials'] = $this->objDbConn->getDataQuery($result);
            }

            $sql = "SELECT 
                        contactaddresses.id,
                        contactaddresses.addresslineone AS line1,
                        contactaddresses.addresslinetwo AS line2,
                        contactaddresses.zip,
                        contactaddresses.city,
                        contactaddresses.countries_id AS country_id,
                        countrydet.name AS country,
                        labels.description AS type,
                        contacttypes.id AS contacttype_id
                    FROM contactaddresses
                        INNER JOIN contacttypes
                            ON contactaddresses.contacttype_id = contacttypes.id
                        LEFT JOIN (
                            SELECT
                                labels.name,
                                labeldetails.description
                            FROM labels
                                LEFT JOIN labeldetails
                                    ON labeldetails.label_id = labels.id
                            WHERE labeldetails.language_id = '{$this->languageId}'
                        ) labels
                            ON labels.name = contacttypes.label_name
                        INNER JOIN countries
                            ON contactaddresses.countries_id = countries.id
                        LEFT JOIN (
                            SELECT 
                                countrydetails.country_id,
                                countrydetails.name
                            FROM countrydetails
                            WHERE countrydetails.language_id = '{$this->languageId}'
                        ) countrydet
                            ON countrydet.country_id = countries.id
                    WHERE contactaddresses.contact_id = {$this->contactId}
                    ORDER BY contactaddresses.id;";

            //file_put_contents(__ROOT__ . '/debugContacts.txt', var_export($sql, true));
            $result = $this->objDbConn->applyQuery($sql);
            $results[] = $result;
            if (!$result) {
                $errors[] = $this->objDbConn->getError();
                $data['addresses'] = array();
            } else {
                $data['addresses'] = $this->objDbConn->getDataQuery($result);
            }
        } else {
            //New contact
            $data['id'] = 0;
            $data['firstname'] = '';
            $data['lastname'] = '';
            $data['media_id'] = 0;
            $data['title'] = '';
            $data['language'] = 'ES';
            $data['companyname'] = '';
            $data['initials'] = '';
            $data['phones'] = array();
            $data['emails'] = array();
            $data['socials'] = array();
            $data['addresses'] = array();
        }

        $sql = "SELECT 
                    contacttypes.id,
                    COALESCE(labels.description, labelsen.description) AS name
                FROM contacttypes
                    LEFT JOIN (
                        SELECT 
                            labels.name,
                            labeldetails.description
                        FROM labels
                            LEFT JOIN labeldetails
                                ON labeldetails.label_id = labels.id
                        WHERE labeldetails.language_id = '{$this->languageId}'
                    ) labels
                        ON labels.name = contacttypes.label_name
                    LEFT JOIN (
                        SELECT
                            labels.name,
                            labeldetails.description
                        FROM labels
                            LEFT JOIN labeldetails
                                ON labeldetails.label_id = labels.id
                        WHERE labeldetails.language_id = 1
                    ) labelsen
                        ON labelsen.name = contacttypes.label_name
                ORDER BY contacttypes.position ,name;";

        //file_put_contents(__ROOT__ . '/debugContacts.txt', var_export($sql, true));
        $result = $this->objDbConn->applyQuery($sql);
        $results[] = $result;
        if (!$result) {
            $errors[] = $this->objDbConn->getError();
            $data['contacttypes'] = array();
        } else {
            $data['contacttypes'] = $this->objDbConn->getDataQuery($result);
        }

        $sql = "SELECT 
                    socialmedia.id,
                    COALESCE(labels.description, labelsen.description) AS name
                FROM socialmedia
                    LEFT JOIN (
                        SELECT 
                            labels.name,
                            labeldetails.description
                        FROM labels
                            LEFT JOIN labeldetails
                                ON labeldetails.label_id = labels.id
                        WHERE labeldetails.language_id = '{$this->languageId}'
                    ) labels
                        ON labels.name = socialmedia.label_name
                    LEFT JOIN (
                        SELECT
                            labels.name,
                            labeldetails.description
                        FROM labels
                            LEFT JOIN labeldetails
                                ON labeldetails.label_id = labels.id
                        WHERE labeldetails.language_id = 1
                    ) labelsen
                        ON labelsen.name = socialmedia.label_name
                ORDER BY name;";

        //file_put_contents(__ROOT__ . '/debugContacts.txt', var_export($sql, true));
        $result = $this->objDbConn->applyQuery($sql);
        $results[] = $result;
        if (!$result) {
            $errors[] = $this->objDbConn->getError();
            $data['socialmediatypes'] = array();
        } else {
            $data['socialmediatypes'] = $this->objDbConn->getDataQuery($result);
        }

        $sql = "SELECT 
                    countries.id,
                    COALESCE(countrydet.name, countrydeten.name) AS name
                FROM countries
                    LEFT JOIN (
                        SELECT 
                            countrydetails.country_id,
                            countrydetails.name
                        FROM countrydetails
                        WHERE countrydetails.language_id = '{$this->languageId}'
                    ) countrydet
                        ON countrydet.country_id = countries.id
                    LEFT JOIN (
                        SELECT 
                            countrydetails.country_id,
                            countrydetails.name
                        FROM countrydetails
                        WHERE countrydetails.language_id = 'en'
                    ) countrydeten
                        ON countrydeten.country_id = countries.id
                    LEFT JOIN (
                        SELECT 
                            contactaddresses.countries_id,
                            COUNT(contactaddresses.countries_id) AS count
                        FROM contactaddresses
                        GROUP BY contactaddresses.countries_id
                    ) countriesused
                        ON countriesused.countries_id = countries.id
                ORDER BY countriesused.count DESC, name;";

        //file_put_contents(__ROOT__ . '/debugContacts.txt', var_export($sql, true));
        $result = $this->objDbConn->applyQuery($sql);
        $results[] = $result;
        if (!$result) {
            $errors[] = $this->objDbConn->getError();
            $data['countries'] = array();
        } else {
            $data['countries'] = $this->objDbConn->getDataQuery($result);
        }

        return array('result' => !in_array(false, $results, true), 'error' => $errors, 'data' => $data, 'contactId' => $this->contactId);
    }

    public function selectContacts() {
        $result = false;
        $error = '';
        $sql = "SELECT DISTINCT
                    contacts.id,
                    contactpersons.firstname,
                    contactpersons.lastname,
                    GROUP_CONCAT(DISTINCT contactphones.number ORDER BY contactphones.number SEPARATOR '|') AS phonenumbers,
                    GROUP_CONCAT(DISTINCT contactemails.emailaddress ORDER BY contactemails.emailaddress SEPARATOR '|') AS emails,
                    contactcompanies.name AS company,
                    contactpersons.media_id AS mediaId,
                    CONCAT(SUBSTR(firstname, 1, 1), SUBSTR(lastname, 1, 1)) AS initials,
                    contacts.id IN (0) AS selected
                FROM contacts
                        LEFT JOIN contactpersons
                            ON contactpersons.contact_id = contacts.id
                        LEFT JOIN contactcompanies
                            ON contactcompanies.contact_id = contacts.id
                        LEFT JOIN contactemails
                            ON contactemails.contact_id = contacts.id
                        LEFT JOIN contactphones
                            ON contactphones.contact_id = contacts.id
                GROUP BY contacts.id , contactcompanies.name
                ORDER BY selected DESC,
                    COALESCE(
                        IF(contactpersons.firstname != '' OR contactpersons.lastname != '',
                            CONCAT(contactpersons.firstname, ' ', contactpersons.lastname),
                            NULL
                        ),
                        contactcompanies.name
                    );";

        //file_put_contents(__ROOT__ . '/debugContacts.txt', var_export($sql, true));
        $result = $this->objDbConn->applyQuery($sql);
        if (!$result) {
            $error = $this->objDbConn->getError();
        } else {
            $data = $this->objDbConn->getDataQuery($result);
        }

        return array('result' => $result, 'error' => $error, 'data' => $data);
    }

    public function createContact() {
        $this->objDbConn->applyQuery("call reset_ai('contacts');");
        $error = '';
        $sql = "INSERT INTO contacts (id) VALUES (NULL);";

        //file_put_contents(__ROOT__ . '/debugContacts.txt', var_export($sql, true));
        $this->objDbConn->resetAI('contacts');
        $result = $this->objDbConn->applyQuery($sql);
        if (!$result) {
            $this->contactId = 0;
            $error = $this->objDbConn->getError();
        } else {
            $this->contactId = $this->objDbConn->getLastId();
        }

        return array('result' => $result, 'error' => $error, 'contactId' => $this->contactId);
    }

    public function updatePerson() {
        $result = false;
        $error = '';

        $this->existsContact();
        if (!$this->contactId) {
            $this->createContact();
        }
        if ($this->contactId) {
            $sql = "INSERT INTO contactpersons (contact_id, firstname, lastname/*, media_id, title, language*/)
                    VALUES ({$this->contactId}, '{$this->firstname}', '{$this->lastname}')
                    ON DUPLICATE KEY UPDATE firstname = VALUES(firstname), lastname = VALUES(lastname);";

            //file_put_contents(__ROOT__ . '/debugContacts.txt', var_export($sql, true));
            $result = $this->objDbConn->applyQuery($sql);
            if (!$result) {
                $error = $this->objDbConn->getError();
            }
        }

        return array('result' => $result, 'error' => $error, 'contactId' => $this->contactId);
    }

    public function updateCompany() {
        $results = false;
        $error = '';
        if (!$this->existsContact()['contactId']) {
            $this->createContact();
        }
        if ($this->contactId) {
            $sql = "INSERT INTO contactcompanies (contact_id, name)
                    VALUES({$this->contactId}, '{$this->company}')
                    ON DUPLICATE KEY UPDATE name = VALUES(name);";

            //file_put_contents(__ROOT__ . '/debugContacts.txt', var_export($sql, true));
            $result = $this->objDbConn->applyQuery($sql);
            if (!$result) {
                $error = $this->objDbConn->getError();
            }
        }

        return array('result' => $result, 'error' => $error, 'contactId' => $this->contactId);
    }

    public function updatePhone() {
        $this->objDbConn->applyQuery("call reset_ai('contactphones');");
        $results = [];
        $errors = [];
        if (!$this->existsContact()['contactId']) {
            $this->createContact();
        }
        if ($this->contactId) {
            $sql = "INSERT INTO contactphones (id, contact_id, contacttype_id, countrycode, number, extension, defaultphone) VALUES
                    ({$this->phoneId}, {$this->contactId}, {$this->contacttypeId}, '{$this->countrycode}', '{$this->number}', {$this->extension}, {$this->default})
                    ON DUPLICATE KEY UPDATE
                        contacttype_id = VALUES(contacttype_id),
                        countrycode = VALUES(countrycode),
                        number = VALUES(number),
                        extension = VALUES(extension),
                        defaultphone = VALUES(defaultphone);";

            //file_put_contents(__ROOT__ . '/debugContacts.txt', var_export($sql, true));
            $result = $this->objDbConn->applyQuery($sql);
            $results[] = $result;
            if (!$result) {
                $error[] = $this->objDbConn->getError();
            } else {
                if (!$this->phoneId) {
                    $this->phoneId = $this->objDbConn->getLastId();
                }
                $sql = "UPDATE contactphones
                        SET defaultphone = 0
                        WHERE contact_id = {$this->contactId}
                            AND id != {$this->phoneId};";

                //file_put_contents(__ROOT__ . '/debugContacts.txt', var_export($sql, true));
                $result = $this->objDbConn->applyQuery($sql);
                $results[] = $result;
                if (!$result) {
                    $error[] = $this->objDbConn->getError();
                }
            }
        }

        return array('result' => !in_array(false, $results, true), 'error' => $errors, 'contactId' => $this->contactId, 'phoneId' => $this->phoneId);
    }

    public function updateEmail() {
        $this->objDbConn->applyQuery("call reset_ai('contactemails');");
        $results = [];
        $errors = [];
        if (!$this->existsContact()['contactId']) {
            $this->createContact();
        }
        if ($this->contactId) {
            $sql = "INSERT INTO contactemails (id, contact_id, contacttype_id, emailaddress, defaultemail) VALUES
            ({$this->emailId}, {$this->contactId}, {$this->contacttypeId}, '{$this->email}', {$this->default})
                    ON DUPLICATE KEY UPDATE
                        contacttype_id = VALUES(contacttype_id),
                        emailaddress = VALUES(emailaddress),
                        defaultemail = VALUES(defaultemail);";

            //file_put_contents(__ROOT__ . '/debugContacts.txt', var_export($sql, true));
            $result = $this->objDbConn->applyQuery($sql);
            $results[] = $result;
            if (!$result) {
                $error[] = $this->objDbConn->getError();
            } else {
                if (!$this->emailId) {
                    $this->emailId = $this->objDbConn->getLastId();
                }
                $sql = "UPDATE contactemails
                        SET defaultemail = 0
                        WHERE contact_id = {$this->contactId}
                            AND id != {$this->emailId};";

                $result = $this->objDbConn->applyQuery($sql);
                $results[] = $result;
                if (!$result) {
                    $error[] = $this->objDbConn->getError();
                }
            }
        }

        return array('result' => !in_array(false, $results, true), 'error' => $errors, 'contactId' => $this->contactId, 'emailId' => $this->emailId);
    }

    public function updateSocial() {
        $this->objDbConn->applyQuery("call reset_ai('contactsocialmedia');");
        $results = false;
        $error = '';
        if (!$this->existsContact()['contactId']) {
            $this->createContact();
        }
        if ($this->contactId) {
            $sql = "INSERT INTO contactsocialmedia (id, name, contact_id, socialmedia_id) VALUES
            ({$this->socialId}, '{$this->socialName}', {$this->contactId}, {$this->socialtypeId})
                    ON DUPLICATE KEY UPDATE
                        name = VALUES(name),
                        socialmedia_id = VALUES(socialmedia_id);";

            //file_put_contents(__ROOT__ . '/debugContacts.txt', var_export($sql, true));
            $result = $this->objDbConn->applyQuery($sql);
            if (!$result) {
                $error = $this->objDbConn->getError();
            } elseif (!$this->socialId) {
                $this->socialId = $this->objDbConn->getLastId();
            }
        }

        return array('result' => $result, 'error' => $error, 'contactId' => $this->contactId, 'socialId' => $this->socialId);
    }

    public function updateAddress() {
        $this->objDbConn->applyQuery("call reset_ai('contactaddresses');");
        $results = false;
        $error = '';
        if (!$this->existsContact()['contactId']) {
            $this->createContact();
        }
        if ($this->contactId) {
            $sql = "INSERT INTO contactaddresses (id, contact_id, contacttype_id, countries_id, addresslineone, addresslinetwo, zip, city) VALUES
                    ({$this->addressId}, {$this->contactId}, {$this->contacttypeId}, '{$this->addressCountry}', '{$this->addressLine1}', '{$this->addressLine2}', '{$this->addressZip}', '{$this->addressCity}')
                    ON DUPLICATE KEY UPDATE
                        addresslineone = VALUES (addresslineone),
                        addresslinetwo = VALUES (addresslinetwo),
                        zip = VALUES (zip),
                        city = VALUES (city);";

            //file_put_contents(__ROOT__ . '/debugContacts.txt', var_export($sql, true));
            $result = $this->objDbConn->applyQuery($sql);
            if (!$result) {
                $error = $this->objDbConn->getError();
            } elseif (!$this->addressId) {
                $this->addressId = $this->objDbConn->getLastId();
            }
        }

        return array('result' => $result, 'error' => $error, 'contactId' => $this->contactId, 'addressId' => $this->addressId);
    }

    public function deletePhone() {
        $results = false;
        $error = '';
        if (!$this->existsContact()['contactId']) {
            $this->createContact();
        }
        if ($this->contactId) {
            $sql = "DELETE FROM contactphones
                    WHERE id = {$this->phoneId}
                        AND contact_id = {$this->contactId};";

            //file_put_contents(__ROOT__ . '/debugContacts.txt', var_export($sql, true));
            $result = $this->objDbConn->applyQuery($sql);
            if (!$result) {
                $error = $this->objDbConn->getError();
            } elseif (!$this->phoneId) {
                $this->phoneId = $this->objDbConn->getLastId();
            }
        }

        return array('result' => $result, 'error' => $error, 'contactId' => $this->contactId, 'phoneId' => $this->phoneId);
    }

    public function deleteEmail() {
        $results = false;
        $error = '';
        if (!$this->existsContact()['contactId']) {
            $this->createContact();
        }
        if ($this->contactId) {
            $sql = "DELETE FROM contactemails
                    WHERE id = {$this->emailId}
                        AND contact_id = {$this->contactId};";

            //file_put_contents(__ROOT__ . '/debugContacts.txt', var_export($sql, true));
            $result = $this->objDbConn->applyQuery($sql);
            if (!$result) {
                $error = $this->objDbConn->getError();
            } elseif (!$this->emailId) {
                $this->emailId = $this->objDbConn->getLastId();
            }
        }

        return array('result' => $result, 'error' => $error, 'contactId' => $this->contactId, 'emailId' => $this->emailId);
    }

    public function deleteSocial() {
        $results = false;
        $error = '';
        if (!$this->existsContact()['contactId']) {
            $this->createContact();
        }
        if ($this->contactId) {
            $sql = "DELETE FROM contactsocialmedia
                    WHERE id = {$this->socialId}
                        AND contact_id = {$this->contactId};";

            //file_put_contents(__ROOT__ . '/debugContacts.txt', var_export($sql, true));
            $result = $this->objDbConn->applyQuery($sql);
            if (!$result) {
                $error = $this->objDbConn->getError();
            } elseif (!$this->socialId) {
                $this->socialId = $this->objDbConn->getLastId();
            }
        }

        return array('result' => $result, 'error' => $error, 'contactId' => $this->contactId, 'socialId' => $this->socialId);
    }

    public function deleteAddress() {
        $results = false;
        $error = '';
        if (!$this->existsContact()['contactId']) {
            $this->createContact();
        }
        if ($this->contactId) {
            $sql = "DELETE FROM contactaddresses
                    WHERE id = {$this->addressId}
                        AND contact_id = {$this->contactId};";

            //file_put_contents(__ROOT__ . '/debugContacts.txt', var_export($sql, true));
            $result = $this->objDbConn->applyQuery($sql);
            if (!$result) {
                $error = $this->objDbConn->getError();
            } elseif (!$this->addressId) {
                $this->addressId = $this->objDbConn->getLastId();
            }
        }

        return array('result' => $result, 'error' => $error, 'contactId' => $this->contactId, 'addressId' => $this->addressId);
    }

    public function deleteContact() {
        $results = false;
        $error = '';
        if ($this->contactId) {
            $sql = "DELETE FROM contacts
                    WHERE id = {$this->contactId};";

            //file_put_contents(__ROOT__ . '/debugContacts.txt', var_export($sql, true));
            $result = $this->objDbConn->applyQuery($sql);
            if (!$result) {
                $error = $this->objDbConn->getError();
            }
        }

        return array('result' => $result, 'error' => $error);
    }

    private function existsContact() {
        $error = '';
        $sql = "SELECT id FROM contacts WHERE id={$this->contactId};";

        //file_put_contents(__ROOT__ . '/debugContacts.txt', var_export($sql, true));
        $result = $this->objDbConn->applyQuery($sql);
        if (!$result) {
            $error = $this->objDbConn->getError();
            $this->contactId = 0;
        } else {
            $data = $this->objDbConn->getDataQuery($result);
            if (count($data)) {
                $this->contactId = $data[0]['id'];
            } else {
                $this->contactId = 0;
            }
        }

        return array('result' => $result, 'error' => $error, 'contactId' => $this->contactId);
    }

    public function setContactId($int) {
        if (is_numeric($int)) {
            $this->contactId = $int;
        } else {
            $this->contactId = 0;
        }
    }

    public function setFirstname($str) {
        if (is_string($str)) {
            $this->firstname = $this->objDbConn->mysqlRealEscape($str);
        } else {
            $this->firstname = '';
        }
    }

    public function setLastname($str) {
        if (is_string($str)) {
            $this->lastname = $this->objDbConn->mysqlRealEscape($str);
        } else {
            $this->lastname = '';
        }
    }

    public function setCompanyname($str) {
        if (is_string($str)) {
            $this->company = $this->objDbConn->mysqlRealEscape($str);
        } else {
            $this->company = '';
        }
    }

    public function setPhoneId($id) {
        if (is_numeric($id)) {
            $this->phoneId = $id;
        } else {
            $this->phoneId = 0;
        }
    }

    public function setContactTypeId($id) {
        if (is_numeric($id)) {
            $this->contacttypeId = $id;
        } else {
            $this->contacttypeId = 1;
        }
    }

    public function setDefault($id) {
        if (is_numeric($id)) {
            $this->default = $id;
        } else {
            $this->default = 0;
        }
    }

    public function setCountryCode($str) {
        if (is_string($str)) {
            $this->countrycode = $this->objDbConn->mysqlRealEscape($str);
        } else {
            $this->countrycode = '';
        }
    }

    public function setPhoneNumber($str) {
        if (is_string($str)) {
            $this->number = $this->objDbConn->mysqlRealEscape($str);
        } else {
            $this->number = '';
        }
    }

    public function setPhoneExtension($str) {
        if (is_string($str) && $str) {
            $this->extension = "'" . $this->objDbConn->mysqlRealEscape($str) . "'";
        } else {
            $this->extension = 'null';
        }
    }

    public function setEmailId($id) {
        if (is_numeric($id)) {
            $this->emailId = $id;
        } else {
            $this->emailId = 0;
        }
    }

    public function setEmailAddress($str) {
        if (is_string($str)) {
            $this->email = $this->objDbConn->mysqlRealEscape($str);
        } else {
            $this->email = '';
        }
    }

    public function setSocialId($id) {
        if (is_numeric($id)) {
            $this->socialId = $id;
        } else {
            $this->socialId = 0;
        }
    }

    public function setSocialName($str) {
        if (is_string($str)) {
            $this->socialName = $this->objDbConn->mysqlRealEscape($str);
        } else {
            $this->socialName = '';
        }
    }

    public function setSocialTypeId($id) {
        if (is_numeric($id)) {
            $this->socialtypeId = $id;
        } else {
            $this->socialtypeId = 0;
        }
    }

    public function setAddressId($id) {
        if (is_numeric($id)) {
            $this->addressId = $id;
        } else {
            $this->addressId = 0;
        }
    }

    public function setAddressLine1($str) {
        if (is_string($str)) {
            $this->addressLine1 = $this->objDbConn->mysqlRealEscape($str);
        } else {
            $this->addressLine1 = '';
        }
    }

    public function setAddressLine2($str) {
        if (is_string($str)) {
            $this->addressLine2 = $this->objDbConn->mysqlRealEscape($str);
        } else {
            $this->addressLine2 = '';
        }
    }

    public function setAddressZip($str) {
        if (is_string($str)) {
            $this->addressZip = $this->objDbConn->mysqlRealEscape($str);
        } else {
            $this->addressZip = '';
        }
    }

    public function setAddressCity($str) {
        if (is_string($str)) {
            $this->addressCity = $this->objDbConn->mysqlRealEscape($str);
        } else {
            $this->addressCity = '';
        }
    }

    public function setAddressCountry($str) {
        if (is_string($str)) {
            $this->addressCountry = $this->objDbConn->mysqlRealEscape($str);
        } else {
            $this->addressCountry = '';
        }
    }

    public function setLanguageId(string $str) {
            $this->languageId = $str;
    }
}

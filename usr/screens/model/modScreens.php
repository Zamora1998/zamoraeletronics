<?php
class tvScreens
{
    protected $objDbConn;

    // --- Clientes ---
    protected $clientId    = 0;
    protected $nombre      = '';
    protected $telefono    = '';
    protected $ubicacion   = '';
    protected $latitud     = null;
    protected $longitud    = null;

    // --- Marcas / Modelos ---
    protected $brandId     = 0;
    protected $brandNombre = '';
    protected $modelId     = 0;
    protected $modelNombre = '';
    protected $pantalla    = '';
    protected $pdfRuta     = '';

    // --- Partes ---
    protected $partId      = 0;
    protected $partNombre  = '';
    protected $partDesc    = '';
    protected $precioCrc   = 0;
    protected $stock       = 0;

    // --- Órdenes de trabajo ---
    protected $orderId        = 0;
    protected $modeloLibre    = '';
    protected $pantallaLibre  = '';
    protected $fallaReportada = '';
    protected $costoEstimado  = 0;
    protected $abonoInicial   = 0;
    protected $estado         = '';
    protected $tipoPago       = 'pendiente';
    protected $firmaRuta      = '';
    protected $fotoRecepcionRuta = '';
    protected $notas          = '';

    // --- Partes de una orden ---
    protected $orderPartId = 0;
    protected $cantidad    = 1;
    protected $precioUnit  = 0;

    public function __construct(&$objDbConn = null)
    {
        if ($objDbConn) {
            $this->objDbConn = $objDbConn;
        } else {
            require_once __ROOT__ . '/assets/php/libDbConn.php';
            $this->objDbConn = new dbConn();
        }
    }

    // =========================================================
    // CLIENTES
    // =========================================================

    public function selectClients()
    {
        $sql = "SELECT
                    c.id,
                    c.nombre,
                    c.telefono,
                    c.ubicacion,
                    c.latitud,
                    c.longitud,
                    c.created_at,
                    COUNT(wo.id) AS total_ordenes,
                    SUM(CASE WHEN wo.estado NOT IN ('entregado') THEN 1 ELSE 0 END) AS ordenes_activas
                FROM tv_clients c
                LEFT JOIN work_orders wo ON wo.client_id = c.id
                GROUP BY c.id
                ORDER BY c.nombre;";

        return $this->objDbConn->processQuery($sql);
    }

    public function selectClient()
    {
        $sql = "SELECT
                    id,
                    nombre,
                    telefono,
                    IFNULL(ubicacion, '') AS ubicacion,
                    latitud,
                    longitud,
                    created_at,
                    updated_at
                FROM tv_clients
                WHERE id = {$this->clientId};";

        $result = $this->objDbConn->processQuery($sql);

        if ($result['result'] && !empty($result['data'])) {
            $result['data'] = $result['data'][0];
        }

        return $result;
    }

    public function saveClient()
    {
        $lat = ($this->latitud  !== null) ? $this->latitud  : 'NULL';
        $lng = ($this->longitud !== null) ? $this->longitud : 'NULL';

        if ($this->clientId) {
            $sql = "UPDATE tv_clients SET
                        nombre    = '{$this->nombre}',
                        telefono  = '{$this->telefono}',
                        ubicacion = '{$this->ubicacion}',
                        latitud   = {$lat},
                        longitud  = {$lng}
                    WHERE id = {$this->clientId};";
        } else {
            $sql = "INSERT INTO tv_clients (nombre, telefono, ubicacion, latitud, longitud)
                    VALUES ('{$this->nombre}', '{$this->telefono}', '{$this->ubicacion}', {$lat}, {$lng});";
        }

        $result = $this->objDbConn->processQuery($sql, true);

        if ($result['result']) {
            if (!$this->clientId) {
                $this->clientId = $this->objDbConn->getLastId();
            }
            $result['data'] = ['clientId' => $this->clientId];
        }

        return $result;
    }

    public function deleteClient()
    {
        $sql = "DELETE FROM tv_clients WHERE id = {$this->clientId};";

        return $this->objDbConn->processQuery($sql);
    }

    // =========================================================
    // MARCAS
    // =========================================================

    public function selectBrands()
    {
        $sql = "SELECT id, nombre FROM tv_brands ORDER BY nombre;";

        return $this->objDbConn->processQuery($sql);
    }

    public function saveBrand()
    {
        if ($this->brandId) {
            $sql = "UPDATE tv_brands SET nombre = '{$this->brandNombre}' WHERE id = {$this->brandId};";
        } else {
            $sql = "INSERT INTO tv_brands (nombre) VALUES ('{$this->brandNombre}');";
        }

        $result = $this->objDbConn->processQuery($sql);

        if ($result['result']) {
            if (!$this->brandId) {
                $this->brandId = $this->objDbConn->getLastId();
            }
            $result['data'] = ['brandId' => $this->brandId];
        }

        return $result;
    }

    public function deleteBrand()
    {
        $sql = "DELETE FROM tv_brands WHERE id = {$this->brandId};";

        return $this->objDbConn->processQuery($sql);
    }

    // =========================================================
    // MODELOS
    // =========================================================

    public function selectModels()
    {
        $brandFilter = $this->brandId ? "WHERE m.brand_id = {$this->brandId}" : '';

        $sql = "SELECT
                    m.id,
                    m.brand_id,
                    b.nombre AS marca,
                    m.modelo,
                    IFNULL(m.pantalla, '') AS pantalla,
                    IFNULL(m.pdf_ruta, '') AS pdf_ruta
                FROM tv_models m
                INNER JOIN tv_brands b ON b.id = m.brand_id
                {$brandFilter}
                ORDER BY b.nombre, m.modelo;";

        return $this->objDbConn->processQuery($sql);
    }

    public function selectModel()
    {
        $sql = "SELECT
                    m.id,
                    m.brand_id,
                    b.nombre AS marca,
                    m.modelo,
                    IFNULL(m.pantalla, '') AS pantalla,
                    IFNULL(m.pdf_ruta, '') AS pdf_ruta
                FROM tv_models m
                INNER JOIN tv_brands b ON b.id = m.brand_id
                WHERE m.id = {$this->modelId};";

        $result = $this->objDbConn->processQuery($sql);

        if ($result['result'] && !empty($result['data'])) {
            $result['data'] = $result['data'][0];
        }

        return $result;
    }

    public function saveModel()
    {
        $pdfSql = $this->pdfRuta ? "'{$this->pdfRuta}'" : 'NULL';

        if ($this->modelId) {
            $sql = "UPDATE tv_models SET
                        brand_id = {$this->brandId},
                        modelo   = '{$this->modelNombre}',
                        pantalla = '{$this->pantalla}',
                        pdf_ruta = {$pdfSql}
                    WHERE id = {$this->modelId};";
        } else {
            $sql = "INSERT INTO tv_models (brand_id, modelo, pantalla, pdf_ruta)
                    VALUES ({$this->brandId}, '{$this->modelNombre}', '{$this->pantalla}', {$pdfSql});";
        }

        $result = $this->objDbConn->processQuery($sql);

        if ($result['result']) {
            if (!$this->modelId) {
                $this->modelId = $this->objDbConn->getLastId();
            }
            $result['data'] = ['modelId' => $this->modelId];
        }

        return $result;
    }

    public function deleteModel()
    {
        $sql = "DELETE FROM tv_models WHERE id = {$this->modelId};";

        return $this->objDbConn->processQuery($sql);
    }

    // =========================================================
    // PARTES / REPUESTOS
    // =========================================================

    public function selectParts()
    {
        $brandFilter = $this->brandId ? "WHERE p.brand_id = {$this->brandId}" : '';

        $sql = "SELECT
                    p.id,
                    p.brand_id,
                    IFNULL(b.nombre, 'Genérico') AS marca,
                    p.nombre,
                    IFNULL(p.descripcion, '') AS descripcion,
                    IFNULL(p.precio_crc, 0) AS precio_crc,
                    p.stock
                FROM tv_parts p
                LEFT JOIN tv_brands b ON b.id = p.brand_id
                {$brandFilter}
                ORDER BY p.nombre;";

        return $this->objDbConn->processQuery($sql);
    }

    public function savePart()
    {
        $brandSql = $this->brandId ? $this->brandId : 'NULL';
        $descSql  = $this->partDesc ? "'{$this->partDesc}'" : 'NULL';

        if ($this->partId) {
            $sql = "UPDATE tv_parts SET
                        brand_id    = {$brandSql},
                        nombre      = '{$this->partNombre}',
                        descripcion = {$descSql},
                        precio_crc  = {$this->precioCrc},
                        stock       = {$this->stock}
                    WHERE id = {$this->partId};";
        } else {
            $sql = "INSERT INTO tv_parts (brand_id, nombre, descripcion, precio_crc, stock)
                    VALUES ({$brandSql}, '{$this->partNombre}', {$descSql}, {$this->precioCrc}, {$this->stock});";
        }

        $result = $this->objDbConn->processQuery($sql);

        if ($result['result']) {
            if (!$this->partId) {
                $this->partId = $this->objDbConn->getLastId();
            }
            $result['data'] = ['partId' => $this->partId];
        }

        return $result;
    }

    public function deletePart()
    {
        $sql = "DELETE FROM tv_parts WHERE id = {$this->partId};";

        return $this->objDbConn->processQuery($sql);
    }

    // =========================================================
    // ÓRDENES DE TRABAJO
    // =========================================================

    public function selectOrders()
    {
        // Filtros opcionales
        $where = [];
        if ($this->clientId)     $where[] = "wo.client_id = {$this->clientId}";
        if ($this->estado !== '') $where[] = "wo.estado = '{$this->estado}'";
        $whereSql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT
                    wo.id,
                    wo.client_id,
                    c.nombre   AS cliente_nombre,
                    c.telefono AS cliente_telefono,
                    c.ubicacion AS cliente_ubicacion,
                    IFNULL(b.nombre, '') AS marca,
                    IFNULL(tm.modelo, wo.modelo_libre) AS modelo,
                    IFNULL(tm.pantalla, wo.pantalla_libre) AS pantalla,
                    wo.falla_reportada,
                    wo.costo_estimado,
                    wo.abono_inicial,
                    wo.estado,
                    wo.tipo_pago,
                    wo.firma_ruta,
                    wo.foto_recepcion_ruta,
                    wo.notas,
                    wo.created_at,
                    wo.updated_at
                FROM work_orders wo
                INNER JOIN tv_clients c  ON c.id = wo.client_id
                LEFT JOIN tv_brands  b  ON b.id = wo.brand_id
                LEFT JOIN tv_models  tm ON tm.id = wo.model_id
                {$whereSql}
                ORDER BY FIELD(wo.estado,'pendiente','en_reparacion','listo','entregado'), wo.id DESC;";

        return $this->objDbConn->processQuery($sql);
    }

    public function selectOrder()
    {
        $sql = "SELECT
                    wo.id,
                    wo.client_id,
                    c.nombre   AS cliente_nombre,
                    c.telefono AS cliente_telefono,
                    c.ubicacion AS cliente_ubicacion,
                    c.latitud,
                    c.longitud,
                    wo.brand_id,
                    IFNULL(b.nombre, '') AS marca,
                    wo.model_id,
                    IFNULL(tm.modelo, '') AS modelo_catalogo,
                    IFNULL(tm.pantalla, '') AS pantalla_catalogo,
                    IFNULL(tm.pdf_ruta, '') AS pdf_ruta,
                    IFNULL(wo.modelo_libre, '') AS modelo_libre,
                    IFNULL(wo.pantalla_libre, '') AS pantalla_libre,
                    wo.falla_reportada,
                    wo.costo_estimado,
                    wo.abono_inicial,
                    wo.estado,
                    wo.tipo_pago,
                    IFNULL(wo.firma_ruta, '') AS firma_ruta,
                    IFNULL(wo.foto_recepcion_ruta, '') AS foto_recepcion_ruta,
                    IFNULL(wo.notas, '') AS notas,
                    wo.created_at,
                    wo.updated_at
                FROM work_orders wo
                INNER JOIN tv_clients c  ON c.id = wo.client_id
                LEFT JOIN tv_brands  b  ON b.id = wo.brand_id
                LEFT JOIN tv_models  tm ON tm.id = wo.model_id
                WHERE wo.id = {$this->orderId};";

        $result = $this->objDbConn->processQuery($sql);

        if ($result['result'] && !empty($result['data'])) {
            $result['data'] = $result['data'][0];
            // Cargar partes de la orden
            if ($this->orderId) {
                $result['data']['partes'] = $this->selectOrderParts()['data'];
            }
        }

        return $result;
    }

    public function selectOrderParts()
    {
        $sql = "SELECT
                    wop.id,
                    wop.part_id,
                    p.nombre AS parte_nombre,
                    wop.cantidad,
                    wop.precio_unit,
                    (wop.cantidad * wop.precio_unit) AS subtotal
                FROM work_order_parts wop
                INNER JOIN tv_parts p ON p.id = wop.part_id
                WHERE wop.order_id = {$this->orderId}
                ORDER BY wop.id;";

        return $this->objDbConn->processQuery($sql);
    }

    public function saveOrder()
    {
        $brandSql    = $this->brandId      ? $this->brandId                      : 'NULL';
        $modelSql    = $this->modelId      ? $this->modelId                      : 'NULL';
        $modeloLib   = $this->modeloLibre   ? "'{$this->modeloLibre}'"            : 'NULL';
        $pantallaLib = $this->pantallaLibre ? "'{$this->pantallaLibre}'"          : 'NULL';
        $notasSql    = $this->notas         ? "'{$this->notas}'"                  : 'NULL';

        if ($this->orderId) {
            $sql = "UPDATE work_orders SET
                        client_id       = {$this->clientId},
                        brand_id        = {$brandSql},
                        model_id        = {$modelSql},
                        modelo_libre    = {$modeloLib},
                        pantalla_libre  = {$pantallaLib},
                        falla_reportada = '{$this->fallaReportada}',
                        costo_estimado  = {$this->costoEstimado},
                        abono_inicial   = {$this->abonoInicial},
                        estado          = '{$this->estado}',
                        tipo_pago       = '{$this->tipoPago}',
                        notas           = {$notasSql}
                    WHERE id = {$this->orderId};";
        } else {
            $sql = "INSERT INTO work_orders
                        (client_id, brand_id, model_id, modelo_libre, pantalla_libre,
                         falla_reportada, costo_estimado, abono_inicial, estado, tipo_pago, notas)
                    VALUES
                        ({$this->clientId}, {$brandSql}, {$modelSql}, {$modeloLib}, {$pantallaLib},
                         '{$this->fallaReportada}', {$this->costoEstimado}, {$this->abonoInicial},
                         '{$this->estado}', '{$this->tipoPago}', {$notasSql});";
        }

        $result = $this->objDbConn->processQuery($sql);

        if ($result['result']) {
            if (!$this->orderId) {
                $this->orderId = $this->objDbConn->getLastId();
            }
            $result['data'] = ['orderId' => $this->orderId];
        }

        return $result;
    }

    public function updateOrderStatus()
    {
        $sql = "UPDATE work_orders SET
                    estado    = '{$this->estado}',
                    tipo_pago = '{$this->tipoPago}'
                WHERE id = {$this->orderId};";

        $result = $this->objDbConn->processQuery($sql);
        if ($result['result']) {
            $result['data'] = ['orderId' => $this->orderId];
        }
        return $result;
    }

    /**
     * Guarda la ruta del archivo PNG de la firma en la BD.
     * El archivo ya debe estar guardado en disco antes de llamar esto.
     */
    public function saveFirmaRuta()
    {
        $sql = "UPDATE work_orders SET firma_ruta = '{$this->firmaRuta}' WHERE id = {$this->orderId};";

        $result = $this->objDbConn->processQuery($sql);
        if ($result['result']) {
            $result['data'] = ['orderId' => $this->orderId];
        }
        return $result;
    }

    /**
     * Guarda la ruta del archivo de la foto de recepción en la BD.
     * El archivo ya debe estar guardado en disco antes de llamar esto.
     */
    public function saveFotoRecepcionRuta()
    {
        $sql = "UPDATE work_orders SET foto_recepcion_ruta = '{$this->fotoRecepcionRuta}' WHERE id = {$this->orderId};";

        $result = $this->objDbConn->processQuery($sql);
        if ($result['result']) {
            $result['data'] = ['orderId' => $this->orderId];
        }
        return $result;
    }

    public function deleteOrder()
    {
        $sql = "DELETE FROM work_orders WHERE id = {$this->orderId};";

        return $this->objDbConn->processQuery($sql);
    }

    // --- Partes de la orden ---

    public function saveOrderPart()
    {
        if ($this->orderPartId) {
            $sql = "UPDATE work_order_parts SET
                        cantidad    = {$this->cantidad},
                        precio_unit = {$this->precioUnit}
                    WHERE id = {$this->orderPartId} AND order_id = {$this->orderId};";
        } else {
            $sql = "INSERT INTO work_order_parts (order_id, part_id, cantidad, precio_unit)
                    VALUES ({$this->orderId}, {$this->partId}, {$this->cantidad}, {$this->precioUnit});";
        }

        $result = $this->objDbConn->processQuery($sql);

        if ($result['result']) {
            if (!$this->orderPartId) {
                $this->orderPartId = $this->objDbConn->getLastId();
            }
            $result['data'] = ['orderPartId' => $this->orderPartId];
        }

        return $result;
    }

    public function deleteOrderPart()
    {
        $sql = "DELETE FROM work_order_parts WHERE id = {$this->orderPartId} AND order_id = {$this->orderId};";

        return $this->objDbConn->processQuery($sql);
    }

    // =========================================================
    // SETTERS
    // =========================================================

    public function setClientId($v)
    {
        $this->clientId    = is_numeric($v) ? (int)$v : 0;
    }
    public function setNombre($v)
    {
        $this->nombre      = $this->objDbConn->mysqlRealEscape((string)$v);
    }
    public function setTelefono($v)
    {
        $this->telefono    = $this->objDbConn->mysqlRealEscape((string)$v);
    }
    public function setUbicacion($v)
    {
        $this->ubicacion   = $this->objDbConn->mysqlRealEscape((string)$v);
    }
    public function setLatitud($v)
    {
        $this->latitud     = is_numeric($v) ? (float)$v : null;
    }
    public function setLongitud($v)
    {
        $this->longitud    = is_numeric($v) ? (float)$v : null;
    }

    public function setBrandId($v)
    {
        $this->brandId     = is_numeric($v) ? (int)$v : 0;
    }
    public function setBrandNombre($v)
    {
        $this->brandNombre = $this->objDbConn->mysqlRealEscape((string)$v);
    }
    public function setModelId($v)
    {
        $this->modelId     = is_numeric($v) ? (int)$v : 0;
    }
    public function setModelNombre($v)
    {
        $this->modelNombre = $this->objDbConn->mysqlRealEscape((string)$v);
    }
    public function setPantalla($v)
    {
        $this->pantalla    = $this->objDbConn->mysqlRealEscape((string)$v);
    }
    public function setPdfRuta($v)
    {
        $this->pdfRuta     = $this->objDbConn->mysqlRealEscape((string)$v);
    }

    public function setPartId($v)
    {
        $this->partId      = is_numeric($v) ? (int)$v : 0;
    }
    public function setPartNombre($v)
    {
        $this->partNombre  = $this->objDbConn->mysqlRealEscape((string)$v);
    }
    public function setPartDesc($v)
    {
        $this->partDesc    = $this->objDbConn->mysqlRealEscape((string)$v);
    }
    public function setPrecioCrc($v)
    {
        $this->precioCrc   = is_numeric($v) ? (float)$v : 0;
    }
    public function setStock($v)
    {
        $this->stock       = is_numeric($v) ? (int)$v : 0;
    }

    public function setOrderId($v)
    {
        $this->orderId        = is_numeric($v) ? (int)$v : 0;
    }
    public function setModeloLibre($v)
    {
        $this->modeloLibre    = $this->objDbConn->mysqlRealEscape((string)$v);
    }
    public function setPantallaLibre($v)
    {
        $this->pantallaLibre  = $this->objDbConn->mysqlRealEscape((string)$v);
    }
    public function setFallaReportada($v)
    {
        $this->fallaReportada = $this->objDbConn->mysqlRealEscape((string)$v);
    }
    public function setCostoEstimado($v)
    {
        $this->costoEstimado  = is_numeric($v) ? (float)$v : 0;
    }
    public function setAbonoInicial($v)
    {
        $this->abonoInicial   = is_numeric($v) ? (float)$v : 0;
    }
    public function setEstado($v)
    {
        $this->estado = $this->objDbConn->mysqlRealEscape((string)$v);
    }
    public function setTipoPago($v)
    {
        $allowed = ['sinpe', 'efectivo', 'mixto', 'pendiente'];
        $this->tipoPago = in_array($v, $allowed) ? $v : 'pendiente';
    }
    public function setFirmaRuta($v)
    {
        $this->firmaRuta      = $this->objDbConn->mysqlRealEscape((string)$v);
    }
    public function setFotoRecepcionRuta($v)
    {
        $this->fotoRecepcionRuta = $this->objDbConn->mysqlRealEscape((string)$v);
    }
    public function setNotas($v)
    {
        $this->notas          = $this->objDbConn->mysqlRealEscape((string)$v);
    }

    public function setOrderPartId($v)
    {
        $this->orderPartId = is_numeric($v) ? (int)$v : 0;
    }
    public function setCantidad($v)
    {
        $this->cantidad    = is_numeric($v) ? (int)$v : 1;
    }
    public function setPrecioUnit($v)
    {
        $this->precioUnit  = is_numeric($v) ? (float)$v : 0;
    }
}
